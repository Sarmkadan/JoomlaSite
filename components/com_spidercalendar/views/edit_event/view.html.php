<?php
 
 /**


defined( '_JEXEC' ) or die( 'Restricted access' );



jimport( 'joomla.application.component.view');







class spidercalendarViewEdit_event extends JView

{

    function display($tpl = null)

		{

			$model = $this->getModel();

			$result = $model->editNote();

			$this->assignRef( 'lists',	$result[0] );
			$this->assignRef( 'row',	$result[1] );
			
			parent::display($tpl);

		}

}

?>