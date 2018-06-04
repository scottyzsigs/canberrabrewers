<?php 
// include for db connectivity
require('/home/canber10/public_html/cbadmin/web_incs/web_db.inc');

// use the forum auth cookies to see if user is logged on

require('/home/canber10/public_html/cbadmin/web_incs/forum_auth.php');
$admin = false;
$admin_type = '';
$debug_mode = false;

if($debug_mode)
{
	error_reporting(E_ALL); 
	ini_set('display_errors', 1);
}
$access_msg = '<h1>Access Denied</h2><p>Please contact webmaster@canberrabrewers.com.au for access.</p>';
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
		$admin_type = $urow['admin_type'];
	}
	
}
if($admin != true)
{
	echo $access_msg;
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
.form-label {padding-bottom:4px;}
.form-row input[type="text"] {min-width:40%;margin-right:10px;}
.form-help {font-size: 0.8em; color:gray;}
.required, .error {color:red;}
table {width:80%;border:1px solid #ccc;}
td {padding:1rem;}
th {background: #ccc;width:20%;}
tr:nth-child(even) {background: #DAE4F0}
tr:nth-child(odd) {background: #FFF}
</style>
</head>
<body>
<h1>Canberra Brewers Member Admin</h1>
<ul>
<li><a href="membership_admin.php?action=emailform">Send email to members</a></li>
<?php
if($admin_type == 'members') {
?>
<li><a href="membership_admin.php?action=memberlist">Manage Membership</a></li>
<?php
}
?>
</ul>
<?php
	// set up variables
	$action = !empty($_GET['action']) ? $_GET['action'] : ''; // was action sent?
	$emailaction = !empty($_GET['emailaction']) ? $_GET['emailaction'] : ''; // was action sent?
	$formaction = !empty($_POST['formaction']) ? $_POST['formaction'] : ''; // was form action sent?
	$message = '';
	$sent_to = '';
	// do the actions for forms
	switch ($formaction) {
		case 'Send email to members':
			$emailsubject = !empty($_POST['emailsubject']) ? $_POST['emailsubject'] : 'Message from Canberra Brewers';
			$msg = '<style type="text/css">body, h1, h2, h3  {font-family:Arial, "Helvetica Neue", Helvetica, sans-serif; } p, td, div {font-family:Arial, "Helvetica Neue", Helvetica, sans-serif; }</style>';
			$msg .= !empty($_POST['emailmsg']) ? $_POST['emailmsg'] : '';
			$from = !empty($_POST['emailfrom']) ? $_POST['emailfrom'] : 'webmaster';
			$to = !empty($_POST['emailto']) ? $_POST['emailto'] : '';
			$mail_headers = "From: Canberra Brewers <". $from ."@canberrabrewers.com.au>\r\n";
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
					if($debug_mode)
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
			$action = 'emailform';
			break;
		case 'updatemember':
			echo "update the member";
			break;
}
switch ($action) {
	case 'emailform':
	
	if($sent_to != '')
{
echo '<p>'.$sent_to.'</p>';
}

if($emailaction == '')
{
	$mailfval = "webmaster"; // bug in mail can't set to this, talk to hosting
	$helpmsg = "You are sending to all active members, this function is for sending monthly reports or other important messages.";
}
else
{
	$year = !empty($_GET['year']) ? $_GET['year'] : '';
	$mailfval = "webmaster";
	$helpmsg = "You are sending to all unpaid members for the year ". $year .", this function is for sending reminders to members who have not paid membership fees.";
}
if($debug_mode) { $helpmsg .= '<br /><strong>debug mode on - do not use - contact webmaster@canberrabrewers.com.au</strong>';}
?>

<script src="https://cdn.ckeditor.com/4.7.1/standard/ckeditor.js"></script>
<h2>Send email to members</h2>
<p><?php echo $helpmsg ?></p>
<form method="post" novalidate="novalidate" id="emailform">
<div class="form-row">
	<div class="form-label"><label for="emailsubject">Email subject</label> <span class="required">*</span></div>
	<input type="text" id="emailsubject" name="emailsubject" required />
</div>
<div class="form-row">
	<div class="form-label"><label for="emailfrom">Email from</label> <span class="required">*</span></div>
	<input type="text" id="emailfrom" name="emailfrom" value="<?php echo $mailfval ?>" required />@canberrabrewers.com.au
</div>
<?php if($emailaction == '') { ?>
<div class="form-row">
	<div class="form-label"><label for="emailto">Test email to</label></div>
	<input type="text" id="emailto" name="emailto" />
	<p class="form-help">Leave this blank to email all members otherwise use a single email address to test</p>
</div>
<?php } ?>
<div class="form-row">
<div class="form-label"><label for="emailmsg">Email body</label> <span class="required">*</span></div>
<textarea name="emailmsg" required><?php echo htmlentities($msg); ?></textarea>
<script>
	CKEDITOR.replace( 'emailmsg' );
</script>
</div>
<?php if($emailaction == '') { ?>
<input type="submit" name="formaction" value="Send email to members" />
<?php } ?>
</form>
<script>
jQuery.noConflict();
jQuery(document).ready(function ($) {
	// validate the form
	$("#emailform").validate();
});
</script>
<?php
// end email form
	break;
	case 'memberlist':
	if($admin_type != 'members')
	{
		echo $access_msg;
	}
	else
	{
	$thisyear = date("Y");
	for ($x = $thisyear; $x >= 2017; $x--) {
    
	$qdate = $x .'-01-01 00:00:00';
	$qedate = $x .'-12-31 23:59:59';	
	$memberlistsql = "SELECT member_firstname, member_surname, member_email, cb_membership.member_id, transaction_code, transaction_date FROM cb_membership LEFT JOIN cb_membership_transactions ON cb_membership_transactions.member_id = cb_membership.member_id WHERE (DATE(`transaction_date`) BETWEEN '".$qdate."' AND '".$qedate."') ORDER BY member_firstname, member_surname"; 
	$member_result=$mysqli->query($memberlistsql);
	$num_rows = mysqli_num_rows($member_result);
//echo $memberlistsql;
	$tbl = "<table><thead><tr><th>Name</th><th>Email</th><th>Paid date</th><th>Transaction ID</th></tr></thead><tbody>%s</tbody></table>";
?>

<?php	
	while ($row = mysqli_fetch_array($member_result))
	{
		$members .= '<tr><td>'.$row['member_firstname'].' '. $row['member_surname']. '</td><td>'. $row['member_email'] .'</td><td>'. $row['transaction_date'] .'</td><td>'. $row['transaction_code'] .'</td></tr>';
	}
	$tbldata = sprintf($tbl,$members);
	echo '<h2>'.$x.' Paid Members ('.$num_rows.')</h2>'.$tbldata;
	$members = '';
	
	$memberlistsql = "SELECT member_firstname, member_surname, member_email, cb_membership.member_id FROM cb_membership LEFT JOIN forumv3_users ON username = member_forum_name WHERE (user_type = 0 or user_type = 3) AND NOT EXISTS (SELECT * FROM cb_membership_transactions WHERE cb_membership_transactions.member_id = cb_membership.member_id AND (DATE(`transaction_date`) BETWEEN '".$qdate."' AND '".$qedate."')) ORDER BY member_firstname, member_surname"; 
	//echo $memberlistsql;
	$member_result=$mysqli->query($memberlistsql);
	$num_rows = mysqli_num_rows($member_result);
	while ($row = mysqli_fetch_array($member_result))
	{
		$members .= '<tr><td>'.$row['member_firstname'].' '. $row['member_surname']. '</td><td>'. $row['member_email'] .'</td><td>Unpaid for '. $x .'</td><td></td></tr>';
	}
	$tbldata = sprintf($tbl,$members);
	echo '<h2>'.$x.' Unpaid Members ('.$num_rows.') | <a href="membership_admin.php?action=emailform&emailaction=members&year='.$x.'">Email unpaid '.$x.' members</a></h2>'.$tbldata;
	$members = '';	
	// end for
	}
	// end admin type check
	}
	break;

}


// end admin check and page
}
?>
</body>
</html>