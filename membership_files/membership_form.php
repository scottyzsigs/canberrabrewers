<?php 
// include for db connectivity
require('/home/canber10/public_html/cbadmin/web_incs/web_db.inc');

// use the forum auth cookies to see if user is logged on

require('/home/canber10/public_html/cbadmin/web_incs/forum_auth.php');

// import fields
// member_firstname,member_surname,member_address,member_suburb,member_state,member_postcode,member_email,member_mobile,member_forum_name

// send to sandbox and show debug?
$sandbox = false;
$debug_mode = false;

if($debug_mode)
{
	error_reporting(E_ALL); 
	ini_set('display_errors', 1);
}
// set up some PP variables for sandbox or not
if($sandbox)
{
	$ppaction = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
	$ppbutton = 'EZ4CUYJTK7JYU';
	$emailsubject = '*** TEST ONLY *** ';
}	
else
{
	$ppaction = 'https://www.paypal.com/cgi-bin/webscr';
	$ppbutton = 'NAEQDZZQ3TR46';
	$emailsubject = '';
}
// set up some global variables for each step
if($user_id == '' || $user_id == '1')
{
	$existing_user = "New User";
}
else
{
	$existing_user = "Existing User";
}
$step0 = empty($_POST['step1']) ? true : false; // initial state
$step1 = !empty($_POST['step1']) ? true : false; // member details form posted
$step2 = false; // set after SQL insert
$step3 = !empty($_GET['tx']) ? true : false; // back from paypal
$step4 = !empty($_GET['step4']) ? true : false; // back from forum or set after sql update if already member
if($step3) {$step0=false;} // cancel out step0
$error_state = false;
$error_message = '<p>Ouch! Something went wrong, we\'ve sent an email to the webmaster to let them know</p>';
$member_id = '';
$forumname_disabled = '';
$email_readonly = '';
$existing_note = '';
$first_name = '';
$last_name = '';
$forum_name = '';
$member_email = '';
$mobile_number = '';
$member_address = '';
$member_suburb = '';
$member_postcode = '';
$member_state = '';
$mail_headers = "From: webmaster@canberrabrewers.com.au\r\n";
// ***** STEP 0 *****
// check if they are on the first step
if ($step0)
{
// set style for form
$style = 'style="display:none;"';
// get some details from membership, email is the key and links forum to membership
if($user_id != '' && $user_id != '1')
{
// set variable to disable forum name
$forumname_disabled = 'readonly';
$user_sql=<<<ENDUSERSQL
SELECT *
FROM cb_membership m
LEFT JOIN forumv3_users u
ON m.member_forum_name = u.username
WHERE u.user_id = '%s'
ENDUSERSQL;
// put user id into string
$user_sql=sprintf($user_sql,$user_id);
// query db 
$user_result=$mysqli->query($user_sql);
// fetch into array
$user_details = $user_result->fetch_assoc();
$first_name = $user_details['member_firstname'];
$last_name = $user_details['member_surname'];
$forum_name = $user_details['username'];
$member_email = $user_details['member_email'];
$mobile_number = $user_details['member_mobile'];
$member_address = $user_details['member_address'];
$member_suburb = $user_details['member_suburb'];
$member_postcode = $user_details['member_postcode'];
$member_state = $user_details['member_state'];
$member_id = $user_details['member_id'];
$cookie_name = "cb_member_id";
$cookie_value = $member_id;
setcookie($cookie_name, $cookie_value, time() + (86400 * 1), "/"); // 86400 = 1 day
$cookie_name = "cb_forum_name";
$cookie_value = $forum_name;
setcookie($cookie_name, $cookie_value, time() + (86400 * 1), "/"); // 86400 = 1 day
}
?>
<p>Welcome to the Canberra Brewers Online Membership system Version 1.0 Beta.</p>
<h4>Please note:</h4>
<ul>
<li>This system is in beta, if you experience any issues registering or paying, please <strong>send an email to webmaster@canberrabrewers.com.au</strong>.</li>
<li>If you are a returning member who is not current and can't access the forum, please <strong>register as a new member, specifying your old forum name</strong>.</li>
<li>If you are a current member who has paid after April 2017 and don't yet have access the forum, please <strong>send an email to webmaster@canberrabrewers.com.au</strong> specifying your preferred forum name.</li>
<li>You can change any of your existing details if they are incorrect <strong>except forum name</strong>. If you want to change that send an email to webmaster@canberrabrewers.com.au.</li>
</ul>
<h3>Step 1: Enter details</h3>
<?php
// 1 is not logged in
if($user_id == '' || $user_id == '1')
{
?>
	<div id="select">
	<p>Select a link below to proceed, current members need to know their forum login to proceed.</p>
		<ul>
			<li><a href="#" class="newmember">I am a new member</a></li>
			<li><a href="#" class="existingmember">I am a current member</a></li>
		</ul>
	</div>
<div id="forumpanel" <?php echo $style ?>>
<?php 
$returnpage = '/membership';
require('/home/canber10/public_html/cbadmin/web_incs/forum_login_form.php');
?>
</div>
<?php
}
else
{
	$style = '';
	$existing_note = '<p>Existing members can not change their forum name or email address. Please contact webmaster@canberrabrewers.com.au if you wish to change either of these</p>';
}
?>
<div id="memberpanel" <?php echo $style ?>>
<?php echo $existing_note ?>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
<form method="post" action novalidate="novalidate" target="_top" id="memberform">
	<fieldset>
		<input type="hidden" name="step1" value="1" />
		<input type="hidden" name="member_id" value="<?php echo $member_id ?>" />
		<div class="form-row">
			<div class="form-label"><label for="first_name">Name</label> <span class="required">*</span></div>
			<input type="text" id="first_name" name="first_name" value="<?php echo $first_name ?>" required />
		</div>
		<div class="form-row">
			<div class="form-label"><label for="last_name">Surname</label> <span class="required">*</span></div>
			<input type="text" id="last_name" name="last_name" value="<?php echo $last_name ?>" required />
		</div>
		<div class="form-row">
			<div class="form-label"><label for="forum_name">Forum name</label> <span class="required">*</span></div>
			<div class="form-help">Enter your Forum Name - if you are new member, pick a name to use for the online forum. It can be your real name or a nickname</div>
			<input type="text" id="forum_name" name="forum_name" value="<?php echo $forum_name ?>" required <?php echo $forumname_disabled ?> />			
		</div>
		<div class="form-row">
			<div class="form-label"><label for="email">Email</label> <span class="required">*</span></div>
			<input type="text" id="email" name="email" value="<?php echo $member_email ?>" required />
		</div>
		<div class="form-row">
			<div class="form-label"><label for="mobile">Mobile number</label></div>
			<input type="text" id="night_phone_b" name="night_phone_b" value="<?php echo $mobile_number ?>" required />
		</div>
		<div class="form-row">
			<div class="form-label"><label for="address1">Address</label> <span class="required">*</span></div>
			<input type="text" id="address1" name="address1" value="<?php echo $member_address ?>" required />
		</div>
		<div class="form-row">
			<div class="form-label"><label for="city">Suburb</label> <span class="required">*</span></div>
			<input type="text" id="city" name="city" value="<?php echo $member_suburb ?>" required />
		</div>
		<div class="form-row">
			<div class="form-label"><label for="state">State</label> <span class="required">*</span></div>
			<select name="state">
				<?php
				$selectedstate = '';
				$states = array("ACT", "NSW", "VIC", "QLD", "SA", "WA", "NT", "TAS", "Overseas");
				foreach($states as $value):
					if($member_state == $value){
						$selectedstate = ' selected';
					}
					else{
						$selectedstate = '';
					}
					echo '<option value="'.$value.'".$selected.>'.$value.'</option>';
				endforeach;
				?>
			</select>
		</div>	
		<div class="form-row">
			<div class="form-label"><label for="zip">Postcode</label> <span class="required">*</span></div>
			<input type="text" id="zip" name="zip" value="<?php echo $member_postcode ?>" required />
		</div>
		<div class="form-row">
			<h4>Affirmation</h4>
			<div class="form-help">I hereby apply to become a member of the Canberra Brewers and agree that I support the aims of, and abide by the rules of, the Canberra Brewers Incorporated</div>
			<label for="affirmation">I agree</label> <span class="required">*</span> <input type="checkbox" id="affirmation" name="affirmation" required />
		</div>
	
	<div class="form-row">
		<input type="submit" value="Proceed to Step 2">
	</div>	
	</fieldset>	
</form>
</div>
<script>
jQuery.noConflict();
jQuery(document).ready(function ($) {
	// validate the main form
	$("#memberform").validate();
	// validate the forum form
	$("#forumform").validate();
	// show the new member form
	$(".newmember").click (function(){
		$("#memberpanel").show();
		$("#select").hide();
		return false;
	});
	// show the forum login
	$(".existingmember").click (function(){
		$("#forumpanel").show();
		$("#select").hide();
		return false;
	});
});
</script>
<?php
// end check for step0
}

// ***** STEP 1 *****
// check if they submitted the form step = 1
if ($step1)
{
// add to DB as unpaid
// set var for unpaid
$a = 0;
if($user_id == '' || $user_id == '1')
{
$member_sql=<<<ENDMEMBERSQL
INSERT INTO cb_membership (member_firstname, member_surname, member_email, member_mobile, member_address, member_suburb, member_state, member_postcode, member_paid)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);
ENDMEMBERSQL;

// prepare and exec
if ($memberStmt = $mysqli->prepare($member_sql)) 
{
$memberStmt->bind_param('ssssssssi',$_POST['first_name'],$_POST['last_name'],$_POST['email'],$_POST['night_phone_b'],$_POST['address1'],$_POST['city'],$_POST['state'],$_POST['zip'], $a);

// execute sql
$memberStmt->execute();
// get the ID
$newID = $memberStmt->insert_id;
// close
if($memberStmt){$memberStmt->close();}

if($debug_mode) { echo $newID; } // debug

// put the new ID on a cookie
$cookie_name = "cb_member_id";
$cookie_value = $newID;
setcookie($cookie_name, $cookie_value, time() + (86400 * 1), "/"); // 86400 = 1 day
$cookie_name = "cb_forum_name";
$cookie_value = $_POST['forum_name'];
setcookie($cookie_name, $cookie_value, time() + (86400 * 1), "/"); // 86400 = 1 day
}
else
{ $error_state = true; }
}
else
{
//update member details
$member_sql=<<<ENDMEMBERSQL
UPDATE cb_membership SET member_firstname=?, member_surname=?, member_mobile=?, member_address=?, member_suburb=?, member_state=?, member_postcode=?, member_paid=?, member_email=? WHERE member_id=?;
ENDMEMBERSQL;

// prepare and exec
if ($memberStmt = $mysqli->prepare($member_sql)) 
{

$memberStmt->bind_param('sssssssisi',$_POST['first_name'],$_POST['last_name'],$_POST['night_phone_b'],$_POST['address1'],$_POST['city'],$_POST['state'],$_POST['zip'],$a,$_POST['email'],$_POST['member_id']);

// execute sql
$memberStmt->execute();

// close
if($memberStmt){$memberStmt->close();}

}
else
{ $error_state = true; }

}
if($error_state)
{
	echo $error_message;
	$msg = "Registration failed\nName: %s %s \nEmail: %s\nType: %s";
	$msg = sprintf($msg,$_POST['first_name'],$_POST['last_name'],$_POST['email'],$existing_user);
	mail("webmaster@canberrabrewers.com.au",$emailsubject."Member registration failed",$msg,$mail_headers);

}
else
{
	// mail the webmaster step 1 complete
	$msg = "Registration started\nName: %s %s \nEmail: %s\nType: %s";
	$msg = sprintf($msg,$_POST['first_name'],$_POST['last_name'],$_POST['email'],$existing_user);
	mail("webmaster@canberrabrewers.com.au",$emailsubject."Member registration started",$msg,$mail_headers);
	// done all that go to step 2

}
$step2 = true;
}
// ***** STEP 2 *****
// check for step 2
if ($step2)
{

?>
<div>
<h3>Step 2: Paypal Payment</h2>
<p>Choose your membership type below and click Buy Now to continue to PayPal. You will be returned to our site for confirmation after you have paid.</p>
</div>

<form action="<?php echo $ppaction ?>" method="post" target="_top">
		<input type="hidden" id="first_name" name="first_name" value="<?php echo $_POST['first_name'] ?>" />
		<input type="hidden" id="last_name" name="last_name" value="<?php echo $_POST['last_name'] ?>" />
		<input type="hidden" id="email" name="email" value="<?php echo $_POST['email'] ?>" />
		<input type="hidden" id="night_phone_b" name="night_phone_b" value="<?php echo $_POST['night_phone_b'] ?>" />
		<input type="hidden" id="address1" name="address1" value="<?php echo $_POST['address1'] ?>" />
		<input type="hidden" id="city" name="city" value="<?php echo $_POST['city'] ?>" />
		<input type="hidden" id="state" name="state" value="<?php echo $_POST['state'] ?>" />
		<input type="hidden" id="zip" name="zip" value="<?php echo $_POST['zip'] ?>" />
	<div class="form-row>
<input type="hidden" name="on0" value="Membership type">
<p>Membership type:</p>
<select name="os0">
	<option value="Standard">Standard $40.00 AUD</option>
	<option value="Remote">Remote $20.00 AUD</option>
</select>
	</div>
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="<?php echo $ppbutton ?>">

<input type="hidden" name="currency_code" value="AUD">
<input type="image" src="https://www.paypalobjects.com/en_AU/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_AU/i/scr/pixel.gif" width="1" height="1">
<INPUT TYPE="hidden" name="country" value="AU" />
</form>
<?php 
// end step 2
}
// check if we came back from PP

if ($step3)
{
	// sql to update member as paid
$member_sql=<<<ENDMEMBERSQL
UPDATE cb_membership SET member_paid_date = ?, member_paid=?, member_transaction=? WHERE member_id=?;
ENDMEMBERSQL;

// prepare and exec
if ($memberStmt = $mysqli->prepare($member_sql)) 
{
$a=1;
$paiddate = date("Y-m-d");
$memberStmt->bind_param('sisi', $paiddate, $a, $_GET['tx'], $_COOKIE['cb_member_id']);

// execute sql
$memberStmt->execute();

if($debug_mode) { echo 'Member details: '.$_COOKIE['cb_member_id'].' '.$_GET['tx'].' '.$paiddate;}

// close
if($memberStmt){$memberStmt->close();}

}
else
{ $error_state = true; }

if($error_state)
{
	echo $error_message;
	$msg = "Registration failed\nMember ID: %s\nType: %s";
	$msg = sprintf($msg,$_COOKIE['cb_member_id'],$existing_user);
	mail("webmaster@canberrabrewers.com.au",$emailsubject."Member registration failed",$msg,$mail_headers);

}
else
{
	// check user for forum
	if($user_id == '' || $user_id == '1')
	{
		// **** TO DO release 2 ****
		// not a user, add with redirect, come back with ?step4=1
		
	}
	else
	{
		$step4 = true;
	}
	
	// **** remove when forum add is complete ****
	$step4 = true;
}
}
// check for final step
if($step4)
{
// TODO: make this a function
$user_sql=<<<ENDUSERSQL
SELECT *
FROM cb_membership m
WHERE m.member_id = '%s'
ENDUSERSQL;
	// put user id into string
	$user_sql=sprintf($user_sql,$_COOKIE['cb_member_id']);
	// query db
	$user_result=$mysqli->query($user_sql);
	// fetch into array
	$user_details = $user_result->fetch_assoc();
	$user_values = "Name: %s %s\nEmail: %s\nForum Name: %s\nAddress: %s %s %s %s\nPhone: %s";
	$user_values = sprintf($user_values,$user_details['member_firstname'],$user_details['member_surname'],$user_details['member_email'],$_COOKIE['cb_forum_name'],$user_details['member_address'],$user_details['member_suburb'],$user_details['member_state'],$user_details['member_postcode'],$user_details['member_mobile']);

	// email to webmaster and treasurer
	$msg = "Registration completed\n%s\nType: %s";
	$msg = sprintf($msg,$user_values,$existing_user);
	mail("webmaster@canberrabrewers.com.au",$emailsubject."CB Member registration completed",$msg,$mail_headers);
	mail("treasurer@canberrabrewers.com.au",$emailsubject."CB Member registration completed",$msg,$mail_headers);
	$msg = "Registration completed\n%s\nType: %s";
	$msg = sprintf($msg,$user_values,$existing_user);
	mail($user_details['member_email'],$emailsubject."Canberra Brewers member registration completed",$msg,$mail_headers);
?>
<div>
<h3>Thank you</h2>
<p>Your membership has been processed, you should receive an email from PayPal for you transaction, and another from us confirming your membership. Please contact webmaster@canberrabrewers.com.au if you do not receive these emails.</p>
<p>For new members we will add your forum membership and you will receive another email to activate it soon.</p>
<p>Your details<br /><pre><?php echo $user_values ?></pre></p>
</div>
<?php 
// end step 4
}
?>