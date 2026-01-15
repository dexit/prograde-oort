<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function wcra_page_tabs( $current = 'wcra_new_api' ) {
    $tabs = array(
        'wcra_new_api'   => __( '<i class="fa fa-plus" aria-hidden="true"></i>&nbsp;New Api Secret', 'custom-wp-rest-api' ), 
        'wcra_api_list'  => __( '<i class="fa fa-user-secret" aria-hidden="true"></i>&nbsp;Secret List', 'custom-wp-rest-api' ),
        'wcra_api_settings'  => __( '<i class="fa fa-wrench" aria-hidden="true"></i>&nbsp;Settings', 'custom-wp-rest-api' ),
        'wcra_api_endpoints'  => __( '<i class="fa fa-list-alt" aria-hidden="true"></i>&nbsp;Endpoint URLs', 'custom-wp-rest-api' ),
        'wcra_api_log'  => __( '<i class="fa fa-history" aria-hidden="true"></i>&nbsp;Log', 'custom-wp-rest-api' ),
        'wcra_api_recent_activity'  => __( '<i class="fa fa-bell" aria-hidden="true"></i>&nbsp;Recent Activity', 'custom-wp-rest-api' ),
    );
    $html = '<h2 class="nav-tab-wrapper">';
    foreach( $tabs as $tab => $name ){
        $class = ( $tab == $current ) ? 'nav-tab-active' : '';
        $html .= '<a class="nav-tab navmar ' . esc_attr($class) . '" href="?page=wcra_settings&tab=' . $tab . '">' . $name . '</a>';
    }
    $html .= '</h2>';
    echo wp_kses_post($html);
}

// Code displayed before the tabs (outside)
// Tabs
$tab = ( ! empty( $_GET['tab'])  ) ?  $_GET['tab']  : 'wcra_new_api';
wcra_page_tabs( $tab );

if ( $tab == 'wcra_new_api' ) {
  require_once 'api_new.php';
}
else if($tab == 'wcra_api_list'){
   require_once 'api_list.php';
}
else if($tab == 'wcra_api_log'){
  require_once 'api_log_display.php';
  require_once 'api_log.php';
   
}
else if($tab == 'wcra_api_settings'){
   require_once 'wpr_api_settings.php';
}
else if($tab == 'wcra_api_endpoints'){
   require_once 'wpr_api_endpoints.php';
}
else if($tab == 'wcra_api_recent_activity'){
   require_once 'wpr_api_recent_activity.php';
}


?>
