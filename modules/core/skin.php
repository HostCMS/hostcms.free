<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Abstract skin
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
abstract class Core_Skin
{
	/**
	 * Show header
	 */
	abstract public function header();

	/**
	 * Show footer
	 */
	abstract public function footer();

	/**
	 * Show main part of page
	 */
	abstract public function index();

	/**
	 * Show Front End panels
	 */
	abstract public function frontend();

	/**
	 * Skin name
	 * @var string
	 */
	protected $_skinName = 'default';

	/**
	 * Set skin name
	 * @param string $skinName skin name
	 * @return self
	 */
	public function skinName($skinName)
	{
		$this->_skinName = $skinName;
		return $this;
	}

	/**
	 * Get skin name
	 * @return string
	 */
	public function getSkinName()
	{
		return $this->_skinName;
	}

	/**
	 * Mode
	 * @var string
	 */
	protected $_mode = NULL;

	/**
	 * Set mode
	 * @param string $mode mode
	 * @return self
	 */
	public function setMode($mode)
	{
		$this->_mode = $mode;
		return $this;
	}

	/**
	 * Get mode
	 * @return string
	 */
	public function getMode()
	{
		return $this->_mode;
	}

	/**
	 * Skin title
	 * @var string
	 */
	protected $_title;

	/**
	 * Set title
	 * @param string $title title
	 * @return self
	 */
	public function title($title)
	{
		$this->_title = $title;
		return $this;
	}

	/**
	 * List of JS files
	 * @var array
	 */
	protected $_js = array();

	/**
	 * Add JS file path
	 * @param string $path file path
	 * @param string $type type, default NULL
	 * @return self
	 */
	public function addJs($path, $type = NULL)
	{
		$this->_js[] = array('src' => $path, 'type' => $type);
		return $this;
	}

	/**
	 * Get array of JS's paths
	 * @return array
	 */
	public function getJs()
	{
		return $this->_js;
	}

	/**
	 * Clear array of JS's paths
	 * @return self
	 */
	public function clearJs()
	{
		$this->_js = array();
		return $this;
	}

	/**
	 * List of CSS files
	 * @var array
	 */
	protected $_css = array();

	/**
	 * Add CSS file path
	 * @param string $path file path
	 * @return self
	 */
	public function addCss($path)
	{
		$this->_css[] = $path;
		return $this;
	}

	/**
	 * Get array of CSS's paths
	 * @return array
	 */
	public function getCss()
	{
		return $this->_css;
	}

	/**
	 * Clear array of CSS's paths
	 * @return self
	 */
	public function clearCss()
	{
		$this->_css = array();
		return $this;
	}

	/**
	 * Get filename. Depends on $aCss
	 * @param array $aCss Array of paths
	 * @return string
	 */
	public function getCssFilename($aCss)
	{
		return md5(implode(',', $aCss) . '-' . Core::getVersion()) . '.css';
	}

	/**
	 * Get filename. Depends on $aJs
	 * @param array $aJs Array of paths
	 * @return string
	 */
	public function getJsFilename($aJs)
	{
		return md5(implode(',', $aJs) . '-' . Core::getVersion()) . '.js';
	}

	/**
	 * Get min dir path
	 * @return string
	 */
	public function getMinDirPath()
	{
		return 'modules/skin/' . $this->_skinName . '/min/';
	}

	/**
	 * Compress and minify css
	 * @return string
	 */
	public function compressCss()
	{
		$sCssFileName = $this->getCssFilename($this->_css);

		$sMinPath = '/' . $this->getMinDirPath();
		$sMinDir = CMS_FOLDER . $this->getMinDirPath();

		if (!Core_File::isFile($sMinDir . $sCssFileName))
		{
			!Core_File::isDir($sMinDir)
				&& Core_File::mkdir($sMinDir);

			$sContent = '';
			foreach ($this->_css as $css)
			{
				$sPath = Core_File::pathCorrection(CMS_FOLDER . ltrim($css, '/\\'));

				if (Core_File::isFile($sPath))
				{
					$str = Core_File::read($sPath);

					$str = Core_Str::removeBOM($str);

					if (strpos($sPath, '.min.') === FALSE)
					{
						if (Core::moduleIsActive('compression'))
						{
							$oCompression_Controller_Css = new Compression_Controller_Css();
							$str = $oCompression_Controller_Css->compressCss($str);
						}
						else
						{
							// Нативная минимизация CSS
							$sContent = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $sContent);
							$sContent = str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $sContent);
							$sContent = preg_replace('/ {2,}/', ' ', $sContent);
							$sContent = preg_replace('/\s*([\{\}\;\:\,\>\+])\s*/', '$1', $sContent);
							$sContent = preg_replace('/;}/', '}', $sContent);
						}
					}

					$dirname = dirname($css) . '/';
					$str = preg_replace(
						'/(url\()\s*(["\']?)(?![a-z\-]+:|data:)([^\/"\'])/i',
						'${1}${2}' . $dirname . '${3}',
						$str
					);

					$sContent .= $str . "\n";
				}
			}

			Core_File::write($sMinDir . $sCssFileName, trim($sContent));
		}

		return $sMinPath . $sCssFileName;
	}

	/**
	 * Compress and minify JS
	 * @param array $aJs
	 * @return string
	 */
	public function compressJs($aJs)
	{
		$sJsFileName = $this->getJsFilename($aJs);

		$sMinPath = '/' . $this->getMinDirPath();
		$sMinDir = CMS_FOLDER . $this->getMinDirPath();

		if (!Core_File::isFile($sMinDir . $sJsFileName))
		{
			$aFilePaths = array();
			$sContent = '';

			foreach ($aJs as $js)
			{
				$sPath = ltrim($js, '/\\');
				$aFilePaths[] = $sPath;
				$sFullPath = Core_File::pathCorrection(CMS_FOLDER . $sPath);

				if (Core_File::isFile($sFullPath))
				{
					$str = Core_File::read($sFullPath);

					$str = $this->_fixJsPaths(
						$str,
						'/' . $sPath,
						$sMinPath . $sJsFileName
					);

					if (strpos($sPath, '.min.') === FALSE)
					{
						if (Core::moduleIsActive('compression'))
						{
							$str = Compression_Controller_JSMin::minify($str);
						}
						else
						{
							$str = Core_Str::removeBOM($str);

							// Удаляем комментарии
							$lines = explode("\n", $str);
							$output = [];
							$inBlockComment = FALSE; // внутри многострочного комментария, который начался с начала строки

							foreach ($lines as $line)
							{
								// Если мы внутри многострочного комментария (начался с /* в начале строки)
								if ($inBlockComment)
								{
									$pos = strpos($line, '*/');

									if ($pos !== FALSE)
									{
										// Нашли закрытие – выходим из режима комментария
										$inBlockComment = FALSE;

										$rest = substr($line, $pos + 2);

										// Если после */ есть код – добавляем его (как новую строку)
										if ($rest !== '')
										{
											$output[] = $rest;
										}
									}

									// Иначе пропускаем всю строку (она внутри комментария)
									continue;
								}

								// Проверяем, начинается ли строка с // (после пробелов)
								$trimmed = ltrim($line);

								if (strpos($trimmed, '//') === 0)
								{
									// Однострочный комментарий – полностью пропускаем строку
									continue;
								}

								// Проверяем, начинается ли строка с /* (после пробелов)
								if (strpos($trimmed, '/*') === 0)
								{
									// Ищем закрытие */ на этой же строке
									$pos = strpos($line, '*/');

									if ($pos !== FALSE)
									{
										// Комментарий закрылся на той же строке – оставляем только то, что после */
										$rest = substr($line, $pos + 2);

										if ($rest !== '')
										{
											$output[] = $rest;
										}
									}
									else
									{
										// Закрытия на этой строке нет – начинаем режим пропуска следующих строк
										$inBlockComment = TRUE;
									}

									continue;
								}

								// Обычная строка (не начинается с комментария)
								$output[] = $line;
							}

							$str = implode("\n", $output);
							// /удаление комментариев

							// Удаляем sourceMappingURL
							$str = preg_replace('~^//[#@]\s*(source(?:Mapping)?URL)=\s*(\S+)~m', '', $str);

							$str = str_replace(array("\r\n", "\r"), "\n", $str);
							while (mb_strpos($str, "\n\n") !== FALSE)
							{
								$str = str_replace("\n\n", "\n", $str);
							}

							// Убираем пробелы вокруг операторов
							$search = [' = ', ' + ', ' - ', ' * ', ' / ', ' > ', ' < ', ' >= ', ' <= ', ' == ', ' != ', ' && ', ' || '];
							$replace = ['=', '+', '-', '*', '/', '>', '<', '>=', '<=', '==', '!=', '&&', '||'];
							$str = str_replace($search, $replace, $str);

							// Убираем пробелы после ключевых слов
							$str = preg_replace('/\b(if|else|for|while|switch|function|return)\s*\(/', '$1(', $str);

							// Убираем пробелы перед else
							$str = preg_replace('/\}\s*else/', '}else', $str);

							$str = preg_replace('/^[ \t]+|[ \t]+$/m', '', $str);
							$str = preg_replace('/[ \t]+/', ' ', $str);

							$str = trim($str);

							// Убеждаемся что файл заканчивается ; или }
							$str = rtrim($str);
							if (!preg_match('/[;}\]]$/', $str)) {
								$str .= ';';
							}
						}
					}

					$sContent .= $str . "\n";
				}
			}

			clearstatcache();
			if (!Core_File::isDir($sMinDir))
			{
				Core_File::mkdir($sMinDir);
			}

			$sMapFileName = $sJsFileName . '.map';
			$sContent .= "\n//# sourceMappingURL={$sMapFileName}";

			// Генерация Source Map
			$aMapContent = new stdClass();
			$aMapContent->version = 3;
			$aMapContent->file = $sJsFileName;
			$aMapContent->sources = $aFilePaths;
			$aMapContent->sourcesContent = array_fill(0, count($aFilePaths), NULL);

			Core_File::write($sMinDir . $sJsFileName, $sContent);
			Core_File::write($sMinDir . $sMapFileName, json_encode($aMapContent));
		}

		return $sMinPath . $sJsFileName;
	}

	/**
	 * Преобразование относительных путей в JS-коде в абсолютные
	 * @param string $content Исходный JS-код
	 * @param string $originalPath Оригинальный путь к файлу (относительно корня сайта)
	 * @param string $targetPath Путь к результирующему файлу (относительно корня сайта)
	 * @return string
	 */
	protected function _fixJsPaths($content, $originalPath, $targetPath)
	{
		// Получаем директорию исходного файла
		$originalDir = dirname($originalPath);

		// Получаем директорию целевого файла
		$targetDir = dirname($targetPath);

		// Если директории совпадают, ничего не делаем
		if ($originalDir == $targetDir) {
			return $content;
		}

		// Заменяем базовый путь в qualifyURL и подобных функциях
		// Ищем паттерны вида: qualifyURL('../path/file.js') или похожие
		$content = preg_replace_callback(
			'/((?:qualifyURL|resolvePath|getPath|normalizePath)\s*\(\s*)(["\'])((?:\.\.\/|\.\/)?[^"\']+)\2/',
			function($matches) use ($originalDir) {
				$funcName = $matches[1];
				$quote = $matches[2];
				$url = $matches[3];

				// Проверяем, является ли путь относительным
				if (strpos($url, './') === 0 || strpos($url, '../') === 0) {
					// Разрешаем относительный путь
					$resolved = $this->_resolvePath($originalDir . '/' . $url);
					return $funcName . $quote . $resolved . $quote;
				}

				// Если путь не абсолютный и не URL, считаем его относительным
				if (!preg_match('#^(/|https?://|data:)#i', $url)) {
					return $funcName . $quote . $originalDir . '/' . $url . $quote;
				}

				return $matches[0];
			},
			$content
		);

		// Заменяем базовый URL в строках с относительными путями
		// Паттерн: basePath = "../" или подобные
		$content = preg_replace_callback(
			'/(\b(?:basePath|rootPath|workerPath|modulePath)\s*=\s*)(["\'])((?:\.\.\/|\.\/)?[^"\']*)\2/',
			function($matches) use ($originalDir) {
				$varName = $matches[1];
				$quote = $matches[2];
				$url = $matches[3];

				// Если путь относительный
				if (strpos($url, './') === 0 || strpos($url, '../') === 0) {
					$resolved = $this->_resolvePath($originalDir . '/' . $url);
					return $varName . $quote . $resolved . $quote;
				}

				// Если путь не абсолютный и не URL
				if (!preg_match('#^(/|https?://|data:)#i', $url) && !empty($url)) {
					return $varName . $quote . $originalDir . '/' . $url . $quote;
				}

				return $matches[0];
			},
			$content
		);

		// Заменяем относительные пути в объектах конфигурации
		// Паттерн: { url: "../path/file.js" } или { path: "../path/file.js" }
		$content = preg_replace_callback(
			'/(\b(?:url|path|src|worker|workerSrc)\s*:\s*)(["\'])((?:\.\.\/|\.\/)?[^"\']+)\2/',
			function($matches) use ($originalDir) {
				$propName = $matches[1];
				$quote = $matches[2];
				$url = $matches[3];

				// Пропускаем URL и data: URI
				if (preg_match('#^(https?://|data:)#i', $url)) {
					return $matches[0];
				}

				// Если путь относительный
				if (strpos($url, './') === 0 || strpos($url, '../') === 0) {
					$resolved = $this->_resolvePath($originalDir . '/' . $url);
					return $propName . $quote . $resolved . $quote;
				}

				// Если путь не абсолютный
				if (!preg_match('#^/#', $url)) {
					return $propName . $quote . $originalDir . '/' . $url . $quote;
				}

				return $matches[0];
			},
			$content
		);

		// Дополнительно: ищем паттерны конкатенации с базовым путем
		// Паттерн: basePath + "../path/file.js" или basePath + 'path/file.js'
		$content = preg_replace_callback(
			'/(\b(?:basePath|rootPath|modulePath|workerPath)\s*\+\s*)(["\'])((?:\.\.\/|\.\/)?[^"\']+)\2/',
			function($matches) use ($originalDir) {
				$prefix = $matches[1];
				$quote = $matches[2];
				$url = $matches[3];

				// Если путь относительный
				if (strpos($url, './') === 0 || strpos($url, '../') === 0) {
					$resolved = $this->_resolvePath($originalDir . '/' . $url);
					return '\'' . $resolved . '\'';
				}

				return $matches[0];
			},
			$content
		);

		return $content;
	}

	/**
	 * Вычисление относительного пути между двумя директориями
	 * @param string $from Исходная директория
	 * @param string $to Целевая директория
	 * @return string
	 */
	protected function _getRelativePath($from, $to)
	{
		$from = trim($from, '/');
		$to = trim($to, '/');

		if ($from === $to) {
			return '';
		}

		$fromParts = explode('/', $from);
		$toParts = explode('/', $to);

		// Находим общую часть пути
		$commonLength = 0;
		$maxCommonLength = min(count($fromParts), count($toParts));

		while ($commonLength < $maxCommonLength && $fromParts[$commonLength] === $toParts[$commonLength]) {
			$commonLength++;
		}

		// Строим относительный путь
		$relativeParts = array_fill(0, count($fromParts) - $commonLength, '..');
		$relativeParts = array_merge($relativeParts, array_slice($toParts, $commonLength));

		return implode('/', $relativeParts);
	}

	/**
	 * Разрешение пути с . и ..
	 * @param string $path Исходный путь
	 * @return string
	 */
	protected function _resolvePath($path)
	{
		$parts = explode('/', trim($path, '/'));
		$resolved = [];

		foreach ($parts as $part) {
			if ($part === '..') {
				array_pop($resolved);
			} elseif ($part !== '.' && $part !== '') {
				$resolved[] = $part;
			}
		}

		return '/' . implode('/', $resolved);
	}

	/**
	 * Answer
	 * @var object
	 */
	protected $_answer = NULL;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$sAnswerName = 'Skin_' . ucfirst($this->_skinName) . '_Answer';
		$this->_answer = new $sAnswerName();
	}

	/**
	 * The singleton instances.
	 * @var array
	 */
	static public $instance = array();

	/**
	 * Get instance of object
	 * @param string $name name of skin
	 * @return mixed
	 */
	static public function instance($name = NULL)
	{
		is_null($name) && $name = isset($_SESSION['skin'])
			? $_SESSION['skin']
			: Core::$mainConfig['skin'];

		// Check skin exists
		$aConfig = Core_Config::instance()->get('skin_config');
		if (!isset($aConfig[$name]))
		{
			$name = Core::$mainConfig['skin'];
		}

		if (!is_string($name))
		{
			throw new Core_Exception('Wrong argument type (expected String)');
		}

		if (!isset(self::$instance[$name]))
		{
			$skin = 'Skin_' . ucfirst($name);
			self::$instance[$name] = new $skin();

			// Set skinname
			self::$instance[$name]->skinName($name);
		}

		return self::$instance[$name];
	}

	/**
	 * Skin config
	 * @var mixed
	 */
	protected $_config = array();

	/**
	 * Get skin config
	 * @return mixed
	 */
	public function getConfig()
	{
		return $this->_config;
	}

	/**
	 * Set skin config
	 * @param mixed $config
	 * @return self
	 */
	public function setConfig($config)
	{
		$this->_config = $config;

		return $this;
	}

	/**
	 * Set answer
	 * @return string
	 */
	public function answer()
	{
		return $this->_answer;
	}

	protected $_timestamp = NULL;

	/**
	 * Mark of current version
	 * @return int
	 */
	protected function _getTimestamp()
	{
		if (is_null($this->_timestamp))
		{
			$currentVersion = Core::getVersion();
			$this->_timestamp = abs(Core::crc32($currentVersion . $currentVersion));
		}

		return $this->_timestamp;
	}

	/**
	 * SkinModule singleton instances.
	 * @var array
	 */
	static public $skinModuleInstance = array();

	/**
	 * Get skin's module
	 * @param string $modulePath module path
	 * @return Core_Module|NULL
	 */
	public function getSkinModule($modulePath)
	{
		if (isset(self::$skinModuleInstance[$modulePath]))
		{
			return self::$skinModuleInstance[$modulePath];
		}

		$sSkinModuleName = $this->getSkinModuleName($modulePath);

		if (class_exists($sSkinModuleName))
		{
			return self::$skinModuleInstance[$modulePath] = new $sSkinModuleName();
		}

		return NULL;
	}

	/**
	 * Get skin's module name
	 * @param string $modulePath module path
	 * @return string
	 */
	public function getSkinModuleName($modulePath)
	{
		return "Skin_{$this->_skinName}_Module_{$modulePath}_Module";
	}

	/**
	 * Get modules list which has been approved for current user
	 * @return array
	 */
	public function _getAllowedModules()
	{
		$oUser = Core_Auth::getCurrentUser();

		if (!$oUser)
		{
			return array();
		}

		$oModule = Core_Entity::factory('Module');
		$oModule
			->queryBuilder()
			->where('active', '=', 1);

		if (!$oUser->superuser)
		{
			$oModule->queryBuilder()
				->select('modules.*')
				->join('company_department_modules', 'modules.id', '=', 'company_department_modules.module_id')
				->join('company_departments', 'company_department_modules.company_department_id', '=', 'company_departments.id')
				->join('company_department_post_users', 'company_department_post_users.company_department_id', '=', 'company_department_modules.company_department_id')
				->where('site_id', '=', CURRENT_SITE)
				->where('company_department_post_users.user_id', '=', $oUser->id)
				->where('company_departments.deleted', '=', 0)
				->groupBy('modules.path')
				/*->join('company_department_modules', 'modules.id', '=', 'company_department_modules.module_id',
				array(
					array('AND' => array('company_department_id', '=', $oUser->user_group_id)),
					array('AND' => array('site_id', '=', CURRENT_SITE))
				))*/;
		}

		return $oModule->findAll();
	}

	/**
	 * Language
	 * @var string
	 */
	protected $_lng = NULL;

	/**
	 * Get language
	 * @return string
	 */
	public function getLng()
	{
		if (is_null($this->_lng))
		{
			if (Core::isInit())
			{
				$oAdmin_Language = Core_Entity::factory('Admin_Language')->getCurrent();
				!is_null($oAdmin_Language) && $this->_lng = htmlspecialchars($oAdmin_Language->shortname);
			}

			is_null($this->_lng)
				&& $this->_lng = Core_I18n::instance()->getLng();
		}
		return $this->_lng;
	}

	/**
	 * Set language
	 * @param string $lng language
	 * @return self
	 */
	public function setLng($lng)
	{
		$this->_lng = $lng;
		return $this;
	}

	/**
	 * Get image href
	 * @return string
	 */
	public function getImageHref()
	{
		return "/modules/skin/{$this->_skinName}/images/";
	}
}