<?php
/**
 * @package        A-Z Directory
 * @subpackage     mod_azdirectory
 * @copyright      Copyright (C) 2016 Bmore Creative, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @website        https://www.bmorecreativeinc.com/joomla/extensions
 */

namespace Joomla\Module\Azdirectory\Site\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use RuntimeException;

class AzdirectoryHelper
{
    private static $azInstance = [];

    /**
     * The Joomla application object
     *
     * @var CMSApplication|null
     */
    private $app;

    /**
     * The module instance
     *
     * @var    \stdClass
     * @since  7.1.0
     */
    private $module;

    /**
     * The module parameters
     *
     * @var Registry|null
     */
    private $params = null;

    /**
     * Database
     *
     * @var       DatabaseInterface
     * @since  7.1.1
     */
    private $dbo;

    /**
     * Constructor
     *
     * @param   array  $config  Configuration parameters
     */
    public function __construct(array $config = [])
    {
        mb_internal_encoding('utf-8');

        $params = $config['params'] ?? null;

        if ($params instanceof Registry) {
            $this->params = $params;
        }

        $this->module = $config['module'] ?? null;
    }

    /**
     * Set the database.
     *
     * @param   DatabaseInterface  $db  The database.
     *
     * @return  void
     *
     * @since   7.1.1
     */
    public function setDatabase(DatabaseInterface $db): void
    {
        $this->dbo = $db;
    }

    /**
     * Method to format name
     */
    public function azFormatName($name, $lastnameFirst)
    {
        if ($lastnameFirst == 1) :
            $parser    = new FullNameParserHelper();
            $words     = $parser->parse_name($name);
            $lastname  = $words['lname'];
            $firstname = $words['fname'];
            $name      = $lastname . ", " . $firstname;
        endif;
        return $name;
    }

    /**
     * Method to sanitize telephone numbers
     */
    public function azSanitizeTelephone($telephone)
    {
        return str_replace(["+", "-"], "", filter_var($telephone, FILTER_SANITIZE_NUMBER_INT));
    }

    /**
     * Method to sanitize URLs
     */
    public function azSanitizeURL($url)
    {
        $filter = InputFilter::getInstance();
        return $filter->clean($url, "string");
    }

    /**
     * Method to get contact information based on first letter of last name
     */
    public function getContactsAjax()
    {
        $app = $this->app;

        // Get the data
        $azdata     = $app->input->getString('data');
        $azdata     = is_array($azdata) ? $azdata : [$azdata];
        $lastletter = filter_var($azdata['letter'] ?? 'All', FILTER_SANITIZE_STRING);
        $start      = filter_var($azdata['start'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
        $title      = filter_var($azdata['title'] ?? null, FILTER_SANITIZE_STRING);

        $this->module = ModuleHelper::getModule('azdirectory', $title);
        $this->params = new Registry($this->module->params);

        $input = clone $this->app->input;
        $input->set('lastletter', $lastletter);
        $input->set('_az_start', $start);

        $layoutData = array_merge([
            'module'   => $this->module,
            'app'      => $this->app,
            'input'    => $this->app->input,
            'params'   => $this->params,
            'template' => $this->app->getTemplate(),
        ], $this->getLayoutData($input));

        ob_clean();

        extract($layoutData);

        // checks for layout override first, then checks for original
        include ModuleHelper::getLayoutPath($this->module->module, $this->params->get('layout', 'default'));

        $app->close();
    }

    /**
     * Get the data to pass to the view template
     *
     * @return  array
     * @since   7.1.0
     */
    public function getLayoutData(?Input $input = null): array
    {
        $input = $input ?? $this->app->input;

        $layoutData = [];

        // Push all parameters as layout data.
        foreach ($this->params->getIterator() as $key => $value) {
            if (is_string($value)) {
                $value = htmlspecialchars($value);
                $this->params->set($key, $value);
                $layoutData[$key] = $value;
            }
        }

        $layoutData['params'] = $this->params;

        // Handle `lastletter` parameter in the URL
        $lastLetter = $input->getString('lastletter');

        if (is_null($lastLetter)) {
            // handle configured last letter
            $defaultLetter = $this->params->get('defaultletter', '');

            if (in_array(
                $this->params->get('sortorder', 'ln'),
                ['fn', 'component', 'sortfield'],
                true
            )) {
                $defaultLetter = $this->params->get('defaultletterfn', '');
            }

            $lastLetter = (($defaultLetter === 'All') ? Text::_('JALL') : $defaultLetter);
        }

        $layoutData['lastletter'] = $lastLetter;

        // Get the rest of the layout data from the helper
        $start = $input->getInt('_az_start', 0);
        [$contacts, $total, $start] = $this->azGenerateQuery($lastLetter, $start);

        $layoutData['contacts']        = $contacts;
        $layoutData['total']           = $total;
        $layoutData['start']           = $start;
        $layoutData['az']              = $this;
        $layoutData['azdirectory']     = $this->getAZDirectory();
        $layoutData['modAZAssetsPath'] = Uri::base() . 'modules/' . $this->module->module . '/assets/';

        return $layoutData;
    }

    /**
     * Method to generate SQL query
     *
     * @param   string    $letter  The currently active letter, or "All"
     * @param   int       $start   How many records to skip
     * @param   Registry  $params  The module parameters
     *
     * @return array
     */
    public function azGenerateQuery(string $letter, int $start): array
    {
        // Get the database object
        $db = $this->getDatabase();
        // Get configuration parameters
        $catid      = $this->params->get('id');
        $tagid      = $this->params->get('tags');
        $sortorder  = $this->params->get('sortorder');
        $pagination = $this->params->get('pagination');
        $alphabet   = $this->params->get('swedish', [0]);
        // Populate variables used in the queries
        $published  = 1;
        $authorised = $this->app->getIdentity()->getAuthorisedViewLevels();
        $nullDate   = $db->getNullDate();
        $nowDate    = Date::getInstance()->toSql();

        // Initialize query
        // whereIn will automatically use the values and add prepared statements
        $query = $db->getQuery(true)
                    ->select(['*'])
                    ->from($db->quoteName('#__contact_details', 'a'));

        if (!empty($catid[0])) {
            $query->whereIn($db->quoteName('a.catid'), $catid);
        }

        if (!empty($tagid[0])) {
            $query
                ->join(
                    'LEFT',
                    $db->quoteName('#__contentitem_tag_map', 'b') . ' ON (' . $db->quoteName(
                        'a.id'
                    ) . ' = ' . $db->quoteName('b.content_item_id') . ')'
                )
                ->where($db->quoteName('b.type_alias') . ' = ' . $db->quote('com_contact.contact'))
                ->whereIn($db->quoteName('b.tag_id'), $tagid);
        }

        $query
            ->whereIn($db->quoteName('a.access'), $authorised)
            ->where($db->quoteName('a.published') . ' = :published')
            ->andWhere(
                [
                    $db->quoteName('a.publish_up') . ' = :nullDate1',
                    $db->quoteName('a.publish_up') . ' IS NULL',
                    $db->quoteName('a.publish_up') . ' <= :nowDate1',
                ]
            )
            ->andWhere(
                [
                    $db->quoteName('a.publish_down') . ' = :nullDate2',
                    $db->quoteName('a.publish_down') . ' IS NULL',
                    $db->quoteName('a.publish_down') . ' >= :nowDate2',
                ]
            )
            ->bind(':nowDate1', $nowDate)
            ->bind(':nowDate2', $nowDate)
            ->bind(':nullDate1', $nullDate)
            ->bind(':nullDate2', $nullDate)
            ->bind(':published', $published);

        $db->setQuery($query);

        $result = $db->loadObjectList();

        foreach ($result as $record) {
            // Add the targeted letter to each object
            $name = $record->name;

            if ($sortorder == 'ln') {
                $parser         = new FullNameParserHelper();
                $words          = $parser->parse_name($name);
                $record->letter = mb_substr($words['lname'], 0, 1, "utf8");
                $record->ln     = $words['lname'];
            } else {
                $record->letter = mb_substr($name, 0, 1, "utf8");
            }

            // Add the category name to each object
            $record->catname = $this->azCategory($record->catid);

            // Add custom fields to each object
            $record->customfields = $this->azCustomFields($record->id);
        }

        // remove objects where the selected letter is not the targeted letter
        if ($letter != Text::_('JALL')) {
            $result = array_filter($result, function ($a) use ($letter) {
                return $a->letter === $letter;
            });
        }

        $locale = 'en_US.UTF-8';

        if (in_array(1, $alphabet)) {
            $locale = 'sv_SE.UTF-8';
        }

        if (in_array(3, $alphabet)) {
            $locale = 'cs_CZ.UTF-8';
        }

        setlocale(LC_ALL, $locale);

        usort($result, function ($a, $b) use ($sortorder) {
            switch ($sortorder) {
                case 'fn' :
                    return strcoll($a->name, $b->name);
                    break;

                case 'ln' :
                    return strcoll($a->ln, $b->ln);
                    break;

                case 'sortfield' :
                    return strcmp($a->sortname1, $b->sortname1);
                    break;

                case 'component' :
                    return strnatcmp($a->ordering, $b->ordering);
                    break;

                default :
                    return strcoll($a->ln, $b->ln);
            }
        });

        // Get the true number of array entries
        $total_rows = sizeof($result);

        // Pagination if not All
        $result = ($pagination === 'All') ? $result : array_slice($result, $start, $pagination);

        return [$result, $total_rows, $start];
    }

    /**
     * Get the database.
     *
     * @return  DatabaseInterface
     *
     * @throws  RuntimeException May be thrown if the database has not been set.
     * @since   7.1.1
     */
    protected function getDatabase(): DatabaseInterface
    {
        if ($this->dbo) {
            return $this->dbo;
        }

        throw new RuntimeException('Database not set in ' . \get_class($this));
    }

    /**
     * Method to get category title from ID
     */
    public function azCategory($catid)
    {
        // access database object
        $db = $this->getDatabase();

        // initialize query
        $query = $db->getQuery(true)
                    ->select($db->quoteName('title'))
                    ->from($db->quoteName('#__categories'))
                    ->where($db->quoteName('id') . ' = :catid')
                    ->bind(':catid', $catid);

        $db->setQuery($query);

        return $db->loadResult();
    }

    /**
     * Method to get custom fields
     */
    public function azCustomFields($id)
    {
        // get custom fields by contact ID
        $azCustomFields = FieldsHelper::getFields('com_contact.contact', $id, true);
        // get custom field names
        $azCustomFieldNames = array_map(function ($o) {
            return $o->title;
        }, $azCustomFields);
        // get custom field IDs
        $azCustomFieldIDs = array_map(function ($o) {
            return $o->id;
        }, $azCustomFields);
        // load fields model
        \JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fields/models', 'FieldsModel');
        $azModel = \JModelLegacy::getInstance('Field', 'FieldsModel', ['ignore_request' => true]);
        // fetch values for custom field IDs
        $azCustomFieldValues = $azModel->getFieldValues($azCustomFieldIDs, $id);
        // create an array of field IDs as keys and field values as values
        $azMap = array_combine($azCustomFieldIDs, $azCustomFieldNames);
        // eliminate array entries with empty values
        $azIntersect = array_intersect_key($azMap, $azCustomFieldValues);
        // create an array setting the value of azIntersect as the keys of azCustomFieldValues
        $azCombine = [];
        foreach ($azIntersect as $key => $column) :
            $azCombine[$column] = $azCustomFieldValues[$key];
        endforeach;

        return $azCombine;
    }

    /**
     * Get module parameters
     *
     * @param   array  $params  An object containing the module parameters
     */
    public function getAZDirectory()
    {
        $doc = $this->app->getDocument();
        $wam = $doc->getWebAssetManager();

        if (!$wam->getRegistry()->exists('style', 'modazdirectory.bootstrap')) {
            $wam->getRegistry()->addExtensionRegistryFile('mod_azdirectory');
        }

        $nameHyperlink = $this->params->get('name_hyperlink', 0) == 1;

        if ($nameHyperlink == 1) :
            // Load only the standard Bootstrap modal and our custom Bootstrap styles
            $wam->useScript('bootstrap.modal');
            $wam->useStyle('mod_azdirectory.bootstrap');
        endif;

        // set a flag whether to load azModal JS
        $doc->addScriptOptions('mod_azdirectory', [
            'nameHyperlink' => $nameHyperlink,
        ]);

        // pass value for JALL language constant to Javascript
        Text::script('JALL');

        // load standard assets
        $wam->useStyle('mod_azdirectory.directory');

        $loadJS = $this->params->get('loadjs');
        if ($loadJS == 1) :
            $wam->useScript('mod_azdirectory.directory');
        else :
            $wam->useScript('mod_azdirectory.formsubmit');
        endif;

        // Get the database object
        $db = $this->getDatabase();
        // Get configuration parameters
        $sortorder = $this->params->get('sortorder');
        // Populate variables used in the queries
        $published  = 1;
        $authorised = $this->app->getIdentity()->getAuthorisedViewLevels();
        $nullDate   = $db->getNullDate();
        $nowDate    = Date::getInstance()->toSql();

        // initialize query
        // whereIn will automatically use the values and add prepared statements
        $query = $db->getQuery(true)
                    ->select($db->quoteName('name') . " AS name")
                    ->from($db->quoteName('#__contact_details', 'a'));

        $catid = $this->params->get('id');
        if (!empty($catid[0])) :
            $query->whereIn($db->quoteName('a.catid'), $catid);
        endif;

        $tagid = (array)$this->params->get('tags');
        if (!empty($tagid[0])) :
            $query
                ->join(
                    'LEFT',
                    $db->quoteName('#__contentitem_tag_map', 'b') . ' ON (' . $db->quoteName(
                        'a.id'
                    ) . ' = ' . $db->quoteName('b.content_item_id') . ')'
                )
                ->where($db->quoteName('b.type_alias') . ' = ' . $db->quote('com_contact.contact'))
                ->whereIn($db->quoteName('b.tag_id'), $tagid);
        endif;

        $query
            ->whereIn($db->quoteName('a.access'), $authorised)
            ->where($db->quoteName('a.published') . ' = :published')
            ->andWhere(
                [
                    $db->quoteName('a.publish_up') . ' = :nullDate1',
                    $db->quoteName('a.publish_up') . ' IS NULL',
                    $db->quoteName('a.publish_up') . ' <= :nowDate1',
                ]
            )
            ->andWhere(
                [
                    $db->quoteName('a.publish_down') . ' = :nullDate2',
                    $db->quoteName('a.publish_down') . ' IS NULL',
                    $db->quoteName('a.publish_down') . ' >= :nowDate2',
                ]
            )
            ->order($db->quoteName('a.name'))
            ->bind(':nowDate1', $nowDate)
            ->bind(':nowDate2', $nowDate)
            ->bind(':nullDate1', $nullDate)
            ->bind(':nullDate2', $nullDate)
            ->bind(':published', $published);

        $db->setQuery($query);
        $rows  = $db->loadAssocList('name');
        $names = array_keys($rows);

        $letters = $alphabets = [];

        foreach ($names as $key => $name):
            if ($sortorder == 'ln') :
                $parser    = new FullNameParserHelper();
                $words     = $parser->parse_name($name);
                $letters[] = mb_substr($words['lname'], 0, 1, "utf8");
            else:
                $letters[] = mb_substr($name, 0, 1, "utf8");
            endif;
        endforeach;

        $alphabet = $this->params->get('swedish');

        // if no Language is selected, default to English (0)
        if (empty($alphabet)) {
            $alphabet = [0];
            $this->params->set('swedish', $alphabet);
        }

        // if more than 1 alphabet is selected
        if (sizeof($alphabet) > 1):
            // return the first/last letters based on fn/ln sortorder
            $letters = array_unique($letters);

            foreach ($letters as $letter) {
                // get the unicode values
                $unicode = dechex(mb_ord($letter));

                // ensure the unicode values are 4 digits by prepending zeroes
                $unicode = $this->azStrPadUnicode($unicode, 4, '0', STR_PAD_LEFT);

                // add them into Latin or Cyrillic arrays
                if (($unicode >= '0041') && ($unicode <= '024f')) {
                    $alphabets['Latin'][] = $letter;
                } elseif (($unicode >= '0400') && ($unicode <= '04ff')) {
                    $alphabets['Cyrillic'][] = $letter;
                }
            }

            // sort them alphabetically
            if (array_key_exists('Latin', $alphabets) && sizeof($alphabets['Latin']) > 0):
                sort($alphabets['Latin'], SORT_LOCALE_STRING);
            endif;

            if (array_key_exists('Cyrillic', $alphabets) && sizeof($alphabets['Cyrillic']) > 0):
                sort($alphabets['Cyrillic'], SORT_LOCALE_STRING);
            endif;

        else:
            // 1 alphabet is selected
            // convert alphabet array to a string
            $alphabet = array_shift($alphabet);

            $english = range("A", "Z");

            $swedish = ["Å", "Ä", "Ö"];

            $cyrillic = [
                "А",
                "Б",
                "В",
                "Г",
                "Д",
                "Е",
                "Ё",
                "Ж",
                "З",
                "И",
                "Й",
                "К",
                "Л",
                "М",
                "Н",
                "О",
                "П",
                "Р",
                "С",
                "Т",
                "У",
                "Ф",
                "Х",
                "Ц",
                "Ч",
                "Ш",
                "Щ",
                "Ъ",
                "Ы",
                "Ь",
                "Э",
                "Ю",
                "Я",
            ];

            $czech = [
                "A",
                "Á",
                "B",
                "C",
                "Č",
                "D",
                "Ď",
                "E",
                "É",
                "Ě",
                "F",
                "G",
                "H",
                "Ch",
                "I",
                "Í",
                "J",
                "K",
                "L",
                "M",
                "N",
                "Ň",
                "O",
                "Ó",
                "P",
                "Q",
                "R",
                "Ř",
                "S",
                "Š",
                "T",
                "Ť",
                "U",
                "Ú",
                "Ů",
                "V",
                "W",
                "X",
                "Y",
                "Ý",
                "Z",
                "Ž",
            ];

            switch ($alphabet):
                case 1:
                    $alphabets['Latin'] = array_merge($english, $swedish);
                    break;
                case 2:
                    $alphabets['Cyrillic'] = $cyrillic;
                    break;
                case 3:
                    $alphabets['Latin'] = $czech;
                    break;
                default:
                    $alphabets['Latin'] = $english;
            endswitch;
        endif;

        $tmpl    = [];
        $tmpl[0] = $alphabets;
        $tmpl[1] = array_unique($letters);

        return $tmpl;
    }

    private function azStrPadUnicode($str, $pad_len, $pad_str = ' ', $dir = STR_PAD_RIGHT)
    {
        $str_len     = mb_strlen($str);
        $pad_str_len = mb_strlen($pad_str);
        if (!$str_len && ($dir == STR_PAD_RIGHT || $dir == STR_PAD_LEFT)) {
            $str_len = 1; // @debug
        }
        if (!$pad_len || !$pad_str_len || $pad_len <= $str_len) {
            return $str;
        }

        $result = null;
        $repeat = ceil($str_len - $pad_str_len + $pad_len);
        if ($dir == STR_PAD_RIGHT) {
            $result = $str . str_repeat($pad_str, $repeat);
            $result = mb_substr($result, 0, $pad_len);
        } else {
            if ($dir == STR_PAD_LEFT) {
                $result = str_repeat($pad_str, $repeat) . $str;
                $result = mb_substr($result, -$pad_len);
            } else {
                if ($dir == STR_PAD_BOTH) {
                    $length = ($pad_len - $str_len) / 2;
                    $repeat = ceil($length / $pad_str_len);
                    $result = mb_substr(str_repeat($pad_str, $repeat), 0, floor($length))
                              . $str
                              . mb_substr(str_repeat($pad_str, $repeat), 0, ceil($length));
                }
            }
        }

        return $result;
    }

    /**
     * Method to get the default option for the select option
     */
    public function azFirstOption($sortorder)
    {
        $language = $this->app->getLanguage();
        $language->load('mod_azdirectory');

        if ($sortorder == 'ln') :
            $modazfirstoption = Text::_('MOD_AZDIRECTORY_SORTORDER_LN');
        else :
            $modazfirstoption = Text::_('MOD_AZDIRECTORY_SORTORDER_FN');
        endif;

        return $modazfirstoption;
    }

    /**
     * Method to format addresses
     */
    public function azFormatAddress($contact, $postcodeFirst)
    {
        if ($this->azVerify('suburb', $contact) || $this->azVerify('state', $contact) || $this->azVerify(
                'postcode',
                $contact
            )) :

            $lines = [];

            if ($postcodeFirst == 1) :
                // international address format
                $line = [];
                if ($this->azVerify('postcode', $contact)) {
                    $line[] = '<span>' . $contact->postcode . '</span>';
                }
                if ($this->azVerify('suburb', $contact)) {
                    $line[] = '<span>' . $contact->suburb . '</span>';
                }
                if ($this->azVerify('state', $contact)) {
                    $line[] = '<span>' . $contact->state . '</span>';
                }
                $lines[] = implode(' ', $line);
            else :
                // US address format
                $line = [];
                if ($this->azVerify('suburb', $contact)) {
                    $line[] = '<span>' . $contact->suburb . '</span>';
                }
                if ($this->azVerify('state', $contact)) {
                    $line[] = '<span>' . $contact->state . '</span>';
                }
                if (count($line)) {
                    $line = [implode(', ', $line)];
                }
                if ($this->azVerify('postcode', $contact)) {
                    $line[] = '<span>' . $contact->postcode . '</span>';
                }
                $lines[] = implode(' ', $line);
            endif;

            return $lines[0];

        endif;

        return "";
    }

    /**
     * Method to verify valid user data
     */
    public function azVerify($key, $values)
    {
        $param = $this->params->get('show_' . $key);
        $value = $values->$key;
        return (($param == 1) && ($value)) ? 1 : 0;
    }

    /**
     * @param   CMSApplication|null  $app
     */
    public function setApplication(?CMSApplication $app): void
    {
        $this->app = $app;
    }
}