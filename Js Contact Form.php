<?php
/*
Plugin Name: Js Contact Form
Plugin URI: https://cxrana.com
Description: Java Scripts Contact Form,with attachment support and Empty Form validation. At first go to plugin editor and (Js Contact Form.php)files and search "yourname@mail.com" then replace it with your own email address. Now create a new page at your WordPress site and insert this code [contact_form]
Author: Cx Rana
Version: 1.0
Author URI: https://cxrana.com
*/

function contact_form_markup() {

$form_action    = get_permalink();
$author_default = $_COOKIE['comment_author_'.COOKIEHASH];
$email_default  = $_COOKIE['comment_author_email_'.COOKIEHASH];

if ( ($_SESSION['contact_form_success']) ) {
$contact_form_success = '<p style="color: green">Thank your for Your Feedback.</p>';
unset($_SESSION['contact_form_success']);
}

$markup = <<<EOT

<div id="commentform">

	{$contact_form_success}
     
   <form onsubmit="return validateForm(this);" action="{$form_action}" method="post" enctype="multipart/form-data" style="text-align: left">
   
   <p><input type="text" name="author" id="author" value="{$author_default}" size="22" /> <label for="author">Your Name *</label></p>
   <p><input type="text" name="email" id="email" value="{$email_default}" size="22" /> <label for="email">E mail *</label></p>
   <p><input type="text" name="subject" id="subject" value="" size="22" /> <label for="subject">Subject *</label></p>
   <p><textarea name="message" id="message" cols="100%" rows="10">Please type here...</textarea></p>
   <p><label for="attachment"><strong>File/photos </strong></label> <input type="file" name="attachment" id="attachment" /></p>
   <p><input name="send" type="submit" id="send" value="Send" /></p>
   
   <input type="hidden" name="contact_form_submitted" value="1">
   
   </form>
   
</div>

EOT;

return $markup;

}

add_shortcode('contact_form', 'contact_form_markup');

function contact_form_process() {

session_start();

 if ( !isset($_POST['contact_form_submitted']) ) return;

 $author  = ( isset($_POST['author']) )  ? trim(strip_tags($_POST['author'])) : null;
 $email   = ( isset($_POST['email']) )   ? trim(strip_tags($_POST['email'])) : null;
 $subject = ( isset($_POST['subject']) ) ? trim(strip_tags($_POST['subject'])) : null;
 $message = ( isset($_POST['message']) ) ? trim(strip_tags($_POST['message'])) : null;

 if ( $author == '' ) wp_die('Error 1: Add your Name Please.'); 
 if ( !is_email($email) ) wp_die('Error 2: Type your Email Please.');
 if ( $subject == '' ) wp_die('Error 3: Add a Subject First.');
 
 //we will add e-mail sending support here soon
 
require_once ABSPATH . WPINC . '/class-phpmailer.php';
$mail_to_send = new PHPMailer();

$mail_to_send->FromName = $author;
$mail_to_send->From     = $email;
$mail_to_send->Subject  = $subject;
$mail_to_send->Body     = $message;

$mail_to_send->AddReplyTo($email);
$mail_to_send->AddAddress('yourname@mail.com'); //contact form destination e-mail

if ( !$_FILES['attachment']['error'] == 4 ) { //something was send
	
	if ( $_FILES['attachment']['error'] == 0 && is_uploaded_file($_FILES['attachment']['tmp_name']) )
	
		$mail_to_send->AddAttachment($_FILES['attachment']['tmp_name'], $_FILES['attachment']['name']);
	
	else 
		
		wp_die('Error: Somethimg was wrong(Please Try again later)');
		
}

if ( !$mail_to_send->Send() ) wp_die('Error : Mail Sending Failed,please check your contact form destination e-mail address,login dashboard>Go to plugin Editor>Js Contact Form.php and findout yourname@mail.com then replace it - Msg:Js Contact Form Developer : ' . $mail_to_send->ErrorInfo);

$_SESSION['contact_form_success'] = 1;

 
 header('Location: ' . $_SERVER['HTTP_REFERER']);
 exit();

} 

add_action('init', 'contact_form_process');

function contact_form_js() { ?>

<script type="text/javascript">
function validateForm(form) {

	var errors = '';
	var regexpEmail = /\w{1,}[@][\w\-]{1,}([.]([\w\-]{1,})){1,3}$/;
		
	if (!form.author.value) errors += "Warning 1 : Name box empty.\n";
	if (!regexpEmail.test(form.email.value)) errors += "Warning 2 : Email box empty.\n";
	if (!form.subject.value) errors += "Warning 3 : Subject.\n";

	if (errors != '') {
		alert(errors);
		return false;
	}
	
return true;
	
}
</script>

<?php }

add_action('wp_head', 'contact_form_js');

?>