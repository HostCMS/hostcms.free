<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 * Типовой контроллер восстановления версии
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Admin_Form_Action_Controller_Type_Rollback extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'title', // Form Title
		'buttonName', // Button name, e.g. 'Move'
		'skipColumns' // Array of skipped columns
	);

	/**
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 */
	public function __construct(Admin_Form_Action_Model $oAdmin_Form_Action)
	{
		parent::__construct($oAdmin_Form_Action);

		// Set default title
		$this->title(
			$this->_Admin_Form_Action->Admin_Word->getWordByLanguage(
				Core_Entity::factory('Admin_Language')->getCurrent()->id
			)->name
		);

		$this->buttonName(Core::_('Admin_Form.restore'));
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		if (is_null($operation))
		{
			// Original windowId
			$windowId = $this->_Admin_Form_Controller->getWindowId();

			$newWindowId = 'Rollback_' . time();

			$oCore_Html_Entity_Form = Core::factory('Core_Html_Entity_Form');

			$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')
				->id($newWindowId)
				->add($oCore_Html_Entity_Form);

			$oCore_Html_Entity_Form->action($this->_Admin_Form_Controller->getPath())
				->method('post');

			$window_Admin_Form_Controller = clone $this->_Admin_Form_Controller;

			$window_Admin_Form_Controller->window($newWindowId);

			// Идентификаторы переносимых указываем скрытыми полями в форме, чтобы не превысить лимит GET
			$aChecked = $this->_Admin_Form_Controller->getChecked();

			// Clear checked list
			$this->_Admin_Form_Controller->clearChecked();

			foreach ($aChecked as $datasetKey => $checkedItems)
			{
				foreach ($checkedItems as $key => $value)
				{
					$oCore_Html_Entity_Form->add(
						Core::factory('Core_Html_Entity_Input')
							->name('hostcms[checked][' . $datasetKey . '][' . $key . ']')
							->value(1)
							->type('hidden')
							//->controller($window_Admin_Form_Controller)
					);

					break;
				}
			}

			$aColors = array(
				'palegreen',
				'warning',
				'info',
				'maroon',
				'darkorange',
				'blue',
				'danger'
			);
			$iCountColors = count($aColors);

			if (Core::moduleIsActive('revision'))
			{
				ob_start();

				$aRevisions = Revision_Controller::getRevisions($this->_object);

				if (count($aRevisions))
				{
					$aRevisions = array_slice($aRevisions, 0, 9);

					?><ul class="timeline timeline-left timeline-revisions"><?php
					$prevDate = NULL;

					$i = 0;

					foreach ($aRevisions as $key => $oRevision)
					{
						$color = $aColors[$i % $iCountColors];

						$iDatetime = Core_Date::sql2timestamp($oRevision->datetime);
						$sDate = Core_Date::timestamp2date($iDatetime);

						if ($prevDate != $sDate)
						{
							?><li class="timeline-node">
								<a class="label label-<?php echo $color?>"><?php echo Core_Date::timestamp2string(Core_Date::date2timestamp($sDate), FALSE)?></a>
							</li><?php

							$prevDate = $sDate;
							$i++;
						}

						$radioColor = $aColors[($i - 1) % $iCountColors];

						$checked = $key == 0 ? 'checked="checked"' : '';

						$oRevision->printValue();
						?>
						<li>
							<div class="form-group col-xs-12">
								<div class="radio">
									<label>
										<input name="revision_version_id" type="radio" value="<?php echo $oRevision->id?>" class="colored-<?php echo $radioColor?>" <?php echo $checked?>/>
										<div class="text"><?php echo Core::_('Revision.version_title', $oRevision->id)?></div>
										<div class="date"><?php echo Core_Date::timestamp2datetime($iDatetime)?></div>
										<div class="value"><a id="revision<?php echo $oRevision->id?>" href="javascript:void(0);"><i class="fa fa-eye <?php echo $radioColor?>"></i></a></div>
										<?php echo $oRevision->User->getAvatarWithName()?>
									</label>
								</div>
							</div>
						</li>
						<?php
					}
					?></ul><?php
				}
				else
				{
					Core_Message::show(Core::_('Revision.empty'), 'warning');
				}

				$content = ob_get_clean();

				$oCore_Html_Entity_Form->add(
					Admin_Form_Entity::factory('Code')
						->html($content)
				);

				if (count($aRevisions))
				{
					$oAdmin_Form_Entity_Button = Admin_Form_Entity::factory('Button')
						->name('apply')
						->type('submit')
						->class('applyButton btn btn-palegreen')
						->value($this->buttonName)
						->onclick(
							'bootbox.hideAll(); '
							. $this->_Admin_Form_Controller->getAdminSendForm(array('operation' => 'apply'))
						)
						->controller($this->_Admin_Form_Controller);

					$oCore_Html_Entity_Form
						->add(
							Admin_Form_Entity::factory('Div')
								->class('form-group col-xs-12 margin-top-15')
								->add($oAdmin_Form_Entity_Button)
						);
				}

				$oCore_Html_Entity_Div->execute();

				ob_start();

				Core::factory('Core_Html_Entity_Script')
					->value("$(function() {
					$('#{$newWindowId}').HostCMSWindow({ autoOpen: true, destroyOnClose: false, title: '" . Core_Str::escapeJavascriptVariable($this->title) . "', AppendTo: '#{$windowId}', width: 750, height: 'auto', addContentPadding: true, modal: false, Maximize: false, Minimize: false }); });")
					->execute();

				$this->addMessage(ob_get_clean());
			}
			else
			{
				Core_Message::show(Core::_('Revision.module_not_active'), 'error');
			}

			// Break execution for other
			return TRUE;
		}
		else
		{
			$revision_version_id = Core_Array::getPost('revision_version_id', 0, 'int');

			if ($revision_version_id)
			{
				$this->_object->backupRevision();
				$this->_object->rollbackRevision($revision_version_id);

				Core_Message::show(Core::_('Revision.revision_success'), 'success');
			}

			return NULL;
		}

		return $this;
	}
}