<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress_Filter_Model
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Ipaddress_Filter_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $img = 1;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'ipaddress_filter_dir' => array(),
		'user' => array()
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
		}
	}

	/**
	 * Backend property
	 */
	public function bannedBackend()
	{
		return '<span title="' . $this->banned . '">' . Core_Str::getTextCount($this->banned) . '</span>';
	}

	/**
	 * Mark entity as deleted
	 * @return Core_Entity
	 */
	public function markDeleted()
	{
		parent::markDeleted();

		Ipaddress_Filter_Controller::instance()->clearCache();

		return $this;
	}

	/**
	 * Change active
	 * @return self
	 * @hostcms-event ipaddress_filter.onBeforeChangeActive
	 * @hostcms-event ipaddress_filter.onAfterChangeActive
	 */
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$this->active = 1 - $this->active;
		$this->save();

		Ipaddress_Filter_Controller::instance()->clearCache();

		Core_Event::notify($this->_modelName . '.onAfterChangeActive', $this);

		return $this;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return htmlspecialchars($this->name);
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$this->block_ip && Core_Html_Entity::factory('Span')
			->class('badge badge-hostcms badge-square darkorange')
			->value('<i class="fa-solid fa-ban"></i> IP')
			->title(Core::_('Ipaddress_Filter.block_ip'))
			->execute();

		$color = $this->mode
			? 'green'
			: 'gray';

		Core_Html_Entity::factory('Span')
			->class('badge badge-round badge-max-width margin-right-5 ' . $color)
			->value(Core::_('Ipaddress_Filter.mode' . $this->mode))
			->title(Core::_('Ipaddress_Filter.mode'))
			->execute();
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function jsonBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$aJson = json_decode($this->json, TRUE);

		foreach ($aJson as $aCondition)
		{
			$type = Core_Array::get($aCondition, 'type', '', 'trim');

			?>
			<div class="d-flex align-items-center margin-bottom-5">
				<span class="badge badge-round badge-max-width margin-right-5 green"><?php echo htmlspecialchars(Core::_('Ipaddress_Filter.' . $type) . ($type == 'get' ? ' ' . Core_Array::get($aCondition, 'get', '', 'trim'): '') . ($type == 'header' ? ' ' . Core_Array::get($aCondition, 'header', '', 'trim') : ''))?></span>
				<span class="badge badge-round badge-max-width margin-right-5 gray"><?php echo htmlspecialchars(Core::_('Ipaddress_Filter.condition_' . Core_Array::get($aCondition, 'condition', '', 'trim')))?></span>
				<span class="badge badge-round badge-max-width margin-right-5 blue"><?php echo htmlspecialchars(Core_Array::get($aCondition, 'value', '', 'trim'))?></span>

				<?php
				if ($aCondition['case_sensitive'])
				{
					?><i title="<?php echo Core::_('Ipaddress_Filter.case_sensitive')?>" class="fa-solid fa-font"></i><?php
				}
				?>
			</div>
			<?php
		}
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event ipaddress_filter.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();
		$newObject->banned = 0;
		$newObject->save();

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Move item to another group
	 * @param int $ipaddress_dir_id target group id
	 * @return Core_Entity
	 * @hostcms-event ipaddress_filter.onBeforeMove
	 * @hostcms-event ipaddress_filter.onAfterMove
	 */
	public function move($ipaddress_filter_dir_id)
	{
		Core_Event::notify($this->_modelName . '.onBeforeMove', $this, array($ipaddress_filter_dir_id));

		$this->ipaddress_filter_dir_id = $ipaddress_filter_dir_id;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterMove', $this);

		return $this;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function exportBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return '<a target="_blank" href="' . $oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'exportFilters', NULL, 1, intval($this->id), 'ipaddress_filter_dir_id=' . Core_Array::getGet('ipaddress_filter_dir_id')) . '"><i class="fa fa-upload"></i></a>';
	}
}