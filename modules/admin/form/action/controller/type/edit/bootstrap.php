<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 * Типовой контроллер редактирования, шаблон bootstrap
 *
 * @package HostCMS 6\Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2014 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Admin_Form_Action_Controller_Type_Edit_Bootstrap extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Show edit form
	 * @return boolean
	 */
	protected function _showEditForm()
	{
		ob_start();

		$oSkin_Bootstrap_Admin_View = new Skin_Bootstrap_Admin_View();
		$oSkin_Bootstrap_Admin_View->children($this->_children);
		
		$oSkin_Bootstrap_Admin_View->showFormBreadcrumbs();
			
		Admin_Form_Entity::factory('Title')
			->name($this->title)
			->execute();
		?>
		<div class="page-body">
			<div class="row">
				<div class="col-md-12">
					<div class="widget">
						<div class="widget-body">
							<div class="table-toolbar">
								<?php
									$oSkin_Bootstrap_Admin_View->showFormMenus();
								?>
							</div>
							<?php
							$oSkin_Bootstrap_Admin_View->showChildren();

							// Форма
							$oAdmin_Form_Entity_Form = new Admin_Form_Entity_Form(
								$this->_Admin_Form_Controller
							);

							$oAdmin_Form_Entity_Form
								->id($this->_formId)
								->class('adminForm')
								->action(
									$this->_Admin_Form_Controller->getPath()
								);

							// Закладки
							$oAdmin_Form_Entity_Tabs = Admin_Form_Entity::factory('Tabs');
							$oAdmin_Form_Entity_Tabs->formId($this->_formId);

							// Все закладки к форме
							$oAdmin_Form_Entity_Form->add(
								$oAdmin_Form_Entity_Tabs
							);

							// Add all tabs to $oAdmin_Form_Entity_Tabs
							foreach ($this->_tabs as $oAdmin_Form_Tab_Entity)
							{
								if ($oAdmin_Form_Tab_Entity->getCountChildren() > 0)
								{
									$oAdmin_Form_Entity_Tabs->add(
										$oAdmin_Form_Tab_Entity
									);
								}
							}

							// Кнопки
							$oAdmin_Form_Entity_Form->add(
								$this->_addButtons()
							)
							->execute();
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php

		$this->addContent(
			ob_get_clean()
		);

		return TRUE;
	}
}