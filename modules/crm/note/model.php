<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Note_Model
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Crm_Note_Model extends Core_Entity
{
	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'text';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array()
	);

	/**
	 * One-to-one relations
	 * @var array
	 */
	protected $_hasOne = array(
		'event_crm_note' => array('foreign_key' => 'crm_note_id'),
		'event' => array('through' => 'event_crm_note'),
		'lead_crm_note' => array('foreign_key' => 'crm_note_id'),
		'lead' => array('through' => 'lead_crm_note'),
		'crm_project_crm_note' => array('foreign_key' => 'crm_note_id'),
		'crm_project' => array('through' => 'crm_project_crm_note'),
		'deal_crm_note' => array('foreign_key' => 'crm_note_id'),
		'deal' => array('through' => 'deal_crm_note'),
		'siteuser_crm_note' => array('foreign_key' => 'crm_note_id'),
		'siteuser' => array('through' => 'siteuser_crm_note'),
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'crm_note_attachment' => array(),
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'crm_notes.datetime' => 'DESC',
		'crm_notes.id' => 'DESC'
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
			$this->_preloadValues['ip'] = Core::getClientIp();
		}
	}

	/**
	 * Set file directory
	 * @param string $dir directory path
	 * @return self
	 */
	public function setDir($dir)
	{
		$this->dir = $dir;
		return $this->save();
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event event.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$aCrm_Note_Attachments = $this->Crm_Note_Attachments->findAll(FALSE);
		foreach ($aCrm_Note_Attachments as $oCrm_Note_Attachment)
		{
			$oCrm_Note_Attachment
				->setDir(CMS_FOLDER . $this->dir)
				->delete();
		}

		return parent::delete($primaryKey);
	}

	/**
	 * Show files block
	 * @return string
	 */
	public function getFilesBlock($oObject)
	{
		$oUser = Core_Auth::getCurrentUser();
		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$bModuleAccess = $oUser->checkModuleAccess(array('crm'), $oSite);

		$aCrm_Note_Attachments = $this->Crm_Note_Attachments->findAll(FALSE);
		if (count($aCrm_Note_Attachments))
		{
			ob_start();

			foreach ($aCrm_Note_Attachments as $oCrm_Note_Attachment)
			{
				$path = $oObject->getModelName() == 'siteuser'
					? $oObject->getDirPath()
					: $oObject->getPath();

				$href = $oObject->getModelName() == 'siteuser'
					? $oObject->getDirHref()
					: $oObject->getHref();

				$file = $oCrm_Note_Attachment->setDir($path)->setHref($href)->getSmallFileHref();

				if ($bModuleAccess)
				{
					$src = '/admin/crm/note/index.php?&' . $oObject->getModelName() . '_id=' . $oObject->id . '&crm_note_attachment_id=' . $oCrm_Note_Attachment->id . '&rand=' . time();

					if (!is_null($file))
					{
						$src .= '&preview';
						$image = '<img src="' . htmlspecialchars($src) . '"/>';
						$onclick = ' onclick="$.showCrmNoteAttachment(this, \'' . $oObject->getModelName() . '\')"';
						$name = htmlspecialchars($oCrm_Note_Attachment->file_name);
					}
					else
					{
						$src .= '&download';
						$image = '<a target="_blank" href="' . htmlspecialchars($src) . '"><i class="' . Core_File::getIcon($oCrm_Note_Attachment->file_name) . '"></i></a>';
						$onclick = '';
						$name = '<a target="_blank" href="' . htmlspecialchars($src) . '">' . htmlspecialchars($oCrm_Note_Attachment->file_name) . '</a>';
					}

					?><div class="crm-note-attachment-item" data-id="<?php echo $oCrm_Note_Attachment->id?>" data-<?php echo $oObject->getModelName()?>-id="<?php echo $oObject->id?>" title="<?php echo htmlspecialchars($oCrm_Note_Attachment->file_name)?>"<?php echo $onclick?>>
						<div class="image">
							<?php echo $image?>
							<span class="size"><?php echo $oCrm_Note_Attachment->getTextSize()?></span>
						</div>
						<div class="name"><?php echo $name?></div>
					</div><?php
				}
				else
				{
					?><div class="crm-note-attachment-item" title="<?php echo htmlspecialchars($oCrm_Note_Attachment->file_name)?>">
					<div class="image">
						<!-- <i class="fa-solid fa-image gray"></i> -->
						<i class="<?php echo Core_File::getIcon($oCrm_Note_Attachment->file_name)?>"></i>
						<span class="size"><?php echo $oCrm_Note_Attachment->getTextSize()?></span>
					</div>
					<div class="name"><?php echo htmlspecialchars($oCrm_Note_Attachment->file_name)?></div>
				</div><?php
				}
			}

			return ob_get_clean();
		}

		return NULL;
	}

	/**
	 * Delete attachment file
	 * @param $crm_note_attachment_id attachment id
	 * @return self
	 */
	public function deleteFile($crm_note_attachment_id)
	{
		$oCrm_Note_Attachment = $this->Crm_Note_Attachments->getById($crm_note_attachment_id);
		if (!is_null($oCrm_Note_Attachment))
		{
			$oCrm_Note_Attachment->setDir(CMS_FOLDER . $this->dir);
			$oCrm_Note_Attachment->delete();
		}

		return $this;
	}

	/**
	 * Check user access to admin form action
	 * @param string $actionName admin form action name
	 * @param User_Model $oUser user object
	 * @return bool
	 */
	public function checkBackendAccess($actionName, $oUser)
	{
		if ($oUser->superuser == 1)
		{
			return TRUE;
		}

		switch ($actionName)
		{
			case 'edit':
				return $this->user_id == $oUser->id;
			break;
			case 'markDeleted':
				return $this->user_id == $oUser->id || $oUser->superuser;
			break;
			case 'delete':
			case 'undelete':
				return $oUser->superuser == 1;
			break;
			case 'addNote':
				return is_null($this->id);
			break;
		}

		return TRUE;
	}
}