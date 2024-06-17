<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Comment_Property Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Order_Comment_Property_Controller_Edit extends Property_Controller_Edit
{
	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 * @hostcms-event Shop_Order_Comment_Property_Controller_Edit.onAfterRedeclaredPrepareForm
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
					->caption(Core::_("Shop_Order.add_value"))
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
	 * @hostcms-event Shop_Comment_Property_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'property':
				$Shop_Order_Comment_Property = $this->_object->Shop_Order_Comment_Property;

				if (Core_Array::getPost('add_value'))
				{
					$offset = 0;
					$limit = 100;

					do {
						$oComments = Core_Entity::factory('Comment');
						$oComments->queryBuilder()
							->straightJoin()
							->join('comment_shop_orders', 'comments.id', '=', 'comment_shop_orders.comment_id')
							->join('shop_orders', 'comment_shop_orders.shop_order_id', '=', 'shop_orders.id')
							->where('shop_orders.deleted', '=', 0)
							->where('shop_orders.shop_id', '=', $Shop_Order_Comment_Property->shop_id)
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