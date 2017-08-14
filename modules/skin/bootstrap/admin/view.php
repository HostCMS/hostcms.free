<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Bootstrap view.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	public function showFormMenus()
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
	}

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
		$title = !is_null($this->module) && isset($this->module->menu[0])
			? Core_Array::get($this->module->menu[0], 'name')
			: $this->pageTitle;

		// Заголовок
		strlen($title) && Admin_Form_Entity::factory('Title')
			->name($title)
			->execute();

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
				$ico = !is_null($this->module) && isset($this->module->menu[0])
					? Core_Array::get($this->module->menu[0], 'ico', 'fa-barcode')
					: 'fa-barcode';

				?><h5 class="row-title before-pink"><i class="fa <?php echo htmlspecialchars($ico)?>"></i><?php echo htmlspecialchars($this->pageTitle)?></h5><?php
			}
			?>
			<div id="id_message"><?php echo $this->message?></div>
			<div class="widget">
				<div class="widget-body">
					<div class="table-toolbar">
						<?php $this->showFormMenus()?>
						<?php
						if (strlen($this->pageSelector))
						{
						?>
						<div class="table-toolbar-right pull-right">
							<?php echo $this->pageSelector?>
						</div>
						<?php
						}
						?>
					</div>

					<?php $this->showChildren()?>

					<?php echo $this->content?>
				</div>
			</div>
		</div><?php
	}
}