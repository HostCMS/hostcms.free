<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Ipaddress_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$modelName = $object->getModelName();

		switch ($modelName)
		{
			case 'ipaddress':
				$this->addSkipColumn('banned');

				if (!$object->id)
				{
					$object->ipaddress_dir_id = intval(Core_Array::getGet('ipaddress_dir_id', 0));
				}
			break;
			case 'ipaddress_dir':
				if (!$object->id)
				{
					$object->parent_id = intval(Core_Array::getGet('ipaddress_dir_id', 0));
				}
			break;
		}

		return parent::setObject($object);
	}

	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'ipaddress':
				$title = $this->_object->id
					? Core::_('Ipaddress.edit_title', $this->_object->getShortName(), FALSE)
					: Core::_('Ipaddress.add_title');

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'));

				$this->getField('ip')
					// clear standart url pattern
					->format(array('lib' => array()));

				/*if (!$this->_object->id)
				{
					// Удаляем стандартный <input>
					$oMainTab->delete($this->getField('ip'));

					$oTextarea_Ips = Admin_Form_Entity::factory('Textarea')
						->cols(140)
						->rows(12)
						->caption(Core::_('Ipaddress.ip'))
						->divAttr(array('class' => 'form-group col-xs-12'))
						->name('ip');

					$oMainRow1->add($oTextarea_Ips);
				}
				else
				{
					$oMainTab
						->move($this->getField('ip')->divAttr(array('class' => 'form-group col-xs-12'))->rows(12), $oMainRow1);
				}*/

				$oMainTab
					->move($this->getField('ip')->divAttr(array('class' => 'form-group col-xs-12'))->rows(12), $oMainRow1);

				// Удаляем группу
				$oAdditionalTab->delete($this->getField('ipaddress_dir_id'));

				$oGroupSelect = Admin_Form_Entity::factory('Select');

				$oGroupSelect
					->caption(Core::_('Ipaddress.ipaddress_dir_id'))
					->options(array(' … ') + self::fillIpaddressDir())
					->name('ipaddress_dir_id')
					->value($this->_object->ipaddress_dir_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

				$oMainRow2->add($oGroupSelect);

				$oMainTab
					->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow2)
					->move($this->getField('deny_access')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2)
					->move($this->getField('deny_backend')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow3)
					->move($this->getField('no_statistic')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow4)
					->move($this->getField('comment')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow5)
					;
			break;
			case 'ipaddress_dir':
				$title = $this->_object->id
					? Core::_('Ipaddress_Dir.edit_title', $this->_object->name, FALSE)
					: Core::_('Ipaddress.add_title');

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					;

				$oMainTab
					->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1);

				// Удаляем группу
				$oAdditionalTab->delete($this->getField('parent_id'));

				$oGroupSelect = Admin_Form_Entity::factory('Select')
					->caption(Core::_('Ipaddress_Dir.parent_id'))
					->options(array(' … ') + self::fillIpaddressDir(0, array($this->_object->id)))
					->name('parent_id')
					->value($this->_object->parent_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

				$oMainRow2->add($oGroupSelect);

				$oMainTab
					->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3')), $oMainRow2);
			break;
		}

		$this->title($title);

		return $this;
	}

	/**
	 * Redirect groups tree
	 * @var array
	 */
	static protected $_aGroupTree = array();

	/**
	 * Build visual representation of group tree
	 * @param int $iIpaddressDirParentId parent ID
	 * @param int $aExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	static public function fillIpaddressDir($iIpaddressDirParentId = 0, $aExclude = array(), $iLevel = 0)
	{
		$iIpaddressDirParentId = intval($iIpaddressDirParentId);
		$iLevel = intval($iLevel);

		if ($iLevel == 0)
		{
			$aTmp = Core_QueryBuilder::select('id', 'parent_id', 'name')
				->from('ipaddress_dirs')
				->where('deleted', '=', 0)
				->orderBy('sorting')
				->orderBy('name')
				->execute()->asAssoc()->result();

			foreach ($aTmp as $aGroup)
			{
				self::$_aGroupTree[$aGroup['parent_id']][] = $aGroup;
			}
		}

		$aReturn = array();

		if (isset(self::$_aGroupTree[$iIpaddressDirParentId]))
		{
			$countExclude = count($aExclude);
			foreach (self::$_aGroupTree[$iIpaddressDirParentId] as $childrenGroup)
			{
				if ($countExclude == 0 || !in_array($childrenGroup['id'], $aExclude))
				{
					$aReturn[$childrenGroup['id']] = str_repeat('  ', $iLevel) . $childrenGroup['name'] . ' [' . $childrenGroup['id'] . ']' ;
					$aReturn += self::fillIpaddressDir($childrenGroup['id'], $aExclude, $iLevel + 1);
				}
			}
		}

		$iLevel == 0 && self::$_aGroupTree = array();

		return $aReturn;
	}

	/**
	 * Prettify list of IPs
	 * @param string $str
	 * @return string
	 */
	protected function _prettifyList($str)
	{
		$array = array_unique(array_map('trim', explode(',', $str)));
		$array = array_filter($array, 'strlen');
		sort($array);
		return implode(', ', $array);
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Ipaddress_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'ipaddress':
				$id = $this->_object->id;

				if (!$id)
				{
					$sValue = trim($this->_formValues['ip']);

					// Массив значений списка
					$aIpaddresses = explode("\n", $sValue);

					$this->_formValues['ip'] = trim(array_shift($aIpaddresses));

					//$this->_formValues['ip'] = $this->_prettifyList($this->_formValues['ip']);
				}
				
				$this->_formValues['ip'] = $this->_prettifyList($this->_formValues['ip']);
				
			break;
		}

		parent::_applyObjectProperty();

		switch ($modelName)
		{
			case 'ipaddress':
				if (!$id)
				{
					foreach ($aIpaddresses as $sValue)
					{
						$sValue = trim($sValue);

						if (strlen($sValue))
						{
							$oSameIpaddress = Core_Entity::factory('Ipaddress')->getByIp($sValue, FALSE);

							if (is_null($oSameIpaddress))
							{
								echo $sValue;
								$oNewIpaddress = clone $this->_object;
								$oNewIpaddress->ip = $this->_prettifyList($sValue);
								$oNewIpaddress->save();
							}
						}
					}
				}

				Ipaddress_Controller::instance()->clearCache();
			break;
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	/*
	 * Show ip with button
	 */
	static public function addBlockButton($oIp, $ip, $comment, $oAdmin_Form_Controller)
	{
		if (Core::moduleIsActive('ipaddress'))
		{
			$onclick = '$.blockIp({ ip: "' . $ip . '", comment: "' . Core_Str::escapeJavascriptVariable($comment) . '" })';

			$oIp
				->add(
					Core_Html_Entity::factory('Span')
						->class('input-group-addon')
						->onclick("res = confirm('" . Core::_('Ipaddress.confirm_ban') . "'); if (res) {" . $onclick . " } else { return false }")
						->add(
							Core_Html_Entity::factory('Span')
								->class('fa fa-ban darkorange')
						)
				);
		}
	}
}