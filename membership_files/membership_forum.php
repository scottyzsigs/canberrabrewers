<?php

	global $db,$cache,$phpEx,$config,$phpbb_root_path;
	
	require_once ('community/config.php');
	
	define ('IN_PHPBB', true);
	
	$phpbb_root_path = "forum/";
	$phpEx = "php";
	
	require_once ($phpbb_root_path . 'common.php');
	
	include ($phpbb_root_path . 'includes/functions_display.' . $phpEx);
	include_once ($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		
	// create an inactive user key to send to them...
	$user_actkey = gen_rand_string(10);
	$key_len = 54 - (strlen($server_url));
	$key_len = ($key_len < 6) ? 6 : $key_len;
	$user_actkey = substr($user_actkey, 0, $key_len);

	// set the user to inactive and the reason to "newly registered"
	$user_type = USER_INACTIVE;
	$user_inactive_reason = INACTIVE_REGISTER;
	$user_inactive_time = time();
	
	$username_clean = utf8_clean_string("MyName");
	$sql_ary = array(
		'username'				=> "MyName",
		'username_clean'		=> $username_clean,
		'user_password'			=> md5("testing"),
		'user_pass_convert'		=> 0,
		'user_email'			=> "me@me.com",
		'user_email_hash'		=> crc32(strtolower("me@me.com")) . strlen("me@me.com"),
		'group_id'				=> 2,
		'user_type'				=> 1,
		'user_actkey'           => $user_actkey,
		'user_ip'               => $user->ip,
		'user_regdate'          => time(),
		'user_inactive_reason'  => $user_inactive_reason,
		'user_inactive_time'    => $user_inactive_time,
	);
	
   	$err = user_add($sql_ary, $cp_data);
   	if ($err == false) 
	{
		// send email saying an error occured with $username_clean
		echo "error";
	}
	else
	{
		// redirect back to success message
		echo "success";
	}
?>