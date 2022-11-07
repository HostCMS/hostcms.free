<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Note_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Crm_Note_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Related object
	 * @var mixed
	 */
	protected $_relatedObject = NULL;

	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('datetime')
			->addSkipColumn('user_id')
			// ->addSkipColumn('event_id')
			->addSkipColumn('dir')
			->addSkipColumn('ip');

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

		$object = $this->_object;

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class(''))
			;

		$this->getField('text')
			->rows(5)
			->wysiwyg(Core::moduleIsActive('wysiwyg'))
			->wysiwygOptions(array(
				'menubar' => 'false',
				'statusbar' => 'false',
				'plugins' => '"advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code wordcount"',
				'toolbar1' => '"bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat"',
				'statusbar' => true
			));

		$oMainTab
			->move($this->getField('text'), $oMainRow1)
			->move($this->getField('result')->divAttr(array('class' => 'col-xs-12 margin-bottom-10')), $oMainRow1);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$relatedId = $this->_relatedObject->getModelName() . '_id';

		$aCrm_Note_Attachments = $object->Crm_Note_Attachments->findAll(FALSE);

		foreach ($aCrm_Note_Attachments as $oCrm_Note_Attachment)
		{
			$oFile = Admin_Form_Entity::factory('File')
				->controller($this->_Admin_Form_Controller)
				->type('file')
				->caption(Core::_('Deal.attachment'))
				->name("file_{$oCrm_Note_Attachment->id}")
				->largeImage(
					array(
						'path' => '/admin/crm/note/index.php?preview&' . $relatedId . '=' . $this->_relatedObject->id . '&crm_note_attachment_id=' . $oCrm_Note_Attachment->id,
						'show_params' => FALSE,
						'originalName' => $oCrm_Note_Attachment->file_name,
						'delete_onclick' => "$.adminLoad({path: '/admin/event/note/index.php', additionalParams: 'hostcms[checked][0][{$this->_object->id}]=1&{$relatedId}={$this->_relatedObject->id}', operation: '{$oCrm_Note_Attachment->id}', action: 'deleteFile', windowId: '{$windowId}'}); return false",
						'delete_href' => ''
					)
				)
				->smallImage(
					array('show' => FALSE)
				)
				->divAttr(array('id' => "file_{$oCrm_Note_Attachment->id}", 'class' => 'input-group col-xs-12'));

			$oMainRow2->add($oFile);
		}

		$oAdmin_Form_Entity_Code = Admin_Form_Entity::factory('Code');
		$oAdmin_Form_Entity_Code->html('<div class="input-group-addon no-padding add-remove-property"><div class="no-padding-left col-lg-12"><div class="btn btn-palegreen" onclick="$.cloneFile(\'' . $windowId .'\'); event.stopPropagation();"><i class="fa fa-plus-circle close"></i></div>
			<div class="btn btn-darkorange" onclick="$(this).parents(\'#file\').remove(); event.stopPropagation();"><i class="fa fa-minus-circle close"></i></div>
			</div>
			</div>');

		$oFileNew = Admin_Form_Entity::factory('File')
			->controller($this->_Admin_Form_Controller)
			->type('file')
			->name("file[]")
			->caption(Core::_('Deal.attachment'))
			->largeImage(
				array(
					'show_params' => FALSE,
					'show_description' => FALSE
				)
			)
			->smallImage(
				array('show' => FALSE)
			)
			->divAttr(array('id' => 'file', 'class' => 'row col-xs-12 add-deal-attachment '))
			->add($oAdmin_Form_Entity_Code);

		$oMainRow3->add($oFileNew);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Crm_Project_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$bAdd = !$this->_object->id;

		parent::_applyObjectProperty();

		// для проектов необходима связь, нет отдельного add-контроллера
		if ($bAdd)
		{
			$this->_relatedObject->add($this->_object);

			$oModule = Core_Entity::factory('Module')->getByPath($this->_relatedObject->getModelName());

			// Добавляем уведомление
			$oNotification = Core_Entity::factory('Notification')
				->title(Core::_('Crm_Project_Note.add_notification', $this->_relatedObject->name, FALSE))
				->description(
					html_entity_decode(strip_tags($this->_object->text), ENT_COMPAT, 'UTF-8')
				)
				->datetime(Core_Date::timestamp2sql(time()))
				->module_id($oModule->id)
				->type(6) // 6 - Добавлена заметка
				->entity_id($this->_relatedObject->id)
				->save();

			// Связываем уведомление с сотрудниками
			Core_Entity::factory('User', $this->_relatedObject->user_id)->add($oNotification);
		}

		// Замена загруженных ранее файлов на новые
		$aCrm_Note_Attachments = $this->_object->Crm_Note_Attachments->findAll(FALSE);

		foreach ($aCrm_Note_Attachments as $oCrm_Note_Attachment)
		{
			!is_null($this->_relatedObject) && $oCrm_Note_Attachment->setDir($this->_relatedObject->getPath());

			$aExistFile = Core_Array::getFiles("file_{$oCrm_Note_Attachment->id}");

			if (!is_null($aExistFile))
			{
				if (Core_File::isValidExtension($aExistFile['name'], Core::$mainConfig['availableExtension']))
				{
					$oCrm_Note_Attachment->saveFile($aExistFile['tmp_name'], $aExistFile['name']);
				}
			}
		}

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		// New values of property
		$aNewFiles = Core_Array::getFiles("file", array());

		// New values of property
		if (is_array($aNewFiles) && isset($aNewFiles['name']))
		{
			$iCount = count($aNewFiles['name']);

			for ($i = 0; $i < $iCount; $i++)
			{
				ob_start();

				$aFile = array(
					'name' => $aNewFiles['name'][$i],
					'tmp_name' => $aNewFiles['tmp_name'][$i],
					'size' => $aNewFiles['size'][$i]
				);

				$oCore_Html_Entity_Script = Core_Html_Entity::factory('Script')
					->value("$(\"#{$windowId} #file:has(input\\[name='file\\[\\]'\\])\").eq(0).remove();");

				if (intval($aFile['size']) > 0)
				{
					$oCrm_Note_Attachment = Core_Entity::factory('Crm_Note_Attachment');

					!is_null($this->_relatedObject) && $oCrm_Note_Attachment->setDir($this->_relatedObject->getPath());

					$oCrm_Note_Attachment->crm_note_id = $this->_object->id;

					$oCrm_Note_Attachment
						->saveFile($aFile['tmp_name'], $aFile['name']);

					if (!is_null($oCrm_Note_Attachment->id))
					{
						$oCore_Html_Entity_Script
							->value("$(\"#{$windowId} #file\").find(\"input[name='file\\[\\]']\").eq(0).attr('name', 'file_{$oCrm_Note_Attachment->id}');");
					}
				}

				$oCore_Html_Entity_Script->execute();

				$this->_Admin_Form_Controller->addMessage(ob_get_clean());
			}
		}
	}
}