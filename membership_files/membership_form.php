<?php 
// include for db connectivity
require('/home/canber10/public_html/cbadmin/web_incs/web_db.inc');

// use the forum auth cookies to see if user is logged on

require('/home/canber10/public_html/cbadmin/web_incs/forum_auth.php');

// ***** STEP 0 *****
// check if they are on the first step
if ($_POST['step'] == '')
{
// set style for form
$style = 'style="display:none;"';
// get some details from membership, email is the key and links forum to membership
if($user_id != '' && $user_id != '1')
{
// set variable to disable forum name
$forumname_disabled = 'disabled';
$user_sql=<<<ENDUSERSQL
SELECT m.*
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
	<input type="hidden" name="step" value="1" />
	<div class="form-row">
		<label for="first_name">Name</label> <span class="required">*</span>
		<input type="text" id="first_name" name="first_name" value="<?php echo $first_name ?>" required />
	</div>
	<div class="form-row">
		<label for="last_name">Surname</label> <span class="required">*</span>
		<input type="text" id="last_name" name="last_name" value="<?php echo $first_name ?>" required />
	</div>
	<div class="form-row">
		<label for="forum_name">Forum name</label> <span class="required">*</span>
		<div class="form-help">Enter your Forum Name - if you are new member, pick a name to use for the online forum. It can be your real name or a nickname</div>
		<input type="text" id="forum_name" name="forum_name" required <?php echo $forumname_disabled ?> />
	</div>
	<div class="form-row">
		<label for="email">Email</label> <span class="required">*</span>
		<input type="text" id="email" name="email" value="<?php echo $member_email ?>" required />
	</div>
	<div class="form-row">
		<label for="mobile">Mobile number</label>
		<input type="text" id="night_phone_b" name="night_phone_b" value="<?php echo $member_mobile ?>" required />
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
if ($_POST['step'] == '1')
{
// add to DB as unpaid
$insert_sql=<<<ENDINSERTSQL
INSERT INTO cb_membership (member_firstname, member_surname, member_email, member_mobile, member_address, member_suburb, member_state, member_postcode)
VALUES (value1, value2, value3, ...);

ENDINSERTSQL;
// put user id into string
$insert_sql=sprintf($insert_sql,$user_id);
// query db
$insert_result=$mysqli->query($insert_sql);
}
// ***** STEP 2 *****
// check for step 2, which is a _GET from redirect after add to DB
if ($_GET['step'] == '2')
{
	
?>
<div>
<h2>Step 2: Paypal Payment</h2>
<p>Choose your membership type below and click Buy Now to continue to PayPal. You will be returned to our site for confirmation after you have paid.</p>
</div>

<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
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
<input type="hidden" name="hosted_button_id" value="NAEQDZZQ3TR46">

<input type="hidden" name="currency_code" value="AUD">
<input type="image" src="https://www.paypalobjects.com/en_AU/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_AU/i/scr/pixel.gif" width="1" height="1">
<INPUT TYPE="hidden" name="address_override" value="1">
<INPUT TYPE="hidden" name="country" value="AU" />
</form>
<?php 
// end step 2
}
?>