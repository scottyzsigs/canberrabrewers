<?php 
// include for db connectivity
require('/home/canber10/public_html/cbadmin/web_incs/web_db.inc');
 
// use the forum auth cookies to see if user is logged on

require('/home/canber10/public_html/cbadmin/web_incs/forum_auth.php');
$admin = false;
$admin_type = '';
$debug_mode = true;
$thisyear = date("Y");
$thismonth = date("F");
$formsubject = '';
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
<li><a href="membership_admin.php?action=emailform&emailaction=unpaidmembers">Send email to unpaid members for <?php echo $thisyear ?></a></li>
<li><a href="membership_admin.php?action=memberlist">Manage Membership</a></li>
<?php
}
?>
</ul>
<?php
	// set up variables
	// user_type = 1 = inactive 0 = active; user_inactive_reason = 3, 0 = active
	// admin user admin1 cbw3b!!
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

					if($emailaction == '')
					{
						$sql = 'SELECT user_email FROM forumv3_users WHERE user_type = 0 or user_type = 3';
					}
					else
					{
						$qdate = $thisyear .'-01-01 00:00:00';
						$qedate = $thisyear .'-12-31 23:59:59';	
						$sql = "SELECT member_firstname, member_surname, member_email, user_email, cb_membership.member_id FROM cb_membership INNER JOIN forumv3_users ON username = member_forum_name WHERE (user_type = 0 or user_type = 3) AND NOT EXISTS (SELECT * FROM cb_membership_transactions WHERE cb_membership_transactions.member_id = cb_membership.member_id AND (DATE(`transaction_date`) BETWEEN '".$qdate."' AND '".$qedate."')) ORDER BY member_firstname, member_surname"; 
					}
					//run the query
					$user_result=$mysqli->query($sql);
					$num_rows = mysqli_num_rows($user_result);
					$sent_to .= $num_rows.' members mailed.<br/>';
					while ($row = mysqli_fetch_array($user_result))
					{
						if($emailaction != '')
						{
							if(!$debug_mode)
							{
								$mf_name = $row['member_firstname'];
							}
							else
							{
								$mf_name = "Scott";
							}
							$msg = str_replace("[member_firstname]", $mf_name, $msg);
						}
						// FIX FOR PROD ***************
						if($row['user_email'] != '')
						{
							if(!$debug_mode)
							{
								mail($row['user_email'],$emailsubject,$msg,$mail_headers);
							}
							$sent_to .= $row['user_email'].'<br/>';
						}
					}
					if($debug_mode)
					{
						mail("webmaster@canberrabrewers.com.au",$emailsubject,$msg,$mail_headers);
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
	$formsubject = 'Canberra Brewers '.$thismonth.' '.$thisyear.' Meeting Preview and other notables';
	
}
else
{
	$mailfval = "webmaster";
	$helpmsg = "You are sending to all unpaid members for the year ". $thisyear .", this function is for sending reminders to members who have not paid membership fees.";
	if($msg == '')
	{
		//should go in a text file
		$msg = "<h1 style='0.6rem;'>Canberra Brewers Membership renewal reminder</h1><p>Hi [member_firstname],</p><p>Our membership database indicates that you have not yet paid your membership for this year. We're sending a reminder because we'd love to see you <a title='Canberra Brewers Membership' href='http://www.canberrabrewers.com.au/membership/'>renew your membership</a>, and we'd hate you to miss out on your continued membership benefits such as:</p><ul><li>access to the Canberra Brewers <a title='Canberra Brewers Forum' href='http://www.canberrabrewers.com.au/forum/' target='_blank'>forum</a> and <a title='Canberra Brewers Wiki' href='http://www.canberrabrewers.com.au/wiki/'>wiki</a></li><li>opportunity to participate in bulk buys of grain, yeast and other brewing equipment</li><li>access to discounts with local retailers</li><li>invitations to brew days with professional brewers</li><li>access to club <a title='Competitions' href='http://www.canberrabrewers.com.au/competitions'>competitions</a></li></ul><p>Thanks for being a member of Canberra Brewers and we hope to see you again this year.</p><h2 style='0.6rem;'><a title='Canberra Brewers Membership' href='http://www.canberrabrewers.com.au/membership/'>Renew my membership</a></h2><p>Canberra Brewers Committee</p>";
	}
	$formsubject = 'Canberra Brewers Membership renewal reminder - '.$thisyear;
}
if($debug_mode) { $helpmsg .= '<br /><strong>debug mode on - do not use - contact webmaster@canberrabrewers.com.au</strong>';}
?>

<script src="https://cdn.ckeditor.com/4.7.1/standard/ckeditor.js"></script>
<h2>Send email to members</h2>
<p><?php echo $helpmsg ?></p>
<form method="post" novalidate="novalidate" id="emailform">
<div class="form-row">
	<div class="form-label"><label for="emailsubject">Email subject</label> <span class="required">*</span></div>
	<input type="text" id="emailsubject" name="emailsubject" value="<?php echo $formsubject ?>" required />
</div>
<div class="form-row">
	<div class="form-label"><label for="emailfrom">Email from</label> <span class="required">*</span></div>
	<input type="text" id="emailfrom" name="emailfrom" value="<?php echo $mailfval ?>" required />@canberrabrewers.com.au
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
<input type="submit" name="formaction" value="Send email to members" />

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
	
	for ($x = $thisyear; $x >= 2017; $x--) {
    
	$qdate = $x .'-01-01 00:00:00';
	$qedate = $x .'-12-31 23:59:59';	
	$members = '';
	$memberlistsql = "SELECT member_firstname, member_surname, member_email, member_forum_name, cb_membership.member_id, transaction_code, transaction_date, username FROM cb_membership LEFT JOIN cb_membership_transactions ON cb_membership_transactions.member_id = cb_membership.member_id INNER JOIN forumv3_users ON username = member_forum_name WHERE (DATE(`transaction_date`) BETWEEN '".$qdate."' AND '".$qedate."') ORDER BY member_firstname, member_surname"; 
	$member_result=$mysqli->query($memberlistsql);
	$num_rows = mysqli_num_rows($member_result);
//echo $memberlistsql;
	$tbl = "<table><thead><tr><th>Name</th><th>Email</th><th>Paid date</th><th>Transaction ID</th></tr></thead><tbody>%s</tbody></table>";
?>

<?php	
	while ($row = mysqli_fetch_array($member_result))
	{
		$members .= '<tr><td>'.$row['member_firstname'].' '. $row['member_surname'].' <span title="Member ID: '.$row['member_id'].' Actual forum name: '.$row['username'].'">('.$row['member_forum_name'].')</span></td><td>'. $row['member_email'] .'</td><td>'. $row['transaction_date'] .'</td><td>'. $row['transaction_code'] .'</td></tr>';
	}
	$tbldata = sprintf($tbl,$members);
	echo '<h2>'.$x.' Paid Members ('.$num_rows.')</h2>'.$tbldata;
	$members = '';
	
	$memberlistsql = "SELECT member_firstname, member_surname, member_forum_name, member_email, cb_membership.member_id, username FROM cb_membership INNER JOIN forumv3_users ON username = member_forum_name WHERE (user_type = 0 or user_type = 3) AND NOT EXISTS (SELECT * FROM cb_membership_transactions WHERE cb_membership_transactions.member_id = cb_membership.member_id AND (DATE(`transaction_date`) BETWEEN '".$qdate."' AND '".$qedate."')) ORDER BY member_firstname, member_surname"; 
	//echo $memberlistsql;
	$member_result=$mysqli->query($memberlistsql);
	$num_rows = mysqli_num_rows($member_result);
	while ($row = mysqli_fetch_array($member_result))
	{
		$members .= '<tr><td>'.$row['member_firstname'].' '. $row['member_surname']. ' <span title="Member ID: '.$row['member_id'].' Actual forum name: '.$row['username'].'">('.$row['member_forum_name'].')</span></td><td>'. $row['member_email'] .'</td><td>Unpaid for '. $x .'</td><td></td></tr>';
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