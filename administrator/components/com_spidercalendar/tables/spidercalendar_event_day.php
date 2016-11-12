<?php

 /**
 * @package Spider Calendar lite
 * @author Web-Dorado
 * @copyright (C) 2011 Web-Dorado. All rights reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 **/

defined('_JEXEC') or die('Restricted access');

class Tablespidercalendar_event_day extends JTable
{
var $id = null;
var $event_id = null;
var $event_day = null;


function __construct(&$db)
{
parent::__construct('#__spidercalendar_event_day','id',$db);
}
}

?>