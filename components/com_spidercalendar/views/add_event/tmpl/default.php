<?php
 
 /**
 * @package Spider Calendar lite
 * @author Web-Dorado
 * @copyright (C) 2011 Web-Dorado. All rights reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 **/
 

defined( '_JEXEC' ) or die( 'Restricted access' );
$task=JRequest::getVar('task');
switch($task)
{
case 'save':
saveNote();
break;
}

$session = JFactory::getSession();
		$document = JFactory::getDocument();
   $document->addScript("includes/js/joomla.javascript.js");
   JHTML::_('behavior.tooltip');	
		JHTML::_('behavior.calendar');
$editor	= JFactory::getEditor();
		$user = JFactory::getUser();
$lists=$this->lists;

$module_id=JRequest::getVar( "module_id");
$calendar_id=JRequest::getVar( "calendar");
$calendar = JTable::getInstance('spidercalendar_calendar', 'Table');
	// load the row from the db table
$calendar->load( $calendar_id);
$allow_publish=$calendar->allow_publish;
jimport('joomla.form.form');
$form = JForm::getInstance('naForm',dirname(__FILE__).DS."datefields.xml");


$mainframe = JFactory::getApplication();

$db		= JFactory::getDBO();
$query = "SELECT * FROM #__spidercalendar_calendar where id=".$calendar_id."";
	$db->setQuery( $query );
	
	$cal = $db->loadObjectList();


$user = JFactory::getUser();

$userGroups = $user->get('groups');


$access=explode(',',$cal[0]->gid);
$userGroups=array_merge(array(),$userGroups);
if(!$userGroups)
$userGroups=array(1);
echo '<div>';
foreach($userGroups as $key=>$value)
{

if (!in_array($userGroups[$key],$access) ){
	$mainframe->redirect(JURI::root(),'access denied','error');

break;
}
}


		?>
window.addEvent('domready', function() {Calendar.setup({
				inputField: "date",
				ifFormat: "%Y-%m-%d",
				button: "date_img",
				align: "Tl",
				singleClick: true,
				firstDay: 0
				});});
				
				window.addEvent('domready', function() {Calendar.setup({
				inputField: "de_d",
				ifFormat: "%Y-%m-%d",
				button: "de_d_img",
				align: "Tl",
				singleClick: true,
				firstDay: 0
				});});
		<!--
		function submitbutton(pressbutton)
		{
			var form = document.adminForm;
			{
				submitform( pressbutton );
				return;
			}
		if(form.date.value.search(/^[0-9]{4}\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])/))
		{
			  alert('Invalid Date');
		}
		else
		if(form.selhour_from.value=="" && form.selminute_from.value=="" && form.selhour_to.value=="" && form.selminute_to.value=="")
			submitform( pressbutton );
			else
			if(form.selhour_from.value!="" && form.selminute_from.value!="" && form.selhour_to.value=="" && form.selminute_to.value=="")
				submitform( pressbutton );
				else
				if(form.selhour_from.value!="" && form.selminute_from.value!="" && form.selhour_to.value!="" && form.selminute_to.value!="")
					submitform( pressbutton );
					else
					alert('Invalid Time');
		}
		function checkhour(id,event)
			{	
				if(typeof(event)!='undefined')
				{
					var e = event; // for trans-browser compatibility
					var charCode = e.which || e.keyCode;
		
						if (charCode > 31 && (charCode < 48 || charCode > 57))
						return false;
		
						hour=""+document.getElementById(id).value+String.fromCharCode(e.charCode);
						hour=parseFloat(hour);
						if((hour<0) || (hour>23))
						return false;
				}
						return true;
			}
		
		function check12hour(id,event)
			{	

				if(typeof(event)!='undefined')
				{
				
					var e = event; // for trans-browser compatibility
					var charCode = e.which || e.keyCode;
						input=document.getElementById(id);
		
		 
						if(charCode==48 && input.value.length==0)
						return false;
						
						if (charCode > 31 && (charCode < 48 || charCode > 57))
						return false;
		
						hour=""+document.getElementById(id).value+String.fromCharCode(e.charCode);
						hour=parseFloat(hour);
						if(document.getSelection()!='')
						return true;
						
						if((hour<0) || (hour>12))
						return false;
				}
						return true;
			}
		
		
		function checknumber(id,event)
			{	
				if(typeof(event)!='undefined')
				{
					var e = event; // for trans-browser compatibility
					var charCode = e.which || e.keyCode;
		
						if (charCode > 31 && (charCode < 48 || charCode > 57))
						return false;
		
				}
						return true;
			}
		
		
		function checkminute(id,event)
			{	
			if(typeof(event)!='undefined')
				{
				var e = event; // for trans-browser compatibility
				var charCode = e.which || e.keyCode;
		
				if (charCode > 31 && (charCode < 48 || charCode > 57))
				return false;
		
					minute=""+document.getElementById(id).value+String.fromCharCode(e.charCode);
					minute=parseFloat(minute);
					if((minute<0) || (minute>59))
				return false;
				}
						return true;
			}		
		function add_0(id)
		{
		
		    input=document.getElementById(id);
		
		    if(input.value.length==1)
		
		    {
		
			input.value='0'+input.value;
		
			input.setAttribute("value", input.value);
		
		    }
		
		}
	

		
		function change_type(type)
{
	if(document.getElementById('daily1').value=='')
		document.getElementById('daily1').value=1;
	else
		document.getElementById('repeat_input').removeAttribute('style');
	
	if(document.getElementById('weekly1').value=='')
		document.getElementById('weekly1').value=1;
	
	if(document.getElementById('monthly1').value=='')
		document.getElementById('monthly1').value=1;
	
	if(document.getElementById('yearly1').value=='')
		document.getElementById('yearly1').value=1;
		
		
	
			
	switch(type)
{
	
	
	case 'no_repeat':	
document.getElementById('daily').setAttribute('style','display:none');
document.getElementById('weekly').setAttribute('style','display:none');
document.getElementById('monthly').setAttribute('style','display:none');
document.getElementById('year_month').setAttribute('style','display:none');
//document.getElementById('repeat_input').value=1;
document.getElementById('month').value='';
document.getElementById('date_end').value=''

document.getElementById('repeat_until').setAttribute('style','display:none');
break;

	case 'daily':	
document.getElementById('daily').removeAttribute('style');
document.getElementById('weekly').setAttribute('style','display:none');
document.getElementById('monthly').setAttribute('style','display:none');
document.getElementById('repeat').innerHTML='Day(s)'
document.getElementById('repeat_input').value=document.getElementById('daily1').value;
document.getElementById('month').value='';
document.getElementById('year_month').setAttribute('style','display:none');
document.getElementById('repeat_until').removeAttribute('style');
document.getElementById('repeat_input').onchange=function onchange(event) {return input_value('daily1')};


break;

case 'weekly':	
document.getElementById('daily').removeAttribute('style');
document.getElementById('weekly').removeAttribute('style');
document.getElementById('monthly').setAttribute('style','display:none');
document.getElementById('repeat').innerHTML='Week(s) on :'
document.getElementById('repeat_input').value=document.getElementById('weekly1').value;
document.getElementById('month').value='';
document.getElementById('year_month').setAttribute('style','display:none');
document.getElementById('repeat_until').removeAttribute('style');
document.getElementById('repeat_input').onchange=function onchange(event) {return input_value('weekly1')};

break;

case 'monthly':	
document.getElementById('daily').removeAttribute('style');
document.getElementById('weekly').setAttribute('style','display:none');
document.getElementById('monthly').removeAttribute('style');
document.getElementById('repeat').innerHTML='Month(s)'
document.getElementById('repeat_input').value=document.getElementById('monthly1').value;
document.getElementById('month').value='';
document.getElementById('year_month').setAttribute('style','display:none');
document.getElementById('repeat_until').removeAttribute('style');
document.getElementById('repeat_input').onchange=function onchange(event) {return input_value('monthly1')};

break;

case 'yearly':	
document.getElementById('daily').removeAttribute('style');
document.getElementById('year_month').removeAttribute('style');
document.getElementById('weekly').setAttribute('style','display:none');
document.getElementById('monthly').removeAttribute('style');
document.getElementById('repeat').innerHTML='Year(s) in '
document.getElementById('repeat_input').value=document.getElementById('yearly1').value;
document.getElementById('month').value='';
document.getElementById('repeat_until').removeAttribute('style');
document.getElementById('repeat_input').onchange=function onchange(event) {return input_value('yearly1')};

break;

	
	
}
		
}

function week_value()
{
var value='';
for(i=1; i<=7; i++)
{

if (document.getElementById('week_'+i).checked)
{
	value=value+document.getElementById('week_'+i).value+',';
	
}
	
}
document.getElementById('week').value=value;



}


	

function input_repeat()
{
	if(document.getElementById('repeat_input').value==1)
	{
	document.getElementById('repeat_input').value='';
	
	}
	document.getElementById('repeat_input').removeAttribute('style');
	
}
		
function radio_month()
{
	if(document.getElementById('radio1').checked==true)
		{	
		document.getElementById('monthly_list').disabled=true;
		document.getElementById('month_week').disabled=true;
		document.getElementById('month').disabled=false;
		}
	else
	{
	document.getElementById('month').disabled=true;
	document.getElementById('monthly_list').disabled=false;
		document.getElementById('month_week').disabled=false;
	}
	

}
	
	
	function input_value(id)
	
{
	document.getElementById(id).value=document.getElementById('repeat_input').value;
}
		
		//-->

		</script>        
        <script src="http://code.jquery.com/jquery-1.9.1.js"></script> 
<style>
#admintable_id table , td, tr
{
border:0px;
}
.but
{
border: none;
height: 30px;
background-color: #1d4284;
color: #fffefe;
font-weight:bold;
}

.but:hover
{
border: none;
height: 30px;
background-color: #18376d;
color: #fffefe;
font-weight:bold;
}

.cancel
{
border: none;
height: 30px;
background-color: #c4c4c4;
color: #555555;
font-weight:bold;
}

.cancel:hover
{
border: none;
height: 30px;
background-color: #9d9d9d;
color: #555555;
font-weight:bold;
}

.cal_input
{
border: none;
background-color: #EBEBEB;
height: 25px;
}

#text_for_date_ifr .mceContentBody 
{
background-color: #EBEBEB;
}

input[type="checkbox"] {
    display:none;
}

input[type="checkbox"] + label {
    color:#707070;
    font-family:Arial, sans-serif;
    font-size:14px;
}

input[type="checkbox"] + label span {
    display:inline-block;
    width:14px;
    height:14px;
    margin:-1px 4px 0 0;
    vertical-align:middle;
    background:url(<?php echo JURI::root(true) ?>/administrator/components/com_spidercalendar/elements/check-box.png) left top no-repeat;
    cursor:pointer;
}

input[type="checkbox"]:checked + label span {
    background:url(<?php echo JURI::root(true) ?>/administrator/components/com_spidercalendar/elements/check-box.png) -17px top no-repeat;
}

input[type="radio"] {
    display:none;
}

input[type="radio"] + label {
    color:#707070;
    font-family:Arial, sans-serif;
    font-size:14px;
}

input[type="radio"] + label span {
    display:inline-block;
    width:14px;
    height:14px;
    margin:-1px 4px 0 0;
    vertical-align:middle;
    background:url(<?php echo JURI::root(true) ?>/administrator/components/com_spidercalendar/elements/check-box.png) -33px top no-repeat;
    cursor:pointer;
}

input[type="radio"]:checked + label span {
    background:url(<?php echo JURI::root(true) ?>/administrator/components/com_spidercalendar/elements/check-box.png) -50px top no-repeat;
}

</style>	


	
      <form  action="" method="post" name="adminForm" id="adminForm">
		<input type="hidden" id="selday" name="selday" value="<?php echo date("d")?>" />
        <input type="hidden" id="selmonth" name="selmonth" value="<?php echo  date("m")?>" />
        <input type="hidden" id="selyear" name="selyear" value="<?php echo date("Y")?>" />
				
<div class="col width-45">
<fieldset class="adminform">
<legend>
<?php echo JText::_('EVENT_DETALIS') ?>
</legend>
                
                <table class="admintable" id="admintable_id">
                  <tr>
					<td class="key">
						<label for="message">
							<?php echo JText::_( 'TITLE' ); ?>:
						</label>
					</td>
                	<td>
                    	<input required="required" class="cal_input" type="text" id="title" name="title" size="41" />
                    </td>
				</tr> 
                <tr>
					<td class="key">
						<label for="message">
							<?php echo JText::_( 'Date' ); ?>:
						</label>
					</td><td><input required="required" style="height: 28px;background-color: #ebebeb;border: none;font-size: 15px;color:#606060" type="text" name="date" id="date" value="" size="15"><img class="calendar" id="date_img" src="administrator/components/com_spidercalendar/elements/calendar.png" alt="calendario" style="position:relative;  top: 9px;left: -25px;"/></td></td></tr>      
                                   
                                  <tr>
					<td class="key">
						<label for="message">
							<?php echo JText::_( 'TIME' ); ?>:
						</label>
					</td>
                                      <?php if($calendar->time_format==1){  ?>
				</tr> 
				<tr>
					<td class="key">
						<label for="message">
							<?php echo JText::_( 'NOTE' ); ?>:
						</label>
					</td>
					<td >
						<?php
                        echo $editor->display('text_for_date','','100%','250','40','6','','','style="background-color:red;"');
						?>
					</td>
				</tr>
<?php
if($allow_publish==1)
{
?>               
<tr>
<td class="key">
<label for="note">
<?php echo JText::_( 'PUBLISHED' ); ?>:
</label>
</td>
<td >
<?php
echo $lists['published'];
?>

</td>
</tr> 
<?php }?>               
</table>   
</fieldset>
</div>

<div class="col width-45">
<fieldset class="adminform">
<legend>
<?php echo JText::_( 'EVENT_REPEAT' ); ?>
</legend>
<table class="admintable" id="admintable_id">
<tr>

<td valign="top" >
 <input id="r1" type="radio" value="no_repeat"  name="repeat_method"  checked="checked" onchange="change_type('no_repeat')"  /><label for="r1"><span></span><?php echo JText::_( 'DONT_REPEAT_EVENT' ); ?></label><br/>
 <input id="r2" type="radio" value="daily" name="repeat_method"   onchange="change_type('daily');"    /><label for="r2"><span></span><?php echo JText::_( 'REPEAT_DAILY' ); ?></label><br/>
 <input id="r3" type="radio" value="weekly" name="repeat_method"  onchange="change_type('weekly');" /><label for="r3"><span></span><?php echo JText::_( 'REPEAT_WEEKLY' ); ?></label><br/>
 <input id="r4" type="radio" value="monthly" name="repeat_method"  onchange="change_type('monthly');"  /><label for="r4"><span></span><?php echo JText::_( 'REPEAT_MONTHLY' ); ?></label><br/>
 <input id="r5" type="radio" value="yearly" name="repeat_method"  onchange="change_type('yearly');"   /><label for="r5"><span></span><?php echo JText::_( 'REPEAT_YEARLY' ); ?></label><br/>
</td>
   
<td style="padding-left:10px" valign="top">
<div id="daily" style="display:none">

<?php echo JText::_( 'REPEAT_EVERY' ); ?> <input class="cal_input" type="text" id="repeat_input" size="5"      name="repeat"  onclick="return input_repeat()"  onkeypress="return checknumber(repeat_input)" value="1"   /> 
<label id="repeat"></label> <label id="year_month" style="display:none"><?php echo $lists['year_month'] ?></label>
</div><br />
  

<input type="hidden"   id="daily1" />
<input type="hidden" id="weekly1" />
<input type="hidden"  id="monthly1" />
<input type="hidden"  id="yearly1" />

<div class="key"  id="weekly" style="display:none">



 <input type="checkbox" value="Mon"  id="week_1" onchange="week_value()"    /><label for="week_1"><span></span>Mon</label>
 <input  type="checkbox" value="Tue" id="week_2"  onchange="week_value()"    /><label for="week_2"><span></span>Tue</label>
 <input type="checkbox" value="Wed" id="week_3" onchange="week_value()"  /><label for="week_3"><span></span>Wed</label>
 <input type="checkbox" value="Thu" id="week_4" onchange="week_value()"   /><label for="week_4"><span></span>Thu</label>
 <input type="checkbox" value="Fri" id="week_5"  onchange="week_value()"   /><label for="week_5"><span></span>Fri</label>
 <input type="checkbox" value="Sat" id="week_6"  onchange="week_value()"   /><label for="week_6"><span></span>Sat</label>
 <input type="checkbox" value="Sun" id="week_7"  onchange="week_value()"   /><label for="week_7"><span></span>Sun</label>

<input type="hidden" name="week" id="week"  />



</div><br />
<div class="key" id="monthly" style="display:none">
<input type="radio" id="radio1"   onchange="radio_month()" name="month_type" value="1" checked="checked"  /><label for="radio1"><span></span><?php echo JText::_( 'ON_THE' ); ?>:</label> <input class="cal_input" type="text" onkeypress="return checknumber(month)" name="month" size="3" id="month"  /><br/>
<input type="radio" id="radio2" onchange="radio_month()" name="month_type" value="2"   /><label for="radio2"><span></span><?php echo JText::_( 'ON_THE' ); ?>:</label>  <?php echo $lists['monthly_list'] ?> <?php echo $lists['month_week'] ?>
</div><br />
<script>
window.onload=radio_month();
</script>
</td>
</tr>
<tr id="repeat_until" style="display:none">
<td>
<?php echo JText::_( 'REPEAT_UNTIL' ); ?>: </td>
<td>
<input style="height: 28px;background-color: #ebebeb;border: none;font-size: 15px;color:#606060" type="text" name="date_end" id="de_d" value="" size="15"><img class="calendar" id="de_d_img" src="administrator/components/com_spidercalendar/elements/calendar.png" alt="calendario" style="position:relative;  top: 9px;left: -25px;" />
</td>
</tr>
</table>
</fieldset>
</div>
		<input type="hidden" name="option" value="com_spidercalendar" />
		<input type="hidden" name="view" value="add_event" />      
        <input type="hidden" name="calendar" value="<?php echo $lists['calendar']; ?>" />   
        <input type="hidden" name="userID" value="<?php echo $user->id; ?>" />
		
		<input type="submit" style="width:80px" class="but" name="save" value="Save" onclick ="Selecttask('save')" />

	   </form>
{

}
{

}
{
document.getElementById('date').required=""
document.getElementById('title').required=""

        <?php
function saveNote(){
	$mainframe = JFactory::getApplication();
	$session = JFactory::getSession();
	$module_id=JRequest::getVar( "module_id");
	$date=JRequest::getVar( 'date');
	$date_end=JRequest::getVar( 'date_end');
	$row = JTable::getInstance('spidercalendar_event', 'Table');
	$task=JRequest::getCmd('task');
	$db = JFactory::getDBO();
	$calendar_id=JRequest::getVar( "calendar");
$calendar = JTable::getInstance('spidercalendar_calendar', 'Table');
	// load the row from the db table

$calendar->load( $calendar_id);
$allow_publish=$calendar->allow_publish;
	if(!$row->bind(JRequest::get('post')))
	{
		JError::raiseError(500, $row->getError() );
	}
	if($allow_publish==0)
	$row->published=0;
	$select_from=JRequest::getVar( 'select_from');
	
	$row->text_for_date = JRequest::getVar( 'text_for_date', '','post', 'string', JREQUEST_ALLOWRAW );
	$row->title = JRequest::getVar( 'title', '','post', 'string', JREQUEST_ALLOWRAW );

	if(!$row->store()){
		JError::raiseError(500, $row->getError() );
	}
		    $body = $user->name." has just created an event titled ".$row->title." for ".$row->date;
			$subject =$user->name." has just created an event";
	{
		case 'save':
			$msg = 'Event Saved';
			$link = 'index.php?option=com_spidercalendar&view=show_events&calendar='.JRequest::getVar('calendar').'&module_id='.$module_id;
			break;
	}	
	
	$mainframe->redirect($link, $msg);	
	}
	