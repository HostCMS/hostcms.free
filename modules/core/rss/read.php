<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Parse RSS 2.0
 *
 * @package HostCMS
 * @subpackage Core\Rss
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Rss_Read
{
	/**
	 * File content
	 * @var string
	 */
	protected $_data = NULL;

	/**
	 * Внутренняя кодировка, используемая при работе с RSS
	 *
	 * @var string
	 */
	protected $_encoding = '';

	/**
	 * Пустой массив с полями записи
	 *
	 * @var array
	 */
	static protected $_defaultRow = array(
		'title' => '',
		'link' => '',
		'description' => '',
		'category' => '',
		'pubdate' => '',
		'yandex:full-text' => ''
	);
	
	/**
	 * Пустой массив с полями канала
	 *
	 * @var array
	 */
	static protected $_defaultChanel = array(
		'title' => '',
		'link' => '',
		'description' => '',
		'image' => array(
			'title' => '',
			'link' => '',
			'url' => ''
		)
	);
	
	/**
	 * XML parser
	 */
	protected $_xml_parser = NULL;

	/**
	 * XML tag
	 * @var string
	 */
	protected $_tag = NULL;
	
	/**
	 * RSS
	 * @var string
	 */
	protected $_rss = NULL;

	/**
	 * Массив с данными о канале
	 *
	 * @var array
	 */
	protected $_chanel = NULL;

	/**
	 * Массив элементов
	 */
	protected $_items = NULL;

	/**
	 * Items count
	 * @var int
	 */
	protected $_itemCount = 0;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->_chanel = self::$_defaultChanel;
	}
	
	/**
	 * Callback function for xml parser
	 * @param string $parser reference to the XML parser to set up character data handler function
	 * @param string $name contains the name of the element for which this handler is called
	 * @param array $attrs contains an associative array with the element's attributes
	 * @return self
	 */
	public function fetchOpen($parser, $name, $attrs)
	{
		if (count($attrs))
		{
			foreach ($attrs as $key => $value)
			{
				$this->_items[$this->_itemCount][mb_strtolower($name)][mb_strtolower($key)] = $value;
			}
		}

		if ($name == 'RSS')
		{
			$this->_rss = '^RSS';
		}
		elseif ($name == 'RDF:RDF')
		{
			$this->_rss = '^RDF:RDF';
		}

		$this->_tag .= '^' . $name;
		
		return $this;
	}

	/**
	 * Callback function for xml parser
	 * @param string $parser reference to the XML parser to set up character data handler function
	 * @param string $name contains the name of the element for which this handler is called
	 * @return self
	 */
	public function fetchClose($parser, $name)
	{
		if ($name == 'ITEM')
		{
			$this->_itemCount++;
			if (!isset($this->_items[$this->_itemCount]))
			{
				$this->_items[$this->_itemCount] = self::$_defaultRow;
			}
		}

		$this->_tag = mb_substr($this->_tag, 0, mb_strrpos($this->_tag, '^'));
		
		return $this;
	}

	/**
	 * Callback function for xml parser
	 * @param string $parser reference to the XML parser to set up character data handler function
	 * @param string $data contains the character data as a string
	 * @return self
	 */
	public function characterData($parser, $data)
	{
		$this->_rssChannel = '';

		if ($data)
		{
			if (strtoupper($this->_encoding) != 'UTF-8')
			{
				$data = @iconv($this->_encoding, "UTF-8//IGNORE//TRANSLIT", $data);
			}

			if ($this->_tag == $this->_rss . '^CHANNEL^TITLE') {
				$this->_chanel['title'] .= $data;
			} elseif ($this->_tag == $this->_rss . '^CHANNEL^LINK') {
				$this->_chanel['link'] .= $data;
			} elseif ($this->_tag == $this->_rss . '^CHANNEL^DESCRIPTION') {
				$this->_chanel['description'] .= $data;
			}

			if ($this->_rss == '^RSS') {
				$this->_rssChannel = '^CHANNEL';
			}

			if ($this->_tag == $this->_rss . $this->_rssChannel . '^ITEM^TITLE') {
				$this->_items[$this->_itemCount]['title'] .= $data;
			} elseif ($this->_tag == $this->_rss . $this->_rssChannel . '^ITEM^LINK') {
				$this->_items[$this->_itemCount]['link'] .= $data;
			} elseif ($this->_tag == $this->_rss . $this->_rssChannel . '^ITEM^YANDEX:FULL-TEXT') {
				$this->_items[$this->_itemCount]['yandex:full-text'] .= $data;
			} elseif ($this->_tag == $this->_rss . $this->_rssChannel . '^ITEM^FULL-TEXT') {
				$this->_items[$this->_itemCount]['yandex:full-text'] .= $data;
			} elseif ($this->_tag == $this->_rss . $this->_rssChannel . '^ITEM^FULLTEXT') {
				$this->_items[$this->_itemCount]['yandex:full-text'] .= $data;
			} elseif ($this->_tag == $this->_rss . $this->_rssChannel . '^ITEM^TEXT') {
				$this->_items[$this->_itemCount]['yandex:full-text'] .= $data;
			} elseif ($this->_tag == $this->_rss . $this->_rssChannel . '^ITEM^CONTENT:ENCODED') {
				$this->_items[$this->_itemCount]['yandex:full-text'] .= $data;
			} elseif ($this->_tag == $this->_rss . $this->_rssChannel . '^ITEM^DESCRIPTION') {
				$this->_items[$this->_itemCount]['description'] .= $data;
			} elseif ($this->_tag == $this->_rss . $this->_rssChannel . '^ITEM^CATEGORY') {
				$this->_items[$this->_itemCount]['category'][] = $data;
			} elseif ($this->_tag == $this->_rss . $this->_rssChannel . '^ITEM^DC:SUBJECT') {
				$this->_items[$this->_itemCount]['category'][] = $data;
			} elseif ($this->_tag == $this->_rss . $this->_rssChannel . '^ITEM^PUBDATE') {
				$this->_items[$this->_itemCount]['pubdate'] .= $data;
			} elseif ($this->_tag == $this->_rss . $this->_rssChannel . '^IMAGE^TITLE') {
				$this->_chanel['image']['title'] .= $data;
			} elseif ($this->_tag == $this->_rss . $this->_rssChannel . '^IMAGE^LINK') {
				$this->_chanel['image']['link'] .= $data;
			} elseif ($this->_tag == $this->_rss . $this->_rssChannel . '^IMAGE^URL') {
				$this->_chanel['image']['url'] .= $data;
			}
		}
		
		return $this;
	}

	/**
	 * Clear object
	 * @return self
	 */
	public function clear()
	{
		$this->_chanel = self::$_defaultChanel;
		$this->_data = $this->_rss = $this->_tag = NULL;

		return $this;
	}

	/**
	 * Load URL 
	 * @param string $url URL
	 * @return self
	 */
	public function loadUrl($url)
	{
		$url = trim($url);

		$Core_Http = Core_Http::instance()
			->url($url)
			->execute();

		$this->_data = $Core_Http->getBody();

		return $this;
	}

	/**
	 * Load file
	 * @param string $path file path
	 * @return self
	 */
	public function loadFile($path)
	{
		$this->_data = Core_File::read($path);
		return $this;
	}

	/**
	 * Чтение данных из RSS
	 *
	 * @param array $encoding кодировка документа
	 * @return array ассоциативнй массив
	 * <br />[chanel] => Array
	 * - (
	 * <br />[title] =>
	 * <br />[link] =>
	 * <br />[description] =>
	 * <br />[image] => Array
	 * <br />(
	 * <br />[title] =>
	 * <br />[link] =>
	 * <br />[url] =>
	 * <br />)
	 * <br />)
	 * <br />[items] => Array
	 * <br />(
	 * <br />[] => Array
	 * <br />(
	 * <br />[title] =>
	 * <br />[link] =>
	 * <br />[desc] =>
	 * <br />[category] =>
	 * <br />[pubdate] =>
	 * <br />[yandex:full-text] =>
	 * <br />)
	 * <br />)
	 */
	public function parse($encoding = NULL)
	{
		$this->_itemCount = 0;

		$this->_items = array (0 => self::$_defaultRow);

		$this->_xml_parser = xml_parser_create($encoding);
		$this->_encoding = xml_parser_get_option($this->_xml_parser, XML_OPTION_TARGET_ENCODING);

		//This is the RIGHT WAY to set everything inside the object.
		xml_set_object($this->_xml_parser, $this);

		xml_set_element_handler($this->_xml_parser, 'fetchOpen', 'fetchClose');
		xml_set_character_data_handler($this->_xml_parser, 'characterData');

		/*
		$this->_xml_parser = xml_parser_create();
		xml_set_element_handler($this->_xml_parser, Array(&$this, 'fetchOpen'), Array(&$this, 'fetchClose'));
		xml_set_character_data_handler($this->_xml_parser, Array(&$this, 'characterData'));
		*/
		if (!empty($this->_data))
		{
			$xmlresult = xml_parse($this->_xml_parser, $this->_data);
			if (!$xmlresult)
			{
				throw new Core_Exception('Parse error %error: line %line', array(
					'%error' => xml_error_string(xml_get_error_code($this->_xml_parser)),
					'%line' => xml_get_current_line_number($this->_xml_parser))
				);
			}
		}
		else
		{
			throw new Core_Exception('Rss data is empty');
		}

		xml_parser_free($this->_xml_parser);

		return array(
			'chanel' => $this->_chanel,
			'items' => $this->_items
		);
	}
}