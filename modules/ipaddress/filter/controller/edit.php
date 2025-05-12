<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress_Filter_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Ipaddress_Filter_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			case 'ipaddress_filter':
				$this
					->addSkipColumn('json')
					->addSkipColumn('banned');

				if (!$object->id)
				{
					$object->ipaddress_filter_dir_id = Core_Array::getGet('ipaddress_filter_dir_id', 0, 'int');
				}
			break;
			case 'ipaddress_filter_dir':
				if (!$object->id)
				{
					$object->parent_id = Core_Array::getGet('ipaddress_filter_dir_id', 0, 'int');
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

		// $windowId = $this->_Admin_Form_Controller->getWindowId();

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'ipaddress_filter':
				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRowButton = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRowConditions = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					// ->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
					;

				$oMainTab
					->move($this->getField('name')->class('form-control input-lg')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
					->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1);

				ob_start();
				?>
				<div class="form-group col-xs-12">
					<a class="btn btn-sky" onclick="$.addIpaddressFilterCondition(this)"><i class="fa fa-plus"></i> <?php echo Core::_('Ipaddress_Filter.condition')?></a>
				</div>
				<script>
					$(function() {
						$.loadIpaddressFilterNestable();
					});
				</script>
				<?php
				$oMainRowButton->add(Admin_Form_Entity::factory('Code')->html(ob_get_clean()));

				$aJson = is_string($this->_object->json)
					? json_decode($this->_object->json, TRUE)
					: array();

				ob_start();
				?>
				<div class="col-xs-12">
					<div class="well well-sm margin-bottom-10 ipaddress-filter-conditions">
						<p class="semi-bold"><i class="widget-icon fa fa-list icon-separator palegreen"></i><?php echo Core::_('Ipaddress_Filter.conditions')?></p>

						<?php
						if (count($aJson))
						{
							foreach ($aJson as $key => $aCondition)
							{
								?>
								<div class="dd">
									<ol class="dd-list">
										<li class="dd-item bordered-palegreen" data-sorting="<?php echo $key?>">
											<div class="dd-handle">
												<div class="form-horizontal">
													<div class="form-group no-margin-bottom ipaddress-filter-row">
														<?php

														$type = Core_Array::get($aCondition, 'type', '', 'trim');

														Admin_Form_Entity::factory('Select')
															->name('type[]')
															->options(Ipaddress_Filter_Controller::getTypes())
															->value($type)
															->divAttr(array('class' => 'col-xs-12 col-sm-2 property-data'))
															->onchange('$.changeIpaddressFilterOption(this)')
															->controller($this->_Admin_Form_Controller)
															->execute();

														$hidden = $type != 'get'
															? ' hidden'
															: '';

														Admin_Form_Entity::factory('Input')
															->name('get_name[]')
															->value(Core_Array::get($aCondition, 'get', '', 'trim'))
															->divAttr(array('class' => 'col-xs-12 col-sm-2 property-data ipaddress-filter-get-name' . $hidden))
															->placeholder(Core::_('Ipaddress_Filter.placeholder_name'))
															->controller($this->_Admin_Form_Controller)
															->execute();

														$hiddenHeader = $type != 'header'
															? ' hidden'
															: '';

														Admin_Form_Entity::factory('Input')
															->name('header_name[]')
															->value(Core_Array::get($aCondition, 'header', '', 'trim'))
															->divAttr(array('class' => 'col-xs-12 col-sm-2 property-data ipaddress-filter-header-name' . $hiddenHeader))
															->placeholder(Core::_('Ipaddress_Filter.placeholder_name'))
															->controller($this->_Admin_Form_Controller)
															->execute();

														Admin_Form_Entity::factory('Select')
															->name('condition[]')
															->options(Ipaddress_Filter_Controller::getConditions())
															->value(Core_Array::get($aCondition, 'condition', '', 'trim'))
															->divAttr(array('class' => 'col-xs-12 col-sm-2 property-data'))
															->controller($this->_Admin_Form_Controller)
															->execute();

														Admin_Form_Entity::factory('Input')
															->name('value[]')
															->value(Core_Array::get($aCondition, 'value', '', 'str')) // trim нельзя, может быть 2 пробела
															->divAttr(array('class' => 'col-xs-12 col-sm-3 property-data'))
															->placeholder(Core::_('Ipaddress_Filter.placeholder_value'))
															->controller($this->_Admin_Form_Controller)
															->execute();

														Admin_Form_Entity::factory('Select')
															->name('case_sensitive[]')
															->options(array(
																Core::_('Ipaddress_Filter.case_unsensitive'),
																Core::_('Ipaddress_Filter.case_sensitive')
															))
															->value(Core_Array::get($aCondition, 'case_sensitive', 1, 'int'))
															->divAttr(array('class' => 'col-xs-12 col-sm-2 property-data'))
															->controller($this->_Admin_Form_Controller)
															->execute();
														?>
														<a class="delete-associated-item" onclick="res = confirm('<?php echo Core::_('Admin_Form.confirm_dialog', htmlspecialchars(Core::_('Admin_Form.delete')))?>'); if (res) { $(this).parents('.dd').remove() } return false"><i class="fa fa-times-circle darkorange"></i></a>
													</div>
												</div>
											</div>
										</li>
									</ol>
								</div>
								<?php
							}
						}
						?>
					</div>
				</div>
				<?php
				$oMainRowConditions->add(Admin_Form_Entity::factory('Code')->html(ob_get_clean()));

				$oMainTab->delete($this->getField('mode'));

				$oMode = Admin_Form_Entity::factory('Select')
					->caption(Core::_('Ipaddress_Filter.mode'))
					->name('mode')
					->options(array(
						0 => Core::_('Ipaddress_Filter.mode0'),
						1 => Core::_('Ipaddress_Filter.mode1')
					))
					->value($this->_object->mode)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-2'));

				$oMainRow3->add($oMode);

				$oMainTab
					->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-2')), $oMainRow3)
					->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-2')), $oMainRow3)
					->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3 margin-top-21')), $oMainRow3)
					->move($this->getField('block_ip')->class('form-control colored-danger times')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3 margin-top-21')), $oMainRow3)
					;

				// Удаляем группу
				$oAdditionalTab->delete($this->getField('ipaddress_filter_dir_id'));

				$oGroupSelect = Admin_Form_Entity::factory('Select');

				$oGroupSelect
					->caption(Core::_('Ipaddress_Filter.ipaddress_filter_dir_id'))
					->options(array(' … ') + self::fillIpaddressFilterDir())
					->name('ipaddress_filter_dir_id')
					->value($this->_object->ipaddress_filter_dir_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

				$oMainRow4->add($oGroupSelect);

				$title = $this->_object->id
					? Core::_('Ipaddress_Filter.edit_title')
					: Core::_('Ipaddress_Filter.add_title');
			break;
			case 'ipaddress_filter_dir':
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
					->options(array(' … ') + self::fillIpaddressFilterDir(0, array($this->_object->id)))
					->name('parent_id')
					->value($this->_object->parent_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

				$oMainRow2->add($oGroupSelect);

				$oMainTab
					->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3')), $oMainRow2);

				$title = $this->_object->id
					? Core::_('Ipaddress_Filter_Dir.edit_title', $this->_object->name)
					: Core::_('Ipaddress_Filter_Dir.add_title');
			break;
		}

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Ipaddress_Filter_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$aTypes = Core_Array::getPost('type', array(), 'array');
		$aConditions = Core_Array::getPost('condition', array(), 'array');
		$aGet_Names = Core_Array::getPost('get_name', array(), 'array');
		$aValues = Core_Array::getPost('value', array(), 'array');
		$aCase_Sensitives = Core_Array::getPost('case_sensitive', array(), 'array');
		$aHeader_Names = Core_Array::getPost('header_name', array(), 'array');

		$aJson = array();
		foreach ($aTypes as $key => $type)
		{
			$aLine = array('type' => $type, 'condition' => Core_Array::get($aConditions, $key), 'value' => Core_Array::get($aValues, $key), 'case_sensitive' => intval(Core_Array::get($aCase_Sensitives, $key)));

			$type === 'get'
				&& $aLine['get'] = Core_Array::get($aGet_Names, $key);

			$type === 'header'
				&& $aLine['header'] = Core_Array::get($aHeader_Names, $key);

			$aJson[] = $aLine;
		}

		$this->removeSkipColumn('json');
		$this->_formValues['json'] = json_encode($aJson);

		parent::_applyObjectProperty();

		Ipaddress_Filter_Controller::instance()->clearCache();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Redirect groups tree
	 * @var array
	 */
	static protected $_aGroupTree = array();

	/**
	 * Build visual representation of group tree
	 * @param int $iIpaddressFilterDirParentId parent ID
	 * @param array $aExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	static public function fillIpaddressFilterDir($iIpaddressFilterDirParentId = 0, $aExclude = array(), $iLevel = 0)
	{
		$iIpaddressFilterDirParentId = intval($iIpaddressFilterDirParentId);
		$iLevel = intval($iLevel);

		if ($iLevel == 0)
		{
			$aTmp = Core_QueryBuilder::select('id', 'parent_id', 'name')
				->from('ipaddress_filter_dirs')
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

		if (isset(self::$_aGroupTree[$iIpaddressFilterDirParentId]))
		{
			$countExclude = count($aExclude);
			foreach (self::$_aGroupTree[$iIpaddressFilterDirParentId] as $childrenGroup)
			{
				if ($countExclude == 0 || !in_array($childrenGroup['id'], $aExclude))
				{
					$aReturn[$childrenGroup['id']] = str_repeat('  ', $iLevel) . $childrenGroup['name'] . ' [' . $childrenGroup['id'] . ']' ;
					$aReturn += self::fillIpaddressFilterDir($childrenGroup['id'], $aExclude, $iLevel + 1);
				}
			}
		}

		$iLevel == 0 && self::$_aGroupTree = array();

		return $aReturn;
	}
}