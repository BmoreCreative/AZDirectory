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
define( 'MODAZPATH', 'modules/mod_azdirectory/assets/' );

class modAZDirectoryHelper
{
	private $params = null;
	private static $_azInstance = null;
	
	public static function azInstance( &$params, $type )
	{
		if( null == self::$_azInstance || empty( $_azInstance[$type] ) ) :
			self::$_azInstance[$type] = new self( $params );
		endif;
	
		return self::$_azInstance[$type];
	}

	public function __construct( &$params )
	{
		mb_internal_encoding( 'utf-8' ); // @important
		$this->params = $params;
	}

    /**
     * @param   array  $params An object containing the module parameters
     *
     * @access public
     */
	 
	// get module parameters
    public function getAZDirectory()
    {
		$doc = JFactory::getDocument();
		
		$nameHyperlink = $this->params->get( 'name_hyperlink' );

		if( $nameHyperlink == 1 ) :
			// load standard Bootstrap and custom Bootstrap styles
			JHtml::_( 'bootstrap.framework' );
			$doc->addStyleSheet( MODAZPATH . 'modazbootstrap.css' );
			// get Display Format for Contacts
			$display_format = JComponentHelper::getParams( 'com_contact' )->get( 'presentation_style' );
			$doc->addScriptDeclaration( 'var modazModalStyle={"displayFormat":"' . $display_format . '"};' );
		endif;
		
		// set a flag whether to load azModal JS
		$doc->addScriptDeclaration( 'var modazNameHyperlink=' . $nameHyperlink . ';' );
				
		// pass value for JALL language constant to Javascript
		JText::script( 'JALL' );
		
		// load standard assets
		$doc->addStyleSheet( MODAZPATH . 'modazdirectory.css' );

		$loadJS = $this->params->get( 'loadjs' );
		if( $loadJS == 1 ) :
			$doc->addScript( MODAZPATH . 'modazdirectory.js' );
		else :
			$doc->addScript( MODAZPATH . 'modazformsubmit.js' );
		endif;

		$doc->addScript( MODAZPATH . 'svgxuse.min.js', 'text/javascript', true, false );

		// access database object
		$db = JFactory::getDbo();
		
		// get sort order
		$sortorder = $this->params->get( 'sortorder' );
		
		// initialize query
		$query = $db->getQuery( true )
					->select( $db->quoteName( 'name' ) . " AS name" )
					->from( $db->quoteName( '#__contact_details', 'a' ) );
		
		$catid = $this->params->get( 'id' );
		
		if( !empty( $catid[0] ) ) :
			$query->where( $db->quoteName( 'a.catid' ) . ' IN ( ' . implode( ',', $catid ) . ' )' );
		endif;
	
		$tagid = $this->params->get( 'tags' );
		if( !empty( $tagid[0] ) ) :
			$query
				->join( 'LEFT', $db->quoteName( '#__contentitem_tag_map', 'b' ) . ' ON (' . $db->quoteName( 'a.id' ) . ' = ' . $db->quoteName( 'b.content_item_id' ) . ')' )
				->where( $db->quoteName( 'b.type_alias' ) . ' = ' . $db->quote( 'com_contact.contact' ) )
				->where( $db->quoteName( 'b.tag_id' ) . ' IN ( ' . implode( ',', $tagid ) . ')' );
		endif;
		
		$query
			->where( $db->quoteName( 'a.published' ) . ' = 1' )
			->order( $db->quoteName( 'a.name' ) );
		
		$db->setQuery( $query );
		$rows = $db->loadAssocList( 'name' );
		$names = array_keys( $rows );
	
		$letters = $alphabets = array();
		
		foreach( $names as $key => $name ):
			if( $sortorder == 'fn' ) :
				$letters[] = mb_substr( $name, 0, 1, "utf8" );
			else: 
				$parser = new FullNameParser();
				$words = $parser->parse_name( $name );
				$letters[] = mb_substr( $words['lname'], 0, 1, "utf8" );
			endif;
		endforeach;
		
		$alphabet = $this->params->get( 'swedish' );
		
		// if no Language is selected, default to English (0)
		if( empty( $alphabet ) ){
			$alphabet = array( 0 );
			$this->params->set( 'swedish', $alphabet );
		}
		
		// if more than 1 alphabet is selected
		if( sizeof( $alphabet ) > 1 ):
			// return the first/last letters based on fn/ln sortorder
			$letters = array_unique( $letters );
			
			foreach( $letters as $letter ){
				
				// get the unicode values
				if( version_compare( phpversion(), '7.2', 'ge' ) ){
					$unicode = dechex( mb_ord( $letter ) );
				} else {
					$unicode = dechex( modAZDirectoryHelper::_azMBOrd( $letter ) );
				}
				
				// ensure the unicode values are 4 digits by prepending zeroes
				$unicode = modAZDirectoryHelper::_azStrPadUnicode( $unicode, 4, '0', STR_PAD_LEFT );
				
				// add them into Latin or Cyrillic arrays
				if( ( $unicode >= '0041' ) && ( $unicode <= '024f' ) )
					$alphabets['Latin'][] = $letter;
				elseif( ( $unicode >= '0400' ) && ($unicode <= '04ff' ) )
					$alphabets['Cyrillic'][] = $letter;
			}
			
			// sort them alphabetically
			if( array_key_exists( 'Latin', $alphabets ) && sizeof( $alphabets['Latin'] ) > 0 ):
				sort( $alphabets['Latin'], SORT_LOCALE_STRING );
			endif;

			if( array_key_exists( 'Cyrillic', $alphabets ) && sizeof( $alphabets['Cyrillic'] ) > 0 ):
				sort( $alphabets['Cyrillic'], SORT_LOCALE_STRING );
			endif;

		else:
			// 1 alphabet is selected
			// convert alphabet array to a string
			$alphabet = array_shift( $alphabet );
			
			$english = range( "A", "Z" );
			
			$swedish = array( "Å", "Ä", "Ö" );
			
			$cyrillic = array(
				"А", "Б", "В", "Г", "Д", "Е", "Ё",
				"Ж", "З", "И", "Й", "К", "Л", "М",
				"Н", "О", "П", "Р", "С", "Т", "У",
				"Ф", "Х", "Ц", "Ч", "Ш", "Щ", "Ъ",
				"Ы", "Ь", "Э", "Ю", "Я"
			);
			
			$czech = array(
				"A", "Á", "B", "C", "Č", "D", "Ď",
				"E", "É", "Ě", "F", "G", "H", "Ch",
				"I", "Í", "J", "K", "L", "M", "N",
				"Ň", "O", "Ó", "P", "Q", "R", "Ř",
				"S", "Š", "T", "Ť", "U", "Ú", "Ů",
				"V", "W", "X", "Y", "Ý", "Z", "Ž" 
			);
		
			switch( $alphabet ):
				case 1:
					$alphabets['Latin'] = array_merge( $english, $swedish );
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
		
		$tmpl = array();
		$tmpl[0] = $alphabets;
		$tmpl[1] = array_unique( $letters );
		
		return $tmpl;
    }

	/**
	 * Method to get contact information based on first letter of last name
	 *
	 * @access    public
	 */
	public static function getContactsAjax()
	{
		$app = JFactory::getApplication();
		
		// get the data
		$azdata = $app->input->getString( 'data' );
		$lastletter = filter_var( $azdata[0], FILTER_SANITIZE_STRING );
		$start = filter_var( $azdata[1], FILTER_SANITIZE_NUMBER_INT );
		$title = filter_var( $azdata[2], FILTER_SANITIZE_STRING );
		
		// get module parameters
		$module = JModuleHelper::getModule( 'azdirectory', $title );
		$params = new JRegistry( $module->params );
		
		$az = self::azInstance( $params, $module->id );
		$modAZAssetsPath = JUri::base() . 'modules/' . $module->module . '/assets/';
		
		// get the contacts
		list( $contacts, $total, $start ) = $az->azGenerateQuery( $lastletter, $start, $params );
		
		// die();
		
		// get parameters specific to the module configuration
		// e.g. $show_image = $params->get('show_image');
		// NOTE: id (category IDs) is an array, so a variable is not created and not used in default.php
		foreach ( $params as $key => $value ):
			if( is_string( $value ) ) :
				$$key = htmlspecialchars( $value );
			endif;
		endforeach;
		
		$azdirectory = $az->getAZDirectory();
		
		ob_clean();
		ob_start();
		
		// checks for layout override first, then checks for original
		require_once JModuleHelper::getLayoutPath( 'mod_azdirectory', $params->get( 'layout', 'default' ) );

		ob_get_contents();

		exit;
		$app->close();
	}

	/**
	 * Method to verify valid user data
	 *
	 * @access    public
	 */
	public function azVerify( $key, $values ){
		$param = $this->params->get( 'show_' . $key );
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

	/**
	 * Method to format name
	 *
	 * @access    public
	 */	
	public static function azFormatName( $name, $lastnameFirst ){
		if( $lastnameFirst == 1 ) :
			$parser = new FullNameParser();
			$words = $parser->parse_name( $name );
			$lastname = $words['lname'];
			$firstname = $words['fname'];
			$name = $lastname . ", " . $firstname;
		endif;
		return $name;
	}
	
	/**
	 * Method to sanitize telephone numbers
	 *
	 * @access    public
	 */
	public static function azSanitizeTelephone( $telephone ){
		return str_replace( array( "+", "-" ), "", filter_var( $telephone, FILTER_SANITIZE_NUMBER_INT ) );
	}
	
	/**
	 * Method to sanitize URLs
	 *
	 * @access    public
	 */
	public static function azSanitizeURL( $url ){
		$filter = JFilterInput::getInstance();
		return $filter->clean( $url, "string" );
	}

	/**
	 * Method to get category title from ID
	 *
	 * @access    public
	 */
	public static function azCategory( $catid ){
		// access database object
		$db = JFactory::getDBo();

		// initialize query
		$query = $db->getQuery( true )
					->select( $db->quoteName( 'title' ) )
					->from( $db->quoteName( '#__categories' ) )
					->where( $db->quoteName( 'id' ) . ' = ' . $catid );
				
		$db->setQuery( $query );
		
		return $db->loadResult();
	}
	
	/**
	 * Method to format addresses
	 *
	 * @access    public
	 */	
	public function azFormatAddress( $contact, $postcodeFirst )
	{
		if ( $this->azVerify( 'suburb', $contact ) || $this->azVerify( 'state', $contact ) || $this->azVerify( 'postcode', $contact ) ) :
			
			$lines = array();
			
			if ( $postcodeFirst == 1 ) :
				// international address format
				$line = array();
				if ( $this->azVerify( 'postcode', $contact ) ) $line[] = '<span>' . $contact->postcode . '</span>';
				if ( $this->azVerify( 'suburb', $contact ) ) $line[] = '<span>' . $contact->suburb . '</span>';
				if ( $this->azVerify( 'state', $contact ) ) $line[] = '<span>' . $contact->state . '</span>';
				$lines[] = implode( ' ', $line );
			else :
				// US address format
				$line = array();
				if ( $this->azVerify( 'suburb', $contact ) ) $line[] = '<span>' . $contact->suburb . '</span>';
				if ( $this->azVerify( 'state', $contact ) ) $line[] = '<span>' . $contact->state . '</span>';
				if ( count( $line ) ) $line = array( implode( ', ', $line ) );
				if ( $this->azVerify( 'postcode', $contact ) ) $line[] = '<span>' . $contact->postcode . '</span>';
				$lines[] = implode( ' ', $line );	
			endif;
		
			return $lines[0];
		
		endif;
		
		return "";
	}

	/**
	 * Method to get the default option for the select option
	 *
	 * @access    public
	 */	
	public static function azFirstOption( $sortorder )
	{
		$language = JFactory::getLanguage();
		$language->load( 'mod_azdirectory' );

		if ( $sortorder == 'fn' ) :
			$modazfirstoption = JText::_( 'MOD_AZDIRECTORY_SORTORDER_FN' );
		else :
			$modazfirstoption = JText::_( 'MOD_AZDIRECTORY_SORTORDER_LN' );
		endif;

		return $modazfirstoption;
	}

	/**
	 * Method to generate SQL query
	 *
	 * @access    public
	 */
	public static function azGenerateQuery( $letter, $start, $params )
	{
		require_once dirname(__FILE__) . '/helpers/parser.php';

		// get category id
		$catid = $params->get( 'id' );
		
		// get the tags
		$tagid = $params->get( 'tags' );
		
		// get the sort order
		$sortorder = $params->get( 'sortorder' );
		
		// get the pagination setting
		$pagination = $params->get( 'pagination' );
		
		// get the alphabet
		$alphabet = $params->get( 'swedish' );
						
		// access database object
		$db = JFactory::getDBo();

		// initialize query
		$query = $db->getQuery( true )
					->select( array('*') )
					->from( $db->quoteName( '#__contact_details', 'a' ) );
		
		if( !empty( $catid[0] ) ) :
			$query->where( $db->quoteName( 'a.catid' ) . ' IN ( ' . implode( ',', $catid ) . ' )' );
		endif;
		
		if( !empty( $tagid[0] ) ) :
			$query
				->join( 'LEFT', $db->quoteName( '#__contentitem_tag_map', 'b' ) . ' ON (' . $db->quoteName( 'a.id' ) . ' = ' . $db->quoteName( 'b.content_item_id' ) . ')' )
				->where( $db->quoteName( 'b.type_alias' ) . ' = ' . $db->quote( 'com_contact.contact' ) )
				->where( $db->quoteName( 'b.tag_id' ) . ' IN ( ' . implode( ',', $tagid ) . ')' );
		endif;

		$query->where($db->quoteName( 'a.published' ) . ' = 1' );
		
		$db->setQuery( $query );

		$result = $db->loadObjectList();

		// add the targeted letter to each object
		foreach( $result as $record ):
			$name = $record->name;
			
			if( $sortorder == 'fn' ) :
				$record->letter = mb_substr( $name, 0, 1, "utf8" );
			else: 
				$parser = new FullNameParser();
				$words = $parser->parse_name( $name );
				$record->letter = mb_substr( $words['lname'], 0, 1, "utf8" );
				$record->ln = $words['lname'];
			endif;
		endforeach;
				
		// remove objects where the selected letter is not the targeted letter
		if( $letter != JText::_( 'JALL' ) ) :
			$result = array_filter( $result, function( $a ) use ( $letter ){
				return $a->letter === $letter;
			});
		endif;
		
		$locale = 'en_US.UTF-8';
		if( in_array( 1, $alphabet ) ) $locale = 'sv_SE.UTF-8';
		if( in_array( 3, $alphabet ) ) $locale = 'cs_CZ.UTF-8';
		setlocale( LC_ALL, $locale );
		
		usort( $result, function( $a, $b ) use ( $sortorder ){
			switch( $sortorder ) :
				case 'fn' :
					return strcoll( $a->name, $b->name );
					break;
				case 'ln' :
					return strcoll( $a->ln, $b->ln );
					break;
				case 'sortfield' :
					return strcmp( $a->sortname1, $b->sortname1 );
					break;
				case 'component' :
					return strcmp( $a->ordering, $b->ordering );
					break;
				default :
					return strcoll( $a->ln, $b->ln );
			endswitch;
		});

		// get the true number of array entries
		$total_rows = sizeof( $result );

		// pagination if not All
		if( $pagination !== 'All' ) :
			$result = array_slice( $result, $start, $pagination );
		endif;
		
		return array( $result, $total_rows, $start );
	}
	
	/**
	 * Method to get numeric value of character as DEC string
	 */
	private static function _azMBConvertEncoding( $str, $to_encoding, $from_encoding = NULL )
	{
		return iconv( ( $from_encoding === NULL ) ? mb_internal_encoding() : $from_encoding, $to_encoding, $str );
	}

	private static function _azMBOrd( $char, $encoding = 'UTF-8' )
	{
		if( $encoding === 'UCS-4BE' ){
			list( , $ord ) = ( strlen( $char ) === 4 ) ? @unpack('N', $char ) : @unpack( 'n', $char );
			return $ord;
		} else {
			return modAZDirectoryHelper::_azMBOrd( modAZDirectoryHelper::_azMBConvertEncoding( $char, 'UCS-4BE', $encoding ), 'UCS-4BE' );
		}
	}

	private static function _azStrPadUnicode( $str, $pad_len, $pad_str = ' ', $dir = STR_PAD_RIGHT )
	{
		$str_len = mb_strlen( $str );
		$pad_str_len = mb_strlen( $pad_str );
		if( !$str_len && ( $dir == STR_PAD_RIGHT || $dir == STR_PAD_LEFT ) ){
			$str_len = 1; // @debug
		}
		if( !$pad_len || !$pad_str_len || $pad_len <= $str_len ){
			return $str;
		}
	   
		$result = null;
		$repeat = ceil( $str_len - $pad_str_len + $pad_len );
		if( $dir == STR_PAD_RIGHT ){
			$result = $str . str_repeat( $pad_str, $repeat );
			$result = mb_substr( $result, 0, $pad_len );
		} else if( $dir == STR_PAD_LEFT ){
			$result = str_repeat( $pad_str, $repeat ) . $str;
			$result = mb_substr( $result, -$pad_len );
		} else if( $dir == STR_PAD_BOTH ){
			$length = ( $pad_len - $str_len ) / 2;
			$repeat = ceil( $length / $pad_str_len );
			$result = mb_substr( str_repeat( $pad_str, $repeat ), 0, floor( $length ) )
						. $str
						   . mb_substr( str_repeat( $pad_str, $repeat ), 0, ceil( $length ) );
		}
	   
		return $result;
	}	
}