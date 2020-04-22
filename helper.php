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
		
		// load javascript after jQuery
		JHtml::_('jquery.framework');
		$doc->addScript('modules/mod_azdirectory/assets/jquery.clippath.min.js');
		$doc->addScript('modules/mod_azdirectory/assets/modazdirectory.js');

		// access database object
		$db = JFactory::getDbo();
		
		// get the first letter of the last name
		$query = $db->getQuery(true);
		$query
			->select('DISTINCT(LEFT(SUBSTRING_INDEX(' . $db->quoteName('name') . ', \' \', -1), 1)) AS lastletter')
			->from($db->quoteName('#__contact_details'))
			->where($db->quoteName('catid') . ' = ' . $params->get('id'))
			->where($db->quoteName('published') . ' = 1')
			->order($db->quoteName('lastletter'));
		$db->setQuery($query);
		$rows = $db->loadAssocList('lastletter');
		$lastletters = array_keys($rows);
		
		// get the alphabet
		$alphabet = range('A', 'Z');
		
		$tmpl = array();
		$tmpl[0] = $alphabet;
		$tmpl[1] = $lastletters;
		
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
		$params = new JRegistry();
		$params->loadString($module->params);

		// create an instance
		$return = new stdClass();
		
		// access database object
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);
		$query
			->select(array('*'))
			->from($db->quoteName('#__contact_details'));
			
		// if a specific letter is selected
		if( $lastletter != 'All' ) :
			$query->where("LEFT(SUBSTRING_INDEX(" . $db->quoteName('name') . ", ' ', -1), 1) = '" . $lastletter . "'");
		endif;
			
		$query
			->where($db->quoteName('catid') . ' = ' . $params->get('id'))
			->where($db->quoteName('published') . ' = 1')
			->order("SUBSTRING_INDEX(" . $db->quoteName('name') . ", ' ', -1)");
	
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		
		$return->data = array();
		$return->data['json_array'] = $rows;
		$return->data['mod_params'] = $params;
		
		// get JUri::base for the full path to the contact image in Javascript
		$return->data['juri_base'] = JUri::base();
		
		// get the last letter
		$return->data['lastletter'] = $lastletter;

		echo json_encode($return);
	
		// close the $app
		$app->close();	
	}

	/**
	 * Method to get contact information based on first letter of last name
	 *
	 * @access    public
	 */
	public static function getContactsNoAjax($lastletter, $params)
	{
		// access database object
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);
		$query
			->select(array('*'))
			->from($db->quoteName('#__contact_details'));
		
		// if a specific letter is selected
		if( $lastletter != 'All' ) :
			$query->where("LEFT(SUBSTRING_INDEX(" . $db->quoteName('name') . ", ' ', -1), 1) = '" . $lastletter . "'");
		endif;
		
		$query
			->where($db->quoteName('catid') . ' = ' . $params->get('id'))
			->where($db->quoteName('published') . " = 1")
			->order("SUBSTRING_INDEX(" . $db->quoteName('name') . ", ' ', -1)");
	
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		return $rows;
	}

	/**
	 * Method to verify valid user data
	 *
	 * @access    public
	 */
	public static function azVerify($key, $values){
		// get module parameters
		$module = JModuleHelper::getModule('azdirectory');
		$params = new JRegistry();
		$params->loadString($module->params);
	
		$param = $params->get('show_' . $key);
		$value = $values->$key;
		return ( ( $param == 1 ) && ( $value ) ) ? 1 : 0;
	}
	
	/**
	 * Method to redirect page based on select
	 *
	 * @access    public
	 */	
	 public static function submit( $azoption ){
		header( 'Location: ' . $azoption );		 
	 }
}