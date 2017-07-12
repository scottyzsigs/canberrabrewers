<?php 
// include for db connectivity
require('/home/canber10/public_html/cbadmin/web_incs/web_db.inc');

// use the forum auth cookies to see if user is logged on

require('/home/canber10/public_html/cbadmin/web_incs/forum_auth.php');
$admin = false;
$debug = true;
if($user_id == '' || $user_id == '1')
{
	$returnpage = '/membership_admin';
	require('/home/canber10/public_html/cbadmin/web_incs/forum_login_form.php');
}
else
{
	// use the lookup table to see if this is an admin
	// true for testing
	$admin = true;
}
if($admin != true)
{
	echo '<h1>Access Denied</h2><p>Please contact webmaster@canberrabrewers.com.au for access.</p>';
}
else
{
?>
<h1>Canberra Brewers Member Admin</h1>
<ul>
<li><a href="/member_admin/?emailform">Send email to members</a></li>
</ul>
<?php
	// set up variables
	$action = !empty($_GET['action']) ? $_GET['action'] : ''; // was action sent?
	$formaction = !empty($_POST['action']) ? $_POST['action'] : ''; // was form action sent?
	$mail_headers = "From: webmaster@canberrabrewers.com.au\r\n";
	
	// do the actions for forms
	switch ($formaction) {
		case 'sendemail':
			$emailsubject = !empty($_POST['emailsubject']) ? $_POST['emailsubject'] : 'Message from Canberra Brewers';
			$msg = !empty($_POST['emailmsg']) ? $_POST['emailmsg'] : '';
			$format = !empty($_POST['emailformat']) ? $_POST['emailformat'];
			if(msg == '')
			{
				echo "You can't send an empty message";
			}
			else
			{
				if($format == 'html')
				{
					$mail_headers .= 'MIME-Version: 1.0' . "\ r\n";
					$mail_headers .= ' Content-type: text/html; charset=iso-8859-1' . "\ r\n";
				}
				// v1 limited to active members from the forum
				$sql = 'SELECT user_email FROM forumv3_users WHERE user_type = 0 or user_type = 3';
				//run the query
				$user_result=$mysqli->query($sql);
				while ($row = mysqli_fetch_array($user_result))
				{
					//echo $row['email'];
					mail($row['email'],$emailsubject,$msg,$mail_headers);
					$sent_to .= $row['email']."<br/>";
				}
			}
			break;
		case 'updatemember':
			echo "update the member";
			break;
}
?>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
<script src="https://cdn.ckeditor.com/4.7.1/standard/ckeditor.js"></script>
<textarea name="editor1"></textarea>
<script>
	CKEDITOR.replace( 'editor1' );
</script>
<?php
// end admin check and page
}
?>