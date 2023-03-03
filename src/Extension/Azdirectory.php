<?php
/**
 * @package        A-Z Directory
 * @subpackage    mod_azdirectory
 * @copyright    Copyright (C) 2016 Bmore Creative, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @website        https://www.bmorecreativeinc.com/joomla/extensions
 */

namespace Joomla\Module\Azdirectory\Site\Extension;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Extension\Module;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;

class Azdirectory extends Module implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Returns a helper instance for the given name.
     *
     * Overridden to pass dependencies needed by our helper.
     *
     * @param   string  $name    The name
     * @param   array   $config  The config
     *
     * @return  \stdClass
     *
     * @since   7.1.0
     */
    public function getHelper(string $name, array $config = [])
    {
        // The parent getHelper method already sets the database object
        $helper = parent::getHelper($name, $config);

        // We pass the application object. Note: we know this module only ever runs in the frontend.
        if (method_exists($helper, 'setApplication')) {
            $helper->setApplication($this->getContainer()->get(SiteApplication::class));
        }

        if (method_exists($helper, 'setDatabase')) {
            $helper->setDatabase($this->getContainer()->get('DatabaseDriver'));
        }

        return $helper;
    }
}