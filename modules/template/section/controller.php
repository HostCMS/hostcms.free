<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Template_Section_Controller
 *
 * @package HostCMS
 * @subpackage Template
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Template_Section_Controller
{
	/**
	 * Template_Section object
	 * @var Template_Section_Model
	 */
	protected $_oTemplate_Section = NULL;

	/**
	 * Template_Section_Lib object
	 * @var Template_Section_Lib_Model
	 */
	protected $_oTemplate_Section_Lib = NULL;

	/**
	 * Set Template_Section_Lib object
	 * @param Template_Section_Lib_Model $oTemplate_Section_Lib
	 * @return self
	 */
	public function templateSectionLib(Template_Section_Lib_Model $oTemplate_Section_Lib)
	{
		$this->_oTemplate_Section_Lib = $oTemplate_Section_Lib;
		return $this;
	}

	/**
	 * Constructor.
	 * @param Template_Section_Model $oTemplate_Section
	 */
	public function __construct(Template_Section_Model $oTemplate_Section)
	{
		$this->_oTemplate_Section = $oTemplate_Section;
	}

	/**
	 * Get dirs
	 * @param boolean $bCache
	 * @return array
	 */
	protected function _getDirs($bCache = TRUE)
	{
		$aReturn = array();

		$oLibs = Core_Entity::factory('Lib');
		$oLibs->queryBuilder()
			->leftJoin('lib_dirs', 'lib_dirs.id', '=', 'libs.lib_dir_id')
			->where('libs.type', '=', 1)
			->where('libs.lib_dir_id', '!=', 0)
			->where('libs.file', '!=', '')
			->clearOrderBy()
			->orderBy('lib_dirs.sorting')
			;

		$aLibs = $oLibs->findAll($bCache);
		foreach ($aLibs as $oLib)
		{
			if (Core_File::isFile($oLib->getFilePath()))
			{
				$oLib_Dir = $oLib->Lib_Dir;

				$aTmpDir = $oLib_Dir;

				// Добавляем все директории от текущей до родителя.
				do {
					$aTmpDir->parent_id && $aReturn[$aTmpDir->id] = $aTmpDir->name;
				} while ($aTmpDir = $aTmpDir->getParent());
			}
		}

		return array_unique($aReturn);
	}

	/**
	 * Get children ids
	 * @param Lib_Dir_Model $oLib_Dir
	 * @param boolean $bCache
	 * @return array
	 */
	protected function _getDirChildrenId(Lib_Dir_Model $oLib_Dir, $bCache = TRUE)
	{
		$aDirIDs = array($oLib_Dir->id);

		$aLib_Dirs = $oLib_Dir->Lib_Dirs->findAll($bCache);
		foreach ($aLib_Dirs as $oLib_Dir_Child)
		{
			$aDirIDs = array_merge(
				$aDirIDs,
				array($oLib_Dir_Child->id),
				$this->_getDirChildrenId($oLib_Dir_Child, $bCache)
			);
		}

		return array_unique($aDirIDs);
	}

	/**
	 * Get widgets
	 * @param Lib_Dir_Model $oLib_Dir
	 * @param int $template_section_id
	 * @return string
	 */
	public function getWidgets(Lib_Dir_Model $oLib_Dir, $template_section_id)
	{
		$template_section_lib_id = !is_null($this->_oTemplate_Section_Lib)
			? $this->_oTemplate_Section_Lib->id
			: 0;

		$aDirIDs = $this->_getDirChildrenId($oLib_Dir);

		ob_start();

		if (count($aDirIDs))
		{
			$oLibs = Core_Entity::factory('Lib');
			$oLibs->queryBuilder()
				->where('libs.type', '=', 1)
				->where('libs.lib_dir_id', 'IN', $aDirIDs)
				->where('libs.file', '!=', '');

			$aLibs = $oLibs->findAll(FALSE);
			foreach ($aLibs as $oLib)
			{
				?><div class="lib-item">
					<div class="title"><?php echo htmlspecialchars($oLib->name)?></div>
					<div class="image">
						<img src="<?php echo htmlspecialchars($oLib->getFileHref())?>"/>
						<i class="fa-solid fa-plus-circle" onclick="hQuery.addWidget(this, <?php echo $template_section_id?>, <?php echo $template_section_lib_id?>, <?php echo $oLib->id?>)"></i>
					</div>
				</div><?php
			}
		}

		return ob_get_clean();
	}

	/**
	 * Show right panel with settings
	 * @return string
	 */
	public function showPanel()
	{
		$template_section_lib_id = !is_null($this->_oTemplate_Section_Lib)
			? $this->_oTemplate_Section_Lib->id
			: 0;

		ob_start();
		?><div id="panel<?php echo $this->_oTemplate_Section->id?>" class="bootsrap-iso template-settings template-section-settings">
			<div class="slidepanel">
				<div class="slidepanel-button-close"><i class="fa-solid fa-xmark"></i></div>
				<div class="section-wrapper">
					<div class="column">
						<div class="dirs-wrapper"><?php
							$aDirs = $this->_getDirs(FALSE);
							$i = 0;
							foreach ($aDirs as $lib_dir_id => $dir_name)
							{
								$active = $i == 0
									? ' active'
									: '';

								?><div class="dir-item <?php echo $active?>" data-dir-id="<?php echo $lib_dir_id?>" onclick="hQuery.showWidgets(this, <?php echo $this->_oTemplate_Section->id?>, <?php echo $template_section_lib_id?>, <?php echo $lib_dir_id?>)">
									<span><?php echo htmlspecialchars($dir_name)?></span>
								</div><?php

								$i++;
							}
						?></div>
					</div>
					<div class="column">
						<div class="libs-wrapper"></div>
					</div>
				</div>
			</div>
		</div><?php
		return ob_get_clean();
	}
}