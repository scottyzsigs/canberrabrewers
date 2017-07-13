<?php 
// include for db connectivity
require('/home/canber10/public_html/cbadmin/web_incs/web_db.inc');

// use the forum auth cookies to see if user is logged on

require('/home/canber10/public_html/cbadmin/web_incs/forum_auth.php');
$admin = false;
$debug = false;
if($user_id == '' || $user_id == '1')
{
	$returnpage = '/membership_files/membership_admin.php';
	require('/home/canber10/public_html/cbadmin/web_incs/forum_login_form.php');
}
else
{
	// use the lookup table to see if this is an admin
	$usql = 'SELECT * FROM cb_admins WHERE forum_id = '.$user_id;
	$u_result=$mysqli->query($usql);
	while ($urow = mysqli_fetch_array($u_result))
	{
		$admin = true;
	}
	
}
if($admin != true)
{
	echo '<h1>Access Denied</h2><p>Please contact webmaster@canberrabrewers.com.au for access.</p>';
}
else
{
?>
<html>
<head>
<script type='text/javascript' src='http://www.canberrabrewers.com.au/wp-includes/js/jquery/jquery.js?ver=1.11.3'></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
<style type="text/css">
body, h1, h2, h3 {font-family:Arial, "Helvetica Neue", Helvetica, sans-serif; }
p, td, div {font-family:Arial, "Helvetica Neue", Helvetica, sans-serif; font-size: 0.9em; }
.form-row {margin-bottom: 15px;}
.form-row label {margin-bottom:4px;}
.form-row input[type="text"] {min-width:40%;margin-right:10px;}
.form-help {font-size: 0.8em; color:gray;}
.required, .error {color:red;}
</style>
</head>
<body>
<h1>Canberra Brewers Member Admin</h1>
<ul>
<li><a href="/member_admin/?emailform">Send email to members</a></li>
</ul>
<?php
	// set up variables
	$action = !empty($_GET['action']) ? $_GET['action'] : ''; // was action sent?
	$formaction = !empty($_POST['formaction']) ? $_POST['formaction'] : ''; // was form action sent?
	$message = '';
	$sent_to = '';
	// do the actions for forms
	switch ($formaction) {
		case 'sendemail':
			$emailsubject = !empty($_POST['emailsubject']) ? $_POST['emailsubject'] : 'Message from Canberra Brewers';
			$msg = '<style type="text/css">body, h1, h2, h3, p, td, div  {font-family:Arial, "Helvetica Neue", Helvetica, sans-serif; font-size: 0.9em;}</style>';
			$msg .= !empty($_POST['emailmsg']) ? $_POST['emailmsg'] : '';
			$from = !empty($_POST['emailfrom']) ? $_POST['emailfrom'] : 'webmaster';
			$to = !empty($_POST['emailto']) ? $_POST['emailto'] : '';
			$mail_headers = "From: ". $from ."@canberrabrewers.com.au\r\n";
			$mail_headers .= "MIME-Version: 1.0\r\n";
			$mail_headers .= "Content-Type: text/html; charset=UTF-8\r\n";
			if($msg == '')
			{
				echo "You can't send an empty message";
			}
			else
			{
				$sent_to = 'Email sent to<br />';
				if($to != '')
				{
					mail($to,$emailsubject,$msg,$mail_headers);
					$sent_to .= $to;
				}
				else
				{
					// v1 limited to active members from the forum
					$sql = '';
					if($debug)
					{
						$sql = "SELECT user_email FROM forumv3_users WHERE username = 'ScottyT'";
					}
					else
					{
						$sql = 'SELECT user_email FROM forumv3_users WHERE user_type = 0 or user_type = 3';
					}
					//run the query
					$user_result=$mysqli->query($sql);
					while ($row = mysqli_fetch_array($user_result))
					{
						mail($row['user_email'],$emailsubject,$msg,$mail_headers);
						$sent_to .= $row['user_email'].'<br/>';
					}
				}
			}
			break;
		case 'updatemember':
			echo "update the member";
			break;
}
echo $sent_to;
if($debug) { echo 'debug mode on';}
?>

<script src="https://cdn.ckeditor.com/4.7.1/standard/ckeditor.js"></script>
<h2>Send email to members</h2>
<form method="post" novalidate="novalidate" id="emailform">
<input type="hidden" name="formaction" value="sendemail" />
<div class="form-row">
	<div class="form-label"><label for="emailsubject">Email subject</label> <span class="required">*</span></div>
	<input type="text" id="emailsubject" name="emailsubject" required />
</div>
<div class="form-row">
	<div class="form-label"><label for="emailfrom">Email from</label> <span class="required">*</span></div>
	<input type="text" id="emailfrom" name="emailfrom" value="webmaster" required />@canberrabrewers.com.au
</div>
<div class="form-row">
	<div class="form-label"><label for="emailto">Test email to</label></div>
	<input type="text" id="emailto" name="emailto" />
	<p class="form-help">Leave this blank to email all members otherwise use a single email address to test</p>
</div>
<div class="form-row">
<div class="form-label"><label for="emailmsg">Email body</label> <span class="required">*</span></div>
<textarea name="emailmsg" required><?php echo htmlentities($msg); ?></textarea>
<script>
	CKEDITOR.replace( 'emailmsg' );
</script>
</div>
<input type="submit" value="Send email to members" />
</form>
<script>
jQuery.noConflict();
jQuery(document).ready(function ($) {
	// validate the form
	$("#emailform").validate();
});
</script>
<?php
// end admin check and page
}
?>
</body>
</html>