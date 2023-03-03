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

use Joomla\CMS\Dispatcher\ModuleDispatcherFactoryInterface;
use Joomla\CMS\Extension\ModuleInterface;
use Joomla\CMS\Extension\Service\Provider\HelperFactory;
use Joomla\CMS\Extension\Service\Provider\Module;
use Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory;
use Joomla\CMS\Helper\HelperFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Module\Azdirectory\Site\Extension\Azdirectory;

/**
 * The ATS Stats module service provider.
 *
 * @since  7.1.0
 */
return new class implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   7.1.0
	 */
	public function register(Container $container)
	{
		$container->registerServiceProvider(new ModuleDispatcherFactory('\\Joomla\\Module\\Azdirectory'));
		$container->registerServiceProvider(new HelperFactory('\\Joomla\\Module\\Azdirectory\\Site\\Helper'));
		$container->registerServiceProvider(new Module());

        $container->set(
            ModuleInterface::class,
            function (Container $container) {
                $module = new Azdirectory(
                    $container->get(ModuleDispatcherFactoryInterface::class),
                    $container->get(HelperFactoryInterface::class)
                );

                if ($module instanceof ContainerAwareInterface) {
                    $module->setContainer($container);
                }

                return $module;
            }
        );
	}
};
