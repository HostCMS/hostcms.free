<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Frontend data, e.g. title, description, template and data hierarchy
 *
 * Доступные методы:
 *
 * - fileTimestamp(TRUE|FALSE) использовать в качестве временной метки дату файла, а не дату изменения макета, по умолчанию FALSE.
 * - compress(TRUE|FALSE) использовать компрессию, по умолчанию TRUE. Требует модуль "Компрессия страниц".
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
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Page extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
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
	 * @return Core_Page
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
	 * @return Core_Page
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
			return $this->_currentObject->execute();
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
	 * @return Core_Page
	 */
	public function clearCss()
	{
		$this->css = array();
		return $this;
	}

	/**
	 * Link $css to the beginning of list
	 * @param string $css path
	 * @return Core_Page
	 */
	public function prependCss($css)
	{
		array_unshift($this->css, $css);
		return $this;
	}

	/**
	 * Link $css onto the end of array
	 * @param string $css path
	 * @return Core_Page
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
			? $this->_getCssCompressed()
			: $this->_getCss($bExternal);

		$this->css = array();

		return $return;
	}

	/**
	 * Get block of linked css
	 * @param boolean $bExternal add as link
	 * @return string
	 */
	protected function _getCss($bExternal = TRUE)
	{
		$sReturn = '';

		foreach ($this->css as $css)
		{
			if ($bExternal)
			{
				$timestamp = $this->fileTimestamp && is_file($sPath = CMS_FOLDER . ltrim($css, DIRECTORY_SEPARATOR))
					? filemtime($sPath)
					: Core_Date::sql2timestamp($this->template->timestamp);

				$sReturn .= '<link rel="stylesheet" type="text/css" href="' . $this->cssCDN . $css . '?' . $timestamp . '" />' . "\n";
			}
			else
			{
				$sPath = CMS_FOLDER . ltrim($css, DIRECTORY_SEPARATOR);
				$sReturn .= "<style type=\"text/css\">\n";
				is_file($sPath) && $sReturn .= Core_File::read($sPath);
				$sReturn .= "\n</style>\n";
			}
		}

		return $sReturn;
	}

	/**
	 * Get block of linked compressed css
	 * @return string
	 */
	protected function _getCssCompressed()
	{
		try
		{
			$sReturn = '';

			$oCompression_Controller = Compression_Controller::instance('css');
			$oCompression_Controller->clear();

			foreach ($this->css as $css)
			{
				$oCompression_Controller->addCss($css);
			}

			$sPath = $oCompression_Controller->getPath();
			$sReturn .= '<link rel="stylesheet" type="text/css" href="' . $this->cssCDN . $sPath . '?' . Core_Date::sql2timestamp($this->template->timestamp) . '" />' . "\n";
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
	 * @return Core_Page
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
	 * @return Core_Page
	 */
	public function clearJs()
	{
		$this->js = array();
		return $this;
	}

	/**
	 * Link $js to the beginning of list
	 * @param string $js path
	 * @param boolean $async Run asynchronously, default FALSE
	 * @return Core_Page
	 */
	public function prependJs($js, $async = FALSE)
	{
		array_unshift($this->js, array($js, $async));
		return $this;
	}

	/**
	 * Link js
	 * @param string $js path
	 * @param boolean $async Run asynchronously, default FALSE
	 * @return Core_Page
	 */
	public function js($js, $async = FALSE)
	{
		$this->js[] = array($js, $async);
		return $this;
	}

	/**
	 * Get block of linked JS and clear added JS list
	 * @param boolean $async Run asynchronously, default FALSE
	 * @return string
	 * @hostcms-event Core_Page.onBeforeGetJs
	 */
	public function getJs($async = FALSE)
	{
		Core_Event::notify(get_class($this) . '.onBeforeGetJs', $this);

		$return = $this->compress && Core::moduleIsActive('compression')
			? $this->_getJsCompressed($async)
			: $this->_getJs();

		$this->js = array();

		return $return;
	}

	/**
	 * Show block of linked JS and clear added JS list
	 * @param boolean $async Run asynchronously, default FALSE
	 * @return Core_Page
	 * @hostcms-event Core_Page.onBeforeShowJs
	 */
	public function showJs($async = FALSE)
	{
		Core_Event::notify(get_class($this) . '.onBeforeShowJs', $this);

		echo $this->getJs($async);
		return $this;
	}

	/**
	 * Get block of linked js
	 * @return string
	 */
	protected function _getJs()
	{
		$sReturn = '';

		foreach ($this->js as $aJs)
		{
			$timestamp = $this->fileTimestamp && is_file($sPath = CMS_FOLDER . ltrim($aJs[0], DIRECTORY_SEPARATOR))
				? filemtime($sPath)
				: NULL;

			$sReturn .= '<script type="text/javascript"' . ($aJs[1] ? ' async="async"' : '') . ' src="' . $this->jsCDN . $aJs[0] . (!is_null($timestamp) ? '?' . $timestamp : '') . '"></script>' . "\n";
		}

		return $sReturn;
	}

	/**
	 * Get block of linked compressed js
	 * @param boolean $async Run asynchronously, default FALSE
	 * @return string
	 */
	protected function _getJsCompressed($async = FALSE)
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

			$sAsync = $async ? ' async="async"' : '';

			$sPath = $oCompression_Controller->getPath();
			$sReturn .= '<script type="text/javascript"' . $sAsync . ' src="' . $this->jsCDN . $sPath . '"></script>' . "\n";
		}
		catch (Exception $e)
		{
			$sReturn = $this->_getJs();
		}

		return $sReturn;
	}

	/**
	 * Show page title
	 * @return Core_Page
	 */
	public function showTitle()
	{
		echo str_replace('&amp;', '&', htmlspecialchars($this->title));
		return $this;
	}

	/**
	 * Show page description
	 * @return Core_Page
	 */
	public function showDescription()
	{
		echo htmlspecialchars($this->description);
		return $this;
	}

	/**
	 * Show page keywords
	 * @return Core_Page
	 */
	public function showKeywords()
	{
		echo htmlspecialchars($this->keywords);
		return $this;
	}

	/**
	 * Add templates
	 * @param Template_Model $oTemplate Template
	 * @return Core_Page
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

		} while($oTemplate = $oTemplate->getParent());

		$this->css = array_merge($this->css, array_reverse($aCss));

		$this->js = array_merge($this->js, array_reverse($aJs));

		return $this;
	}

	/**
	 * Show 403 error
	 * @return self
	 */
	public function error403()
	{
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

			if ($oStructure->type == 0)
			{
				$this->template($oStructure->Document->Template);
			}
			// Если динамическая страница или типовая дин. страница
			elseif ($oStructure->type == 1 || $oStructure->type == 2)
			{
				$this->template($oStructure->Template);
			}

			$this->addChild($oStructure->getRelatedObjectByType());
			$oStructure->setCorePageSeo($this);
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
	 */
	public function error404()
	{
		$oCore_Response = $this->deleteChild()->response->status(404);

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		if ($oSite->error404)
		{
			$oStructure = Core_Entity::factory('Structure')->find($oSite->error404);

			// страница с 404 ошибкой не найдена
			if (is_null($oStructure->id))
			{
				throw new Core_Exception('Structure 404 not found');
			}

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

				if (is_file($LibConfig) && is_readable($LibConfig))
				{
					include $LibConfig;
				}
			}

			$this
				->structure($oStructure)
				->addChild($oStructure->getRelatedObjectByType());

			$oStructure->setCorePageSeo($this);

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
					. '<title>404</title>'
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