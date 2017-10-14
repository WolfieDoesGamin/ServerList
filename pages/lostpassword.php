<?php
/*
	This script was created by Grohs Fabian
	The official link for this script is: https://github.com/grohsfabian/minecraft-servers-list-lite
	Check out my Twitter @grohsfabian | My website http://grohsfabian.com
	Portfolio: http://codecanyon.net/user/grohsfabian
 */

User::logged_in_redirect();
include 'core/functions/recaptchalib.php';


if(!empty($_POST)) {
	/* Clean the posted variable */
	$_POST['email'] = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

	/* Define the captcha variable */
	$captcha = recaptcha_check_answer ($settings->private_key, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

	/* Check for any errors */
	if(!User::x_exists('email', $_POST['email'])) {
		$_SESSION['error'][] = $language['errors']['email_doesnt_exist'];
	}
	if(!$captcha->is_valid) {
		$_SESSION['error'][] = $language['errors']['captcha_not_valid'];
	}

	/* If there are no errors, resend the activation link */
	if(empty($_SESSION['error'])) {
		/* Define some variables */
		$user_id 			= User::x_to_y('email', 'user_id', $_POST['email']);
		$lost_password_code = md5($_POST['email'] + microtime());

		/* Update the current activation email */
		$database->query("UPDATE `users` SET `lost_password_code` = '{$lost_password_code}' WHERE `user_id` = {$user_id}");

		/* Send the email */
		sendmail($_POST['email'],  $settings->contact_email, $language['misc']['lost_password'], sprintf($language['misc']['lost_password_email'], $settings->url, $_POST['email'], $lost_password_code));
		//printf($language['misc']['lost_password_email'], $settings->url, $_POST['email'], $lost_password_code);

		/* Set success message */
		$_SESSION['success'][] = $language['messages']['lostpassword'];
	}

	display_notifications();

}



?>

<h4><?php echo $language['headers']['lostpassword']; ?></h4>

<form action="" method="post" role="form">
	<div class="form-group">
		<label><?php echo $language['forms']['email']; ?></label>
		<input type="text" name="email" class="form-control" />
	</div>

	<div class="form-group">
		  <?php echo recaptcha_get_html($settings->public_key, null, $settings->is_secure); ?>
	</div>

	<div class="form-group">
		<button type="submit" name="submit" class="btn btn-primary"><?php echo $language['forms']['submit']; ?></button><br /><br />
	</div>

</form>
