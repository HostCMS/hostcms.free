<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Document Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Document
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Document_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$modelName = $object->getModelName();

		if (!$object->id)
		{
			switch ($modelName)
			{
				case 'document':
					$object->document_dir_id = Core_Array::getGet('document_dir_id');

					$aTypographConfig = Typograph_Controller::instance()->getConfig();

					$object->typograph = $aTypographConfig['typograph'];
					$object->trailing_punctuation = $aTypographConfig['trailing_punctuation'];
				break;
				case 'document_dir':
					$object->parent_id = Core_Array::getGet('document_dir_id');
				break;
			}
		}

		$modelName == 'document' && $object->datetime(Core_Date::timestamp2sql(time()));

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

		$modelName = $this->_object->getModelName();

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oSelect_Dirs = Admin_Form_Entity::factory('Select');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		switch ($modelName)
		{
			case 'document':
				$title = $this->_object->id
					? Core::_('Document.edit', $this->_object->name, FALSE)
					: Core::_('Document.add');

				$oMainTab->move($this->getField('name'), $oMainRow1);

				$oMainTab->delete($this->getField('text'));

				$oTextarea_Document = Admin_Form_Entity::factory('Textarea')
					->value($this->_object->text)
					->rows(18)
					->caption(Core::_('Document.text'))
					->name('text')
					->wysiwyg(Core::moduleIsActive('wysiwyg'))
					->data('ai_prompt_default', Core::_('Document.ai_prompt_default'))
					->divAttr(array('class' => 'form-group col-xs-12'))
					->template_id($this->_object->template_id);

				$oMainRow2->add($oTextarea_Document);

				$oMainTab
					->delete($this->getField('typograph'))
					->delete($this->getField('trailing_punctuation'));

				if (Core::moduleIsActive('typograph'))
				{
					// $aTypographConfig = Typograph_Controller::instance()->getConfig();

					$oTextarea_Document->value(
						Typograph_Controller::instance()->eraseOpticalAlignment($oTextarea_Document->value)
					);

					$oUseTypograph = Admin_Form_Entity::factory('Checkbox')
						->name("typograph")
						->caption(Core::_('Document.use_typograph'))
						->value(1)
						->divAttr(array('class' => 'form-group col-sm-12 col-md-6'))
						->checked($this->_object->typograph);

					$oUseTrailingPunctuation = Admin_Form_Entity::factory('Checkbox')
						->name("trailing_punctuation")
						->caption(Core::_('Document.use_trailing_punctuation'))
						->value(1)
						->divAttr(array('class' => 'form-group col-sm-12 col-md-6'))
						->checked($this->_object->trailing_punctuation);

					$oMainTab
						->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'));

					$oMainRow3
						->add($oUseTypograph)
						->add($oUseTrailingPunctuation);
				}

				$oMainTab
					->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'));

				$oAdditionalTab->delete($this->getField('document_dir_id'));

				$oSelect_Dirs
					->options(
						array(' … ') + $this->fillDocumentDir(CURRENT_SITE, 0)
					)
					->name('document_dir_id')
					->value($this->_object->document_dir_id)
					->caption(Core::_('Document.document_dir_id'))
					->divAttr(array('class' => 'form-group col-sm-12 col-md-6'));

				$oAdditionalTab->delete($this->getField('template_id'));

				// Выбор макета
				$Template_Controller_Edit = new Template_Controller_Edit($this->_Admin_Form_Action);

				$aTemplateOptions = $Template_Controller_Edit->fillTemplateList($this->_object->site_id);

				$oSelect_Template_Id = Admin_Form_Entity::factory('Select')
					->options(
						count($aTemplateOptions) ? $aTemplateOptions : array(' … ')
					)
					->name('template_id')
					->value($this->_object->template_id)
					->caption(Core::_('Document.template_id'))
					->divAttr(array('class' => 'form-group col-sm-12 col-md-6'));

				$oMainRow4
					->add($oSelect_Dirs)
					->add($oSelect_Template_Id);

				// Статус документа
				$oAdditionalTab
					->delete($this->getField('document_status_id'));

				$Document_Status_Controller_Edit = new Document_Status_Controller_Edit($this->_Admin_Form_Action);

				$oSelect_Statuses = Admin_Form_Entity::factory('Select')
					->options(
						array(' … ') + $Document_Status_Controller_Edit->fillDocumentStatus(CURRENT_SITE)
					)
					->name('document_status_id')
					->value($this->_object->document_status_id)
					->caption(Core::_('Document.document_status_id'))
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

				$oMainRow5->add($oSelect_Statuses);

				$oMainTab->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow5);
			break;
			case 'document_dir':
			default:
				$title = $this->_object->id
					? Core::_('Document_Dir.edit_title', $this->_object->name, FALSE)
					: Core::_('Document_Dir.add_title');

				// Удаляем стандартный <input>
				$oAdditionalTab->delete($this->getField('parent_id'));

				$oSelect_Dirs
					->options(
						array(' … ') + $this->fillDocumentDir(CURRENT_SITE, 0, $this->_object->id)
					)
					->name('parent_id')
					->value($this->_object->parent_id)
					->caption(Core::_('Document_Dir.parent_id'))
					->divAttr(array('class' => 'form-group col-xs-12'));

				$this->getField('name')
					->divAttr(array('class' => 'form-group col-xs-12'));

				$oMainTab->move($this->getField('name'), $oMainRow1);

				$oMainRow1->add($oSelect_Dirs);
			break;
		}

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Document_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$modelName = $this->_object->getModelName();

		if ($modelName == 'document')
		{
			// Backup revision
			if (Core::moduleIsActive('revision') && $this->_object->id)
			{
				$this->_object->backupRevision();
			}

			if (Core::moduleIsActive('typograph') && Core_Array::getPost('typograph'))
			{
				$this->_formValues['text'] = Typograph_Controller::instance()->process($this->_formValues['text'], Core_Array::getPost('trailing_punctuation'));
			}
		}

		parent::_applyObjectProperty();

		if ($modelName == 'document' && Core::moduleIsActive('cache'))
		{
			$oStructures = Core_Entity::factory('Structure');
			$oStructures->queryBuilder()
				->where('structures.document_id', '=',  $this->_object->id);

			$aStructures = $oStructures->findAll(FALSE);

			foreach ($aStructures as $oStructure)
			{
				$oStructure->clearCache();

				if ($oStructure->indexing && Core::moduleIsActive('search'))
				{
					Search_Controller::indexingSearchPages(array(
						$oStructure->indexing()
					));
				}
			}
		}

		Core::moduleIsActive('wysiwyg') && Wysiwyg_Controller::uploadImages($this->_formValues, $this->_object, $this->_Admin_Form_Controller);

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Create visual tree of the document directories
	 * @param int $iSiteId site ID
	 * @param int $iDocumentDirParentId initial directory
	 * @param boolean $bExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	public function fillDocumentDir($iSiteId, $iDocumentDirParentId = 0, $bExclude = FALSE, $iLevel = 0)
	{
		$iSiteId = intval($iSiteId);
		$iDocumentDirParentId = intval($iDocumentDirParentId);
		$iLevel = intval($iLevel);

		$oDocument_Dir = Core_Entity::factory('Document_Dir', $iDocumentDirParentId);

		$aReturn = array();

		// Дочерние разделы
		$childrenDirs = $oDocument_Dir->Document_Dirs->getBySiteId($iSiteId);

		if (count($childrenDirs))
		{
			foreach ($childrenDirs as $childrenDir)
			{
				if ($bExclude != $childrenDir->id)
				{
					$aReturn[$childrenDir->id] = str_repeat('  ', $iLevel) . $childrenDir->name;
					$aReturn += $this->fillDocumentDir($iSiteId, $childrenDir->id, $bExclude, $iLevel+1);
				}
			}
		}

		return $aReturn;
	}
}