<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Printlayout_Module_Controller_Set
 *
 * @package HostCMS 6
 * @subpackage Printlayout
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Printlayout_Module_Controller_Set extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'title', // Form Title
		'skipColumns', // Array of skipped columns
		'buttonName',
	);

	protected $_printlayout_id = NULL;

	/**
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 */
	public function __construct(Admin_Form_Action_Model $oAdmin_Form_Action)
	{
		parent::__construct($oAdmin_Form_Action);

		$this->buttonName(Core::_('Admin_Form.apply'));
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		// Идентификаторы переносимых указываем скрытыми полями в форме, чтобы не превысить лимит GET
		$aChecked = $this->_Admin_Form_Controller->getChecked();

		// Clear checked list
		$this->_Admin_Form_Controller->clearChecked();

		if (isset($aChecked[1]))
		{
			$this->_printlayout_id = intval(key($aChecked[1]));

			$oPrintlayout = Core_Entity::factory('Printlayout')->find($this->_printlayout_id);

			if (is_null($operation))
			{
				$printlayout_dir = !is_null($oPrintlayout->id) && $oPrintlayout->printlayout_dir_id
					? '?printlayout_dir_id=' . $oPrintlayout->printlayout_dir_id
					: '';

				// Original windowId
				$windowId = $this->_Admin_Form_Controller->getWindowId();

				$newWindowId = 'Printlayout_Print_' . time();

				$oCore_Html_Entity_Form = Core::factory('Core_Html_Entity_Form');

				$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')
					->id($newWindowId)
					->add($oCore_Html_Entity_Form);

				$oCore_Html_Entity_Form
					->action($this->_Admin_Form_Controller->getPath() . $printlayout_dir)
					// ->target('_blank')
					->method('post');

				$oCore_Html_Entity_Form
					->add(
						 Core::factory('Core_Html_Entity_Input')
							->name('hostcms[checked][1][' . $this->_printlayout_id . ']')
							->value(1)
							->type('hidden')
					)->add(
						 Core::factory('Core_Html_Entity_Input')
							->name('hostcms[action]')
							->value('setModules')
							->type('hidden')
					)->add(
						 Core::factory('Core_Html_Entity_Input')
							->name('hostcms[operation]')
							->value('apply')
							->type('hidden')
					);

				$oCore_Html_Entity_Form
					->add(Admin_Form_Entity::factory('Code')->html($this->_showEditForm()));

				$oAdmin_Form_Entity_Button = Admin_Form_Entity::factory('Button')
					->name('apply')
					->type('submit')
					->class('applyButton btn btn-blue')
					->value($this->buttonName)
					->onclick(
						//'$("#' . $newWindowId . '").parents(".modal").remove(); '
						'bootbox.hideAll(); '
						. $this->_Admin_Form_Controller->getAdminSendForm(array('operation' => 'apply'))
					)
					->controller($this->_Admin_Form_Controller);

				$oCore_Html_Entity_Form
					->add(
						Admin_Form_Entity::factory('Div')
							->class('form-group col-xs-12')
							->add($oAdmin_Form_Entity_Button)
					);

				$oCore_Html_Entity_Div->execute();

				ob_start();

				$iHeight = $this->_rowsCount < 11
					? 80 + $this->_rowsCount * 30
					: 400;

				Core::factory('Core_Html_Entity_Script')
					->value("$(function() {						
						$('#{$newWindowId}').HostCMSWindow({ autoOpen: true, destroyOnClose: false, title: '" . $this->title . "', AppendTo: '#{$windowId}', width: 500, height: {$iHeight}, addContentPadding: true, modal: false, Maximize: false, Minimize: false }); });")
					->execute();

				$this->addMessage(ob_get_clean());

				// Break execution for other
				return TRUE;
			}
			else
			{
				if (!is_null($oPrintlayout->id))
				{
					$aPrintlayout_Modules = $oPrintlayout->Printlayout_Modules->findAll();

					$aExists = array();
					foreach ($aPrintlayout_Modules as $oPrintlayout_Module)
					{
						$aExists[$oPrintlayout_Module->module_id][$oPrintlayout_Module->type] = $oPrintlayout_Module;
					}

					$aModulesPrintlayouts = $this->_getModulesPrintlayouts();

					foreach ($aModulesPrintlayouts as $module_id => $aResult)
					{
						if (count($aResult))
						{
							foreach ($aResult as $aData)
							{
								if (isset($aData['dir']) && isset($aData['items']) && count($aData['items']))
								{
									foreach ($aData['items'] as $type => $aEntity)
									{
										$value = Core_Array::getPost('type_' . $module_id . '_' . $type);

										if (is_null($value))
										{
											isset($aExists[$module_id][$type])
												&& $aExists[$module_id][$type]->delete();
										}
										elseif (!isset($aExists[$module_id][$type]))
										{
											$oPrintlayout_Module = Core_Entity::factory('Printlayout_Module');
											$oPrintlayout_Module->printlayout_id = $oPrintlayout->id;
											$oPrintlayout_Module->module_id = $module_id;
											$oPrintlayout_Module->type = $type;
											$oPrintlayout_Module->save();
										}
									}
								}
							}
						}
					}
				}
			}
		}

		return $this;
	}

	protected function _getModulesPrintlayouts()
	{
		$aResult = array();
		$aModules = Core_Entity::factory('Module')->getAllByActive(1);
		foreach ($aModules as $oModule)
		{
			$oModule->loadModule();
			if (method_exists($oModule->Core_Module, 'getPrintlayouts'))
			{
				$aPrintlayouts = $oModule->Core_Module->getPrintlayouts();

				if (count($aPrintlayouts))
				{
					$aResult[$oModule->id] = $aPrintlayouts;
				}
			}
		}

		return $aResult;
	}

	protected $_rowsCount = 0;

	/**
	 * Show edit form
	 * @return boolean
	 */
	protected function _showEditForm()
	{
		$this->_rowsCount = 0;

		ob_start();

		$aModulesPrintlayouts = $this->_getModulesPrintlayouts();

		foreach ($aModulesPrintlayouts as $module_id => $aResult)
		{
			if (count($aResult))
			{
				$oModule = Core_Entity::factory('Module', $module_id);
				?>
				<div class="form-group col-xs-12">
					<div class="semi-bold"><?php echo htmlspecialchars($oModule->name)?></div>
					<?php
						foreach ($aResult as $aData)
						{
							if (isset($aData['dir']) && isset($aData['items']) && count($aData['items']))
							{
								$this->_rowsCount++;

								?><div class="col-xs-12"><?php echo htmlspecialchars($aData['dir'])?></div><?php

								foreach ($aData['items'] as $type => $aEntity)
								{
									if (isset($aEntity['name']))
									{
										$this->_rowsCount++;
										$oPrintlayout_Module = $this->_getPrintlayoutModule($oModule->id, $type);

										$checked = !is_null($oPrintlayout_Module)
											? 'checked="checked"'
											: '';

											?><div class="margin-left-20 clear">
												<div class="col-xs-6" style="margin: 5px 0;"><?php echo htmlspecialchars($aEntity['name'])?></div>
												<div class="col-xs-6">
													<div class="checkbox" style="margin: 5px 0">
														<label>
															<input name="type_<?php echo $oModule->id . '_' . $type?>" value="1" type="checkbox" <?php echo $checked?>/>
															<span class="text"></span>
														</label>
													</div>
												</div>
											</div>
										<?php
									}
								}
							}
						}
					?>
				</div>
				<?php
			}

		}

		return ob_get_clean();
	}

	protected function _getPrintlayoutModule($module_id, $type)
	{
		$oPrintlayout_Modules = Core_Entity::factory('Printlayout_Module');
		$oPrintlayout_Modules->queryBuilder()
			->where('printlayout_modules.printlayout_id', '=', $this->_printlayout_id)
			->where('printlayout_modules.module_id', '=', $module_id)
			->where('printlayout_modules.type', '=', $type)
			->limit(1);

		$aPrintlayout_Modules = $oPrintlayout_Modules->findAll(FALSE);

		return isset($aPrintlayout_Modules[0])
			? $aPrintlayout_Modules[0]
			: NULL;
	}
}