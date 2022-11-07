<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * XSL.
 *
 * <code>
 * $return = Xsl_Processor::instance()
 *	->xml($sXml)
 *	->xsl($sXsl)
 *	->process();
 *
 * echo $return;
 * </code>
 *
 * @package HostCMS
 * @subpackage Xsl
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Xsl_Processor
{
	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = NULL;

	/**
	 * Delete XML header
	 * @var boolean
	 */
	protected $_deleteXmlHeader = NULL;

	/**
	 * Constructor.
	 */
	protected function __construct()
	{
		$this->_deleteXmlHeader = !defined('DELETE_XML_HEADER') || DELETE_XML_HEADER;
	}

	/**
	 * Execute processor
	 * @return mixed
	 */
	abstract public function process();

	/**
	 * XML
	 * @var string
	 */
	protected $_xml = NULL;

	/**
	 * Set XML
	 * @param string $xml XML
	 * @return self
	 */
	public function xml($xml)
	{
		$this->_xml = $xml;
		return $this;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 */
	public function getXml()
	{
		return $this->_xml;
	}

	/**
	 * XSL
	 * @var Xsl_Model
	 */
	protected $_xsl = NULL;

	/**
	 * Set XSL
	 * @param Xsl_Model $oXsl XSL
	 * @return self
	 */
	public function xsl(Xsl_Model $oXsl)
	{
		$this->_xsl = $oXsl;
		return $this;
	}

	/**
	 * Get XSL
	 * @return Xsl_Model
	 */
	public function getXsl()
	{
		return $this->_xsl;
	}

	/**
	 * formatOutput
	 * @return NULL|TRUE|FALSE
	 */
	protected $_formatOutput = NULL;

	/**
	 * Set formatOutput
	 * @param NULL|TRUE|FALSE $formatOutput
	 * @return self
	 */
	public function formatOutput($formatOutput)
	{
		$this->_formatOutput = $formatOutput;
		return $this;
	}

	/**
	 * Clear XSL
	 * @return self
	 */
	public function clear()
	{
		$this->_xml = $this->_xsl = NULL;
		return $this;
	}

	/**
	 * Clear XMLNS attribute
	 * @param string source data
	 * @return string
	 */
	protected function _clearXmlns($sXsl)
	{
		return self::clearXmlns($sXsl);
	}
	
	/**
	 * Clear XMLNS attribute
	 * @param string source data
	 * @return string
	 */
	static public function clearXmlns($sXsl)
	{
		/*$sXsl = str_replace('exclude-result-prefixes="hostcms"', '', $sXsl);
		$sXsl = preg_replace('/xmlns:hostcms="[^"]*"/', '', $sXsl);*/
		$sXsl = preg_replace('/hostcms:[a-zA-Z]*="[^"]*"/', '', $sXsl);
		return $sXsl;
	}

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		if (is_null(self::$instance))
	 	{
			if (class_exists('DomDocument'))
			{
				$driver = __CLASS__ . '_Xslt';
			}
			/*elseif (function_exists('domxml_xslt_stylesheet'))
			{
				$driver = __CLASS__ . '_DomXml';
			}
			elseif (function_exists('xslt_create'))
			{
				$driver = __CLASS__ . '_Sablotron';
			}*/
			else
			{
				throw new Core_Exception('XSLT processor does not exist.');
			}

			self::$instance = new $driver();

			if (!in_array('lang', stream_get_wrappers()))
			{
				stream_wrapper_register('lang', 'Xsl_Stream_Lang');
			}

			if (!in_array('import', stream_get_wrappers()))
			{
				stream_wrapper_register('import', 'Xsl_Stream_Import');
			}
		}

		return self::$instance;
	}

	/**
	 * Delete XML header
	 * @param string $content source data
	 * @return string
	 */
	protected function _deleteXmlHeader($content)
	{
		if ($this->_deleteXmlHeader)
		{
			$search = array (
				"/<\?xml .*?\?>/si", // u
				"/<!DOCTYPE.+?>/si" // u
			);

			$xsl_result_1024 = mb_substr($content, 0, 1024);
			$xsl_result_1024 = preg_replace($search, '', $xsl_result_1024);

			$content = $xsl_result_1024 . mb_substr($content, 1024);
		}

		return $content;
	}

	/**
	 * Format XML
	 * @param string $content source data
	 * @return string
	 */
	public function formatXml($content)
	{
		$aLines = explode("\n", $content);

		// Число отступов от левого края
		$iCurrentLevel = 0;

		// Количество открывающихся тегов
		$iOpeningTag = 0;

		$bCdata = FALSE;

		foreach ($aLines as $key => $value)
		{
			stripos($value, '<![CDATA[') !== FALSE
				&& $bCdata = TRUE;

			stripos($value, ']]>') !== FALSE
				&& $bCdata = FALSE;

			if ($bCdata)
			{
				$value = rtrim($value, "\r");
			}
			else
			{
				// Удаляем в строке лишние пробелы
				$value = trim($value);

				$tmp = preg_replace(array("'<!--.*?-->'siu", "'<!.*?>'siu",), '', $value);

				// Подсчитываем количество открывающихся тегов
				$iTags = mb_substr_count($tmp, '<')
					- mb_substr_count($tmp, '</') * 2 // считается и как открытый
					- mb_substr_count($tmp, '/>')
					- mb_substr_count($tmp, '<?xml');

				// В строке больше закрывающих тегов, например, </xsl:choose>, смещаем на таб назад
				$iCurrentLevel = $iTags < 0
					? $iOpeningTag + $iTags
					: $iOpeningTag;

				$iCurrentLevel < 0 && $iCurrentLevel = 0;

				$iOpeningTag += $iTags;
			}

			// Добиваем строку нужным количеством табуляций
			$aLines[$key] = $bCdata
				? $value
				: ($value !== ''
					? str_pad('', $iCurrentLevel, "\t") . $value
					: ''
				);
		}

		return implode("\r\n", $aLines);
	}
}