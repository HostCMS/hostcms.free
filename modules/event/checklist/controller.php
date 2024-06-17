<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Checklist_Controller.
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Event_Checklist_Controller
{
	static public function addBlock($oAdmin_Form_Controller, $index, $prefix, $oEvent_Checklist = NULL)
	{
		$windowId = $oAdmin_Form_Controller->getWindowId();

		$data_new = is_null($oEvent_Checklist)
			? 'data-new="true"'
			: '';

		ob_start();
		?><div class="well with-header" data-index="<?php echo $index?>" <?php echo $data_new?>>
			<div class="header"><?php
				$oInputName = Admin_Form_Entity::factory('Input')
					->divAttr(array('class' => ''))
					->class('form-control checklist-name')
					->name("{$prefix}_name{$index}")
					->placeholder(Core::_('Event.checklist_name'))
					->controller($oAdmin_Form_Controller);

				!is_null($oEvent_Checklist)
					&& $oInputName->value = $oEvent_Checklist->name;

				$oInputName->execute();
			?></div>
			<div class="event-cheklist-items-wrapper">
				<?php
					if (!is_null($oEvent_Checklist))
					{
						// Пустышка
						?><div class="row d-flex align-items-center event-checklist-item-row" data-index="<?php echo $index?>"><?php
						echo self::addRow($oAdmin_Form_Controller, 'new_checklist_item', $index);
						?></div><?php

						$aEvent_Checklist_Items = $oEvent_Checklist->Event_Checklist_Items->findAll(FALSE);
						foreach ($aEvent_Checklist_Items as $oEvent_Checklist_Item)
						{
							?><div class="row d-flex align-items-center event-checklist-item-row" data-index="<?php echo $index?>"><?php
							echo self::addRow($oAdmin_Form_Controller, $prefix . '_item', $oEvent_Checklist->id, $oEvent_Checklist_Item);
							?></div><?php
						}
					}
					else
					{
						?><div class="row d-flex align-items-center event-checklist-item-row" data-index="<?php echo $index?>"><?php
						echo self::addRow($oAdmin_Form_Controller, $prefix . '_item', $index);
						?></div><?php
					}
				?>
				<div class="d-flex align-items-center justify-content-between">
					<a onclick="$.addEventChecklistItem($(this), '<?php echo $windowId?>', 'new_checklist', <?php echo $index?>)" class="add-checklist-item representative-show-link darkgray"><?php echo Core::_('Event.add_checklist_item')?></a>
					<span class="representative-show-link darkorange" onclick="$.removeEventChecklist($(this))"><?php echo Core::_('Event.remove_checklist')?></span>
				</div>
			</div>
		</div></div><?php
		return ob_get_clean();
	}

	static public function addRow($oAdmin_Form_Controller, $prefix, $index, $oEvent_Checklist_Item = NULL)
	{
		$suffix = is_null($oEvent_Checklist_Item)
			? "{$index}[]"
			: "{$index}[{$oEvent_Checklist_Item->id}]";

		$hidden = is_null($oEvent_Checklist_Item)
			? ' hidden'
			: '';

		ob_start();

		$oCheckbox = Admin_Form_Entity::factory('Checkbox')
			->divAttr(array('class' => 'form-group col-xs-1' . $hidden))
			->name("{$prefix}_completed{$suffix}")
			->value(1)
			->controller($oAdmin_Form_Controller)
			->checked(FALSE);

		is_null($oEvent_Checklist_Item)
			&& $oCheckbox->disabled('disabled');

		!is_null($oEvent_Checklist_Item) && $oEvent_Checklist_Item->completed == 1
			&& $oCheckbox->checked('checked');

		$oCheckbox->execute();

		$oInput = Admin_Form_Entity::factory('Input')
			->divAttr(array('class' => 'form-group col-xs-10' . $hidden))
			->class('form-control checklist-item-name')
			->name("{$prefix}_name{$suffix}")
			->placeholder(Core::_('Event.checklist_item_name'))
			->controller($oAdmin_Form_Controller);

		is_null($oEvent_Checklist_Item)
			&& $oInput->disabled('disabled');

		!is_null($oEvent_Checklist_Item)
			&& $oInput->value = $oEvent_Checklist_Item->name;

		$oInput->execute();

		?>
		<div class="col-xs-1 remove-event-checklist-item<?php echo $hidden?>"><i class="fa-solid fa-trash darkorange" onclick="$.removeEventChecklistItem($(this))"></i></div>
		<?php

		return ob_get_clean();
	}
}