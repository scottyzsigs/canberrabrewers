<?php

//include for db connectivity
require('/home/canber10/public_html/cbadmin/web_incs/web_db.inc');
set_error_handler('jfh_handle_error',E_ALL & ~NOTICE );
session_start('beer_auth');
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
END;
echo sprintf($header_section,'16-02-2009','16-02-2009','gPHPEdit','Cb submit entry form','Competition Entry Form submission','compstyles.css');
//print top of page
$start_page=<<<ENDSTARTPAGE
<body>
<div class='centerIt'><img src="http://www.canberrabrewers.com.au/forum/styles/rockn_roll/imageset/header.jpg" border="0" alt="Canberra Brewers Club" /></div>
<br />
ENDSTARTPAGE;
echo $start_page;
//check we have a db connection
if (!$mysqli)
{
jfh_error(true,"Unable to connect to the db!");	
	
}
//get sessId and userId from cookie or request
$sessId='';
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

//check post
$compId= isIntAtLeast($_POST['compId'],1);//clean - we know this is just an int
if($compId ==null || $compId===false || empty($compId))
{
	jfh_error(true, "No compId passed!");
}
$userId= isIntAtLeast($_POST['userId'],1);//clean - we know this is just an int
if($userId ==null || $userId===false || empty($userId))
{
	jfh_error(TRUE, "No userId passed!");
}
// if it does not appear to be current user bomb out
//note:
//$userId - from post - the one we will use once validated here
//$user_id from cookie or request as part of phpbb3 session mechanism
if($user_id == "1" && $userId =="-1")
{
  #okay - cookie set as anon user be we use -1 to identify public users
}
elseif(!$user_id || $user_id != $userId)
{
		jfh_error(TRUE, "The user you are trying to make an entry for is not the same as the current user");
}

//Check to see if user is just checking entries
if($_REQUEST['check_only']!='true')
{
	$brewerFullName=isNotEmpty($_POST['brewerFullName']);//clean - isNotEmpty cleans
	if($brewerFullName===false)
	{
		jfh_error(TRUE,"Missing brewer name!");
	}
	$brewerEmail=isNotEmpty($_POST['brewerEmail']);//clean - isNotEmpty cleans
	if($brewerEmail===false)
	{
		jfh_error(TRUE,"Missing brewer email!");
	}

	$brewerPhone=isIntAtLeast($_POST['brewerPhone'],8);//clean - we know this is just an int
	if($brewerPhone ==null || $brewerPhone === false || empty($brewerPhone))
	{
		jfh_error(TRUE,"Missing brewer phone number!");
	}
    $returnBottles = $_POST['brewerReturnBottles'];
	//For other post vars we loop
	$style = '';
	foreach($_POST as $key => $value)
	{

		if(strstr($key, '_')) //ie a column related to an entry
		{
			$data = split('_',$key);
			if($data[1] !=$style)
			{
				$style=$data[1];
				$entries[$style]['userId']=$userId;
				$entries[$style]['fullname']=$brewerFullName;
				$entries[$style]['email']=$brewerEmail;
				$entries[$style]['phone']=$brewerPhone;
				$entries[$style]['compId']=$compId;
                $entries[$style]['returnBottles']=$returnBottles;
			}
			//check the data
			if($data[0]=='brewName')
			{
				if(!isNotEmpty($value))//cleaned by isNotEmpty
				{
					jfhError(TRUE,"Empty value for brew name!");
				}
			}
			elseif($data[0]=='brewMethod')
			{
				if($value!='All Grain' && $value !='Mini Mash' && $value != 'Kit')//cleaned by this test
				{
					jfhError(TRUE,"Invalid value for brew method!");
				}
			}
			elseif($data[0]=='yeast')
			{
				if(!isNotEmpty($value))//cleaned by isNotEmpty
				{
					jfhError(TRUE,"Empty value for yeast!");
				}
				
			}
			elseif($data[0]=='og')
			{
				if(!isGravity($value))//cleaned by isGravity
				{
					jfhError(TRUE,"Invalid value for og!");
				}
			}
			elseif($data[0]=='fg')
			{
				if(!isGravity($value))//cleaned by isGravity
				{
					jfhError(TRUE,"Invalid value for fg!");
				}
			}
			elseif($data[0]=='comments')
			{
				$value=isNotEmpty($value);
			}
			else
			{
				jfh_error(true, "Unexpected input!" . $key);
			}
			$entries[$style][$data[0]]=$value;
		}
	}

	//we now have a two dimensional array containing style as key for an array of entry data
	$currentFullStyle='';
	$styleSql=<<<ENDSTYLESQL
	SELECT DISTINCT cs.style_id 
	FROM cb_styles cs, cb_comp_styles ccs
	WHERE cs.style_id = ccs.style_id
	AND cs.style=? 
	AND cs. sub_style=?
	AND ccs.comp_id =?
	LIMIT 1
ENDSTYLESQL;
	$insertSql=<<<ENDINSERTSQL
	INSERT INTO cb_entries VALUES(
	null, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)
ENDINSERTSQL;
	foreach($entries as $fullStyle => $cols)
	{
		//get the style id from the styles table
		if($fullStyle !=$currentFullStyle)
		{
				$currentFullStyle=$fullStyle;
				$cat=substr($currentFullStyle,0,-1);
				$sub_style=substr($currentFullStyle,-1);
				//TODOneed to add additional field to db for comp type? or join?
				if ($styleStmt = $mysqli->prepare($styleSql)) 
				{
					$styleStmt->bind_param('isi', $cat,$sub_style, $compId);
					$styleStmt->execute();
					$styleStmt->bind_result($styleId);
					if(!$styleStmt->fetch())
					{
						jfh_error(true, "The styles entered do not seem to be valid");
					}
					if($styleStmt){$styleStmt->close();}
				}
		}
			//now build the insert sql
			if ($insertStmt = $mysqli->prepare($insertSql)) 
				{
					//Hard coding no for return bottles at request of Billy comp 2011
					$insertStmt->bind_param('issssisssddsss', $cols['userId'], $cols['fullname'], $cols['email'], $cols['phone'],  $styleId, $cols['compId']  ,$cols['brewName'], $cols['brewMethod'],$cols['yeast'], $cols['og'], $cols['fg'], $cols['comments'],date('Y-m-d H:i:s', time()), $cols['returnBottles']);
					//$insertStmt->bind_param('issssisssddsss', $cols['userId'], $cols['fullname'], $cols['email'], $cols['phone'],  $styleId, $cols['compId']  ,$cols['brewName'], $cols['brewMethod'],$cols['yeast'], $cols['og'], $cols['fg'], $cols['comments'],date('Y-m-d H:i:s', time()), 'False');
					
					if(!$insertStmt->execute())
					{
						//check for unique constraint violation to customize message
						if ($inserStmt->errno = 1062)
						{
							jfh_error(true, "You are trying to enter the same beer twice. </br >If you want to do that give the beers different names" .$insertStmt->error);
						}	
						else
						{
							jfh_error(true, "There was a problem with the entry: " .$insertStmt->error);
						}
					}

					if($insertStmt){$insertStmt->close();}		
		}
	}
}

//Now requery to give the user back their details

if($userId == "-1")
{
	//check using email
	$checkSql=<<<ENDCHECKSQL
	SELECT cc.comp_name, ce.fullname, ce.entry_number, cs.sub_style_name, ce.brew_name
	FROM cb_styles cs, cb_comp_styles ccs, cb_entries ce, cb_competitions cc
	WHERE cs.style_id = ccs.style_id
	AND cs.style_id = ce.style_id
	AND cc.comp_id = ccs.comp_id
	AND ce.email = ?
	AND ccs.comp_id = ?
	AND ce.comp_id = ?
	AND ce.user_id = -1
ENDCHECKSQL;
}
else
{
//check using user id
	$checkSql=<<<ENDCHECKSQL
	SELECT cc.comp_name, ce.fullname, ce.entry_number, cs.sub_style_name, ce.brew_name
	FROM cb_styles cs, cb_comp_styles ccs, cb_entries ce, cb_competitions cc
	WHERE cs.style_id = ccs.style_id
	AND cs.style_id = ce.style_id
	AND cc.comp_id = ccs.comp_id
	AND ce.user_id = ?
	AND ccs.comp_id = ?
	AND ce.comp_id = ?
ENDCHECKSQL;
}

if($checkStmt = $mysqli->prepare($checkSql))
{
	if($userId == "-1")
    {
      //todo rework this and above if statement into one test
      //check we have a brewerEMail
      $checkEmail = empty($brewerEmail) ? $_POST['brewerEmailHidden'] : $brewerEmail;
      $checkStmt->bind_param('sii',  $checkEmail, $compId, $compId);  
    }
    else
    {
        $checkStmt->bind_param('iii', $userId, $compId, $compId);
    }
	$checkStmt->execute();
	$checkStmt->bind_result($comp_name, $entry_fullname, $entry_number, $style_sub_style, $entry_brew_name);
	while($checkResult=$checkStmt->fetch())
	{
		$details[]=array($comp_name, $entry_fullname, $entry_number, $style_sub_style, $entry_brew_name);
	}
	if($checkStmt)
	{
		$checkStmt->close();
	}
	echo renderEntries($details);
}
else
{
	jfh_error(TRUE, "Unable to retrieve your entry details:". $mysqli->error);
}

function renderEntries($details)
{
$html=<<<ENDHTML
<h4 class='centerIt'>%s</h4>
<table class='entries'>
<tr>
	<td class='labelTd'>Entry Number</td>
	<td class='labelTd'>Style</td>
	<td class='labelTd'>Brew Name</td>
</tr>
ENDHTML;
$descriptiveText=empty($details[0][1]) ? "You don't have any beers entered  yet". $details[0][0] : $details[0][1] . ' - You have the following beers entered for ' . $details[0][0];
$html=sprintf($html,$descriptiveText);

if ($details)
{
	foreach($details as $rows)
	{
		$html.=<<<ENDTABLEBODY
		<tr>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>	
		</tr>
ENDTABLEBODY;
	$html=sprintf($html, $rows[2], $rows[3], $rows[4]);
	}
}
$html.=<<<ENDFOOTER
</table>
<br />
<div class='centerIt'>
	<a href='/forum/comps/cb_entries.php' title='Return to Entry Form'>Return to Entry Form</a>
</div>
<br />
<div class='centerIt'>
	<a href='/forum/index.php?view=forum' title='Return to forum'>Return to forum</a>
</div>
</body>
</html>
ENDFOOTER;
return $html;
}
function isGravity($grav)
{
	$testGrav=str_replace(' ','',$grav);
	$pat = '/^\d{1}\.\d{3}$/';
	preg_match($pat, $testGrav, $hits);
	
	if( is_array($hits) && count($hits)!=0)
	{
		return $testGrav;
	}
	else
	{
		return false;
	}
}

function isIntAtLeast($testInt, $length)
{
	//For public use form:
    if($testInt == -1){return -1;}
    $testInt=str_replace(' ','',$testInt);
	$pat='/^[0-9]{' . $length . ',}$/';
	preg_match($pat, $testInt, $hits);
	if( is_array($hits) && count($hits)!=0)
	{
		return $testInt;
	}
	else
	{
		return false;
	}
}

function isNotEmpty($testData)
{
	global $mysqli;
	if($testData == '' || empty($testData) || str_replace(' ','',$testData)=='')
	{
		return false;
	}
	else
	{
		return $mysqli->escape_string(htmlspecialchars($testData));
	}
}
	
	

function jfh_error($close, $msg)
{
	global $mysqli;
	$msg=(!$msg || $msg=='') ? 'Unknown Error' : $msg;
	$html="<h2 class='errorMessage'>$msg<br /><br />Whinge to the <a href='mailto:webmaster@canberrabrewers.com.au' title='Send whingergram'>webminion</a></h2>";
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
	jfh_error(true,$msg.' '.$file.' '.$line);
}
?>