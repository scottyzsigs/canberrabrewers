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
<script type="text/javascript"
src="%s">
</script>
</head>
<a  name='top'></a>
<body>
<div class='centerIt'><img src="http://www.canberrabrewers.com.au/forum/styles/rockn_roll/imageset/header.jpg" border="0" alt="Canberra Brewers Club" /></div>
END;
echo sprintf($header_section,'09-02-2009','09-02-2009','gPHPEdit','Cb entry form','Competition Entry Form','compstyles.css','');

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
if(!$comp_result || $comp_result->num_rows > 1)
{
	jfh_error(true, "More than one comp open and this script can't handle that yet"); //TODO change msg when fixed
}
elseif ($comp_result->num_rows == 0)
{
	jfh_error(true, "There are no competitions open at the moment"); 
}
//We have one comp 
$comp_details=$comp_result->fetch_assoc();
$compId=$comp_details['comp_id'];
if($comp_result){$comp_result->free();}
//#####remove after testing##############################################
//(UserID 127 = Jamie; 297 = Daff; Mal = 387; Billy = 208; Lawfo = 410, Scotty = 401)
$valid_users = array(401,529);

if(!in_array($user_id,$valid_users))
{
	jfh_error(TRUE, "You are not the Comp Sec so you can't see this page");
}

//###################################################

//Get the summary of entries thus far
$summary_sql=<<<ENDSUMMARYSQL
SELECT  cs.sub_style_name, count( ce.entry_number ) entered
FROM cb_styles cs
LEFT JOIN cb_entries ce ON ce.style_id = cs.style_id
JOIN (cb_comp_styles ccs, cb_competitions cc)
ON ccs.style_id = cs.style_id
AND ccs.comp_id = cc.comp_id
AND ce.comp_id = cc.comp_id
AND cc.comp_id = ?
GROUP BY cs.sub_style_name
ORDER BY entered DESC, cs.sub_style_name ASC
ENDSUMMARYSQL;
if($summaryStmt = $mysqli->prepare($summary_sql))
{
	$summaryStmt->bind_param('i', $compId);
	$summaryStmt->execute();
	$summaryStmt->bind_result($style_name, $style_count);
	while($summaryResult=$summaryStmt->fetch())
	{
		$details[]=array($style_name, $style_count);
	}
	if($summaryStmt){$summaryStmt->close();}
	echo renderSummary($details);
	
}
else
{
	jfh_error(TRUE, "Unable to retrieve your entry details:". $mysqli->error);
}



//pass to render summary
function renderSummary($details)
{
$html=<<<ENDHTML
<h4 class='centerIt'>%s</h4>
<table class='summary'>
<tr>
	<td class='labelTd'>Sub Style</td>
	<td class='labelTd'>Total Entries</td>
ENDHTML;
$beer_count=0;
foreach($details as $beers)
{
	$beer_count+=$beers[1];
}
if ($beer_count ==0)
{
	$descriptiveText="There are no beers entered yet";
}
else
{
	$descriptiveText='There are ' . $beer_count .' beers entered so far<br />Click <a href="get_comp_entries.php" title="Download as csv file" >here</a> to download as a spreadsheet';
}
$html=sprintf($html,$descriptiveText);

foreach($details as $rows)
{
	$html.=<<<ENDTABLEBODY
<tr>
	<td>%s</td>
	<td class='%s'>%s</td>
</tr>
ENDTABLEBODY;
//work out if bold or not
if($rows[1]==0)
{
	$class_name='';
}
else
{
	$class_name='labelTd';
}
$html=sprintf($html, $rows[0],$class_name, $rows[1]);
}
$html.="
<table/>
";
return $html;
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
