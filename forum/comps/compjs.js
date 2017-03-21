//comp js library

function pageInit()
{
	b = document.getElementById('initialForm');
	if(b)
    {
        b.reset();
    }
	
}
function toggleDisplay(style_checkbox)
{
		var styleId=style_checkbox.name.substring(style_checkbox.name.indexOf('_')+1);
		var style_table=document.getElementById(styleId);
		var submitImage=document.getElementById('submitImage');
		if (style_checkbox.checked)
		{
			style_table.style.display='';
			submitImage.style.display='';
		}
		else
		{
			style_table.style.display='none';
			//we have to loop to see if any styles remained checked before deciding
			//on display for submit image
			var oForm = document.getElementById('initialForm');
			var hideIt=true;
			for(i=0; i<oForm.length; i++)
			{
				if (oForm.elements[i].type=='checkbox' && oForm.elements[i].checked)
				{
					hideIt=false;
				}
			}
			if(hideIt)
			{
				submitImage.style.display='none';
			}
			
		}
}

function checkData()
{
	var oForm = document.getElementById('initialForm');
	var entered = new Array();
	//check brewer and comp fields
	var comp=document.getElementById('compId');
	if(!isNum(comp.value,1))
	{
		//this is a serious problem so alert straight away
		alert("Comp Id not found! This is not your fault - its the webminion's problem. \nContact him to resolve the issue with this form");
		return;
	}
	var errorText=''; //keep track of errors in a string for later output
	//now check name
	var brewer=document.getElementById('fullName');
	if(isEmpty(brewer.value))
	{
		errorText+='You must fill in the  Full Name field\n';
	}
	var email=document.getElementById('email');
	if(isEmpty(email.value))
	{
		errorText+='You must fill in the  E-mail field\n';
	}
	var phone=document.getElementById('phone');
	if(!isNum(phone.value,8))
	{
		errorText+='You must fill in the  Phone field with a valid number\n';
	}
	
    var returnBottles=document.getElementById('returnBottles');
    //Nothing to check for this check box
	for(i=0; i<oForm.length; i++)
	{
		if (oForm.elements[i].type=='checkbox' && oForm.elements[i].checked)
		{
			entered[entered.length]=oForm.elements[i];
		}
	}
	//loop to validate mathching inputs, build new form and submit.
	
	for(i=0; i<entered.length; i++)
	{
		var styleId=entered[i].name.substring(entered[i].name.indexOf('_')+1);
		//check brew name
		bname=document.getElementById('brewName_'+styleId);
		if (isEmpty(bname.value))
		{
			errorText+='The brew name for style ' + styleId + ' is empty - please fill it in\n';
		}
		//check yeast
		yname=document.getElementById('yeast_'+styleId);
		if (isEmpty(yname.value))
		{
			errorText+='The yeast for style ' + styleId + ' is empty - please fill it in\n';
		}
		//check og
		og=document.getElementById('og_'+styleId);
		var testOg=isGravity(og.value);
		if (testOg==false)
		{
			errorText+='The og for style ' + styleId + ' is not a valid og (decimal to 3 places) - please amend\n';
		}
		else
		{
			og.value=testOg;
		}
		//check fg
		fg=document.getElementById('fg_'+styleId);
		var testFg=isGravity(fg.value);
		if (testFg==false)
		{
			errorText+='The fg for style ' + styleId + ' is not a valid fg (decimal to 3 places) - please amend\n';
		}
		else
		{
			fg.value=testFg;
		}
	}
	//check user actually chose some styles
	if(entered.length==0)
	{
		errorText+='You have to enter at least one style!';
	}
	if(errorText.length==0)
	{
		//build our new form here
		var brewerInput=document.getElementById('brewerFullName');
		brewerInput.value=brewer.value;
		var brewerEmail = document.getElementById('brewerEmail');
		brewerEmail.value=email.value;
		var brewerPhone = document.getElementById('brewerPhone');
		brewerPhone.value= phone.value;
        var brewerReturnBottles = document.getElementById('brewerReturnBottles');
        //Hard coded to false at request of Billy for comp 3 2011
        //brewerReturnBottles.value = returnBottles.checked
        brewerReturnBottles.value = false;
		//now create style related inputs dynamically
		oRealForm=document.getElementById('realForm');

		for (var j=0; j<entered.length; j++)
		{
			//TODO - could use clone here ?
			var styleId=entered[j].name.substring(entered[j].name.indexOf('_')+1);

			//brewMethod
			var tmpMethod=document.createElement('input');
			tmpMethod.setAttribute('type','hidden');
			tmpMethod.setAttribute('name', 'brewMethod_' + styleId);
			tmpMethod.setAttribute('value', document.getElementById('brewMethod_' + styleId).value);
			oRealForm.appendChild(tmpMethod);
			//brewName
			var tmpBrewName=document.createElement('input');
			tmpBrewName.setAttribute('type','hidden');
			tmpBrewName.setAttribute('name', 'brewName_' + styleId);
			tmpBrewName.setAttribute('value', document.getElementById('brewName_' + styleId).value);
			oRealForm.appendChild(tmpBrewName);
			//og
			var tmpOg=document.createElement('input');
			tmpOg.setAttribute('type','hidden');
			tmpOg.setAttribute('name', 'og_' + styleId);
			tmpOg.setAttribute('value', document.getElementById('og_' + styleId).value);
			oRealForm.appendChild(tmpOg);
			//fg
			var tmpFg=document.createElement('input');
			tmpFg.setAttribute('type','hidden');
			tmpFg.setAttribute('name', 'fg_' + styleId);
			tmpFg.setAttribute('value', document.getElementById('fg_' + styleId).value);
			oRealForm.appendChild(tmpFg);
			//yeast
			var tmpYeast=document.createElement('input');
			tmpYeast.setAttribute('type','hidden');
			tmpYeast.setAttribute('name', 'yeast_' + styleId);
			tmpYeast.setAttribute('value', document.getElementById('yeast_' + styleId).value);
			oRealForm.appendChild(tmpYeast);
			//comments
			
			var tmpComments=document.createElement('input');
			tmpComments.setAttribute('type','hidden');
			tmpComments.setAttribute('name', 'comments_' + styleId);
			tmpComments.setAttribute('value', document.getElementById('comments_' + styleId).value);
			oRealForm.appendChild(tmpComments);
			
		}
		
		//now submit form
		oRealForm.submit();

		
	}
	else
	{
		
		alert('There were some problems with your entry:\n\n' + errorText +'\n');
	}
}

function isGravity(sGrav)
{
	testGrav=stripSpaces(sGrav);
	var re = /^\d{1}\.\d{3}$/;
	if(testGrav.match(re))
	{
		return testGrav;
	}
	else
	{
		return false;
	}
}

function isNum(sNum,dMinLength)
{
	var testNum=stripSpaces(sNum);
	var sRe = "^[0-9]{" + dMinLength +",}$";
	if (testNum.match(sRe))
	{
		return true;
	}
	else
	{
		return false;
	}
	
	
	
}

function isEmpty(sText)
{
	testString=stripSpaces(sText);
	if(testString == null || testString.length == 0)
	{
		return true;
	}
	return false;
	
}

function stripSpaces(sText)
{
	var re = / /g;
	returnString= sText.replace(re, '');
	return returnString;
}

function checkForEmail()
{
//For non members they must provide an email to check entries
    var email=document.getElementById('email');
    var userId=document.getElementById('userId');
    if(isEmpty(email.value) && userId.value == "-1")
    {
        alert("You must fill in the  E-mail field to retrieve your previous entries\n");
        return false;
    }
    else
    {
        //set the hidden email field
        document.getElementById('brewerEmailHidden').value = email.value;
        document.getElementById('check_only_form').submit;
    }


}