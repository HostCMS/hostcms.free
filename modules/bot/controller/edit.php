<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Bot Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Bot
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Bot_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		parent::setObject($object);

		$modelName = $this->_object->getModelName();

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oSelect_Dirs = Admin_Form_Entity::factory('Select');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'));

		switch ($modelName)
		{
			case 'bot':
				$title = $this->_object->id
					? Core::_('Bot.edit_title', $this->_object->name)
					: Core::_('Bot.add_title');

				if (!$this->_object->id)
				{
					$this->_object->bot_dir_id = Core_Array::getGet('bot_dir_id');
				}

				$oMainTab
					->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
					->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2);

				// Удаляем стандартный <input>
				$oAdditionalTab->delete($this->getField('bot_dir_id'));

				// Селектор с группой
				$oSelect_Dirs
					->options(
						array(' … ') + $this->fillBotDir()
					)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
					->name('bot_dir_id')
					->value($this->_object->bot_dir_id)
					->caption(Core::_('Bot.bot_dir_id'));

				$oMainRow3->add($oSelect_Dirs);

				$oMainTab
					->move($this->getField('class')->divAttr(array('class' => 'form-group col-xs-12 col-lg-3')), $oMainRow3)
					->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-lg-3')), $oMainRow3);
			break;

			case 'bot_dir':
			default:
				$title = $this->_object->id
					? Core::_('Bot_Dir.edit_title', $this->_object->name)
					: Core::_('Bot_Dir.add_title');

				// Значения директории для добавляемого объекта
				if (!$this->_object->id)
				{
					$this->_object->parent_id = Core_Array::getGet('bot_dir_id');
				}

				// Удаляем стандартный <input>
				$oAdditionalTab->delete($this->getField('parent_id'));

				$oSelect_Dirs
					->options(
						array(' … ') + $this->fillBotDir(0, $this->_object->id)
					)
					->name('parent_id')
					->value($this->_object->parent_id)
					->caption(Core::_('Bot_Dir.parent_id'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

				$oMainRow3->add($oSelect_Dirs);

				$oMainTab->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-5 col-lg-3')), $oMainRow3);
			break;
		}

		$this->title($title);

		return $this;
	}

	/**
	 * Create visual tree of the directories
	 * @param int $iBotDirParentId parent directory ID
	 * @param boolean $bExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	public function fillBotDir($iBotDirParentId = 0, $bExclude = FALSE, $iLevel = 0)
	{
		$iBotDirParentId = intval($iBotDirParentId);
		$iLevel = intval($iLevel);

		$oBot_Dir = Core_Entity::factory('Bot_Dir', $iBotDirParentId);

		$aReturn = array();

		// Дочерние разделы
		$childrenDirs = $oBot_Dir->Bot_Dirs->findAll();

		if (count($childrenDirs))
		{
			foreach ($childrenDirs as $childrenDir)
			{
				if ($bExclude != $childrenDir->id)
				{
					$aReturn[$childrenDir->id] = str_repeat('  ', $iLevel) . $childrenDir->name . ' [' . $childrenDir->id . ']';
					$aReturn += $this->fillBotDir($childrenDir->id, $bExclude, $iLevel + 1);
				}
			}
		}

		return $aReturn;
	}
}