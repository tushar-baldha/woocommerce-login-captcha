<?php
/*
 * Plugin Name:   Woocommerce login captcha
 * Plugin URI:    
 * Description:   Add captcha from woocommerce login form
 * Version:       1.0
 * Author:        David
 * Author URI:    
 */
 
add_action('admin_notices', 'my_plugin_admin_notices');
function my_plugin_admin_notices() {
	if (!class_exists('ReallySimpleCaptcha')) {
		echo "<div class='notice notice-warning is-dismissible'><p>Please install and active <a href='http://wordpress.org/extend/plugins/really-simple-captcha/' target='_blank'>Really Simple Captcha</a> plugin before activating Woocommerce Login Captch plugin.</p></div>";
	}
}
 
/* add captcha code in login page */
// define the woocommerce_login_form callback 
function david_woocommerce_login_form1(){
	
	if ( ( ! is_user_logged_in() ) && ( class_exists('ReallySimpleCaptcha') ) ) {
		// Instantiate the ReallySimpleCaptcha class, which will handle all of the heavy lifting
		$cbnet_rscc_captcha = new ReallySimpleCaptcha();
		// Generate random word and image prefix
		$cbnet_rscc_captcha_word = $cbnet_rscc_captcha->generate_random_word();
		$cbnet_rscc_captcha_prefix = mt_rand();
		// Generate CAPTCHA image
		$cbnet_rscc_captcha_image_name = $cbnet_rscc_captcha->generate_image($cbnet_rscc_captcha_prefix, $cbnet_rscc_captcha_word);
		// Define values for comment form CAPTCHA fields
		$cbnet_rscc_captcha_image_url =  get_bloginfo('wpurl') . '/wp-content/plugins/really-simple-captcha/tmp/';
		$cbnet_rscc_captcha_image_src = $cbnet_rscc_captcha_image_url . $cbnet_rscc_captcha_image_name;
		$cbnet_rscc_captcha_image_width = $cbnet_rscc_captcha->img_size[0];
		$cbnet_rscc_captcha_image_height = $cbnet_rscc_captcha->img_size[1];
		$cbnet_rscc_captcha_field_size = $cbnet_rscc_captcha->char_length;
		?>
		<style>.woocommerce .col2-set .col-1 .form-captcha input{ border: 1px solid #d3ced2; font-size: 13px; line-height: 18px;  padding: 9px;}
.woocommerce .form-captcha img{margin-top:7px;}</style>
		<p class="form-captcha"><img src="<?php echo $cbnet_rscc_captcha_image_src; ?>" alt="captcha" width="<?php echo $cbnet_rscc_captcha_image_width; ?>" height="<?php echo $cbnet_rscc_captcha_image_height; ?>" />
			<label for="captcha_code"><?php echo $cbnet_rscc_captcha_form_label; ?></label>
			<?php echo "<input type='text' class='input-text' name='comment_captcha_code' id='comment_captcha_code' size='$cbnet_rscc_captcha_field_size'/>"; ?>
			<input type="hidden" name="comment_captcha_prefix" id="comment_captcha_prefix" value="<?php echo $cbnet_rscc_captcha_prefix; ?>" />
			<p id="cbnet-rscc-captcha-verify">Please enter the CAPTCHA text</p>
		</p> <?php
	
	}
}
add_action('woocommerce_login_form', 'david_woocommerce_login_form1', 10, 0 );

// define the woocommerce_process_login_errors callback 
function filter_woocommerce_process_login_errors( $validation_error, $post_username, $post_password ) { 
    // make filter magic happen here... 
	if ( ( ! is_user_logged_in() ) && ( class_exists('ReallySimpleCaptcha') ) ) {
		$cbnet_rscc_captcha = new ReallySimpleCaptcha();
		// This variable holds the CAPTCHA image prefix, which corresponds to the correct answer
		$cbnet_rscc_captcha_prefix = $_POST['comment_captcha_prefix'];
		// This variable holds the CAPTCHA response, entered by the user
		$cbnet_rscc_captcha_code = $_POST['comment_captcha_code'];
		// Validate the CAPTCHA response
		$cbnet_rscc_captcha_correct = $cbnet_rscc_captcha->check( $cbnet_rscc_captcha_prefix, $cbnet_rscc_captcha_code );
		// If CAPTCHA validation fails (incorrect value entered in CAPTCHA field) mark comment as spam.
		if ( true != $cbnet_rscc_captcha_correct ) {
			$validation_error = new WP_Error( 'empty_captcha', '<strong>ERROR</strong>: Please retry CAPTCHA', 'wc-no-captcha');
		}
		// clean up the tmp directory
		$cbnet_rscc_captcha->remove($cbnet_rscc_captcha_prefix);
		$cbnet_rscc_captcha->cleanup();
	}
    return $validation_error; 
}
         
// add the filter 
add_filter( 'woocommerce_process_login_errors', 'filter_woocommerce_process_login_errors', 10, 3 ); 
