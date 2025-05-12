<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Project_Model
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Crm_Project_Model extends Core_Entity
{
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'event' => array(),
		'deal' => array(),
		// 'crm_project_note' => array(),
		'crm_project_crm_note' => array(),
		'crm_note' => array('through' => 'crm_project_crm_note'),
		'crm_project_attachment' => array(),
		'dms_document' => array(),
		'event_crm_project' => array(),
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array(),
		'site' => array(),
		'crm_icon' => array()
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
			$this->_preloadValues['site_id'] = defined('CURRENT_SITE') ? CURRENT_SITE : 0;
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
		}
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$link = $oAdmin_Form_Field->link;
		$onclick = $oAdmin_Form_Field->onclick;

		$link = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $link);
		$onclick = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $onclick);

		$oCrm_Icon = $this->crm_icon_id
			? $this->Crm_Icon
			: Core_Entity::factory('Crm_Icon')->value('');

		return '<span class="crm-project-list-icon" style="color:' . ($this->color ? htmlspecialchars($this->color) : '#aebec4') . '">' . $oCrm_Icon->getIcon() . '</span> '
			. '<a href="' . $link . '" onclick="' . $onclick . '">' . htmlspecialchars($this->name) . '</a>';
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if (Core::moduleIsActive('event') && $countEvents = $this->Events->getCount())
		{
			$eventColor = '#53a93f';

			?><span class="badge badge-round badge-max-width margin-left-5" title="<?php echo Core::_('Crm_Project.events_count', $countEvents)?>" style="color: <?php echo Core_Str::hex2darker($eventColor, 0.2)?>; background-color:<?php echo Core_Str::hex2lighter($eventColor, 0.88)?>"><i class="fa fa-tasks fa-fw"></i> <?php echo $countEvents?></span><?php
		}

		if (Core::moduleIsActive('deal') && $countDeals = $this->Deals->getCount())
		{
			$dealColor = '#57b5e3';

			?><span class="badge badge-round badge-max-width margin-left-5" title="<?php echo Core::_('Crm_Project.deals_count', $countDeals)?>" style="color: <?php echo Core_Str::hex2darker($dealColor, 0.2)?>; background-color:<?php echo Core_Str::hex2lighter($dealColor, 0.88)?>"><i class="fa fa-handshake-o fa-fw"></i> <?php echo $countDeals?></span><?php
		}

		if (Core::moduleIsActive('dms') && $countDocuments = $this->Dms_Documents->getCount())
		{
			$dmsColor = '#d73d32';

			?><span class="badge badge-round badge-max-width margin-left-5" title="<?php echo Core::_('Crm_Project.documents_count', $countDocuments)?>" style="color: <?php echo Core_Str::hex2darker($dmsColor, 0.2)?>; background-color:<?php echo Core_Str::hex2lighter($dmsColor, 0.88)?>"><i class="fa fa-columns fa-fw"></i> <?php echo $countDocuments?></span><?php
		}

		$countNotes = $this->Crm_Project_Crm_Notes->getCount();
		if ($countNotes)
		{
			$notesColor = '#f4b400';

			?><span class="badge badge-round badge-max-width margin-left-5" title="<?php echo Core::_('Crm_Project.notes_count', $countNotes)?>" style="color: <?php echo Core_Str::hex2darker($notesColor, 0.2)?>; background-color:<?php echo Core_Str::hex2lighter($notesColor, 0.88)?>"><i class="fa fa-comment-o fa-fw"></i> <?php echo $countNotes?></span><?php
		}

		$countFiles = $this->Crm_Project_Attachments->getCount();
		if ($countFiles)
		{
			$filesColor = '#981b48';

			?><span class="badge badge-round badge-max-width margin-left-5" title="<?php echo Core::_('Crm_Project.files_count', $countFiles)?>" style="color: <?php echo Core_Str::hex2darker($filesColor, 0.2)?>; background-color:<?php echo Core_Str::hex2lighter($filesColor, 0.88)?>"><i class="fa fa-file-text-o fa-fw"></i> <?php echo $countFiles?></span><?php
		}
	}

	/**
	 * Backend callback method
	 * @return string
	 */
 	public function datetimeBackend()
	{
		return $this->datetime != '0000-00-00 00:00:00'
			? '<span class="small2">' . Core_Date::timestamp2string(Core_Date::sql2timestamp($this->datetime)) . '</span>'
			: '—';
	}

	/**
	 * Backend callback method
	 * @return string
	 */
 	public function deadlineBackend()
	{
		$color = '';

		if ($this->deadline != '0000-00-00 00:00:00')
		{
			if ($this->completed)
			{
				// $class = 'darkgray';
				$color = '#777';
			}
			elseif (Core_Date::sql2timestamp($this->deadline) < time())
			{
				// $class = 'badge badge-orange';
				$color = '#fb6e52';
			}
			elseif (Core_Date::timestamp2sqldate(Core_Date::sql2timestamp($this->deadline)) == Core_Date::timestamp2sqldate(time()))
			{
				// $class = 'badge badge-palegreen';
				$color = '#a0d468';
			}
			else
			{
				// $class = 'badge badge-lightgray';
				$color = '#999';
			}
		}

		$style = $color != ''
			? "border-color: " . $color . "; color: " . Core_Str::hex2darker($color, 0.2) . "; background-color: " . Core_Str::hex2lighter($color, 0.88)
			: '';

		return $style != '' && $this->deadline != '0000-00-00 00:00:00'
			// ? '<span class="' . $class . ' small2">' . Core_Date::timestamp2string(Core_Date::sql2timestamp($this->deadline)) . '</span>'
			? '<span class="badge badge-round badge-max-width" style="' . $style . '">' . Core_Date::timestamp2string(Core_Date::sql2timestamp($this->deadline)) . '</span>'
			: '';
	}

	/**
	 * Get deadline class
	 * @return string
	 */
	public function getDeadlineClass()
	{
		$deadlineTimestamp = Core_Date::sql2timestamp($this->deadline);

		$today = strtotime('today');
		$tomorrow = strtotime('+1 day', $today);
		$after_tomorrow = strtotime('+2 day', $today);

		if($deadlineTimestamp < time())
		{
			$class = 'darkorange';
		}
		elseif (($deadlineTimestamp > $today && $deadlineTimestamp < $tomorrow)
			|| ($deadlineTimestamp > $tomorrow && $deadlineTimestamp < $after_tomorrow)
		)
		{
			$class = 'palegreen';
		}
		else
		{
			$class = '';
		}

		return $class;
	}

	/**
	 * Show crm project badge
	 * @param Admin_Form_Controller_Model $oAdmin_Form_Controller
	 * @return string
	 */
	public function showBadge($oAdmin_Form_Controller)
	{
		$oUser = Core_Auth::getCurrentUser();
		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$bModuleAccess = $oUser->checkModuleAccess(array('crm_project'), $oSite);

		$color = strlen($this->color)
			? htmlspecialchars($this->color)
			: '#aebec4';

		$name = $bModuleAccess
			? '<a style="color: ' . $color . '" href="' . Admin_Form_Controller::correctBackendPath('/{admin}/crm/project/entity/index.php') . '?crm_project_id=' . $this->id . '" onclick="$.modalLoad({path: hostcmsBackend + \'/crm/project/entity/index.php\', action: \'\', operation: \'modal\', view: \'view\', additionalParams: \'show_entities=1&crm_project_id=' . $this->id . '\', windowId: \'' . $oAdmin_Form_Controller->getWindowId() . '\'}); return false">' . htmlspecialchars($this->name). '</a>'
			: htmlspecialchars($this->name);

		$icon = $this->crm_icon_id
			? $this->Crm_Icon->value
			: 'fas fa-tasks';

		?><span class="badge badge-square margin-right-5" style="color: <?php echo $color?>; background-color:<?php echo Core_Str::hex2lighter($color, 0.88)?>"><i class="<?php echo $icon?>"></i> <?php echo $name?></span><?php
	}

	/**
	 * Change completed status
	 * @return self
	 * @hostcms-event crm_project.onBeforeChangeCompleted
	 * @hostcms-event crm_project.onAfterChangeCompleted
	 */
	public function changeCompleted()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeCompleted', $this);

		$this->completed = 1 - $this->completed;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterChangeCompleted', $this);

		return $this;
	}

	/**
	 * Get message files href
	 * @return string
	 */
	public function getHref()
	{
		 return $this->Site->uploaddir . 'private/crm/projects/' . Core_File::getNestingDirPath($this->id, 3) . '/project_' . $this->id . '/';
	}

	/**
	 * Get path for files
	 * @return string
	 */
	public function getPath()
	{
		return CMS_FOLDER . $this->getHref();
	}

	/**
	 * Create message files directory
	 * @return self
	 */
	public function createDir()
	{
		if (!Core_File::isDir($this->getPath()))
		{
			try
			{
				Core_File::mkdir($this->getPath(), CHMOD, TRUE);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Delete message files directory
	 * @return self
	 */
	public function deleteDir()
	{
		if (Core_File::isDir($this->getPath()))
		{
			try
			{
				Core_File::deleteDir($this->getPath());
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event crm_project.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Crm_Notes->deleteAll(FALSE);
		$this->Crm_Project_Crm_Notes->deleteAll(FALSE);

		if (Core::moduleIsActive('event'))
		{
			Core_QueryBuilder::update('events')
				->set('crm_project_id', 0)
				->where('crm_project_id', '=', $this->id)
				->execute();

			$this->Event_Crm_Projects->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('deal'))
		{
			Core_QueryBuilder::update('deals')
				->set('crm_project_id', 0)
				->where('crm_project_id', '=', $this->id)
				->execute();
		}

		if (Core::moduleIsActive('dms'))
		{
			Core_QueryBuilder::update('dms_documents')
				->set('crm_project_id', 0)
				->where('crm_project_id', '=', $this->id)
				->execute();
		}

		$this->deleteDir();

		return parent::delete($primaryKey);
	}

	/**
	 * Show kanban line
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return self
	 */
	public function showKanbanLine($oAdmin_Form_Controller)
	{
		$color = strlen($this->color)
			? htmlspecialchars($this->color)
			: '#aebec4';

		?><span class="label label-related margin-right-5" style="color: <?php echo $color?>; background-color:<?php echo Core_Str::hex2lighter($color, 0.88)?>"><i class="fa-regular fa-folder-open margin-right-5"></i><a style="color: inherit;" href="<?php echo Admin_Form_Controller::correctBackendPath('/{admin}/crm/project/index.php')?>?hostcms[action]=edit&hostcms[checked][0][<?php echo $this->id?>]=1" onclick="$.modalLoad({path: hostcmsBackend + '/crm/project/index.php', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $this->id?>]=1', windowId: '<?php echo $oAdmin_Form_Controller->getWindowId()?>'}); return false"><?php echo htmlspecialchars($this->name)?></a></span><?php

		return $this;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event crm_project.onBeforeGetRelatedSite
	 * @hostcms-event crm_project.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}