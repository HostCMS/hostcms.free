<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Antispam_Domain_Controller_Edit.
 *
 * @package HostCMS
 * @subpackage Antispam
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Antispam_Domain_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		parent::setObject($object);

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		if (!$this->_object->id)
		{
			// Удаляем стандартный <input>
			$oMainTab->delete($this->getField('domain'));

			$oTextarea = Admin_Form_Entity::factory('Textarea')
				->cols(140)
				->rows(5)
				->caption(Core::_('Antispam_Domain.domain'))
				->divAttr(array('class' => 'form-group col-xs-12'))
				->name('domain');

			$oMainRow1->add($oTextarea);
		}
		else
		{
			$this->getField('domain')
			->format(
				array(
					'maxlen' => array('value' => 255),
					'minlen' => array('value' => 3)
				)
			);
		}

		$this->title(
			$this->_object->id
				? Core::_('Antispam_Stopword.edit_title', $this->_object->domain)
				: Core::_('Antispam_Stopword.add_title')
		);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event Antispam_Domain_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$id = $this->_object->id;

		if (!$id)
		{
			$sDomain = trim(Core_Array::getPost('domain'));

			// Массив значений списка
			$aDomains = explode("\n", $sDomain);

			foreach ($aDomains as $sDomain)
			{
				$sDomain = trim($sDomain);

				if (strlen($sDomain))
				{
					$oSame_Antispam_Domain = Core_Entity::factory('Antispam_Domain')->getByDomain($sDomain, FALSE);

					if (is_null($oSame_Antispam_Domain))
					{
						$oNew_Domain = Core_Entity::factory('Antispam_Domain');
						$oNew_Domain->domain = $this->_sanitizeDomain($sDomain);
						$oNew_Domain->save();
					}
				}
			}
		}
		else
		{
			parent::_applyObjectProperty();

			if (substr($this->_object->domain, 0, 4) === 'http')
			{
				$this->_object->domain = $this->_sanitizeDomain($this->_object->domain);
				$this->_object->save();
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	protected function _sanitizeDomain($domain)
	{
		return preg_replace('(^https?://)', '', $domain);
	}
}