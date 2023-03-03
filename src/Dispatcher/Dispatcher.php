<?php
/**
 * @package        A-Z Directory
 * @subpackage     mod_azdirectory
 * @copyright      Copyright (C) 2016 Bmore Creative, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @website        https://www.bmorecreativeinc.com/joomla/extensions
 */

namespace Joomla\Module\Azdirectory\Site\Dispatcher;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Extension\ModuleInterface;
use Joomla\CMS\Language\Text;
use Joomla\Input\Input;
use Joomla\Module\Azdirectory\Site\Helper\AzdirectoryHelper;
use Joomla\Registry\Registry;

class Dispatcher extends AbstractModuleDispatcher
{
    /**
     * The module extension. Used to fetch the module helper.
     *
     * @var   ModuleInterface|null
     * @since 7.1.0
     */
    private $moduleExtension;

    /** @inheritdoc */
    public function __construct(\stdClass $module, CMSApplicationInterface $app, Input $input)
    {
        parent::__construct($module, $app, $input);

        $this->moduleExtension = $this->app->bootModule('mod_azdirectory', 'site');
    }

    public function dispatch()
    {
        $this->loadLanguage();

        if ($this->input->get('modazdirectory__select')) {
            $this->handleSubmission();
        }

        $doc = $this->app->getDocument();

        if (!$doc instanceof HtmlDocument) {
            return;
        }

        $doc->addScriptDeclaration('var modazModuleTitle="' . $this->module->title . '";');

        parent::dispatch();
    }

    /**
     * Handles form submission
     *
     * @return never-return
     * @since  7.1.0
     */
    public function handleSubmission(): void
    {
        /** @var \Joomla\CMS\Session\Session $session */
        $session = $this->app->getSession();

        if (!$session->checkToken()) {
            $this->app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
            $this->app->redirect('index.php');
        }

        $this->app->redirect($this->input->getString('modazdirectory__select', ''));
    }

    /** @inheritDoc */
    protected function getLayoutData()
    {
        // Get the rest of the layout data from the helper
        /** @var AzdirectoryHelper $helper */
        $helper = $this->moduleExtension->getHelper('AzdirectoryHelper', [
            'params' => new Registry($this->module->params),
            'module' => $this->module,
        ]);

        return array_merge(parent::getLayoutData(), $helper->getLayoutData($this->input));
    }
}