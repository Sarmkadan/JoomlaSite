<?php
 /**
 * @package Spider Random Article
 * @author Web-Dorado
 * @copyright (C) 2012 Web-Dorado. All rights reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 **/
 
defined('_JEXEC') or die('Restricted access');

class JFormFieldEventFromDInterval extends JFormField
{
	var	$_name = 'eventfromdinterval';
function getInput()
{
	
        ob_start();
        static $embedded;
                if(!$embedded)
        {

            $embedded=true;
        }
		
		JHTML::_('behavior.calendar');
		 $name = $this->name;
		$value = $this->value;
		$node =  $this->element;
		
       ?>
	   
	<br/><br/><br/><br/><br/><br/>
<fieldset class="radio">
<input type="radio" name="<?php echo $this->name;?>" value="0" <?php if($this->value==0)echo'checked="checked"'?>onChange="showd_(0)" id="showd0" ><label for="showd0">Current Date</label>
<input type="radio" name="<?php echo $this->name;?>" value="1" <?php if($this->value==1)echo'checked="checked"'?>onChange="showd_(1)" id="showd1"><label for="showd1">Start Date</label> 
</fieldset>


   

<span id="evd"></span>
<script type="text/javascript">
var show0=document.getElementById('show0').checked;
var show2=document.getElementById('show2').checked;
if(show0 || show2 )
{
document.getElementById('evd').parentNode.setAttribute('style','display:none');
}


function showd_(y)
{
  if(y==0)
  {
document.getElementById('evtype').parentNode.parentNode.childNodes[7].setAttribute('style','display:none');
document.getElementById('evd').parentNode.parentNode.childNodes[9].removeAttribute('style');
document.getElementById('evd').parentNode.parentNode.childNodes[11].removeAttribute('style');
document.getElementById('evd').parentNode.parentNode.childNodes[13].removeAttribute('style');
document.getElementById('evd').parentNode.parentNode.childNodes[15].removeAttribute('style');
document.getElementById('evd').parentNode.parentNode.childNodes[17].setAttribute('style','display:none');
document.getElementById('evd').parentNode.parentNode.childNodes[19].setAttribute('style','display:none');
document.getElementById('evd').parentNode.parentNode.childNodes[21].setAttribute('style','display:none');
document.getElementById('evd').parentNode.parentNode.childNodes[23].setAttribute('style','display:none');
document.getElementById('evtype').parentNode.parentNode.childNodes[25].setAttribute('style','display:none');  
  }
  else
  {
document.getElementById('evtype').parentNode.parentNode.childNodes[7].setAttribute('style','display:none');
document.getElementById('evd').parentNode.parentNode.childNodes[9].removeAttribute('style');
document.getElementById('evtype').parentNode.parentNode.childNodes[11].setAttribute('style','display:none');
document.getElementById('evtype').parentNode.parentNode.childNodes[13].setAttribute('style','display:none');
document.getElementById('evtype').parentNode.parentNode.childNodes[15].setAttribute('style','display:none');
document.getElementById('evtype').parentNode.parentNode.childNodes[17].removeAttribute('style');
document.getElementById('evtype').parentNode.parentNode.childNodes[19].removeAttribute('style');
document.getElementById('evtype').parentNode.parentNode.childNodes[21].removeAttribute('style');
document.getElementById('evtype').parentNode.parentNode.childNodes[23].removeAttribute('style'); 
document.getElementById('evtype').parentNode.parentNode.childNodes[25].setAttribute('style','display:none'); 
  }

}



</script>





        <?php

        $content=ob_get_contents();

        ob_end_clean();
        return $content;

    }
	}
	
	?>