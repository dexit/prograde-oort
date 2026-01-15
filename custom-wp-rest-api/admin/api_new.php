<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$api_user = isset($_POST['api_user']) ? sanitize_text_field($_POST['api_user']) : '';
$api_email = isset($_POST['api_email']) ? sanitize_email($_POST['api_email']) : '';
include_once('header.php');
 ?>
  <div class="gsr_back_body">
<div class="wraparea">
<h2>Generate Api Secret</h2>
<form action="" method="post">
<table class="form-table">
  <tbody>

    <tr>
    <td> 
      <div class="form-group">
        <label for="api_user" class="control-label"></label>
        <input type="text" class="form-control" name="api_user" id="api_user" value="<?php echo esc_attr($api_user); ?>" placeholder="Full Name">
      </div>
    </td>
    </tr>

    <tr>
    <td> 
      <div class="form-group">
        <label for="api_email" class="control-label"></label>
        <input type="email" class="form-control" name="api_email" value="<?php echo esc_attr($api_email); ?>" placeholder="Email">
      </div>
    </td>
    </tr>

  </tbody>
</table>
<p class="submit"><input name="submit" id="submit_access" class="button button-primary" value="<?php esc_html_e('Save' , 'custom-wp-rest-api'); ?>" type="submit"></p>
</form>
</div>
</div>

<?php 
if(isset($_POST['submit'])){
  global $wpdb;
  $tab = $wpdb->prefix . WCRA_DB . 'api_base';
  unset($_POST['submit']);

  // Sanitize input
  $api_email = sanitize_email($_POST['api_email']);
  $api_user  = sanitize_text_field($_POST['api_user']);

  // Use wpdb::prepare to safely construct the SQL query
  $checkQ = $wpdb->prepare(
      "SELECT * FROM $tab WHERE Email = %s",
      $api_email
  );

  // Get the result
  $get = $wpdb->get_row($checkQ);

  //print_r($get);die;
  if(empty($api_user)){
     echo '<script>alert("Full Name required!");</script>';
  }else if(empty($api_email)){
    echo '<script>alert("Email required!");</script>';
  }else if(!empty($get)){
    echo '<script>alert("Email already in use");</script>';
  }else{
    $ApiSecret = wcra_api_key_gen();
    $data = array('Fullname' => $api_user , 'Email' => $api_email , 'ApiSecret' => $ApiSecret , 'CreatedAt' => gmdate('Y-m-d H:i:s'));
    $insert = $wpdb->insert($tab , $data );
    if($insert){
      $notification = "<strong>1</strong> Secret Key has been generated for <strong> $api_email</strong>";
        wcra_save_recent_activity(array('txt' => $notification ));
      echo '<script>alert("Secret Generated Successfully");</script>';
      print('<script>window.location.href="admin.php?page=wcra_api_list"</script>');
    }
  }

}

 ?>
