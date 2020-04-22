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

require_once dirname(__FILE__) . '/helper.php';
$jinput = JFactory::getApplication()->input;

// process form submission
if( $jinput->post->get('modazdirectory__submit', 'Submit', STRING) ) :
	modAZDirectoryHelper::submit($jinput->post->get('modazdirectory__select', '', STRING));
endif;

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
$azdirectory = modAZDirectoryHelper::getAZDirectory($params);
if( !is_null( $jinput->get('lastletter') ) ) : 
	$lastletter = $jinput->get('lastletter');
	$show_image = $params->get('show_image');
	$contacts =  modAZDirectoryHelper::getContactsNoAjax( $lastletter, $params );
endif;
require JModuleHelper::getLayoutPath('mod_azdirectory');