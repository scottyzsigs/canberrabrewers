<?php
//include for db connectivity
require('/home/canber10/public_html/cbadmin/web_incs/web_db.inc');
set_error_handler('jfh_handle_error',E_ALL & ~NOTICE );

$header_section=<<<END
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" 
    "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=ISO-8859-1" />
<meta name="Author" content="Jamie" />
<meta name="Date" content="%s" />
<meta name="Modified" content="%s" />
<meta name="Editor" content="%s" />
<meta name="description" content="%s" />
<title>%s</title>
<link rel='stylesheet' type="text/css" href="%s" />
</head>
<a  name='top'></a>
<body onload='pageInit()'>
<div class='centerIt'><img src="http://www.canberrabrewers.com.au/forum/styles/rockn_roll/imageset/header.jpg" border="0" alt="Canberra Brewers Club" /></div>
END;

//summary we have a db connection
if (!$mysqli)
{
jfh_error(true,"Unable to connect to the db!");	
	
}
//get sessId and userId from cookie or request
$sessId='';
if(!empty($_COOKIE["brewers_cookie_sid"]))
{
    $sessId = $_COOKIE["brewers_cookie_sid"];
}
elseif(!empty($_REQUEST["brewers_cookie_sid"]))
{
	$sessId = $_REQUEST["brewers_cookie_sid"];
}
if(!empty($_COOKIE["brewers_cookie_u"]))
{	
	$user_id = $_COOKIE["brewers_cookie_u"];
}
elseif(!empty($_REQUEST["brewers_cookie_u"]))
{ 
    $user_id = $_REQUEST["brewers_cookie_u"];
}

//If we have no user_id bad! if no sessId no big deal
if(!$user_id)
{
		echo sprintf($header_section,'09-02-2009','09-02-2009','gPHPEdit','Comp Entries','Get Comp Entries','compstyles.css');
		jfh_error(TRUE, "No user data  found!");
}
//now get the current comp
$comp_sql=<<<ENDCOMPSQL
SELECT comp_id
FROM cb_competitions
WHERE comp_status='OPEN' 
ENDCOMPSQL;
$comp_result=$mysqli->query($comp_sql);
//TODO: for now assume one comp open at a time  - need to handle multi comp situation
if(!$comp_result || $comp_result->num_rows!=1)
{
	jfh_error(true, "More than one comp open and this script can't handle that yet"); //TODO change msg when fixed
}
//We have one comp 
$comp_details=$comp_result->fetch_assoc();
$compId=$comp_details['comp_id'];
if($comp_result){$comp_result->free();}
//#####remove after testing##############
/*(UserID 127 = Jamie; 297 = Daff; Mal = 387; Billy = 208)
$valid_users = array(127,297,387,208);
if(!in_array($user_id,$valid_users))
{
	echo sprintf($header_section,'09-02-2009','09-02-2009','gPHPEdit','Comp Entries','Get Comp Entries','compstyles.css');
	jfh_error(TRUE, "You are not the Comp Sec so you can't see this page");
}
*/
//#####################################

//Get the summary of entries thus far
$summary_sql=<<<ENDSUMMARYSQL
SELECT CONCAT(style, sub_style) as style, style_name, sub_style_name, entry_number, fullname, email, phone, brew_name, method, yeast, original_gravity, final_gravity, comments, date, bottles_returned as 'bottle returned?'
FROM cb_styles cs, cb_entries ce, cb_comp_styles ccs, cb_competitions cc
WHERE ce.style_id = cs.style_id
AND ccs.style_id = cs.style_id
AND ccs.comp_id = cc.comp_id
AND ccs.comp_id = ce.comp_id
AND cc.comp_id =$compId
ENDSUMMARYSQL;
$summary_sql=sprintf($summary_sql, $compId);
if($summaryStmt = $mysqli->query($summary_sql))
{
	while($summaryResult=$summaryStmt->fetch_assoc())
	{
		$details[]=$summaryResult;
	}
	if($summaryStmt){$summaryStmt->close();}
	//echo renderSummary($details);

}
else
{
	echo sprintf($header_section,'09-02-2009','09-02-2009','gPHPEdit','Comp Entries','Get Comp Entries','compstyles.css');
	jfh_error(TRUE, "Unable to retrieve your entry details:". $mysqli->error);
}
if(count($details) ==0)
{
	echo sprintf($header_section,'09-02-2009','09-02-2009','gPHPEdit','Comp Entries','Get Comp Entries','compstyles.css');
	echo "<h4 class='centerIt'>There are no beers entered yet</h4>";
}
else
{
	//send header for csv?
	$buf='';
	foreach($details[0] as $colname=>$value)
	{
		$buf.="\"$colname\",";
	}
	$buf=substr($buf,0,-1)."\n";
	foreach($details as $row)
	{
		foreach($row as $colvalue)
		{
			$buf.="\"$colvalue\",";
		}
		$buf=substr($buf,0,-1)."\n";
	}
header("Content-Type: text/csv");
header("Content-Disposition: inline; filename=comp_entries.csv ");
echo $buf;
}

function jfh_error($close, $msg)
{
	global $mysqli;
	$msg=(!$msg || $msg=='') ? 'Unknown Error' : $msg;
	$html="<h2 class='errorMessage'>$msg<br /><br />Whinge to the <a href='mailto:webmaster@canberrabrewers.com.au' alt='Send whingergram'>webminion</a></h2>";
	echo $html;
	if($mysqli)
	{
		$mysqli->close();
	}
	if($close===true)
	{
		exit;
	}
}

function jfh_handle_error($code,$msg,$file,$line)
{
	jfh_error(true,$msg.$file.$line);
}
?>
