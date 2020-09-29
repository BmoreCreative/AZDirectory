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
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once dirname(__FILE__) . '/helper.php';
require_once dirname(__FILE__) . '/helpers/parser.php';
$az = modAZDirectoryHelper::azInstance( $params, $module->id );

$jinput = JFactory::getApplication()->input;
$modAZAssetsPath = JUri::base() . 'modules/' . $module->module . '/assets/';

// set the module title to get a unique instance in the Ajax callback
$doc = JFactory::getDocument();
$doc->addScriptDeclaration( 'var modazModuleTitle="' . $module->title . '";' );

// process form submission
if( $jinput->get( 'modazdirectory__select' ) ) :
	JSession::checkToken() or die( 'Invalid Token' );
	$az->submit( $jinput->getString( 'modazdirectory__select', '' ) );
endif;

// get parameters specific to the module configuration
// e.g. $show_image = $params->get('show_image');
// NOTE: id (category IDs) is an array, so a variable is not created and not used in default.php
foreach ( $params as $key => $value ):
	if( is_string( $value ) ) :
		$$key = htmlspecialchars( $value );
	endif;
endforeach;

$azdirectory = $az->getAZDirectory();

// handle lastletter parameter in the URL
if( !is_null( $jinput->get( 'lastletter' ) ) ) :
	$lastletter = $jinput->getString( 'lastletter' );
else :
	// handle configured last letter
	$defaultletter = ( $sortorder == 'fn' ) ? $defaultletterfn : $defaultletter;
	if( $defaultletter != '' ) :
		// trap for All
		if( $defaultletter == 'All' ) :
			$defaultletter = JText::_( 'JALL' );
		endif;
		$lastletter = $defaultletter;
	endif;
endif;

list( $contacts, $total, $start ) = $az->azGenerateQuery( $lastletter, 0, $params );
require JModuleHelper::getLayoutPath( 'mod_azdirectory', $params->get( 'layout', 'default' ) );