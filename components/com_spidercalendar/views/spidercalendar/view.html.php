<?php
 
 /**



defined( '_JEXEC' ) or die( 'Restricted access' );



jimport( 'joomla.application.component.view');




class spidercalendarViewspidercalendar extends JView
{

    function display($tpl = null)
		{

			$model = $this->getModel();

			$result = $model->showcalendar();

			$this->assignRef( 'rows',	$result[0] );

			$this->assignRef( 'option',	$result[1] );
						
			$this->assignRef( 'eventIDs',	$result[2] );
			parent::display($tpl);

		}

}

?>