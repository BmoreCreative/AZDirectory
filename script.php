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

/**
 * Script file of A-Z Directory module
 */
class mod_AZDirectoryInstallerScript
{
	/**
	 * Method to install the extension
	 * $parent is the class calling this method
	 *
	 * @return void
	 */
	function install($parent)
	{
		echo '<p>The module has been installed</p>';
	}
 
	/**
	 * Method to uninstall the extension
	 * $parent is the class calling this method
	 *
	 * @return void
	 */
	function uninstall($parent) 
	{
		echo '<p>The module has been uninstalled</p>';
	}
 
	/**
	 * Method to update the extension
	 * $parent is the class calling this method
	 *
	 * @return void
	 */
	function update($parent) 
	{
		echo '<p>The module has been updated to version ' . $parent->get('manifest')->version . '</p>';
	}
 
	/**
	 * Method to run before an install/update/uninstall method
	 * $parent is the class calling this method
	 * $type is the type of change (install, update or discover_install)
	 *
	 * @return void
	 */
	function preflight($type, $parent) 
	{
	}

	/**
	 * Method to run after an install/update/uninstall method
	 * $parent is the class calling this method
	 * $type is the type of change (install, update or discover_install)
	 *
	 * @return void
	 */
	function postflight($type, $parent) 
	{
		JFile::delete(JPATH_SITE . '/modules/mod_azdirectory/assets/jquery.clippath.min.js');
	}
}