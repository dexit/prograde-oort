<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $wpdb;
include_once('header.php');

$tab = $wpdb->prefix . WCRA_DB . 'api_endpoints';

if (isset($_GET['a'], $_GET['id'])) {
    $id = intval($_GET['id']);

    if ($id > 0) {
        $_get_base_by_id = wcra_get_base_by_id($id);

        // Manually prepare the DELETE query
        $query = $wpdb->prepare("DELETE FROM $tab WHERE id = %d", $id);
        $deleted = $wpdb->query($query); // Run the prepared query

        if ($deleted) {
            $notification = "<strong>1</strong> Base has been deleted - <strong>{$_get_base_by_id}</strong>";
            wcra_save_recent_activity(['txt' => $notification]);
            echo '<script>window.location.href="admin.php?page=wcra_api_endpoints"</script>';
            exit;
        }
    }
}


if(isset($_POST['wpr_save_end_settings'])){
    if(empty($_POST['wpr_set_base'])){
      echo '<script>alert("Base required!");</script>';
    }else if(empty($_POST['wpr_sec_set'])){
      echo '<script>alert("Secret required!");</script>';
    }else{
      $wpr_set_base = sanitize_text_field($_POST['wpr_set_base']);

      $q = $wpdb->prepare(
          "SELECT * FROM $tab WHERE base = %s",
          $wpr_set_base
      );

      $get = $wpdb->get_row($q);

      if(!empty($get)){
        echo '<script>alert("Base already exists!");</script>';
      }else{ 
        $wpr_get_params = array();
        if(isset($_POST['wpr_params'])){
          $wpr_params = $_POST['wpr_params'];
          if(is_array($wpr_params)){
            foreach ($wpr_params as  $value) {
              if(!empty($value)){
                $wpr_get_params[] = sanitize_text_field($value);
              }
            }
          }
        }
        
        
        $mt_rand = wp_rand(100, 10000);
        $callback = WCRA_DB . $wpr_set_base . '_callback';
        $permission_callback = WCRA_DB . $wpr_set_base . '_permission_callback';

        $basedata = array('callback' => $callback);
        $param_serialized = serialize($wpr_get_params);
        $basedata_serialized = serialize($basedata);
        $secret = sanitize_text_field($_POST['wpr_sec_set']);

        // Prepare the insert query manually
        $insert_query = $wpdb->prepare(
            "INSERT INTO $tab (base, basedata, param, secret) VALUES (%s, %s, %s, %s)",
            $wpr_set_base,
            $basedata_serialized,
            $param_serialized,
            $secret
        );

        // Execute the query
        $insert = $wpdb->query($insert_query);

        $notification = "<strong>1</strong> New base has been created - <strong>$wpr_set_base</strong>";
        wcra_save_recent_activity(array('txt' => $notification ));
                
      }
    }

    
}
$wpr_set_base = isset($_POST['wpr_set_base']) ? sanitize_text_field($_POST['wpr_set_base']) : '';


 ?>
  <div class="gsr_back_body">
 <div class="wraparea">
<h2><?php esc_html_e('New Endpoint URL' , 'custom-wp-rest-api'); ?></h2>

<form action="" method="post">
<table class="form-table" id="wpr_edpts_tab">
  <tr>
    <th class="row"><?php esc_html_e('Base' , 'custom-wp-rest-api'); ?> </th>
    <td><input type="text" name="wpr_set_base" value="<?php echo esc_attr($wpr_set_base); ?>" >
    </td>
  </tr>
  <tr>
    <th class="row"><?php esc_html_e('Select Secret' , 'custom-wp-rest-api'); ?> </th>
    <td>
    <div class="form-group">
      <label for="wpr_sec_set" class="control-label"></label>
      <select class="form-control" name="wpr_sec_set">
       <?php $_secret_list = wcra_secret_list();
      $_get_root_secret = wcra_get_root_secret();
        if(is_array($_secret_list) && count($_secret_list)){
          echo '<option value="">Select</option>';
          foreach ($_secret_list as $key => $value) {
            echo '<option value="'.esc_attr($key).'">'.esc_attr($value).'</option>';
          }
         // echo '<option value="'.$_get_root_secret.'">Root</option>';
        }
       ?>
    </select>
   
    <div class="customtooltip"><i class="fa fa-info-circle" aria-hidden="true"></i>
    <span class="customtooltiptext">You can create your own secret <a href='?page=wcra_new_api'>here</a></span>
  </div>
    
    
    </div>
    </td>
  </tr>
</table>
<p class="submit"><input name="wpr_save_end_settings" id="submit" class="button button-primary" value="<?php esc_html_e('Add New' , 'custom-wp-rest-api'); ?>" type="submit"></p>
</form>

<h3><?php esc_html_e('Custom Endpoint/Routes URLs' , 'custom-wp-rest-api'); ?></h3>
<table class="wp-list-table widefat fixed striped posts" id="">
<thead><tr><td width="5%"><?php esc_html_e('#ID' , 'custom-wp-rest-api'); ?></td><td width="25"><?php esc_html_e('Secret Used' , 'custom-wp-rest-api'); ?></td><td width="50%"><?php esc_html_e('Endpoint' , 'custom-wp-rest-api'); ?></td><td width="10%"><?php esc_html_e('Filter Hook' , 'custom-wp-rest-api'); ?></td><td width="10%"><?php esc_html_e('Action' , 'custom-wp-rest-api'); ?></td></tr></thead>
<?php 
$get_endspts = wcra_endpoints_data();

if(is_array($get_endspts) && count($get_endspts)){
  foreach ($get_endspts as $key => $value) {
    $params = unserialize($value->param);
    if (!is_array($params)) {
      $params = array();
    }
    $params['secret_key'] = $value->secret;
    $callback = unserialize($value->basedata);
    
    $html = '<div id="popup_endp_'.$value->id.'" class="overlay popup_endp">
              <div class="popup">
                <h3>Put the below code snippet in your functions.php or any function page</h3>
                <a class="close" href="#">&times;</a>
                <p class="content codesnip" id="codesnipid_'.$value->id.'" style="overflow:hidden;">
                '.wcra_help_content($callback["callback"]).'
                </p>
                <a href="javascript:;" class="btn btn-info" onclick="copyToClipboard(\'#codesnipid_'.$value->id.'\')">Copy To Clipboard</a>
              </div>
            </div>';
  ?>
  <tr>
    <td><?php echo esc_attr($value->id); ?></td>
    <td><?php echo wcra_get_username($value->secret) ? esc_attr(wcra_get_username($value->secret)): 'Root'; ?></td>
    <td><a href="<?php echo esc_url_raw(wcra_get_end_url($value->base , $params)); ?>" target="_blank"><?php echo esc_url_raw(wcra_get_end_url($value->base , $params)); ?></a></td>
    <td><a class="page-title-action" href="#popup_endp_<?php echo esc_attr($value->id);?>"><i class="fa fa-info-circle" aria-hidden="true"></i>Show Me</a><?php  echo wp_kses_post($html);?></td>
    <td>
      <?php if($value->base != 'wcra_test'){ ?>
        <a class="_delete_endpoints btn btn-info" href="<?php 
    echo esc_url( add_query_arg(
        array(
            'a'  => 1,
            'id' => $value->id,
        ),
        admin_url('admin.php?page=wcra_api_endpoints')
    ) );
?>">
    Delete
</a>

   <?php } ?>
   </td>
   
  </tr>
  <?php
}

}else{
  echo '<tr><td colspan="5">no endpoints recorded!</td></tr>';

}?>
<tfoot><tr><td><?php esc_html_e('#ID' ,'custom-wp-rest-api'); ?></td><td></td><td><?php esc_html_e('Endpoint' , 'custom-wp-rest-api'); ?></td><td><?php esc_html_e('Filter Hook' , 'custom-wp-rest-api'); ?></td><td><?php esc_html_e('Action' , 'custom-wp-rest-api'); ?></td></tr></tfoot>
</table>
</div>
</div>
<script type="text/javascript">
  function copyToClipboard(element) {
        var $temp = jQuery("<input>");
        jQuery("body").append($temp);
        $temp.val(jQuery(element).text()).select();
        document.execCommand("copy");
        $temp.remove();
        alert('Copied to clipboard!');
      }
</script>
