<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Informationsystem_Comment_Property Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Informationsystem_Comment_Property_Controller_Edit extends Property_Controller_Edit
{
	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 * @hostcms-event Informationsystem_Comment_Property_Controller_Edit.onAfterRedeclaredPrepareForm
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$object = $this->_object;

		$modelName = $this->_object->getModelName();

		$oMainTab = $this->getTab('main');

		switch ($modelName)
		{
			case 'property':

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'));

				$oAddValueCheckbox = Admin_Form_Entity::factory('Checkbox')
					->value(1)
					->checked(is_null($object->id))
					->caption(Core::_("Informationsystem_Item.add_value"))
					->class('colored-danger')
					->name("add_value");

				$oMainRow1->add($oAddValueCheckbox);
			break;
			case 'property_dir':
			default:
			break;
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredPrepareForm', $this, array($this->_object, $this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Informationsystem_Comment_Property_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'property':
				$Informationsystem_Comment_Property = $this->_object->Informationsystem_Comment_Property;

				if (Core_Array::getPost('add_value'))
				{
					$offset = 0;
					$limit = 100;

					do {
						$oComments = Core_Entity::factory('Comment');
						$oComments->queryBuilder()
							->straightJoin()
							->join('comment_informationsystem_items', 'comments.id', '=', 'comment_informationsystem_items.comment_id')
							->join('informationsystem_items', 'comment_informationsystem_items.informationsystem_item_id', '=', 'informationsystem_items.id')
							->where('informationsystem_items.deleted', '=', 0)
							->where('informationsystem_items.informationsystem_id', '=', $Informationsystem_Comment_Property->informationsystem_id)
							->clearOrderBy()
							->orderBy('comments.id', 'ASC')
							->offset($offset)
							->limit($limit);

						$aComments = $oComments->findAll(FALSE);

						foreach ($aComments as $oComment)
						{
							$aProperty_Values = $this->_object->getValues($oComment->id, FALSE);

							if (!count($aProperty_Values))
							{
								$oProperty_Value = $this->_object->createNewValue($oComment->id);

								switch ($this->_object->type)
								{
									case 2: // Файл
									break;
									default:
										$oProperty_Value->value($this->_object->default_value);
								}

								$oProperty_Value->save();
							}
						}

						$offset += $limit;
					}

					while (count($aComments));
				}
			break;
			case 'property_dir':
			break;
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}