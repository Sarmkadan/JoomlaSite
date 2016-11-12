<?php 
  
 /**
 * @package Form Creator
 * @author Web-Dorado
 * @copyright (C) 2011 Web-Dorado. All rights reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 **/

defined('_JEXEC') or die('Restricted access');

class JFormFieldCalendarSelect extends JFormField
{

	var	$_name = 'calendarselect';

	function getInput()
	{
		
		
		$db = JFactory::getDBO();

		$query = 'SELECT id , title FROM #__spidercalendar_calendar WHERE published=1';
		$db->setQuery( $query );
		$options = $db->loadObjectList();
        $name = $this->name;
		$value = $this->value;
		$node =  $this->element;

	?>
	<script>
	function selectcal()
	{

	var a = document.getElementById('list').parentNode.parentNode.childNodes[25].childNodes[19].childNodes[1].childNodes[0].childNodes[1].childNodes[1];
	var calendar=document.getElementById('list').parentNode.childNodes[7];
	
	var selectcalendarvalue = document.getElementById('list').parentNode.childNodes[7].value;
	
    a.href=a.href+'&cal='+selectcalendarvalue;
}

	
	</script>

	<span id="list"></span>
	
<select name="<?php echo $this->name ;?>" onchange="selectcal()" class="inputbox" size="1">
<option value="">-Select Calendar-</option>
    <?php
		
	 for($i=0, $n=count($options); $i < $n ; $i++)
	{
      $row = $options[$i];
	
    ?>
<option  value="<?php echo $row->id; ?>" <?php if ($this->value==$row->id) echo 'selected="selected"'; ?>><?php echo $row->title;?></option>
        <?php
	}
	?>
    </select> 
	
		
		
		
	<?php	
		  
	
}
}
?>