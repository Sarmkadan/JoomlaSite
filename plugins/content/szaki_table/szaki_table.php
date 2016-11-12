<?php
/**
* @version		$Id: szaki_gallery.php 2010. 12. 29.
* @package		Szaki Table
* @copyright	Copyright (C) 2010 szathmari.hu All rights reserved.
* @license		GNU/GPL, see license on http://szathmari.hu
*/
defined( '_JEXEC' ) or die( 'Restricted access' );;
jimport('joomla.plugin.plugin');

class plgContentSzaki_Table extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}
	
	public function onContentPrepare($context, &$article, &$params, $limitstart)
	{
		$app = JFactory::getApplication();
    	if (strpos($article->text, 'szakitable') === false) {
			return true;
		} 
	
		define('SZAKI_TABLE_URI', JURI::root().'plugins/content/szaki_table/assets/');
		
		/* load jQuery */
		if ($this->params->get('jQuery', 1)) {
			if (!JFactory::getApplication()->get('jquery')) {
				JFactory::getApplication()->set('jquery', true);
				JHTML::script('jquery.js', SZAKI_TABLE_URI);
			}
		}
		
        JHTML::script( 'jquery.tablesorter.js', SZAKI_TABLE_URI );
		JHTML::stylesheet('szaki_table.css', SZAKI_TABLE_URI);
		
		
		/* Start Plugin */
		$regex_one		= '|{szakitable\s*(.[^}]*)}(.*?){/szakitable}|si';
		$regex_all		= '|{szakitable\s*.*?{/szakitable}|si';
	    $matches 		= array();
	    $count_matches	= preg_match_all($regex_all,$article->text, $matches, PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
		
			
		 // Start if count_matches
		if ($count_matches != 0) {
			for($i = 0; $i < $count_matches; $i++) {
            	$parts = array();
				$szaki = $matches[0][$i][0];

				preg_match($regex_one, $szaki, $parts);
				$rid = rand(100000,999999);
				
				/* szakitable variables */
				preg_match ( "#filter\s*=\s*\"\s*(.*?)\s*\"#s", $parts[1], $mf );
				if ($mf[1]) 
						$livefJs = "$('#$rid').liveFilter('$mf[1]');";
					else
						$livef = '';
				
				preg_match ( "#zebra\s*=\s*\"\s*(.*?)\s*\"#s", $parts[1], $mz );
				$zebra = '';
				if ($mz[1]) 
					$zebra = "widgets: ['zebra']";
			
				/* expand table size */
				preg_match ( "#width\s*=\s*\"\s*(.*?)\s*\"#s", $parts[1], $witdth );
				if ($witdth[1]) {
					$resizeJsDec = " $('#$rid').resize('$witdth[1]');";
					$resize = "<div class='resize'>";
				}
				else {
					$resize = '';
					$resizeJsDec = '';
				}
			
				$livef = "<div class='filter'><label class='szakitable' for='livef'>".JText::_( 'Filter' ).": </label><input class='szakitable $rid' name='livef' type='text'  /> </div>";
				
				$close = '</div>';
				
				$output = "<div class='szakitable' id='$rid'>$livef\n$resize\n";
				if ($resize) $close .= '</div>';
				$output .= $parts[2].$close;
				
				$article->text = preg_replace($regex_all, $output, $article->text, 1);
				
				/* table sorter, zebra, filter */
				JFactory::getDocument()->addScriptDeclaration("
				jQuery.noConflict();(function($) {
					$(window).load(function(){
						$('#$rid table').tablesorter({ $zebra });
						$livefJs
						$resizeJsDec
					});})(jQuery);
				");
				
			
			} //end for matches
		}// end if count_matches
	}
}
?>
