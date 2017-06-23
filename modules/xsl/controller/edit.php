<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * XSL Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Xsl
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Xsl_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Get Languages
	 * @return array
	 */
	protected function _getLngs()
	{
		$aConfig = Core_Config::instance()->get('xsl_config', array()) + array(
			'lngs' => array()
		);

		$aLngs = $aConfig['lngs'];

		$aRows = Site_Controller::instance()->getLngList();
		foreach ($aRows as $aRow)
		{
			if (!in_array($aRow['lng'], $aLngs))
			{
				$aLngs[] = $aRow['lng'];
			}
		}

		return $aLngs;
	}

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
			case 'xsl':
				$title = $this->_object->id
					? Core::_('Xsl.edit_title')
					: Core::_('Xsl.add_title');

				if (!$this->_object->id)
				{
					$this->_object->xsl_dir_id = Core_Array::getGet('xsl_dir_id');
				}

				// Удаляем стандартный <input>
				$oAdditionalTab->delete(
					 $this->getField('xsl_dir_id')
				);

				// Селектор с группой
				$oSelect_Dirs
					->options(
						array(' … ') + $this->fillXslDir()
					)
					->name('xsl_dir_id')
					->value($this->_object->xsl_dir_id)
					->caption(Core::_('Xsl.xsl_dir_id'));

				$oMainRow1->add($oSelect_Dirs);

				$oTextarea_Xsl = Admin_Form_Entity::factory('Textarea');

				$oTmpOptions = $oTextarea_Xsl->syntaxHighlighterOptions;
				$oTmpOptions['mode'] = 'xml';

				$oTextarea_Xsl
					->value(
						$this->_object->loadXslFile()
					)
					->rows(30)
					->caption(Core::_('Xsl.value'))
					->name('xsl_value')
					->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
					->syntaxHighlighterOptions($oTmpOptions)
					->divAttr(array('class' => 'form-group col-xs-12'));

				$oMainRow2->add($oTextarea_Xsl);

				$oMainTab
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'));

				$this->getField('sorting')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-6 col-lg-6'));
				$this->getField('format')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-6 col-lg-6 margin-top-21'));

				$oMainTab->move($this->getField('sorting'), $oMainRow3);
				$oMainTab->move($this->getField('format'), $oMainRow3);

				// Комментарий
				$oDescriptionTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Xsl.tab2'))
					->name('tab_xsl_description');

				$this->addTabAfter($oDescriptionTab, $oMainTab);

				$oDescriptionTab
					->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'));

				$this->getField('description')->divAttr(array('class' => 'form-group col-xs-12'));

				$oMainTab->move($this->getField('description'), $oMainRow6);

				// DTD для всех языков
				$aLngs = $this->_getLngs();

				foreach ($aLngs as $sLng)
				{
					$oTab_Lng = Admin_Form_Entity::factory('Tab')
						->caption(Core::_('Xsl.tab_dtd', $sLng))
						->name('lng_' . $sLng);

					$this->addTabAfter($oTab_Lng, $oMainTab);

					$oTab_Lng->add($oLng_Tab_Row1 = Admin_Form_Entity::factory('Div')->class('row'));

					$oTextarea_Lng = Admin_Form_Entity::factory('Textarea');

					$oTmpOptions = $oTextarea_Lng->syntaxHighlighterOptions;
					$oTmpOptions['mode'] = 'xml';

					$oTextarea_Lng
						->value(
							$this->_object->loadLngDtdFile($sLng)
						)
						->rows(30)
						->caption(Core::_('Xsl.dtd', $sLng))
						->name('lng_' . $sLng)
						->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
						->syntaxHighlighterOptions($oTmpOptions)
						->divAttr(array('class' => 'form-group col-xs-12'));

					$oLng_Tab_Row1->add($oTextarea_Lng);
				}

			break;

			case 'xsl_dir':
			default:
				$title = $this->_object->id
					? Core::_('Xsl_Dir.edit_title')
					: Core::_('Xsl_Dir.add_title');

				// Значения директории для добавляемого объекта
				if (!$this->_object->id)
				{
					$this->_object->parent_id = Core_Array::getGet('xsl_dir_id');
				}

				// Удаляем стандартный <input>
				$oAdditionalTab->delete(
					 $this->getField('parent_id')
				);

				$oSelect_Dirs
					->options(
						array(' … ') + $this->fillXslDir(0, $this->_object->id)
					)
					->name('parent_id')
					->value($this->_object->parent_id)
					->caption(Core::_('Xsl_Dir.parent_id'))
					->divAttr(array('class' => 'form-group col-xs-12'));

				$oMainRow1->add($oSelect_Dirs);

				$this->getField('sorting')->divAttr(array('class' => 'form-group col-sm-6 col-md-5 col-lg-4'));
				$oMainTab->move($this->getField('sorting'), $oMainRow2);

			break;
		}

		$this->title($title);

		return $this;
	}

	/**
	 * Create visual tree of the directories
	 * @param int $iXslDirParentId parent directory ID
	 * @param boolean $bExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	public function fillXslDir($iXslDirParentId = 0, $bExclude = FALSE, $iLevel = 0)
	{
		$iXslDirParentId = intval($iXslDirParentId);
		$iLevel = intval($iLevel);

		$oXsl_Dir = Core_Entity::factory('Xsl_Dir', $iXslDirParentId);

		$aReturn = array();

		// Дочерние разделы
		$childrenDirs = $oXsl_Dir->Xsl_Dirs->findAll();

		if (count($childrenDirs))
		{
			foreach ($childrenDirs as $childrenDir)
			{
				if ($bExclude != $childrenDir->id)
				{
					$aReturn[$childrenDir->id] = str_repeat('  ', $iLevel) . $childrenDir->name;
					$aReturn += $this->fillXslDir($childrenDir->id, $bExclude, $iLevel+1);
				}
			}
		}

		return $aReturn;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Xsl_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'xsl':
				$xsl_value = Core_Array::getPost('xsl_value');

				if (Core_Array::getPost('format'))
				{
					$xsl_value = Xsl_Processor::instance()->formatXml($xsl_value);
				}

				$this->_object->saveXslFile($xsl_value);

				// DTD для всех языков
				$aLngs = $this->_getLngs();

				foreach ($aLngs as $sLng)
				{
					$content = Core_Array::getPost('lng_' . $sLng);

					$this->_object->saveLngDtdFile($sLng, $content);
				}

				// Backup revision
				if (Core::moduleIsActive('revision'))
				{
					$this->_object->backupRevision();
				}
			break;
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return boolean
	 */
	public function execute($operation = NULL)
	{
		if (!is_null($operation) && $operation != '')
		{
			$modelName = $this->_object->getModelName();

			switch ($modelName)
			{
				case 'xsl':
					$name = Core_Array::getRequest('name');
					$id = Core_Array::getRequest('id');
					$oSameXsl = Core_Entity::factory('Xsl')->getByName($name);

					if (!is_null($oSameXsl) && $oSameXsl->id != $id)
					{
						$this->addMessage(
							Core_Message::get(Core::_('Xsl.xsl_already_exists'))
						);
						return TRUE;
					}
			}
		}

		return parent::execute($operation);
	}
}