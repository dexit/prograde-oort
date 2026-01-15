<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * In this part you are going to define custom table list class,
 * that will display your database records in nice looking table
 * http://codex.wordpress.org/Class_Reference/WP_List_Table
 * http://wordpress.org/extend/plugins/custom-list-table-example/
 */

if (!class_exists('WP_List_Table')) {

    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * bc_booking_system_List_Table class that will display our custom table
 * records in nice table
 */

class WPR_Api_Log_Display extends WP_List_Table
{

    //[REQUIRED] You must declare constructor and give some basic params

    function __construct()
    {
        global $status, $page;
        parent::__construct(array(
        
            'singular' => 'project',
            'plural' => 'projects',
        ));
    }

    public function no_items() {
        esc_html_e( 'No Log captured yet!.' , 'custom-wp-rest-api');
    }


    /*
     * [REQUIRED] this is a default column renderer
     * @param $item - row (key, value array)
     * @param $column_name - string (key)
     * @return HTML
     */

    function column_default($item, $column_name)
    {
        global $wpdb;
        
        switch($column_name) {

            case 'user':
            //return '<i class="fa fa-star-o" aria-hidden="true"></i>';
            $from_details = unserialize($item['secret']);
            $_get_root_secret = wcra_get_root_secret();
            if(wcra_get_username(esc_attr($from_details['secret']))){
                $html = wcra_get_username(esc_attr($from_details['secret']));
            }else if( esc_attr($from_details['secret']) == $_get_root_secret){
                $html = 'Root';
            }else{
                $html = '<span>'.$from_details["msg"].'</span>';
            }
            // $user = _get_username($item['secret']) ? _get_username($item['secret']) : '<span style="color:red">Invalid Secret</span>';
             return $html;
            break;
            case 'connected_at' :
                $connectedAt = gmdate('j M, Y g:i A' , strtotime(esc_attr($item['connectedAt'])) );         
                return $connectedAt;

            break;

            case 'on_status':
                $human_time_diff = human_time_diff( strtotime(esc_attr($item['connectedAt'])), strtotime(gmdate('Y-m-d H;i:s')) ) . ' ago';
                return '<span style="color:green;">'.$human_time_diff.'</span>';
            break;


            case 'msg' :
                $response_delivered = json_decode(esc_attr($item['response_delivered']));
                return $response_delivered->response;
            break;

           

            case 'action_trig' :
            $from_details = unserialize($item['secret']);
            $_get_root_secret = wcra_get_root_secret();
            if(wcra_get_username(esc_attr($from_details['secret']))){
                $html = wcra_get_username(esc_attr($from_details['secret']));
            }else if( esc_attr($from_details['secret']) == $_get_root_secret){
                $html = 'Root';
            }else{
                $html = '<span style="color:red">Invalid Secret</span>';
            }
            $user = $html;
            $connectedAt = gmdate('j M, Y g:i A' , strtotime(esc_attr($item['connectedAt']) ));
            $response_delivered = json_decode($item['response_delivered']);
            $Response = $response_delivered->response;
            $url = '<div class="box"><a class="button" href="#popup_'.esc_attr($item["id"]).'">Details</a></div>';
            $from_details = unserialize($item['secret']);
            $html = '<div id="popup_'.esc_attr($item["id"]).'" class="overlay">
              <div class="popup">
                <h2>Log #'.esc_attr($item["id"]).'</h2>
                <a class="close" href="#">&times;</a>
                <div class="content">
                <p><strong>Source :</strong> '.$user.'</p>
                <p><strong>Api Secret :</strong> '.$from_details['secret'].'</p>
                <p><strong>Requested URI :</strong> '.esc_attr($item["requested_url"]).'</p>
                <p><strong>IP :</strong> '.esc_attr($item["Ip"]).'</p>
                <p><strong>Requested At :</strong> '.esc_attr($connectedAt).'</p>
                <p><strong>Response :</strong> '.esc_attr($Response).'</p>
                <p><strong>Response Data :</strong> '.esc_attr($item['response_delivered']).'</p>
                <p><strong>System Info :</strong> '.esc_attr($item["System_info"]).'</p>
                </div>
              </div>
            </div>';
                return $url.$html;
            break;
            
        
            default:
               return $item[$column_name];
        }        
    }



    /**
     * [OPTIONAL] this is example, how to render specific column
     * method name must be like this: "column_[column_name]"
     * @param $item - row (key, value array)
     * @return HTML
     */

    function column_id($item)
    {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &person=2
        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        $actions = array(
            'delete' => sprintf(
                '<a href="%s">%s</a>',
                esc_url(
                    add_query_arg(
                        array(
                            'tab'    => 'wcra_api_log',
                            'page'   => esc_attr($page),
                            'action' => 'delete',
                            'id'     => isset($item['id']) ? intval($item['id']) : 0,
                        )
                    )
                ),
                esc_html__('Delete', 'custom-wp-rest-api')
            ),
        );
        

        return sprintf('%s %s',

            $item['id'],
            $this->row_actions($actions)
        );

    }
    
    /**
     * [REQUIRED] this is how checkbox column renders
     * @param $item - row (key, value array)
     * @return HTML
     */

    function column_cb($item)
    {
        return sprintf(

            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    /**

     * [REQUIRED] This method return columns to display in table
     * you can skip columns that you do not want to show
     * like content, or description
     * @return array
     */

    function get_columns()
    {
        $columns = array(

           'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'id' => __('# ID', 'custom-wp-rest-api'),
            'user' => __('Requested From' , 'custom-wp-rest-api'),
            //'requested_url' => __('Requested URI'),
            'Ip' => __('From IP' , 'custom-wp-rest-api'),
            'connected_at' => __('Requested At' , 'custom-wp-rest-api'),
           // 'msg' => __('Message'),
            'on_status' => __('On' , 'custom-wp-rest-api'), 
            'action_trig' => __('Details' , 'custom-wp-rest-api')
        );

        return $columns;
    }




    /*
     * [OPTIONAL] This method return columns that may be used to sort table
     * all strings in array - is column names
     * notice that true on name column means that its default sort
     * @return array
     */

    function get_sortable_columns()
    {
        $sortable_columns = array(
        
            'timestamp' => array('timestamp', true),
        );

        return $sortable_columns;
    }
    
    
    //Use the "manage{$page}columnshidden" option, maintained by WordPress core:
    function get_hidden_columns(){
        
        $columns =  (array) get_user_option( 'managewcra_settingscolumnshidden' );
        return $columns;
    }

    /*
     * [OPTIONAL] Return array of bult actions if has any
     * @return array
     */

    function get_bulk_actions()
    {
        $actions = array(

            'delete' => 'Delete',
        );
        return $actions;
    }

    /*
     * [OPTIONAL] This method processes bulk actions
     * it can be outside of class
     * it can not use wp_redirect coz there is output already
     * in this example we are processing delete action
     * message about successful deletion will be shown on page in next part
     */

     function process_bulk_action() {
        global $wpdb;
    
        $table_name = $wpdb->prefix . WCRA_DB . 'api_log';
    
        if ('delete' === $this->current_action()) {
    
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
    
            // Sanitize and validate as array of integers
            if (!is_array($ids)) {
                $ids = array($ids); // force into array if single ID
            }
    
            // Filter to retain only numeric IDs
            $ids = array_filter($ids, function ($id) {
                return is_numeric($id) && intval($id) > 0;
            });
    
            if (!empty($ids)) {
                // Prepare placeholders for the IN clause
                $placeholders = implode(',', array_fill(0, count($ids), '%d'));
    
                // Prepare and execute count query
                $count_query = $wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_name WHERE id IN ($placeholders)",
                    ...$ids
                );
                $cnt = $wpdb->get_var($count_query);
    
                $_get_logged_user = wcra_get_logged_user();
                $notification = "<strong>$cnt</strong> Log entries have been deleted by <strong>{$_get_logged_user}</strong>";
                wcra_save_recent_activity(['txt' => $notification]);
    
                // Prepare and execute delete query
                $delete_query = $wpdb->prepare(
                    "DELETE FROM $table_name WHERE id IN ($placeholders)",
                    ...$ids
                );
                $wpdb->query($delete_query);
            }
        }
    }
    

    /**
     * [REQUIRED] This is the most important method
     * It will get rows from database and prepare them to be showed in table
     */

     function prepare_items()
     {
         global $wpdb, $current_user;
     
         // Secure the table name construction
         $table_name = esc_sql($wpdb->prefix . WCRA_DB . 'api_log');
     
         // Get per-page preference, fallback to 20
         $per_page = (int) get_user_meta($current_user->ID, 'messages_per_page', true);
         $per_page = $per_page > 0 ? $per_page : 20;
     
         // Table headers
         $columns = $this->get_columns();
         $hidden = $this->get_hidden_columns();
         $sortable = $this->get_sortable_columns();
         $sortable_keys = array_keys($sortable);
         $this->_column_headers = array($columns, $hidden, $sortable);
     
         // Process bulk actions (if any)
         $this->process_bulk_action();
     
         // Get total count for pagination
         $total_items = (int) $wpdb->get_var("SELECT COUNT(id) FROM {$table_name}");
     
         // Get current page, orderby, and order values
         $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
     
         $orderby = isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], $sortable_keys, true)
             ? sanitize_sql_orderby($_REQUEST['orderby'])
             : 'id';
     
         $order = isset($_REQUEST['order']) && in_array(strtolower($_REQUEST['order']), array('asc', 'desc'), true)
             ? strtoupper($_REQUEST['order'])
             : 'DESC';
     
         // Construct safe query
         $sql = "SELECT * FROM {$table_name} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
     
         // Run prepared query
         $this->items = $wpdb->get_results($wpdb->prepare($sql, $per_page, $paged), ARRAY_A);
     
         // Pagination config
         $this->set_pagination_args(array(
             'total_items' => $total_items,
             'per_page'    => $per_page,
             'total_pages' => ceil($total_items / $per_page),
         ));
     }
     
}

/*
 * List page handler
 * This function renders our custom table
 * Notice how we display message about successfull deletion
 * Actualy this is very easy, and you can add as many features
 * as you want.
 * Look into /wp-admin/includes/class-wp-*-list-table.php for examples
 */

function _api_log_render()
{


    global $wpdb;
    
    $table = new WPR_Api_Log_Display();

    $table->prepare_items();
    
    $message = '';

    if ('delete' === $table->current_action() && isset($_REQUEST['id']) && is_array($_REQUEST['id']) ) {

        $message = '<div class="updated below-h2" id="message"><p>' . sprintf('Log deleted: %d', count($_REQUEST['id']) ) . '</p></div>';
    }

    ?>

<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    
    <?php 
        if('edit' === $table->current_action()){
            
            
        } else if('view' === $table->current_action()){
            
            //require_once(BDR_PLUGIN_DIR.'/admin-templates/view-maintenance.php');
            
        }
        else{
    ?>
    
    <input type="hidden" class="date_day" value="<?php echo esc_attr(gmdate('d'));?>">
     <h2 class="main_head"><span><?php esc_html_e('Requests/Responses Log' , 'custom-wp-rest-api')?></span>
    </h2>
    <?php echo wp_kses_post($message); ?>
    
    <form id="api_log_tab" method="post" class="api_log">
        <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page'] );?>"/>
        <?php $table->display() ?>
    </form>
    <?php /*?><div class="loader_area" style="display:none;"><img src="<?php echo BDR_PLUGIN_URL .'/images/loader.gif'?>"></div><?php */?>
   

    <?php } ?>
    
    
</div>

<?php

}?>
