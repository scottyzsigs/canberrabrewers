<form action="/forum/ucp.php?mode=login" novalidate="novalidate" method="post" id="forumform">
	<input type="hidden" name="redirect" value="<?php echo $returnpage ?>" />
    <h3>Forum login</h3>
	<p>Please log into with your forum username and pasword to continue. After you log in you will be redirected back to this page.</p>
	<fieldset>
		<div class="form-row">
			<div class="form-row">
			<div class="form-label">
			<label for="username">Username:</label> <span class="required">*</span>&nbsp;
			</div>
			<input type="text" name="username" id="username" size="10" title="Username" required />
			</div>
			<div class="form-row">
			<div class="form-label">
			<label for="password">Password:</label> <span class="required">*</span>&nbsp;
			</div>
			<input type="password" name="password" id="password" size="10" title="Password" required />
			</div>
		</div>
		<input type="submit" name="login" value="Login" />
	</fieldset>
</form>