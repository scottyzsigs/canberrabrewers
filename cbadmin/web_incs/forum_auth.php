<?php 
$sessId='';
if(!empty($_COOKIE["brewers_cookies_sid"]))
{
    $sessId = $_COOKIE["forum_cookies_sid"];
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
echo "<!-- user ID: " . $user_id . " -->";
?>