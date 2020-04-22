<?php
/**
 * @package		A-Z Directory
 * @subpackage	mod_azdirectory
 * @copyright	Copyright (C) 2016 Bmore Creative, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @website		https://www.bmorecreativeinc.com/joomla/extensions
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class modAZDirectoryHelper
{
    /**
     *
     * @param   array  $params An object containing the module parameters
     *
     * @access public
     */
	 
	// get module parameters
    public static function getAZDirectory($params)
    {
		// load stylesheet
		$doc = JFactory::getDocument();
		$doc->addStyleSheet('modules/mod_azdirectory/assets/modazdirectory.css');
		
		// load Javascript after jQuery
		JHtml::_('jquery.framework');
		$doc->addScript('modules/mod_azdirectory/assets/modazdirectory.js');

		// access database object
		$db = JFactory::getDbo();
		
		// set collation
		$collation = ( $params->get('swedish') == 1 ) ? self::_azGetCollation() : "";
		
		// get sort order
		$sortorder = $params->get('sortorder');
		
		// initialize query
		$query = $db->getQuery(true);
		
		if( $sortorder == 'fn' ) :
			// get the first letter of the first name
			$query->select("DISTINCT(LEFT(" . $db->quoteName('name') . ", 1))" . $collation . " AS letter");
		else :
			// get the first letter of the last name
			$query->select("DISTINCT(LEFT(SUBSTRING_INDEX(" . $db->quoteName('name') . ", ' ', -1), 1))" . $collation . " AS letter");
		endif;
		
		$query->from($db->quoteName('#__contact_details'));
		
		if( $params->get('id') ) :
			$query->where($db->quoteName('catid') . ' = ' . $params->get('id'));
		endif;
		
		$query
			->where($db->quoteName('published') . ' = 1')
			->order($db->quoteName('letter'));

		$db->setQuery($query);
		$rows = $db->loadAssocList('letter');
		$letters = array_keys($rows);
		
		// get the alphabet
		$english = range('A', 'Z');
		
		if( $params->get('swedish') == 1 ) :
			$swedish = array("&Aring;", "&Auml;", "&Ouml;");
			array_walk($english, 'self::_azDecode');
			array_walk($swedish, 'self::_azDecode');
			$alphabet = array_merge($english, $swedish);
		else :
			$alphabet = $english;
		endif;
		
		$tmpl = array();
		$tmpl[0] = $alphabet;
		$tmpl[1] = $letters;
		
		return $tmpl;
    }

	/**
	 * Method to get contact information based on first letter of last name
	 *
	 * @access    public
	 */
	public static function getContactsAjax()
	{
		// get the letter
		$app = JFactory::getApplication();
		$lastletter = $app->input->getString('data');
		
		// get module parameters
		$module = JModuleHelper::getModule('azdirectory');
		$modparams = new JRegistry($module->params);
		$catid = $modparams->get('id');
		$sortorder = $modparams->get('sortorder');
		
		// set collation
		$collation = ( $modparams->get('swedish') == 1 ) ? self::_azGetCollation() : "";

		// get the contacts
		$contacts = self::_azGenerateQuery($collation, $lastletter, $catid, $sortorder);

		// get parameters specific to the module configuration
		foreach ($modparams as $key => $value) :
			$$key = htmlspecialchars($value);
		endforeach;
		
		$azdirectory = modAZDirectoryHelper::getAZDirectory($modparams);
		
		ob_clean();
		ob_start();

		$app= & JFactory::getApplication();
		$template = $app->getTemplate();
		$filename = JPATH_THEMES.'/'.$template.'/html/mod_azdirectory/default.php';
		
		if (file_exists($filename)) :
			require_once $filename;
		else :
			require_once dirname(__FILE__) . '/tmpl/default.php';
		endif;

		ob_get_contents();

		exit;
		$app->close();
	}

	/**
	 * Method to get contact information based on first letter of last name
	 *
	 * @access    public
	 */
	public static function getContactsNoAjax($lastletter, $params)
	{
		// get category id
		$catid = $params->get('id');
		
		// get the sort order
		$sortorder = $params->get('sortorder');

		// set collation
		$collation = ( $params->get('swedish') == 1 ) ? self::_azGetCollation() : "";

		// get the contacts
		$contacts = self::_azGenerateQuery($collation, $lastletter, $catid, $sortorder);

		return $contacts;
	}

	/**
	 * Method to verify valid user data
	 *
	 * @access    public
	 */
	public static function azVerify($key, $values){
		// get module parameters
		$module = JModuleHelper::getModule('azdirectory');
		$params = new JRegistry($module->params);
	
		$param = $params->get('show_' . $key);
		$value = $values->$key;
		return ( ( $param == 1 ) && ( $value ) ) ? 1 : 0;
	}
	
	/**
	 * Method to redirect page based on select
	 *
	 * @access    public
	 */	
	 public static function submit($azoption){
		header( 'Location: ' . $azoption );		 
	 }

	/**
	 * Method to format name
	 *
	 * @access    public
	 */	
	public static function azFormatName($name, $lastnameFirst){
		if( $lastnameFirst == 1 ) :
			$nameparts = explode( " ", $name );
			$lastname = array_pop( $nameparts );
			$firstname = implode( " ", $nameparts );
			$name = $lastname . ", " . $firstname;
		endif;
		return $name;
	}
	
	/**
	 * Method to sanitize telephone numbers
	 *
	 * @access    public
	 */
	public static function azSanitizeTelephone($telephone){
		return str_replace( array( "+", "-" ), "", filter_var( $telephone, FILTER_SANITIZE_NUMBER_INT ) );
	}
	
	/**
	 * Method to sanitize URLs
	 *
	 * @access    public
	 */
	public static function azSanitizeURL($url){
		$filter = JFilterInput::getInstance();
		return $filter->clean($url, "string");
	}
	
	/**
	 * Method to format addresses
	 *
	 * @access    public
	 */	
	public static function azFormatAddress($contact, $postcodeFirst){
		$lines = array();
		if ( self::azVerify( 'suburb', $contact ) || self::azVerify( 'state', $contact ) || self::azVerify( 'postcode', $contact ) ) :
			if ( $postcodeFirst == 1 ) :
				// international address format
				$line = array();
				if ( self::azVerify( 'postcode', $contact ) ) $line[] = '<span>' . $contact->postcode . '</span>';
				if ( self::azVerify( 'suburb', $contact ) ) $line[] = '<span>' . $contact->suburb . '</span>';
				if ( self::azVerify( 'state', $contact ) ) $line[] = '<span>' . $contact->state . '</span>';
				$lines[] = implode( ' ', $line );
			else :
				// US address format
				$line = array();
				if ( self::azVerify( 'suburb', $contact ) ) $line[] = '<span>' . $contact->suburb . '</span>';
				if ( self::azVerify( 'state', $contact ) ) $line[] = '<span>' . $contact->state . '</span>';
				if ( count( $line ) ) $line = array( implode( ', ', $line ) );
				if ( self::azVerify( 'postcode', $contact ) ) $line[] = '<span>' . $contact->postcode . '</span>';
				$lines[] = implode( ' ', $line );	
			endif;
		endif;
		
		return $lines[0];
	}	

	/**
	 * Method to get the default option for the select option
	 *
	 * @access    public
	 */	
	public static function azFirstOption($sortorder){
		$language = JFactory::getLanguage();
		$language->load('mod_azdirectory');

		if ( $sortorder == 'fn' ) :
			$modazfirstoption = JText::_('MOD_AZDIRECTORY_SORTORDER_FN');
		else :
			$modazfirstoption = JText::_('MOD_AZDIRECTORY_SORTORDER_LN');
		endif;

		return $modazfirstoption;
	}

	/**
	 * Method to generate SQL query
	 *
	 * @access    private
	 */
	private static function _azGenerateQuery($collation, $letter, $catid, $sortorder){
		// access database object
		$db = JFactory::getDBO();

		// initialize query
		$query = $db->getQuery(true);

		$query->select(array('*'));
		
		if( $sortorder == 'fn' ) :
			// get the first letter of the first name
			$query->select("LEFT(" . $db->quoteName('name') . ", 1) AS letter");
		else :
			// get the first letter of the last name
			$query->select("LEFT(SUBSTRING_INDEX(" . $db->quoteName('name') . ", ' ', -1), 1) AS letter");
		endif;
		
		$query->from($db->quoteName('#__contact_details'));
			
		// if a specific letter is selected
		if( $letter != JText::_('JALL') ) :
			if( $sortorder == 'fn' ) :
				// get the first letter of the first name
				$query->where("LEFT(" . $db->quoteName('name') . ", 1)" . $collation . " = '" . $letter . "'");
			else :
				// get the first letter of the last name
				$query->where("LEFT(SUBSTRING_INDEX(" . $db->quoteName('name') . ", ' ', -1), 1)" . $collation . " = '" . $letter . "'");
			endif;
		endif;
		
		if( $catid ) :
			$query->where($db->quoteName('catid') . ' = ' . $catid);
		endif;
			
		$query->where($db->quoteName('published') . ' = 1');
		
		// set the sort order
		switch( $sortorder ) :
			case 'fn' :
				$query->order($db->quoteName('name') . $collation);
				break;
			case 'ln' :
				$query->order("SUBSTRING_INDEX(" . $db->quoteName('name') . ", ' ', -1)" . $collation);
				break;
			case 'sortfield' :
				$query
					->order($db->escape('sortname1'))
					->order($db->escape('sortname2'))
					->order($db->escape('sortname3'));
				break;
			case 'component' :
				$query->order($db->escape('ordering'));
				break;
			default :
				$query->order("SUBSTRING_INDEX(" . $db->quoteName('name') . ", ' ', -1)" . $collation);
		endswitch;

		$db->setQuery($query);
		
		return $db->loadObjectList();
	}

	/**
	 * Method to decode HTML entities
	 *
	 * @access    private
	 */
	 private static function _azDecode(&$item){
		$item = html_entity_decode($item, ENT_NOQUOTES, 'UTF-8');
		return $item;
	}

	/**
	 * Method to set collation string
	 *
	 * @access    private
	 */
	private static function _azGetCollation(){
		$config = JFactory::getConfig();
		$schema = $config->get('db');
		$dbprefix = $config->get('dbprefix');
				
		// access database object
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);
		
		$query
			->select($db->quoteName('character_set_name'))
			->from('information_schema.' . $db->quoteName('COLUMNS'))
			->where($db->quoteName('table_schema') . ' = ' . $db->quote($schema))
			->where($db->quoteName('table_name') . ' = ' . $db->quote($dbprefix . 'contact_details'))
			->where($db->quoteName('column_name') . ' = ' . $db->quote('name'));
		
		$db->setQuery($query);
		$charSet = $db->loadResult(); // utf8mb4 or utf8

		// set collation
		$collationStr = " COLLATE " . $charSet . "_swedish_ci";

		return $collationStr;
	}
}