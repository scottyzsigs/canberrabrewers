<?php 
// include for db connectivity
require('/home/canber10/public_html/cbadmin/web_incs/web_db.inc');

// use the forum auth cookies to see if user is logged on

require('/home/canber10/public_html/cbadmin/web_incs/forum_auth.php');

// send to sandbox and show debug?
$sandbox = 'true';
// set up some PP variables for sandbox or not
if($sandbox == 'true')
{
	$ppaction = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
	$ppbutton = 'EZ4CUYJTK7JYU';
	$emailsubject = '*** TEST ONLY *** ';
	error_reporting(E_ALL); 
	ini_set('display_errors', 1);
}	
else
{
	$ppaction = 'https://www.paypal.com/cgi-bin/webscr';
	$ppbutton = 'NAEQDZZQ3TR46';
	$emailsubject = '';
}
// set up some global variables for each step
if($user_id == '' && $user_id == '1')
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
$forumname_disabled = 'disabled';
$user_sql=<<<ENDUSERSQL
SELECT *
FROM cb_membership m
LEFT JOIN forumv3_users u
ON m.member_email = u.user_email
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
}

?>


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
<form action="/forum/ucp.php?mode=login" novalidate="novalidate" method="post" id="forumform">
	<input type="hidden" name="redirect" value="/?page_id=1059&preview=true" />
    <h3>Forum login</h3>
	<p>Please log into with your forum username and pasword to continue. After you log in you will be redirected back to this page.</p>
    <fieldset>
        <label for="username">Username:</label> <span class="required">*</span>&nbsp;
        <input type="text" name="username" id="username" size="10" title="Username" required />
        <label for="password">Password:</label> <span class="required">*</span>&nbsp;
        <input type="password" name="password" id="password" size="10" title="Password" required />
        <input type="submit" name="login" value="Login" />
    </fieldset>
</form>
</div>
<?php
}
else
{
	$style = '';
}
?>
<div id="memberpanel" <?php echo $style ?>>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
<form method="post" action novalidate="novalidate" target="_top" id="memberform">
	<input type="hidden" name="step1" value="1" />
	<div class="form-row">
		<label for="first_name">Name</label> <span class="required">*</span>
		<input type="text" id="first_name" name="first_name" value="<?php echo $first_name ?>" required />
	</div>
	<div class="form-row">
		<label for="last_name">Surname</label> <span class="required">*</span>
		<input type="text" id="last_name" name="last_name" value="<?php echo $last_name ?>" required />
	</div>
	<div class="form-row">
		<label for="forum_name">Forum name</label> <span class="required">*</span>
		<div class="form-help">Enter your Forum Name - if you are new member, pick a name to use for the online forum. It can be your real name or a nickname</div>
		<input type="text" id="forum_name" name="forum_name" value="<?php echo $forum_name ?>" required <?php echo $forumname_disabled ?> />
	</div>
	<div class="form-row">
		<label for="email">Email</label> <span class="required">*</span>
		<input type="text" id="email" name="email" value="<?php echo $member_email ?>" required />
	</div>
	<div class="form-row">
		<label for="mobile">Mobile number</label>
		<input type="text" id="night_phone_b" name="night_phone_b" value="<?php echo $mobile_number ?>" required />
	</div>
	<div class="form-row">
		<label for="address1">Address</label> <span class="required">*</span>
		<input type="text" id="address1" name="address1" value="<?php echo $member_address ?>" required />
	</div>
	<div class="form-row">
		<label for="city">Suburb</label> <span class="required">*</span>
		<input type="text" id="city" name="city" value="<?php echo $member_suburb ?>" required />
	</div>
	<div class="form-row">
		<label for="state">State</label> <span class="required">*</span>
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
		<label for="zip">Postcode</label> <span class="required">*</span>
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
// end check for step == ''
}

// ***** STEP 1 *****
// check if they submitted the form step = 1
if ($step1)
{
// add to DB as unpaid
if($user_id == '' || $user_id == '1')
{
$insert_sql=<<<ENDINSERTSQL
INSERT INTO cb_membership (member_firstname, member_surname, member_email, member_mobile, member_address, member_suburb, member_state, member_postcode, member_paid)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);
ENDINSERTSQL;

// prepare and exec
if ($insStmt = $mysqli->prepare($insert_sql)) 
{
// set var for paid
$a = 0;
$insStmt->bind_param('ssssssssi',$_POST['first_name'],$_POST['last_name'],$_POST['email'],$_POST['night_phone_b'],$_POST['address1'],$_POST['city'],$_POST['state'],$_POST['zip'], $a);

// execute sql
$insStmt->execute();
// get the ID
$newID = $insStmt->insert_id;
// close
if($insStmt){$insStmt->close();}

// echo $newID; // debug
// put the new ID on the session
$_SESSION['member_ID'] = $newID;
}
}


// mail the webmaster step 1 complete
$msg = "Registration started\nName: %s %s \nEmail: %s\nType: %s";
$msg = sprintf($msg,$_POST['first_name'],$_POST['last_name'],$_POST['email'],$existing_user);
mail("webmaster@canberrabrewers.com.au",$emailsubject."Member registration started",$msg);
// done all that go to step 2
$step2 = true;

}
// ***** STEP 2 *****
// check for step 2, which is a _GET from redirect after add to DB
if ($step2)
{

?>
<div>
<h2>Step 2: Paypal Payment</h2>
<p>Choose your membership type below and click Buy Now to continue to PayPal. You will be returned to our site for confirmation after you have paid.</p>
</div>

<form action="<?php echo $ppaction ?>" method="post" target="_top">
		<input type="hidden" id="first_name" name="first_name" />
		<input type="hidden" id="last_name" name="last_name" />
		<input type="hidden" id="email" name="email" />
		<input type="hidden" id="night_phone_b" name="night_phone_b" />
		<input type="hidden" id="address1" name="address1" />
		<input type="hidden" id="city" name="city" />
		<input type="hidden" id="state" name="state" />
		<input type="hidden" id="zip" name="zip" />
	<div class="form-row>
<input type="hidden" name="on0" value="Membership type">Membership type<select name="os0">
	<option value="Standard">Standard $40.00 AUD</option>
	<option value="Remote">Remote $20.00 AUD</option>
</select>
	</div>
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="<?php echo $ppbutton ?>">

<input type="hidden" name="currency_code" value="AUD">
<input type="image" src="https://www.paypalobjects.com/en_AU/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_AU/i/scr/pixel.gif" width="1" height="1">
<INPUT TYPE="hidden" name="address_override" value="1">
<INPUT TYPE="hidden" name="country" value="AU" />
</form>
<?php 
// end step 2
}
// check if we came back from PP

if ($step3)
{
	// sql to update member as paid

	// check user for forum
	if($user_id == '' || $user_id == '1')
	{
		// not a user, add with redirect, come back with ?step4=1
	}
	else
	{
		$step4 = true;
	}
}
// check for final step
if($step4)
{
	// email to webmaster and treasurer
	$msg = "Registration completed\nName: %s %s \nEmail: %s\nType: %s";
	$msg = sprintf($msg,$_POST['first_name'],$_POST['last_name'],$_POST['email'],$existing_user);
	mail("webmaster@canberrabrewers.com.au",$emailsubject."Member registration completed",$msg);
	mail("treasurer@canberrabrewers.com.au",$emailsubject."Member registration completed",$msg);
	
?>
<div>
<h2>Thank you</h2>
<p>Your membership has been processed, you should receive an email from PayPal for you transaction, another with membership details and a third email for forum activation. Please contact webmaster@canberrabrewers.com.au if you do not receive these emails.</p>
</div>
<?php 
// end step 4
}
?>