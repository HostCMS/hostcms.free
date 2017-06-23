<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Seo_Query Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Seo
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Seo_Query_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		parent::setObject($object);

		$this->title(
			$this->_object->id
				? Core::_('Seo_Query.edit_title')
				: Core::_('Seo_Query.add_title')
		);

		$oMainTab = $this->getTab('main');

		//	При редактировании запроса выводит <input> вместо <textarea>
		if ($this->_object->id)
		{
			$oMainTab
				->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'));
		
			$oAdmin_Form_Entity_Input_Value = Admin_Form_Entity::factory('Input');
			$oAdmin_Form_Entity_Input_Value
				->name('query')
				->value($this->_object->query)
				->caption(Core::_('Seo_Query.query'))
				->format(array(
					'minlen' => array('value' => 1)
					)
				);

			$oMainTab->delete($this->getField('query'));

			$oMainRow1->add($oAdmin_Form_Entity_Input_Value);
		}

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event Seo_Query_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$id = $this->_object->id;

		if (!$id)
		{
			$sQuery = trim(Core_Array::getPost('query'));

			// Массив контекстных фраз
			$aQueries = explode("\n", $sQuery);

			// Значение для первой контекстной фразы
			$this->_formValues['query'] = array_shift($aQueries);
		}

		$this->_formValues['query'] = trim($this->_formValues['query']);

		if (!empty($this->_formValues['query']))
		{
			parent::_applyObjectProperty();
		}

		if (!$id)
		{
			foreach ($aQueries as $sQuery)
			{
				$sQuery = trim($sQuery);

				if (!empty($sQuery))
				{
					$oNewQuery = clone $this->_object;

					$oNewQuery->query = $sQuery;
					$oNewQuery->save();
				}
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}
}