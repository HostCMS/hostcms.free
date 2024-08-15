<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shortcode Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shortcode
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shortcode_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			case 'shortcode':
				if (!$object->id)
				{
					$object->shortcode_dir_id = Core_Array::getGet('shortcode_dir_id', 0);
				}

				parent::setObject($object);

				$title = $this->_object->id
					? Core::_('Shortcode.shortcode_edit_form_title', $this->_object->name, FALSE)
					: Core::_('Shortcode.shortcode_add_form_title');

				$oMainTab = $this->getTab('main');
				$oAdditionalTab = $this->getTab('additional');

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'));

				$oMainTab
					->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
					->move($this->getField('shortcode')->divAttr(array('class' => 'form-group col-xs-12'))->format(
						array(
							'minlen' => array('value' => 1),
							'reg' => array('value' => '^[A-Za-z_]+[A-Za-z0-9_]*$')
						)
					), $oMainRow2)
					->move($this->getField('example')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2);

				$oMainTab->delete($this->getField('php'));

				$oPhp_Textarea = Admin_Form_Entity::factory('Textarea');

				$oTmpOptions = $oPhp_Textarea->syntaxHighlighterOptions;
				$oTmpOptions['mode'] = '"ace/mode/php"';

				$oPhp_Textarea
					->value($this->_object->php)
					->cols(140)
					->rows(30)
					->caption(Core::_('Shortcode.php'))
					->name('php')
					->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
					->syntaxHighlighterOptions($oTmpOptions)
					->divAttr(array('class' => 'form-group col-xs-12'));

				$oMainRow3->add($oPhp_Textarea);

				// Удаляем группу
				$oAdditionalTab->delete($this->getField('shortcode_dir_id'));

				$oGroupSelect = Admin_Form_Entity::factory('Select');

				$oGroupSelect
					->caption(Core::_('Shortcode.shortcode_dir_id'))
					->options(array(' … ') + self::fillShortcodeDir())
					->name('shortcode_dir_id')
					->value($this->_object->shortcode_dir_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

				$oMainRow4->add($oGroupSelect);

				$oMainTab
					->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3')), $oMainRow4)
					->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3')), $oMainRow5);

			break;

			case 'shortcode_dir':
				if (!$object->id)
				{
					$object->parent_id = Core_Array::getGet('shortcode_dir_id');
				}

				parent::setObject($object);

				$title = $this->_object->id
					? Core::_("Shortcode_Dir.shortcode_dir_edit_form_title", $this->_object->name, FALSE)
					: Core::_("Shortcode_Dir.shortcode_dir_add_form_title");

				// Получаем стандартные вкладки
				$oMainTab = $this->getTab('main');
				$oAdditionalTab = $this->getTab('additional');

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					;

				$oMainTab
					->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1);

				// Удаляем группу
				$oAdditionalTab->delete($this->getField('parent_id'));

				$oGroupSelect = Admin_Form_Entity::factory('Select')
					->caption(Core::_('Shortcode_Dir.parent_id'))
					->options(array(' … ') + self::fillShortcodeDir(0, array($this->_object->id)))
					->name('parent_id')
					->value($this->_object->parent_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

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
	 * @param int $iShortcodeDirParentId parent ID
	 * @param int $aExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	static public function fillShortcodeDir($iShortcodeDirParentId = 0, $aExclude = array(), $iLevel = 0)
	{
		$iShortcodeDirParentId = intval($iShortcodeDirParentId);
		$iLevel = intval($iLevel);

		if ($iLevel == 0)
		{
			$aTmp = Core_QueryBuilder::select('id', 'parent_id', 'name')
				->from('shortcode_dirs')
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

		if (isset(self::$_aGroupTree[$iShortcodeDirParentId]))
		{
			$countExclude = count($aExclude);
			foreach (self::$_aGroupTree[$iShortcodeDirParentId] as $childrenGroup)
			{
				if ($countExclude == 0 || !in_array($childrenGroup['id'], $aExclude))
				{
					$aReturn[$childrenGroup['id']] = str_repeat('  ', $iLevel) . $childrenGroup['name'] . ' [' . $childrenGroup['id'] . ']' ;
					$aReturn += self::fillShortcodeDir($childrenGroup['id'], $aExclude, $iLevel + 1);
				}
			}
		}

		$iLevel == 0 && self::$_aGroupTree = array();

		return $aReturn;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shortcode_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 * @return self
	 */
	protected function _applyObjectProperty()
	{
		$modelName = $this->_object->getModelName();

		// Backup revision
		if (Core::moduleIsActive('revision') && $this->_object->id)
		{
			$modelName == 'shortcode'
				&& $this->_object->backupRevision();
		}

		parent::_applyObjectProperty();

		switch ($modelName)
		{
			case 'shortcode':
				// Rebuild shortcodes list
				Shortcode_Controller::instance()->rebuild();
			break;
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		if (!is_null($operation) && $operation != '')
		{
			$modelName = $this->_object->getModelName();

			$shortcode = Core_Array::get($this->_formValues, 'shortcode');

			switch ($modelName)
			{
				case 'shortcode':
					$oSameShortcode = Core_Entity::factory('Shortcode')->getByShortcode($shortcode);

					if (!is_null($oSameShortcode) && $oSameShortcode->id != Core_Array::get($this->_formValues, 'id'))
					{
						$this->addMessage(
							Core_Message::get(Core::_('Shortcode.error_shortcode'), 'error')
						);

						return TRUE;
					}
				break;
			}
		}

		return parent::execute($operation);
	}
}