<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Sites.
 *
 * @package HostCMS
 * @subpackage Site
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Site_Controller_Template extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'server',
		'chmodFile',
		'templatePath',
		'templateFilename',
		'templateSelectedFilename',
	);

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		defined('HOSTCMS_UPDATE_SERVER') && $this->server = 'http://' . HOSTCMS_UPDATE_SERVER;
		$this->chmodFile = 0644;
		$this->templatePath = CMS_FOLDER . TMP_DIR;
		$this->templateFilename = 'templates.xml';
		$this->templateSelectedFilename = 'template.xml';
	}

	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = NULL;

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Load selected template
	 * @return SimpleXMLElement
	 */
	public function loadSelectedTemplateXml()
	{
		$template_content_filepath = $this->templatePath . $this->templateSelectedFilename;

		if (Core_File::isFile($template_content_filepath) && ($sTemplateContentXml = Core_File::read($template_content_filepath)))
		{
			return simplexml_load_string($sTemplateContentXml);
		}

		Core_Message::show(
			Core::_('install.write_error', $template_content_filepath)
		, 'error');
	}

	/**
	 * Get XML of selected template
	 * @return mixed
	 */
	public function getSelectedTemplateXml()
	{
		$template_content_filepath = $this->templatePath . $this->templateSelectedFilename;

		if (Core_File::isFile($template_content_filepath) && ($sTemplateContentXml = Core_File::read($template_content_filepath)))
		{
			if ($aTemplateContentXml = Core_XML::xml2array($sTemplateContentXml))
			{
				return $aTemplateContentXml;
			}
		}

		return FALSE;
	}

	/**
	 * Get name of the driver
	 * @return string
	 */
	static public function getDriverName()
	{
		$aConfig = Core::$config->get('core_http', array());

		$driverName = $aConfig['default']['driver'];

		if ($driverName == 'socket' && !Core::isFunctionEnable('fsockopen'))
		{
			$driverName = 'curl';
		}
		return $driverName;
	}

	/**
	 * Get XML of templates list
	 * @return string
	 */
	public function getTemplateXml()
	{
		$template_filepath = $this->templatePath . $this->templateFilename;

		$driverName = Site_Controller_Template::getDriverName();

		$Core_Http = Core_Http::instance($driverName)
			->url($this->server . '/hostcms/templates/xml/')
			->port(80)
			->timeout(5)
			->execute();

		$sXmlTemplates = $Core_Http->getDecompressedBody();

		if ($sXmlTemplates)
		{
			Core_File::write($template_filepath, $sXmlTemplates, $this->chmodFile);
		}
		elseif (file_exists($template_filepath))
		{
			$sXmlTemplates = Core_File::read($template_filepath);
		}

		$sXmlTemplates = str_replace('&nbsp;', ' ', $sXmlTemplates);

		return $sXmlTemplates;
	}

	/**
	 * Load template's XML
	 * @return mixed
	 */
	public function loadTemplatesXml()
	{
		$sTemplatesXml = $this->getTemplateXml();

		if ($sTemplatesXml)
		{
			return simplexml_load_string($sTemplatesXml);
		}
		else
		{
			Core_Message::show(
				Core::_('install.write_error', $this->templatePath . $this->templateFilename)
			, 'error');
		}

		return FALSE;
	}

	/**
	 * Convert XML to array
	 * @return array
	 */
	public function getTemplateXmlArray()
	{
		$sXmlTemplates = $this->getTemplateXml();
		return Core_XML::xml2array($sXmlTemplates);
	}

	/**
	 * Get fields
	 * @param array $array
	 * @return array
	 */
	public function getFields(array $array)
	{
		$return = array();

		// цикл по дереву 'fields'
		foreach ($array as $aFieldsValue)
		{
			$tmp = array(
				'Name' => strval($aFieldsValue->name),
				'Type' => strval($aFieldsValue->attributes()->type),
				'Value' => strval($aFieldsValue->value),
				'Macros' => strval($aFieldsValue->macros),
				'Path' => strval($aFieldsValue->path),
				'Extension' => strval($aFieldsValue->extension),
				'MaxWidth' => strval($aFieldsValue->max_width),
				'MaxHeight' => strval($aFieldsValue->max_height),
				'ListValue' => array()
			);

			$aXmlValues = $aFieldsValue->xpath("value/list");

			// Значения для списка
			if (count($aXmlValues))
			{
				foreach ($aXmlValues as $oXmlValue)
				{
						$tmp['ListValue'][strval($oXmlValue->attributes()->value)]
						= strval($oXmlValue);
				}
			}

			$return[] = $tmp;
		}

		return $return;
	}

	/**
	 * Replace of macroses in input string
	 * @param string $str source string
	 * @param array $aReplace macroses
	 * @return string
	 */
	public function macroReplace($str, $aReplace)
	{
		if (count($aReplace) > 0)
		{
			foreach ($aReplace as $key => $value)
			{
				$str = str_replace($key, $value, $str);
			}
		}

		return $str;
	}

	/**
	 * Load file with replace of macroses
	 * @param string $filename file name
	 * @param array $aReplace macroses
	 * @return string
	 */
	public function loadFile($filename, $aReplace = array())
	{
		$filecontent = Core_File::read($filename);
		if ($filecontent)
		{
			$filecontent = $this->macroReplace($filecontent, $aReplace);
		}
		return $filecontent;
	}

	/**
	 * Заменяет макросы в уже существующем файле
	 *
	 * @param string $filename путь к файлу
	 * @param array $aReplace массив замен
	 */
	public function replaceFile($filename, $aReplace = array())
	{
		file_put_contents($filename, $this->loadFile($filename, $aReplace));
		return $this;
	}

	/**
	* Определение расширения файла по его названию без расширения
	*
	* @param $path_file_without_extension - путь к файлу без расширения
	*/
	public function getFileExtension($path_file_without_extension)
	{
		clearstatcache();

		// Директория, в которой находится файл
		$dirname = dirname($path_file_without_extension);

		// Имя файла
		$filename = basename($path_file_without_extension);

		$extension = '';

		if (Core_File::isDir($dirname) && !Core_File::isLink($dirname) && $handle = @opendir($dirname))
		{
			// Шаблон сравнения
			$str = "/^" . $filename . ".([[:alpha:]]*)$/";

			// Просматриваем файлы директории
			while (false !== ($file = readdir($handle)))
			{
				if (preg_match($str, $file , $regs) && Core_Type_Conversion::toStr($regs[1])!= '')
				{
					$extension = $regs[1];
					break;
				}
			}
			closedir($handle);

			return $extension;
		}
		return NULL;
	}
}