<?php /** @noinspection ALL */

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Template_Section_Lib_Model
 *
 * @package HostCMS
 * @subpackage Template
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Template_Section_Lib_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'template_section' => array(),
		'lib' => array(),
		'user' => array(),
	);

	/**
	 * Backend property
	 * @var mixed
	 */
	public $rollback = 0;

	/**
	 * Backend property
	 * @var int
	 */
	public $name = NULL;

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'lib_id';

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'active' => 1,
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
		}
	}

	/**
	 * Change status
	 * @return self
	 * @hostcms-event template_section_lib.onBeforeChangeActive
	 * @hostcms-event template_section_lib.onAfterChangeActive
	 */
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$this->active = 1 - $this->active;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterChangeActive', $this);

		return $this;
	}

	/**
	 * Executes the business logic.
	 * @hostcms-event template_section_lib.onBeforeExecute
	 * @hostcms-event template_section_lib.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify($this->_modelName . '.onBeforeExecute', $this);

		try {
			$bUserAccess = $this->Template_Section->Template->checkUserAccess();
			// $bUserAccess = Core::checkPanel() && Core_Auth::logged();

			$bCreateDiv = $this->style != '' || $this->class != '' || $bUserAccess;
			if ($bCreateDiv)
			{
				$class = $bUserAccess
					? trim($this->class . ' hostcmsSectionWidget')
					: $this->class;

				if (!$this->active)
				{
					$class .= ' dash';
				}

				?><div<?php echo $class != '' ? ' class="' . htmlspecialchars($class) . '"' : ''?><?php echo $this->style != '' ? ' style="' . htmlspecialchars($this->style) . '"' : ''?><?php echo $bUserAccess ? ' id="hostcmsSectionWidget-' . $this->id . '"' : ''?>><?php
			}

			if ($bUserAccess)
			{
				$oTemplate_Section = $this->Template_Section;

				Core_Page::instance()->templateSection = $oTemplate_Section;
				Core_Page::instance()->templateSectionLib = $this;

				$structure_id = is_object(Core_Page::instance()->structure)
					? Core_Page::instance()->structure->id
					: intval(Core_Array::getGet('structure_id', 0));

				$sSettings = '&structure_id=' . $structure_id;

				if (!is_object(Core_Page::instance()->structure))
				{
					Core_Page::instance()->structure = Core_Entity::factory('Structure', $structure_id);
					define('CURRENT_STRUCTURE_ID', Core_Page::instance()->structure->id);
				}

				!is_object(Core_Page::instance()->template)
					&& Core_Page::instance()->template = $this->Template_Section->Template;

				if (isset($_GET['hostcmsAction']) && $_GET['hostcmsAction'] == 'SHOW_DESIGN')
				{
					?><div class="hostcmsWidgetIcon" data-template-section-id="<?php echo $this->Template_Section->id?>" onclick="hQuery.showWidgetPanel(<?php echo $this->Template_Section->id?>, <?php echo $this->id?>)"><i class="fa-solid fa-plus"></i></div>
					<div class="hostcmsSectionWidgetPanel" style="display: none">
						<div class="draggable-indicator">
							<svg width="16px" height="16px" viewBox="0 0 32 32"><rect height="4" width="4" y="4" x="4" /><rect height="4" width="4" y="12" x="4" /><rect height="4" width="4" y="4" x="12"/><rect height="4" width="4" y="12" x="12"/><rect height="4" width="4" y="4" x="20"/><rect height="4" width="4" y="12" x="20"/><rect height="4" width="4" y="4" x="28"/><rect height="4" width="4" y="12" x="28"/></svg>
						</div>
						<div><a href="#" onclick="hQuery.showDesignPanel(<?php echo $this->id?>); return false"><i class="fa-solid fa-fill-drip"></i></a></div>
						<?php
						// Настройки виджета
						$sPath = Admin_Form_Controller::correctBackendPath('/{admin}/template/section/lib/index.php');
						$sAdditional = "hostcms[action]=edit&template_section_id={$oTemplate_Section->id}&hostcms[checked][0][{$this->id}]=1{$sSettings}";
						$sTitleWidgetSettings = Core::_('Template_Section_Lib.widget_settings', $this->Lib->name);
						$sOnclick = "hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', title: '" . Core_Str::escapeJavascriptVariable($sTitleWidgetSettings) . "', dialogClass: 'hostcms6'}); return false";
						?>
						<div><a href="<?php echo "{$sPath}?{$sAdditional}"?>" onclick="<?php echo $sOnclick ?>" alt="<?php echo $sTitleWidgetSettings ?>" title="<?php echo $sTitleWidgetSettings ?>"><i class="fa-solid fa-fw fa-gear"></i></a></div>
						<?php
						// Изменение активности
						$sActiveUrl = "hQuery.changeActive({path: '/template-section-lib.php?template_section_lib_id={$this->id}{$sSettings}&active=' + (1 - hQuery(this).children('i').hasClass('active')), goal: hQuery('#hostcmsSectionWidget-{$this->id}')}); return false";
						$sTitleWidgetActive = Core::_('Template_Section_Lib.widget_active', $this->Lib->name);
						$widgetActiveIcon = 'fa-lightbulb';
						$this->active && $widgetActiveIcon .= ' active';
						?>
						<div><span onclick="<?php echo $sActiveUrl?>" alt="<?php echo $sTitleWidgetActive ?>" title="<?php echo $sTitleWidgetActive ?>"><i class="fa-solid fa-fw <?php echo $widgetActiveIcon ?>"></i></span></div>
						<?php
						// Удаление виджета
						$sTitleWidgetDelete = htmlspecialchars(Core::_('Template_Section_Lib.widget_delete', $this->Lib->name));
						$sDeleteUrl = "res = confirm('" . Core::_('Admin_Form.confirm_dialog', Core::_('Admin_Form.delete')) . "'); if (res) { hQuery.deleteWidget({path: '/template-section-lib.php?template_section_lib_id={$this->id}&delete=1{$sSettings}', goal: hQuery('#hostcmsSection{$oTemplate_Section->id}')}); return false }else { return false }";
						?>
						<div><span onclick="<?php echo $sDeleteUrl?>" alt="<?php echo $sTitleWidgetDelete ?>" title="<?php echo $sTitleWidgetDelete ?>"><i class="fa-solid fa-fw fa-trash-can"></i></span></div>
					</div><?php
				}
			}

			if (Core_Auth::logged() || $this->active)
			{
				Core_Page::instance()->widgetParams = !empty($this->options)
					? json_decode($this->options, TRUE)
					: array();

				$this->Lib->execute();
			}

			if ($bUserAccess)
			{
				?><div class="drag-handle" style="display: none"><i class="fa-regular fa-hand-back-fist fa-fw"></i></div><?php
			}

			if ($bCreateDiv)
			{
				?></div><?php
			}
		}
		catch (Exception $e)
		{
			Core_Message::show($e->getMessage(), 'error');
		}

		Core_Event::notify($this->_modelName . '.onAfterExecute', $this);

		return $this;
	}

	/**
	 * Backend
	 */
	public function widget()
	{
		$oLib = Core_Entity::factory('Lib', $this->lib_id);

		if (!is_null($oLib))
		{
			return htmlspecialchars((string) $oLib->name);
		}
	}

	/**
	 * Get template section lib file href
	 * @return string
	 */
	public function getLibFileHref()
	{
		return $this->Template_Section->Template->Site->uploaddir . 'libs/lib_' . intval($this->lib_id) . '/widget_' . intval($this->id) . '/';
	}

	/**
	 * Get template section lib file path
	 * @return string
	 */
	public function getLibFilePath()
	{
		return CMS_FOLDER . $this->getLibFileHref();
	}

	/**
	 * Backup revision
	 * @return self
	 */
	public function backupRevision()
	{
		if (Core::moduleIsActive('revision'))
		{
			$aBackup = array(
				'template_section_id' => $this->template_section_id,
				'lib_id' => $this->lib_id,
				'description' => $this->description,
				'options' => $this->options,
				'field_classes' => $this->field_classes,
				'field_styles' => $this->field_styles,
				'class' => $this->class,
				'style' => $this->style,
				'active' => $this->active,
				'sorting' => $this->sorting,
				'user_id' => $this->user_id
			);

			Revision_Controller::backup($this, $aBackup);
		}

		return $this;
	}

	/**
	 * Rollback Revision
	 * @param int $revision_id Revision ID
	 * @return self
	 */
	public function rollbackRevision($revision_id)
	{
		if (Core::moduleIsActive('revision'))
		{
			$oRevision = Core_Entity::factory('Revision', $revision_id);

			$aBackup = json_decode($oRevision->value, TRUE);

			if (is_array($aBackup))
			{
				$this->template_section_id = Core_Array::get($aBackup, 'template_section_id');
				$this->lib_id = Core_Array::get($aBackup, 'lib_id');
				$this->description = Core_Array::get($aBackup, 'description');
				$this->options = Core_Array::get($aBackup, 'options');
				$this->field_classes = Core_Array::get($aBackup, 'field_classes');
				$this->field_styles = Core_Array::get($aBackup, 'field_styles');
				$this->style = Core_Array::get($aBackup, 'style');
				$this->active = Core_Array::get($aBackup, 'active');
				$this->sorting = Core_Array::get($aBackup, 'sorting');
				$this->user_id = Core_Array::get($aBackup, 'user_id');
				$this->save();
			}
		}

		return $this;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event template_section_lib.onBeforeGetRelatedSite
	 * @hostcms-event template_section_lib.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Template_Section->Template->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}