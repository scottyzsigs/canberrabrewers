<?php
//include for db connectivity
require('/home/canber10/public_html/cbadmin/web_incs/web_db.inc');
error_reporting(E_ALL ^E_NOTICE);
set_error_handler('jfh_handle_error',E_ALL & ~NOTICE );
$header_section=<<<END
<script type="text/javascript"
src="%s">
</script>
<script>
pageInit();
</script>

END;
echo sprintf($header_section,'comps/compjs.js');

//check we have a db connection
if (!$mysqli)
{
jfh_error(true,"Unable to connect to the db!");	
	
}
//get sessId and userId from cookie or request
require('/home/canber10/public_html/cbadmin/web_incs/forum_auth.php');

//If we have no user_id bad! if no sessId no big deal
if($_SESSION['publicUser'] &&  $_SESSION['user_id'] == "-1")
{
  #do nothing - revisiting authenticated public user
}
elseif($user_id == "1" || (empty($user_id) && (empty($_SESSION['publicUser']) || $_SESSION['publicUser'] == false)))
{
	//check to see if this is a successful beerAuth attempt
    $sent = empty($_POST['erty'])? null: $_POST['erty'];
    $required = empty($_SESSION['order']) ? null : $_SESSION['order'];
    if(is_array($sent) && is_array($required))
    {
        sort($sent);
        sort($required);
        if($sent == $required)
        {   
            //Set session auth here
            $_SESSION['publicUser'] = true;
            $_SESSION['user_id'] = "-1";
            $user_id = "-1";
        }
        else
        {
            render_beer_auth();
            exit;
        }
    }
    else
    {
        render_beer_auth();
        exit;
    }
}
else if(empty($user_id) && $_SESSION['publicUser'] == true)
{

    $user_id = $_SESSSION['user_id'];
}

//now get the current comp
$comp_sql=<<<ENDCOMPSQL
SELECT comp_id, comp_name, username comp_organizer_name, DATE_FORMAT(comp_date,'%D %b %Y') comp_date, comp_location, comp_styles, comp_organizer, DATE_FORMAT(comp_cutoff, '%D %b %Y') comp_cutoff
FROM cb_competitions, forumv3_users
WHERE user_id = comp_organizer 
AND comp_status='OPEN' 
ENDCOMPSQL;
$comp_result=$mysqli->query($comp_sql);
//TODO: for now assume one comp open at a time  - need to handle mujfh_error(true, "There are no competitions open at the moment"); lti comp situation
if(!$comp_result || $comp_result->num_rows > 1)
{
	jfh_error(true, "More than one comp open and this script can't handle that yet"); //TODO change msg when fixed
}
elseif ($comp_result->num_rows == 0 )
{
	jfh_error(true, "There are no Competitions open at the moment."); 
}
//We have one comp 
$comp_details=$comp_result->fetch_assoc();
if($comp_result){$comp_result->free();}
//pass to render_comp function
$comp_span=render_comp($comp_details);
//We have a session id so try and find the user's details
//note pu.username is temporarily unavailable
$user_sql=<<<ENDUSERSQL
SELECT DISTINCT pu.user_id, pu.username, IFNULL(cbe.fullname, null) user_fullname, pu.user_avatar, IFNULL(cbe.email, pu.user_email) user_email, IFNULL(cbe.phone, '') user_phone
FROM forumv3_users pu
LEFT JOIN cb_entries cbe
ON pu.user_id= cbe.user_id
WHERE pu.user_id ='%s'
ORDER BY cbe.date DESC LIMIT 1
ENDUSERSQL;
$user_sql=sprintf($user_sql,$user_id);
$user_result=$mysqli->query($user_sql);
if((!$user_result || $user_result->num_rows!=1) && $user_id != -1)
{
	jfh_error(true, "Could not find the user for this session");
}
//we should have a single row to retrieve but check for non-members
if($user_id == -1)
{
    $user_details = array("userId" => "-1"); //pass an empty array to render function
}
else
{
    
    $user_details = $user_result->fetch_assoc();
    if($user_result) {$user_result->free();}
}


//pass details to render_user function
$user_span=render_user($user_details);

//Now get styles for this comp

$styles_sql=<<<ENDSTYLESSQL
SELECT cs.style_id, style, style_name, sub_style, sub_style_name 
FROM cb_styles cs, cb_competitions cc, cb_comp_styles ccs
WHERE cs.style_id = ccs.style_id 
AND cc.comp_id = ccs.comp_id
AND cc.comp_id =
ENDSTYLESSQL;
$styles_sql .= $comp_details['comp_id'];
$styles_sql .= ' order by style asc, sub_style asc';
$styles_result=$mysqli->query($styles_sql);
if(!$styles_result || $styles_result->num_rows==0)
{
	jfh_error(true, "Could not find any styles for this competition");
}

//we will have more than one style (hopefully!) so loop for each
$checkOnly=sprintf($checkOnly,$comp_details['comp_id']);
$styles_list="<table class='stylesList'><tr><td class='titleTd' colspan='%s'>Tick the styles you wish to enter and fill in the details for each style:";
$styles_list.="<br />Note: Brewers can enter up to two beers per style category, but not two beers into the same style.</td>";
$styles_buf='';
$current_style = '';
$styles_html='';
$styles_count=$styles_result->num_rows;
//TODO: Move to config:
$styles_table_width = 4;
$count=0;
$current_style = '';
while ($styles_details=$styles_result->fetch_assoc())
{
		$style = $styles_details['style'];
		$style_name =$styles_details['style_name'];
		$sub_style = strtolower($styles_details['sub_style']);
		$sub_style_name = $styles_details['sub_style_name'];
		//shorthand for combined style:
		$full_style= $style . $sub_style;
		if($current_style != $styles_details['style'])
		{
			if($count%$styles_table_width != 0)
			{
				//Pad with empty cells
				$remaining= $styles_table_width -($count%$styles_table_width);
				$styles_buf .= str_repeat('<td>&nbsp;</td>',$remaining*2);
			}
			//new style so create separator
			$styles_buf .= "<tr><td " . ($styles_table_width * 2) . ">&nbsp;</td></tr>" ;
			$styles_buf .= "<tr><td class='styleHeading' colspan= '" . ($styles_table_width * 2) . "'>$style. $style_name</td></tr>";
			$current_style = $style;
			$count = 0;
		}	
		
		if($count%$styles_table_width == 0 || $count==0)
		{
			//start a new column
			$styles_buf.="</tr><tr>";
			$count = 0;
		}
		
		$styles_buf .="<td>&nbsp;<input type='checkbox' id='check_".$full_style ."' name='check_".$full_style ."' onclick=\"toggleDisplay(this)\" /><label for='check_".$full_style ."'>".$sub_style_name."</label></td>"; 
		$this_style_html=render_styles($styles_details);
		$styles_html.=$this_style_html;
		$count++;
}	
//add any missing cells to styles_list
if($styles_count > $styles_table_width && $count%$styles_table_width != 0)
{
	$remaining= $styles_table_width -($count%$styles_table_width);
	$styles_buf .= str_repeat('<td>&nbsp;</td>',$remaining*2);
}

$styles_list = sprintf($styles_list, $styles_count*2) . $styles_buf ."</tr></table>";
//Put it all together
$start_page=<<<ENDSTARTPAGE
<br />
%s 
<br />
%s
<br />
<form id='check_only_form' name='check_only_form' method='POST' action='comps/submit_entries_new.php' >
<input  type='hidden' name='compId' value='%s' />
<input  type='hidden' name='userId' value='%s' />
<input type='hidden' id='brewerEmailHidden' name='brewerEmailHidden' />
<input  type='hidden' name='check_only' value='true' />
<div class='centerIt'><input  type='submit' name='submit' value="Check what I've already entered" title='Check current entries only' onclick = 'return checkForEmail()' /></div>
</form>
<form id='initialForm' name='initialForm' method='POST' action=''>
ENDSTARTPAGE;
printf($start_page,$comp_span,$user_span,$comp_details['comp_id'],$user_id);
echo $checkOnly;
echo $styles_list;
echo $styles_html;
$end_page=<<<ENDPAGE
</form>
<div class='centerIt' >
<input style='display: none; padding:5px;' id='submitImage' type='button' onclick='checkData()' value='Submit your entry' class='button2'/>

</div>
<form style='display: none' id='realForm' name='realForm' method='POST' action='submit_entries.php'>
<input type='hidden' name='compId' value='%s' id = 'compId' />
<input type='hidden' name='userId' value='%s' id = 'userId' />
<input type='hidden' id='brewerFullName' name='brewerFullName' value='' />
<input type='hidden' id='brewerEmail' name='brewerEmail' value='' />
<input type='hidden' id='brewerPhone' name='brewerPhone' value='' />
<input type='hidden' id='brewerReturnBottles' name='brewerReturnBottles' value='' />
</form>
<br />
</body>
</html>
ENDPAGE;
printf($end_page, $comp_details['comp_id'], $user_id);

//Function definitions from here
function render_comp($details)
{
//should have a details array to process TODO - Check we do!
$html=<<<ENDCOMPHTML
<table class='compDetails' >
	<tr>
		<td class='titleTd' colspan=4>
			Competiton
		</td>
	</tr>
	<tr>
		<td class='labelTd'>
			Competition Name:
		</td>
		<td>
				%s
		</td>
		<td class='labelTd'>
			Organizer:
		</td>
		<td>
			%s&nbsp<a class='smallLink' href='http://www.canberrabrewers.com.au/forum/memberlist.php?mode=viewprofile&u=%s' title="view organizer's profile" target='_blank'>Contact Details</a>
		</td>
	</tr>
	<tr>
		<td class='labelTd'>
			Judging Date:
		</td>
		<td>
			%s
		</td>
		<td class='labelTd'>
			Venue:
		</td>
		<td d='venueTd' >
			%s&nbsp<a class='smallLink' href='http://goo.gl/T5UQh7' title="View Comp Location" target='_blank'>Google Map</a>
		</td>
	</tr>
	<tr>
		<td class='labelTd'>
			Entries in by:
		</td>
		<td>
			%s
		</td>
		<td>
		</td>
		
	</tr>
</table>
ENDCOMPHTML;
return sprintf($html,$details['comp_name'],$details['comp_organizer_name'],$details['comp_organizer'],$details['comp_date'],$details['comp_location'],$details['comp_cutoff']);
}


function render_user($details)
{
	global $sessId;
//should have a details array to process TODO - Check we do!
$html=<<<ENDUSERHTML
<table class='userDetails' >
<tr>
		<td class='titleTd' colspan=4>
			Brewer
		</td>
	</tr>
	<tr>
		<td class='labelTd'>
			<label for='fullName'>Full name:</label>
		</td>
		<td>
			<input type='text' name='fullName' id='fullName' value='%s' />
		</td>
		<td class='labelTd'>
			<label for='forumName'>Forum name:</label>
		</td>
		<td>
				%s <a class='smallLink' href='http://www.canberrabrewers.com.au/forum/ucp.php?mode=logout&sid=%s' title='Not you? Click here'>&nbsp;Not you?</a>
		</td>
	</tr>
	<tr>
		<td class='labelTd'>
		<label for='email'>E-mail:</label>
		</td>
		<td>
		<input type='text' name='email' id='email' value='%s' />
		</td>
		<td class='labelTd'>
		<label for='phone'>Phone:</label>
		</td>
		<td>
		<input type='text' name='Phone' id='phone' value='%s'/>
		</td>
	</tr>
	<!-- commented out for comp 3 2011 at Billy's request 
	<tr>
        <td class='labelTd'>
        
        <label for='returnBottles'>I want my bottles back:</label>
        </td>
		<td>
        <input type='checkbox' id = 'returnBottles' name='returnBottles'  />   
		</td>
        <td>
        </td>
        <td>
        </td>

	</tr>
	-->
</table>
</td>
ENDUSERHTML;
return sprintf($html, $details['user_fullname'],$details['username'],$sessId,$details['user_email'],$details['user_phone']);
}

function render_styles($details)
{
//should have a details array to process TODO - Check we do!
$styles_html=<<<ENDSTYLESHTML
<table id='%s' style='display: none' >
	<tr>
		<td class='labelTd'>
			<label for='style'>%s</label>
		</td>
		<td>
			&nbsp;
		</td>
		<td class='labelTd'>
			<label for='brewName'>Brew Name:</label>
		</td>
		<td>
			<input id='brewName_%s' type='text' name='brewName_%s' title='Enter a name for your brew' />
		</td>
	</tr>
	<tr>
		<td class='labelTd'>
			<label for='brewMethod'>Method:</label>
		</td>
		<td> 
			<select id='brewMethod_%s' name='brewMethod_%s' title='Choose the brewing method'>
				<option value='All Grain'>All Grain</option>
				<option value='Mini Mash'>Mini Mash</option>
				<option value='Kit'>Kit</option>
			</select>
		</td>
		<td class='labelTd'>
			<label for='yeast'>Yeast:</label>
		</td>
		<td>
			<input id='yeast_%s' type='text' name='yeast_%s' title='The main yeast used for this brew' />
		</td>
	</tr>
	<tr>
		<td class='labelTd'>
			<label for='og'>OG:</label>
		</td>
		<td>
			<input id='og_%s' type='text' name='og_%s' title='The original gravity of this brew' />
		</td>
		<td class='labelTd'>
			<label for='fg'>FG:</label>
		</td>
		<td>
			<input id='fg_%s' type='text' name='fg_%s' title='The final gravity of this brew' />
		</td>
	</tr>
	<tr>
		<td class='labelTd'>
			<label for='comments_%s'>Comments</label>
		</td>
		<td colspan=3>
			<textarea id='comments_%s' name='comments_%s' title='Enter any additional comments - required for speciality brews'  cols=90 rows=4></textarea>
		</td>
	</tr>
	<tr>
		<td colspan='4'>
			<a class='topLink' href='#top' title='Go to top of screen'>Top</a>
		</td>
	</tr>
</table>
ENDSTYLESHTML;
//TODO This is untidy - sort it!
$full_style=$details['style']. strtolower($details['sub_style']);
//$full_style=$details['style'].$details['sub_style'];
$dotted_style=$details['style']. "." .$details['sub_style'];
$style_text="<a name='sub_style";
$style_text.=$full_style;
//$style_text.="' href='http://www.bjcp.org/2008styles/style";
$style_text.="' href='http://www.canberrabrewers.com.au/entries/AABCStyles";

$style_text.= ".htm#" .$full_style . "' title='Look up Style details' target='_blank'>" .$dotted_style.":&nbsp;" .$details['sub_style_name']."</a>";
//$style_text.= $full_style . "' title='Look up Style details' target='_blank'>" .$full_style.":&nbsp;" .$details['sub_style_name']."</a>";
//TODO: Is there an easier way to do this - maybe using str_repeat?	
$styles_html=sprintf($styles_html, $full_style,$style_text,$full_style,$full_style, $full_style, $full_style, $full_style, $full_style, $full_style,$full_style,$full_style, $full_style, $full_style, $full_style, $full_style,$full_style,$full_style, $full_style, $full_style, $full_style, $full_style,$full_style, $full_style);
return $styles_html;
}

function jfh_error($close, $msg)
{
	global $mysqli;
	$msg=(!$msg || $msg=='') ? 'Unknown Error' : $msg;
	$html="<h2 class='errorMessage'>$msg<br /><br />Whinge to the <a href='mailto:webmaster@canberrabrewers.com.au' alt='Send whingergram'>webmaster</a>.</h2>";
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

function render_beer_auth()
{
    $needed = 8;
    $beerMax=3;
    $nonBeer = array("one.jpg", "two.jpg","three.jpg","four.jpg","five.jpg","six.jpg","seven.jpg","eight.jpg","nine.jpg","ten.jpg");
    $beer = array("hops.jpg","grain.jpg","fermenter.jpg","guinness.jpg","leffe.jpg");
    //pick the beer relate
    $beerCount = rand(0,$beerMax -1);
    $selected = array();
    for($i = 0 ; $i <= $beerCount; $i++)
    {
        $img = array_splice($beer, rand(0, count($beer) -1),1);
        array_push($selected,$img[0]);
    }
    //Set in session here 
    $_SESSION['order'] = $selected;
   // var_dump($selected);
    $remaining = $needed - count($selected);
    for($j = 0 ; $j <= $remaining; $j++)
    {
        $img = array_splice($nonBeer, rand(0, count($nonBeer) -1),1);
        array_push($selected, $img[0]);
    
    }
    function random_sort($a, $b)
    {
        $a = rand(0,1);
        $b = rand(0,1);
        if ($a == $b) {
            return 0;
        }
        return ($a < $b) ? -1 : 1;
    }
    usort($selected, "random_sort");
    
    
    
    
    $count = 1;
    $imageGroup = "<tr>";
    $rowOne = "";
    $rowTwo = "";
    foreach($selected as $value)
    {
        
        $buff1=<<<ENDLINEONE
        <td>
        <label for="%s">
        <img src="images/%s" title="Click to select" height=100px></img>
        </label>
        </td>
ENDLINEONE;
        $buff2=<<<ENDLINETWO
        <td>
            <input type="checkbox" id ="%s" name = 'erty[]' value="%s"/>
        </td>
ENDLINETWO;
        $rowOne .= sprintf($buff1, $value, $value);
        $rowTwo .= sprintf($buff2, $value, $value);
        if($count%3==0)
        {
            //wrap a rowCount
            $rowOne.="</tr><tr>";
            $rowTwo.="</tr><tr>";
            $imageGroup.=$rowOne.$rowTwo;
            $rowOne="";
            $rowTwo="";
        }
        $count++;
    }
    //remove trailing <tr>
    $imageGroup = substr($imageGroup,0, -4);
    echo "<form name=\"qwekd\" method=\"POST\" action=\"cb_entries.php\">";
    echo "<div id=\"imageDiv\"><table id=\"imageTable\">$imageGroup</table></div>";
    $buttonString=<<<ENDBUTTON
    <div id="submit">
    <div>This is a simple test to see if you are a real person or a <a href="http://en.wikipedia.org/wiki/Spambot#Forum_spambots" target="_blank">spam bot</a></div>
    <div>Simply tick the images which are beer related (i.e. beer glasses, equipment, ingredientes etc.) then click Enter</div>
    <input  type = "submit" name="Enter" value="Enter" />
    </div>
    </form>
ENDBUTTON;
    echo $buttonString;
}


?>

