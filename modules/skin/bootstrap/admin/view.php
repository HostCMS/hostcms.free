<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Bootstrap view.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_View extends Admin_View
{
	/**
	 * Show children elements
	 * @return self
	 */
	public function showFormBreadcrumbs()
	{
		?><div class="page-breadcrumbs">
		<ul class="breadcrumb">
			<li>
				<i class="fa fa-home"></i>
				<a href="/admin/index.php" onclick="$.adminLoad({path: '/admin/index.php'}); return false"><?php echo Core::_('Admin.home')?></a>
			</li><?php

		// Связанные с формой элементы (меню, строка навигации и т.д.)
		foreach ($this->_children as $oAdmin_Form_Entity)
		{
			if ($oAdmin_Form_Entity instanceof Skin_Bootstrap_Admin_Form_Entity_Breadcrumbs)
			{
				$oAdmin_Form_Entity->execute();
			}
		}
		?></ul>
		</div><?php

		return $this;
	}

	/**
	 * Show children elements
	 * @return self
	 */
	/*public function showFormMenus()
	{
		// Связанные с формой элементы (меню, строка навигации и т.д.)
		foreach ($this->_children as $oAdmin_Form_Entity)
		{
			if ($oAdmin_Form_Entity instanceof Skin_Bootstrap_Admin_Form_Entity_Menus)
			{
				$oAdmin_Form_Entity->execute();
			}
		}

		return $this;
	}*/

	/**
	 * Show children elements
	 * @return self
	 */
	public function showChildren()
	{
		// Связанные с формой элементы (меню, строка навигации и т.д.)
		foreach ($this->_children as $oAdmin_Form_Entity)
		{
			if (!($oAdmin_Form_Entity instanceof Skin_Bootstrap_Admin_Form_Entity_Breadcrumbs
				|| $oAdmin_Form_Entity instanceof Skin_Bootstrap_Admin_Form_Entity_Menus))
			{
				$oAdmin_Form_Entity->execute();
			}
		}

		return $this;
	}

	public function showTitle()
	{
		$title = !is_null($this->module)
			&& ($aMenu = $this->module->getMenu())
			&& isset($aMenu[0])
			? Core_Array::get($aMenu[0], 'name')
			: $this->pageTitle;

		// Заголовок
		if (strlen($title))
		{
			?><div class="page-header position-relative">
			<div class="header-title">
				<h1>
					<?php echo htmlspecialchars($title)?>
				</h1>
			</div>
			<?php
			if (Core_Auth::logged())
			{
				$moduleName = $this->module->getModuleName();
				$oModule = Core_Entity::factory('Module')->getByPath($moduleName);

				$module_id = !is_null($oModule) ? $oModule->id : 0;

				$sPagePath = strval(Core_Array::get($_SERVER,'REQUEST_URI'));

				$oUser = Core_Entity::factory('User')->getCurrent();
				$oUser_Bookmark = isset($oUser->User_Bookmarks)
					// Удалить isset в 6.8.1
					? $oUser->User_Bookmarks->getByPath($sPagePath)
					: NULL;
				$class = !is_null($oUser_Bookmark) ? 'active' : '';

				?><div class="header-buttons">
					<a href="#" class="sidebar-toggler">
						<i class="fa fa-arrows-h"></i>
					</a>
					<a href="#" id="refresh-toggler" class="refresh">
						<i class="glyphicon glyphicon-refresh"></i>
					</a>
					<a href="#" id="fullscreen-toggler" class="fullscreen">
						<i class="glyphicon glyphicon-fullscreen"></i>
					</a>
					<a id="bookmark-toggler" class="bookmark <?php echo $class?>" onclick="$.addUserBookmark({title: '<?php echo Core::_("User_Bookmark.title")?>', value: '<?php echo Core_Str::escapeJavascriptVariable(htmlspecialchars($this->pageTitle))?>', submit: '<?php echo Core::_("User_Bookmark.submit")?>', cancel: '<?php echo Core::_("User_Bookmark.cancel")?>' , module_id: <?php echo $module_id?>, path: '<?php echo Core_Str::escapeJavascriptVariable($sPagePath)?>'});">
						<i class="glyphicon glyphicon-star-empty"></i>
					</a>
				</div><?php
			}
			?>
			</div><?php
		}

		return $this;
	}

	public function show()
	{
		$this->showFormBreadcrumbs();
		$this->showTitle();

		?><div class="page-body">
			<?php
			// Заголовок формы
			//$this->showH5($this->pageTitle, $this->module);
			if (strlen($this->pageTitle))
			{
				$ico = !is_null($this->module)
					&& ($aMenu = $this->module->getMenu())
					&& isset($aMenu[0])
					? Core_Array::get($aMenu[0], 'ico', 'fa-barcode')
					: 'fa-barcode';

				?><h5 class="row-title before-pink"><i class="fa <?php echo htmlspecialchars($ico)?>"></i><?php echo htmlspecialchars(html_entity_decode($this->pageTitle, ENT_COMPAT, 'UTF-8'))?></h5><?php
			}
			?>
			<div id="id_message"><?php echo $this->message?></div>
			<div class="widget">
				<div class="widget-body">
					<?php $this->showChildren()?>

					<?php echo $this->content?>
				</div>
			</div>
		</div><?php
	}
}