<?php 
// include for db connectivity
require('/home/canber10/public_html/cbadmin/web_incs/web_db.inc');

// use the forum auth cookies to see if user is logged on

require('/home/canber10/public_html/cbadmin/web_incs/forum_auth.php');

?>

<div>
<h3>Step 1: Enter details</h3>

</div>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
<form method="post" action novalidate="novalidate" target="_top" id="memberform">
<div id="form">
	<div class="form-row">
		<label for="first_name">Name</label> <span class="required">*</span>
		<input type="text" id="first_name" name="first_name" required />
	</div>
	<div class="form-row">
		<label for="last_name">Surname</label> <span class="required">*</span>
		<input type="text" id="last_name" name="last_name" required />
	</div>
	<div class="form-row">
		<label for="forum_name">Forum name</label> <span class="required">*</span>
		<div class="form-help">Enter your Forum Name - if you are new member, pick a name to use for the online forum. It can be your real name or a nickname</div>
		<input type="text" id="forum_name" name="forum_name" required />
	</div>
	<div class="form-row">
		<label for="email">Email</label> <span class="required">*</span>
		<input type="text" id="email" name="email" required />
	</div>
	<div class="form-row">
		<label for="mobile">Mobile number</label>
		<input type="text" id="night_phone_b" name="night_phone_b" />
	</div>
	<div class="form-row">
		<label for="address1">Address</label> <span class="required">*</span>
		<input type="text" id="address1" name="address1" required />
	</div>
	<div class="form-row">
		<label for="city">Suburb</label> <span class="required">*</span>
		<input type="text" id="city" name="city" required />
	</div>
	<div class="form-row">
		<label for="state">State</label> <span class="required">*</span>
		<input type="text" id="state" name="state" value="ACT" required />
	</div>	
	<div class="form-row">
		<label for="zip">Postcode</label> <span class="required">*</span>
		<input type="text" id="zip" name="zip" required />
	</div>
	<div class="form-row">
		<h4>Affirmation</h4>
		<div class="form-help">I hereby apply to become a member of the Canberra Brewers and agree that I support the aims of, and abide by the rules of, the Canberra Brewers Incorporated</div>
		<label for="affirmation">I agree</label> <span class="required">*</span> <input type="checkbox" id="affirmation" name="affirmation" required />
	</div>
	<div class="form-row">
		<input type="submit" value="Proceed to Step 2">
	</div>
</div>	
</form>
<script>
jQuery.noConflict();
jQuery(document).ready(function ($) {
	$("#memberform").validate();
});
</script>