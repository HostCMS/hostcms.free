<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Frontend data, e.g. title, description, template and data hierarchy
 *
 * Доступные методы:
 *
 * - fileTimestamp(TRUE|FALSE) использовать в качестве временной метки дату файла, а не дату изменения макета, по умолчанию FALSE.
 * - compress(TRUE|FALSE) использовать компрессию, по умолчанию TRUE. Требует модуль "Компрессия страниц".
 * - doctype('html'|'xhtml') используемый DOCTYPE, влияет на формирование мета-тегов.
 *
 * <code>
 * // Get Title
 * $title = Core_Page::instance()->title;
 * </code>
 *
 * <code>
 * // Set Title
 * Core_Page::instance()->title('New title');
 * </code>
 *
 * <code>
 * // Get description
 * $description = Core_Page::instance()->description;
 * </code>
 *
 * <code>
 * // Set description
 * Core_Page::instance()->description('New description');
 * </code>
 *
 * <code>
 * // Get keywords
 * $keywords = Core_Page::instance()->keywords;
 * </code>
 *
 * <code>
 * // Set keywords
 * Core_Page::instance()->keywords('New keywords');
 * </code>
 *
 * <code>
 * // Get Template object
 * $oTemplate = Core_Page::instance()->template;
 * var_dump($oTemplate->id);
 * </code>
 *
 * <code>
 * // Get Structure object
 * $oStructure = Core_Page::instance()->structure;
 * var_dump($oStructure->id);
 * </code>
 *
 * <code>
 * // Get Core_Response object
 * $oCore_Response = Core_Page::instance()->response;
 * // Set HTTP status
 * $oCore_Response->status(404);
 * </code>
 *
 * <code>
 * // Get array of lib params
 * $array = Core_Page::instance()->libParams;
 * </code>
 *
 *
 * <code>
 * // Get controller object
 * $object = Core_Page::instance()->object;
 *
 * if (is_object(Core_Page::instance()->object)
 * && get_class(Core_Page::instance()->object) == 'Informationsystem_Controller_Show')
 * {
 * 	$Informationsystem_Controller_Show = Core_Page::instance()->object;
 * }
 * </code>
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Page extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'doctype',
		'title',
		'description',
		'keywords',
		'template',
		'structure',
		'response',
		'libParams',
		'widgetParams',
		'object',
		'buildingPage',
		'fileTimestamp',
		'compress',
		'cssCDN',
		'jsCDN',
		'informationsystemCDN',
		'shopCDN',
		'structureCDN'
	);

	/**
	 * Children entities
	 * @var array
	 */
	protected $_children = array();

	/**
	 * Add child to an hierarchy
	 * @param object $object object
	 * @return self
	 */
	public function addChild($object)
	{
		array_unshift($this->_children, $object);
		return $this;
	}

	/*public function addLastChild($object)
	{
		$this->_children[] = $object;
		return $this;
	}*/

	/**
	 * Delete first child
	 * @return self
	 */
	public function deleteChild()
	{
		array_shift($this->_children);
		return $this;
	}

	/**
	 * Current executing object
	 */
	protected $_currentObject = NULL;

	/**
	 * Get current executing object
	 * @return mixed
	 */
	public function getCurrentObject()
	{
		return $this->_currentObject;
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		if (count($this->_children))
		{
			$this->_currentObject = array_shift($this->_children);

			$bLib = get_class($this->_currentObject) == 'Lib_Model';

			if ($bLib)
			{
				$bLogged = Core_Auth::logged();
				$bLogged && $fBeginTimeConfig = Core::getmicrotime();
			}

			$return = $this->_currentObject->execute();

			if ($bLib)
			{
				$bLogged && Core_Page::instance()->addFrontendExecutionTimes(
					Core::_('Core.time_page', Core::getmicrotime() - $fBeginTimeConfig)
				);
			}

			return $return;
		}

		return $this;
	}

	/**
	 * Get children
	 * @return array
	 */
	public function getChildren()
	{
		return $this->_children;
	}

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->libParams = array();
		$this->buildingPage = $this->fileTimestamp = FALSE;
		$this->compress = TRUE;
		$this->doctype = 'html';
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
	 * Linking css
	 * @var array
	 */
	public $css = array();

	/**
	 * Clear $css list
	 * @return self
	 */
	public function clearCss()
	{
		$this->css = array();
		return $this;
	}

	/**
	 * Link $css to the beginning of list
	 * @param string $css path
	 * @return self
	 */
	public function prependCss($css)
	{
		array_unshift($this->css, $css);
		return $this;
	}

	/**
	 * Link $css onto the end of array
	 * @param string $css path
	 * @return self
	 */
	public function css($css)
	{
		$this->css[] = $css;
		return $this;
	}

	/**
	 * Get block of linked css and clear added CSS list
	 * @param boolean $bExternal add as link
	 * @return string
	 * @hostcms-event Core_Page.onBeforeGetCss
	 */
	public function getCss($bExternal = TRUE)
	{
		Core_Event::notify(get_class($this) . '.onBeforeGetCss', $this);

		$return = $this->compress && Core::moduleIsActive('compression')
			? $this->_getCssCompressed($bExternal)
			: $this->_getCss($bExternal);

		$this->css = array();

		return $return;
	}

	/**
	 * Get block of linked css
	 * @param boolean $bExternal add as link, default TRUE
	 * @return string
	 */
	protected function _getCss($bExternal = TRUE)
	{
		$sReturn = $bExternal
			? ''
			: "<style type=\"text/css\">\n";

		foreach ($this->css as $css)
		{
			if ($bExternal)
			{
				$timestamp = $this->fileTimestamp && Core_File::isFile($sPath = CMS_FOLDER . ltrim($css, DIRECTORY_SEPARATOR))
					? filemtime($sPath)
					: Core_Date::sql2timestamp($this->template->timestamp);

				$sReturn .= '<link rel="stylesheet" type="text/css" href="' . $this->cssCDN . $css . '?' . $timestamp . '"' . ($this->doctype === 'xhtml' ? ' />' : '>') . "\n";
			}
			else
			{
				$sPath = CMS_FOLDER . ltrim($css, DIRECTORY_SEPARATOR);
				Core_File::isFile($sPath)
					&& $sReturn .= Core_File::read($sPath);
			}
		}

		!$bExternal
			&& $sReturn .= "\n</style>\n";

		return $sReturn;
	}

	/**
	 * Get block of linked compressed css
	 * @param boolean $bExternal add as link, default TRUE
	 * @return string
	 */
	protected function _getCssCompressed($bExternal = TRUE)
	{
		try
		{
			$oCompression_Controller = Compression_Controller::instance('css');
			$oCompression_Controller->clear();

			foreach ($this->css as $css)
			{
				$oCompression_Controller->addCss($css);
			}

			$sReturn = $bExternal
				? '<link rel="stylesheet" type="text/css" href="' . $this->cssCDN . $oCompression_Controller->getPath() . '?' . Core_Date::sql2timestamp($this->template->timestamp) . '"' . ($this->doctype === 'xhtml' ? ' />' : '>') . "\n"
				: "<style type=\"text/css\">\n" . $oCompression_Controller->getContent() . "\n</style>\n";
		}
		catch (Exception $e)
		{
			$sReturn = $this->_getCss();
		}

		return $sReturn;
	}

	/**
	 * Show block of linked css and clear added CSS list
	 * @param boolean $bExternal add as link
	 * @return self
	 * @hostcms-event Core_Page.onBeforeShowCss
	 */
	public function showCss($bExternal = TRUE)
	{
		Core_Event::notify(get_class($this) . '.onBeforeShowCss', $this);

		echo $this->getCss($bExternal);
		return $this;
	}

	/**
	 * Linking js
	 * @var array
	 */
	public $js = array();

	/**
	 * Clear $js list
	 * @return self
	 */
	public function clearJs()
	{
		$this->js = array();
		return $this;
	}

	/**
	 * Link $js to the beginning of list
	 * @param string $js path
	 * @param boolean $mode async|defer|TRUE|FALSE, default FALSE
	 * @return self
	 */
	public function prependJs($js, $mode = FALSE)
	{
		array_unshift($this->js, array($js, $mode));
		return $this;
	}

	/**
	 * Link js
	 * @param string $js path
	 * @param boolean $mode async|defer|TRUE|FALSE, default FALSE
	 * @return self
	 */
	public function js($js, $mode = FALSE)
	{
		$this->js[] = array($js, $mode);
		return $this;
	}

	/**
	 * Get block of linked JS and clear added JS list
	 * @param boolean $mode async|defer|TRUE|FALSE, default FALSE
	 * @return string
	 * @hostcms-event Core_Page.onBeforeGetJs
	 */
	public function getJs($mode = FALSE)
	{
		Core_Event::notify(get_class($this) . '.onBeforeGetJs', $this);

		$return = $this->compress && Core::moduleIsActive('compression')
			? $this->_getJsCompressed($mode)
			: $this->_getJs($mode);

		$this->js = array();

		return $return;
	}

	/**
	 * Show block of linked JS and clear added JS list
	 * @param boolean $mode async|defer|TRUE|FALSE, default FALSE
	 * @return self
	 * @hostcms-event Core_Page.onBeforeShowJs
	 */
	public function showJs($mode = FALSE)
	{
		Core_Event::notify(get_class($this) . '.onBeforeShowJs', $this);

		echo $this->getJs($mode);
		return $this;
	}

	/**
	 * Get block of linked js
	 * @param boolean $mode async|defer|TRUE|FALSE, default FALSE
	 * @return string
	 */
	protected function _getJs($mode = FALSE)
	{
		$sReturn = '';

		foreach ($this->js as $aJs)
		{
			$timestamp = $this->fileTimestamp && Core_File::isFile($sPath = CMS_FOLDER . ltrim($aJs[0], DIRECTORY_SEPARATOR))
				? filemtime($sPath)
				: NULL;

			$sReturn .= '<script' . $this->_getMode($aJs[1] === FALSE ? $mode : $aJs[1]) . ' src="' . $this->jsCDN . $aJs[0] . (!is_null($timestamp) ? '?' . $timestamp : '') . '"></script>' . "\n";
		}

		return $sReturn;
	}

	/**
	 * Get JS mode
	 * @param $mode
	 * @return string
	 */
	protected function _getMode($mode)
	{
		switch ($mode)
		{
			case 'defer':
				$return = ' defer="defer"';
			break;
			case TRUE:
			case 'async':
				$return = ' async="async"';
			break;
			default:
				$return = '';
		}

		return $return;
	}

	/**
	 * Get block of linked compressed js
	 * @param boolean $mode async|defer|TRUE|FALSE, default FALSE
	 * @return string
	 */
	protected function _getJsCompressed($mode = FALSE)
	{
		try
		{
			$sReturn = '';

			$oCompression_Controller = Compression_Controller::instance('js');
			$oCompression_Controller->clear();

			foreach ($this->js as $aJs)
			{
				$oCompression_Controller->addJs($aJs[0]);
			}

			$sPath = $oCompression_Controller->getPath();

			$timestamp = $this->fileTimestamp && Core_File::isFile(CMS_FOLDER . $sPath)
				? filemtime(CMS_FOLDER . $sPath)
				: NULL;

			$sReturn .= '<script' . $this->_getMode($mode) . ' src="' . $this->jsCDN . $sPath . (!is_null($timestamp) ? '?' . $timestamp : '') . '"></script>' . "\n";
		}
		catch (Exception $e)
		{
			$sReturn = $this->_getJs();
		}

		return $sReturn;
	}

	/**
	 * Show page title
	 * @return self
	 */
	public function showTitle()
	{
		echo str_replace('&amp;', '&', htmlspecialchars($this->title));
		return $this;
	}

	/**
	 * Show page description
	 * @return self
	 */
	public function showDescription()
	{
		echo htmlspecialchars($this->description);
		return $this;
	}

	/**
	 * Show page keywords
	 * @return self
	 */
	public function showKeywords()
	{
		echo htmlspecialchars($this->keywords);
		return $this;
	}

	/**
	 * Add templates
	 * @param Template_Model $oTemplate Template
	 * @return self
	 */
	public function addTemplates(Template_Model $oTemplate)
	{
		$aCss = $aJs = array();

		do {
			$this
				//->css($oTemplate->getTemplateCssFileHref())
				->addChild($oTemplate);

			$aCss[] = $oTemplate->getTemplateCssFileHref();
			$aJs[] = array($oTemplate->getTemplateJsFileHref(), FALSE);

		} while ($oTemplate = $oTemplate->getParent());

		$this->css = array_merge($this->css, array_reverse($aCss));

		$this->js = array_merge($this->js, array_reverse($aJs));

		return $this;
	}

	/**
	 * Prepare Core_Page by Structure
	 * @param Structure_Model $oStructure
	 * @return self
	 */
	public function prepareByStructure(Structure_Model $oStructure)
	{
		if ($oStructure->type == 0)
		{
			$this->template($oStructure->Document->Template);
		}
		// Если динамическая страница или типовая дин. страница
		elseif ($oStructure->type == 1 || $oStructure->type == 2)
		{
			$this->template($oStructure->Template);
		}

		if ($oStructure->type == 2)
		{
			$this->libParams
				= $oStructure->Lib->getDat($oStructure->id);

			$LibConfig = $oStructure->Lib->getLibConfigFilePath();

			if (Core_File::isFile($LibConfig) && is_readable($LibConfig))
			{
				include $LibConfig;
			}
		}

		$this
			->structure($oStructure)
			->addChild($oStructure->getRelatedObjectByType());

		$oStructure->setCorePageSeo($this);

		return $this;
	}

	/**
	 * Show 403 error
	 * @return self
	 * @hostcms-event Core_Page.onBeforeError403
	 */
	public function error403()
	{
		Core_Event::notify(get_class($this) . '.onBeforeError403', $this);

		$oCore_Response = $this->deleteChild()->response->status(403);

		// Если определена константа с ID страницы для 403 ошибки и она не равна нулю
		$oSite = Core_Entity::factory('Site', CURRENT_SITE);
		if ($oSite->error403)
		{
			$oStructure = Core_Entity::factory('Structure')->find($oSite->error403);

			// страница с 403 ошибкой не найдена
			if (is_null($oStructure->id))
			{
				throw new Core_Exception('Structure 403 not found');
			}

			$this->prepareByStructure($oStructure);
		}
		else
		{
			if (Core::$url['path'] != '/')
			{
				// Редирект на главную страницу
				$oCore_Response->header('Location', '/');
			}
		}

		return $this;
	}

	/**
	 * Show 404 error
	 * @return self
	 * @hostcms-event Core_Page.onBeforeError404
	 */
	public function error404()
	{
		Core_Event::notify(get_class($this) . '.onBeforeError404', $this);

		return $this->_errorNotFound(404);
	}
	
	/**
	 * Show 410 error
	 * @return self
	 * @hostcms-event Core_Page.onBeforeError410
	 */
	public function error410()
	{
		Core_Event::notify(get_class($this) . '.onBeforeError410', $this);

		return $this->_errorNotFound(410);
	}

	/**
	 * Show 404 error
	 * @return self
	 * @hostcms-event Core_Page.onBeforeError404
	 */
	protected function _errorNotFound($code)
	{
		$code = intval($code);

		$oCore_Response = $this->deleteChild()->response->status($code);

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		if ($oSite->error404)
		{
			$oStructure = Core_Entity::factory('Structure')->find($oSite->error404);

			// страница с 404 ошибкой не найдена
			if (is_null($oStructure->id))
			{
				throw new Core_Exception("Structure {$code} Not Found");
			}

			$this->prepareByStructure($oStructure);

			// Если уже идет генерация страницы, то добавленный потомок не будет вызван
			$this->buildingPage && $this->execute();
		}
		else
		{
			if (Core::$url['path'] != '/')
			{
				//$oCore_Response->header('Location', '/');

				$oCore_Response->body('<!DOCTYPE html>'
					. '<html>'
					. '<head>'
					. '<meta charset="utf-8">'
					. '<title>' . $code . '</title>'
					. '<meta http-equiv="refresh" content="0; url=/">'
					. '</head>'
					. '</html>'
				)
				->sendHeaders()
				->showBody();

				exit();
			}
		}

		return $this;
	}

	/**
	 * frontendExecutionTimes
	 * @var array
	 */
	protected $_frontendExecutionTimes = array();

	/**
	 * Add Frontend Execution Time
	 * @var string $value
	 * @return self
	 */
	public function addFrontendExecutionTimes($value)
	{
		$this->_frontendExecutionTimes[] = $value;
		return $this;
	}

	/**
	 * Get array of Frontend Execution Time
	 * @return array
	 */
	public function getFrontendExecutionTimes()
	{
		return $this->_frontendExecutionTimes;
	}

	/**
	 * Fix name bug
	 */
	public function addFrontentExecutionTimes($value)
	{
		return $this->addFrontendExecutionTimes($value);
	}
}