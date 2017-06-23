<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Abstract skin
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	 * @return self
	 */
	public function addJs($path)
	{
		$this->_js[] = $path;
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

	/**
	 * Mark of current version
	 * @return int
	 */
	protected function _getTimestamp()
	{
		$currentVersion = defined('CURRENT_VERSION') ? CURRENT_VERSION : '6.0';
		return abs(Core::crc32($currentVersion . $currentVersion));
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
		$oUser = Core_Entity::factory('User')->getCurrent();

		if (!$oUser)
		{
			return array();
		}

		$oModule = Core_Entity::factory('Module');
		$oModule->queryBuilder()->where('active', '=', 1);

		if ($oUser->superuser == 0)
		{
			$oModule->queryBuilder()
				->select('modules.*')
				->join('user_modules', 'modules.id', '=', 'user_modules.module_id',
				array(
					array('AND' => array('user_group_id', '=', $oUser->user_group_id)),
					array('AND' => array('site_id', '=', CURRENT_SITE))
				));
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

	/**
	 * Show Front End panels
	 */
	public function frontend()
	{
		$iTimestamp = abs(Core::crc32(defined('CURRENT_VERSION') ? CURRENT_VERSION : '6.0'));

		?><link rel="stylesheet" type="text/css" href="/modules/skin/default/frontend/bootstrap-iso.css?<?php echo $iTimestamp?>" /><?php
		?><link rel="stylesheet" type="text/css" href="/modules/skin/default/frontend/frontend.css?<?php echo $iTimestamp?>" /><?php
		?><link rel="stylesheet" type="text/css" href="/modules/skin/bootstrap/js/toastr/toastr.css?<?php echo $iTimestamp?>" /><?php
		?><link rel="stylesheet" type="text/css" href="/modules/skin/default/frontend/fontawesome/css/font-awesome.min.css?<?php echo $iTimestamp?>" /><?php
		?><script src="/modules/skin/default/frontend/jquery.min.js"></script><?php
		?><script src="/modules/skin/default/frontend/jquery-ui.min.js" type="text/javascript"></script><?php
		?><script src="/admin/wysiwyg/jquery.tinymce.min.js" type="text/javascript"></script><?php
		?><script src="/modules/skin/bootstrap/js/colorpicker/jquery.minicolors.min.js" type="text/javascript"></script><?php
		?><script src="/modules/skin/bootstrap/js/jquery.slimscroll.min.js" type="text/javascript"></script><?php
		?><script src="/modules/skin/bootstrap/js/toastr/toastr.js" type="text/javascript"></script><?php
		?><script type="text/javascript">var hQuery = $.noConflict(true);</script><?php
		?><script src="/modules/skin/default/frontend/frontend.js" type="text/javascript"></script>

		<?php
		$oTemplate = Core_Page::instance()->template;
		$aTemplates = array();
		$bLess = FALSE;
		do {
			$aTemplates[] = $oTemplate;

			$oTemplate->less && $bLess = TRUE;
		} while($oTemplate = $oTemplate->getParent());

		$aTemplates = array_reverse($aTemplates);

		if ($bLess)
		{
			?><div class="bootstrap-iso">
				<div class="template-settings">
					<span id="slidepanel-settings" onclick="hQuery.toggleSlidePanel()"><i class="fa fa-fw fa-cog"></i></span>
					<div class="slidepanel">
						<div class="container scroll-template-settings">
							<?php
							foreach ($aTemplates as $oTemplate)
							{
								$oTemplate->showManifest();
							}
							?>
						</div>
					</div>
				</div>
			</div>

			<script type="text/javascript">
			hQuery('.bootstrap-iso .colorpicker').each(function () {
				hQuery(this).minicolors({
					control: $(this).attr('data-control') || 'hue',
					defaultValue: $(this).attr('data-defaultValue') || '',
					inline: $(this).attr('data-inline') === 'true',
					letterCase: $(this).attr('data-letterCase') || 'lowercase',
					opacity: $(this).attr('data-rgba'),
					position: $(this).attr('data-position') || 'bottom right',
					format: $(this).attr('data-format') || 'hex',
					change: function (hex, opacity) {
						if (!hex) return;
						if (opacity) hex += ', ' + opacity;
						try {
						} catch (e) { }
					},
					hide: /*function() {*/
						hQuery.sendLessVariable
					/*}*/,
					theme: 'bootstrap'
				});
			});

			hQuery('.bootstrap-iso input:not(.colorpicker)').on('change', hQuery.sendLessVariable);

			hQuery('.scroll-template-settings').slimscroll({
				height: '100%',
				color: '#fff',
				size: '5px',
				railOpacity: 1,
				opacity: 1,
			});
			</script>
			<?php
		}

		$oHostcmsTopPanel = Core::factory('Core_Html_Entity_Div')
			->class('hostcmsPanel hostcmsTopPanel');

		$oHostcmsSubPanel = Core::factory('Core_Html_Entity_Div')
			->class('hostcmsSubPanel')
			->add(
				Core::factory('Core_Html_Entity_Img')
					->width(3)->height(16)
					->src('/hostcmsfiles/images/drag_bg.gif')
			);

		$oHostcmsTopPanel->add($oHostcmsSubPanel);

		if (defined('CURRENT_STRUCTURE_ID'))
		{
			//if ($bIsUtf8)
			//{
			// Structure
			$oStructure = Core_Entity::factory('Structure', CURRENT_STRUCTURE_ID);
			$sPath = '/admin/structure/index.php';
			$sAdditional = "hostcms[action]=edit&parent_id={$oStructure->parent_id}&hostcms[checked][0][{$oStructure->id}]=1";

			$oHostcmsSubPanel->add(
				Core::factory('Core_Html_Entity_A')
					->href("{$sPath}?{$sAdditional}")
					->onclick("hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6', title: '" . Core_Str::escapeJavascriptVariable(Core::_('Structure.edit_title')) . "'}); return false")
					->add(
						Core::factory('Core_Html_Entity_Img')
							->width(16)->height(16)
							->src('/hostcmsfiles/images/structure_edit.gif')
							->id('hostcmsEditStructure')
							->alt(Core::_('Structure.edit_title'))
							->title(Core::_('Structure.edit_title'))
					)
			);

			// Template
			if ($oStructure->type == 0)
			{
				$oTemplate = $oStructure->Document->Template;
			}
			else
			{
				$oTemplate = $oStructure->Template;
			}

			if ($oTemplate && $oTemplate->id)
			{
				$sPath = '/admin/template/index.php';
				$sAdditional = "hostcms[action]=edit&hostcms[checked][1][{$oTemplate->id}]=1";

				$oHostcmsSubPanel->add(
					Core::factory('Core_Html_Entity_A')
					->href("{$sPath}?{$sAdditional}")
					->onclick("hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6', title: '" . Core_Str::escapeJavascriptVariable(Core::_('Template.title_edit', $oTemplate->name)) . "'}); return false")
					->add(
						Core::factory('Core_Html_Entity_Img')
							->width(16)->height(16)
							->src('/hostcmsfiles/images/template_edit.gif')
							->id('hostcmsEditTemplate')
							->alt(Core::_('Template.title_edit', $oTemplate->name))
							->title(Core::_('Template.title_edit', $oTemplate->name))
					)
				);
			}

			// Document
			if ($oStructure->type == 0 && $oStructure->document_id)
			{
				$sPath = '/admin/document/index.php';
				$sAdditional = "hostcms[action]=edit&document_dir_id={$oStructure->Document->document_dir_id}&hostcms[checked][1][{$oStructure->Document->id}]=1";

				$oHostcmsSubPanel->add(
					Core::factory('Core_Html_Entity_A')
						->href("{$sPath}?{$sAdditional}")
						->onclick("hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6', title: '" . Core_Str::escapeJavascriptVariable(Core::_('Document.edit')) . "'}); return false")
						->add(
							Core::factory('Core_Html_Entity_Img')
								->width(16)->height(16)
								->src('/hostcmsfiles/images/page_edit.gif')
								->id('hostcmsEditDocument')
								->alt(Core::_('Document.edit'))
								->title(Core::_('Document.edit'))
						)
				);
			}

			// Informationsystem
			if (Core::moduleIsActive('informationsystem'))
			{
				$oInformationsystem = Core_Entity::factory('Informationsystem')
					->getByStructureId($oStructure->id);

				if ($oInformationsystem)
				{
					$sPath = '/admin/informationsystem/index.php';
					$sAdditional = "hostcms[action]=edit&informationsystem_dir_id={$oInformationsystem->informationsystem_dir_id}&hostcms[checked][1][{$oInformationsystem->id}]=1";

					$oHostcmsSubPanel->add(
						Core::factory('Core_Html_Entity_A')
							->href("{$sPath}?{$sAdditional}")
							->onclick("hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6', title: '" . Core_Str::escapeJavascriptVariable(Core::_('Informationsystem.edit_title')) . "'}); return false")
							->add(
								Core::factory('Core_Html_Entity_Img')
									->width(16)->height(16)
									->src('/hostcmsfiles/images/folder_page_edit.gif')
									->id('hostcmsEditInformationsystem')
									->alt(Core::_('Informationsystem.edit_title'))
									->title(Core::_('Informationsystem.edit_title'))
							)
					);
				}
			}

			// Shop
			if (Core::moduleIsActive('shop'))
			{
				$oShop = Core_Entity::factory('Shop')
					->getByStructureId($oStructure->id);

				if ($oShop)
				{
					$sPath = '/admin/shop/index.php';
					$sAdditional = "hostcms[action]=edit&shop_dir_id={$oShop->shop_dir_id}&hostcms[checked][1][{$oShop->id}]=1";

					$oHostcmsSubPanel->add(
						Core::factory('Core_Html_Entity_A')
							->href("{$sPath}?{$sAdditional}")
							->onclick("hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6', title: '" . Core_Str::escapeJavascriptVariable(Core::_('Shop.edit_title')) . "'}); return false")
							->add(
								Core::factory('Core_Html_Entity_Img')
									->width(16)->height(16)
									->src('/hostcmsfiles/images/shop_edit.gif')
									->id('hostcmsEditShop')
									->alt(Core::_('Shop.edit_title'))
									->title(Core::_('Shop.edit_title'))
							)
					);
				}
			}
			//}
		}

		// Separator
		$oHostcmsSubPanel->add(
			Core::factory('Core_Html_Entity_Span')
				->style('padding-left: 10px')
		)
		->add(
			Core::factory('Core_Html_Entity_A')
				->href('/admin/')
				->target('_blank')
				->add(
					Core::factory('Core_Html_Entity_Img')
						->width(16)->height(16)
						->src('/hostcmsfiles/images/system.gif')
						->id('hostcmsAdministrationCenter')
						->alt(Core::_('Core.administration_center'))
						->title(Core::_('Core.administration_center'))
				)
		);

		// Debug window
		ob_start();

		$oCore_Registry = Core_Registry::instance();

		$oDebugWindow = Core::factory('Core_Html_Entity_Div')
			->class('hostcmsModalWindow')
			->add(
				Core::factory('Core_Html_Entity_Span')
					->value(Core::_('Core.total_time', $oCore_Registry->get('Core_Statistics.totalTime')))
			);

		$oDebugWindowUl = Core::factory('Core_Html_Entity_Ul');
		$oDebugWindow->add($oDebugWindowUl);

		$aFrontendExecutionTimes = Core_Page::instance()->getFrontendExecutionTimes();
		foreach ($aFrontendExecutionTimes as $sFrontendExecutionTimes)
		{
			$oDebugWindowUl->add(
				Core::factory('Core_Html_Entity_Li')
					->liValue($sFrontendExecutionTimes)
			);
		}

		// Fixed Options
		$oDebugWindowUl
			->add(
				Core::factory('Core_Html_Entity_Li')
					->liValue(Core::_('Core.time_database_connection', $oCore_Registry->get('Core_DataBase.connectTime', 0)))
			)
			->add(
				Core::factory('Core_Html_Entity_Li')
					->liValue(Core::_('Core.time_database_select', $oCore_Registry->get('Core_DataBase.selectDbTime', 0)))
			)
			->add(
				Core::factory('Core_Html_Entity_Li')
					->liValue(Core::_('Core.time_sql_execution', $oCore_Registry->get('Core_DataBase.queryTime', 0)))
			)
			->add(
				Core::factory('Core_Html_Entity_Li')
					->liValue(Core::_('Core.time_xml_execution',$oCore_Registry->get('Xsl_Processor.process', 0)))
			);

		if (function_exists('memory_get_usage') && substr(PHP_OS, 0, 3) != 'WIN')
		{
			$oDebugWindow->add(
				Core::factory('Core_Html_Entity_Div')
					->value(Core::_('Core.memory_usage', memory_get_usage() / 1048576))
			);
		}

		$oDebugWindow->add(
			Core::factory('Core_Html_Entity_Div')
				->value(Core::_('Core.number_of_queries', $oCore_Registry->get('Core_DataBase.queryCount', 0)))
		)
		->add(
			Core::factory('Core_Html_Entity_Div')
				->value(
					Core::_('Core.compression', (Core::moduleIsActive('compression')
						? Core::_('Admin_Form.enabled') : Core::_('Admin_Form.disabled')))
				)
		)
		->add(
			Core::factory('Core_Html_Entity_Div')
				->value(Core::_('Core.cache', (Core::moduleIsActive('cache')
					? Core::_('Admin_Form.enabled') : Core::_('Admin_Form.disabled'))))
		);

		if (Core::moduleIsActive('cache'))
		{
			$oDebugWindow->add(
				Core::factory('Core_Html_Entity_Ul')
					->add(
						Core::factory('Core_Html_Entity_Li')
							->liValue(Core::_('Core.cache_insert_time', $oCore_Registry->get('Core_Cache.setTime', 0)))
					)
					->add(
						Core::factory('Core_Html_Entity_Li')
							->liValue(Core::_('Core.cache_write_requests', $oCore_Registry->get('Core_Cache.setCount', 0)))
					)
					->add(
						Core::factory('Core_Html_Entity_Li')
							->liValue(Core::_('Core.cache_read_time', $oCore_Registry->get('Core_Cache.getTime', 0)))
					)
					->add(
						Core::factory('Core_Html_Entity_Li')
							->liValue(Core::_('Core.cache_read_requests', $oCore_Registry->get('Core_Cache.getCount', 0)))
					)
			);
		}
		$oDebugWindow->execute();
		$form_content = ob_get_clean();

		$oHostcmsSubPanel->add(
			Core::factory('Core_Html_Entity_A')
				->onclick("hQuery.showWindow('debugWindow', '" . Core_Str::escapeJavascriptVariable($form_content) . "', {width: 400, height: 220, title: '" . Core::_('Core.debug_information') . "', Maximize: false})")
				->add(
					Core::factory('Core_Html_Entity_Img')
						->src('/hostcmsfiles/images/chart_bar.gif')
						->id('hostcmsShowDebugWindow')
						->alt(Core::_('Core.debug_information'))
						->title(Core::_('Core.debug_information'))
				)
		);

		if (defined('ALLOW_SHOW_SQL') && ALLOW_SHOW_SQL)
		{
			// SQL window
			ob_start();

			$oSqlWindow = Core::factory('Core_Html_Entity_Div')
				->class('hostcmsModalWindow');

			$aQueryLogs = $oCore_Registry->get('Core_DataBase.queryLog', array());

			if (is_array($aQueryLogs) && count($aQueryLogs) > 0)
			{
				$aTmp = array();

				$oCore_DataBase = Core_DataBase::instance();

				$aTdColors = array(
					'system' => '#008000',
					'const' => '#008000',
					'eq_ref' => '#D9E700',
					'ref' => '#E7B300',
					'range' => '#E78200',
					'index' => '#E76200',
					'all' => '#E70B00'
				);

				foreach ($aQueryLogs as $key => $aQueryLog)
				{
					$iCrc32 = crc32($aQueryLog['trimquery']);

					$sClassName = in_array($iCrc32, $aTmp)
						? 'sql_qd'
						: 'sql_q';

					$aTmp[] = $iCrc32;

					$oSqlWindow
						->add(
							Core::factory('Core_Html_Entity_Div')
								->class($sClassName)
								->value(
									$oCore_DataBase->highlightSql(htmlspecialchars($aQueryLog['query']))
								)
						);

					if (isset($aQueryLog['debug_backtrace']) && count($aQueryLog['debug_backtrace']) > 0)
					{
						$sdebugBacktrace = '';

						foreach ($aQueryLog['debug_backtrace'] as $history)
						{
							if (isset($history['file']) && isset($history['line']))
							{
								$sdebugBacktrace .= Core::_('Core.sql_debug_backtrace', Core_Exception::cutRootPath($history['file']), $history['line']);
							}
						}

						$oSqlWindow->add(
							Core::factory('Core_Html_Entity_Div')
								->class('sql_db')
								->id("sql_h{$key}")
								->value($sdebugBacktrace)
						);
					}

					$oSqlDivDescription = Core::factory('Core_Html_Entity_Div')
						->class('sql_t')
						->value(Core::_('Core.sql_statistics', $aQueryLog['time'], $key));

					if (isset($aQueryLog['explain']) && count($aQueryLog['explain']) > 0)
					{
						$oSqlDivDescription
							->add(
								Core::factory('Core_Html_Entity_Div')
									->value('Explain:')
							);

						$oExplainTable = Core::factory('Core_Html_Entity_Table')
							->class('sql_explain');

						$oExplainTableTr = Core::factory('Core_Html_Entity_Tr');
						$oExplainTable->add($oExplainTableTr);

						foreach ($aQueryLog['explain'][0] as $explain_key => $aExplain)
						{
							$oExplainTableTr
								->add(
									Core::factory('Core_Html_Entity_Td')
										->add(
											Core::factory('Core_Html_Entity_Strong')
												->value($explain_key)
										)
								);
						}

						foreach ($aQueryLog['explain'] as $aExplain)
						{
							$oExplainTableTr = Core::factory('Core_Html_Entity_Tr');

							foreach ($aExplain as $sExplainKey => $sExplainValue)
							{
								$oExplainTableTd = Core::factory('Core_Html_Entity_Td');

								if ($sExplainKey == 'type')
								{
									$sIndexName = strtolower($sExplainValue);

									$color = isset($aTdColors[$sIndexName])
										? $aTdColors[$sIndexName]
										: '#777777';

									$oExplainTableTd->style("color: {$color}");
								}

								$oExplainTableTr
									->add($oExplainTableTd)
									->value(str_replace(',', ', ', $sExplainValue));
							}
						}
					}

					$oSqlWindow->add($oSqlDivDescription);
				}
				unset($aTmp);
			}

			$oSqlWindow->execute();
			$form_content = ob_get_clean();

			$oHostcmsSubPanel->add(
				Core::factory('Core_Html_Entity_A')
				->onclick("hQuery.showWindow('sqlWindow', '" . Core_Str::escapeJavascriptVariable($form_content) . "', {width: '70%', height: 500, title: '" . Core::_('Core.sql_queries') . "'})")
				->add(
					Core::factory('Core_Html_Entity_Img')
						->src('/hostcmsfiles/images/sql.gif')
						->id('hostcmsShowSql')
						->alt(Core::_('Core.sql_queries'))
						->title(Core::_('Core.sql_queries'))
				)
			);
		}

		if (defined('ALLOW_SHOW_XML') && ALLOW_SHOW_XML)
		{
			$oHostcmsSubPanel->add(
				Core::factory('Core_Html_Entity_A')
					->href(
						'?hostcmsAction=' . (Core_Type_Conversion::toBool($_SESSION['HOSTCMS_SHOW_XML'])
						? 'HIDE_XML'
						: 'SHOW_XML')
					)
					->add(
						Core::factory('Core_Html_Entity_Img')
							->width(16)->height(16)
							->src('/hostcmsfiles/images/xsl.gif')
							->id('hostcmsXml')
							->alt(Core::_(
								Core_Type_Conversion::toBool($_SESSION['HOSTCMS_SHOW_XML'])
									? 'Core.hide_xml'
									: 'Core.show_xml'
							))
							->title(Core::_(
								Core_Type_Conversion::toBool($_SESSION['HOSTCMS_SHOW_XML'])
									? 'Core.hide_xml'
									: 'Core.show_xml'
							))
					)
			);
		}

		$oHostcmsSubPanel->add(
			// Separator
			Core::factory('Core_Html_Entity_Span')
				->style('padding-left: 10px')
		)
		->add(
			Core::factory('Core_Html_Entity_A')
				->href('/admin/logout.php')
				->onclick("hQuery.ajax({url: '/admin/logout.php', dataType: 'html', success: function() {location.reload()}}); return false;")
				->add(
					Core::factory('Core_Html_Entity_Img')
						->width(16)->height(16)
						->src('/hostcmsfiles/images/exit.gif')
						->id('hostcmsLogout')
						->alt(Core::_('Core.logout'))
						->title(Core::_('Core.logout'))
				)
		);

		$oHostcmsTopPanel
			->add(
				Core::factory('Core_Html_Entity_Script')
					->type('text/javascript')
					->value(
						'(function($){' .
						'$("body").addClass("backendBody");' .
						'$(".hostcmsPanel,.hostcmsSectionPanel,.hostcmsSectionWidgetPanel").draggable({containment: "document"});' .
						'$.sortWidget();' .
						'$("*[hostcms\\\\:id]").hostcmsEditable({path: "/edit-in-place.php"});'.
						'})(hQuery);'
					)
			);

		$oHostcmsTopPanel->execute();
	}
}