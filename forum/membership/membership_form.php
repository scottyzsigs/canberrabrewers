<?php 
// include for db connectivity
require('/home/canber10/public_html/cbadmin/web_incs/web_db.inc');

// first get sessId and userId from cookie or request
	$sessId='';
	$user_id='';
	if(!empty($_COOKIE["brewers_cookies_sid"]))
	{
		$sessId = $_COOKIE["brewers_cookies_sid"];
	}
	elseif(!empty($_REQUEST["brewers_cookies_sid"]))
	{
		$sessId = $_REQUEST["brewers_cookies_sid"];
	}
	if(!empty($_COOKIE["brewers_cookies_u"]))
	{	
		$user_id = $_COOKIE["brewers_cookies_u"];
	}
	elseif(!empty($_REQUEST["brewers_cookies_u"]))
	{ 
		$user_id = $_REQUEST["brewers_cookies_u"];
	}

	if($user_id != '')
	{
		// get the details from the membership table by forum ID
	}
?>

<div>
<h2>Step 1: Enter details</h2>

</div>
<script src="https://cdn.jsdelivr.net/jquery/3.2.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
<form method="post" target="_top" id="memberform">
<div id="form">
	<div class="form-row">
		<label for="first_name">Name</label><span class="required">*</span>
		<input type="text" id="first_name" name="first_name" required />
	</div>
	<div class="form-row">
		<label for="last_name">Surname</label>
		<input type="text" id="last_name" name="last_name" required />
	</div>
	<div class="form-row">
		<label for="forum_name">Forum name</label>
		<div class="form-help">Enter your Forum Name - if you are new member, pick a name to use for the online forum. It can be your real name or a nickname</div>
		<input type="text" id="forum_name" name="forum_name" required />
	</div>
	<div class="form-row">
		<label for="email">Email</label>
		<input type="text" id="email" name="email" required />
	</div>
	<div class="form-row">
		<label for="mobile">Mobile number</label>
		<input type="text" id="night_phone_b" name="night_phone_b" />
	</div>
	<div class="form-row">
		<label for="address1">Address</label>
		<input type="text" id="address1" name="address1" required />
	</div>
	<div class="form-row">
		<label for="city">Suburb</label>
		<input type="text" id="city" name="city" required />
	</div>
	<div class="form-row">
		<label for="state">State</label>
		<input type="text" id="state" name="state" required />
	</div>	
	<div class="form-row">
		<label for="zip">Postcode</label>
		<input type="text" id="zip" name="zip" required />
	</div>
</div>	
</form>
<script>
$("#memberform").validate();
</script>