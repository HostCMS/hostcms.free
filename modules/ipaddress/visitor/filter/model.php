<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress_Visitor_Filter_Model
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Ipaddress_Visitor_Filter_Model extends Core_Entity
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
		'ipaddress_visitor_filter_dir' => array(),
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

		Ipaddress_Visitor_Filter_Controller::instance()->clearCache();

		return $this;
	}

	/**
	 * Change active
	 * @return self
	 * @hostcms-event ipaddress_visitor_filter.onBeforeChangeActive
	 * @hostcms-event ipaddress_visitor_filter.onAfterChangeActive
	 */
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$this->active = 1 - $this->active;
		$this->save();

		Ipaddress_Visitor_Filter_Controller::instance()->clearCache();

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
		if ($this->block_mode)
		{
			Core_Html_Entity::factory('Span')
				->class('badge badge-round blue white')
				->value('<i class="fa-solid fa-robot"></i> ' . Core::_('Ipaddress_Visitor_Filter.block_mode1'))
				->execute();
		}
		else
		{
			$this->ban_hours && Core_Html_Entity::factory('Span')
				->class('badge badge-round orange white')
				->value('<i class="fa fa-ban"></i> ' . $this->_getHoursByText($this->ban_hours))
				->execute();
		}

		$color = $this->mode
			? 'green'
			: 'gray';

		Core_Html_Entity::factory('Span')
			->class('badge badge-round badge-max-width margin-right-5 ' . $color)
			->value(Core::_('Ipaddress_Visitor_Filter.mode' . $this->mode))
			->title(Core::_('Ipaddress_Visitor_Filter.mode'))
			->execute();
	}

	/**
	 * Get Hours by text
	 * @param int $hours e.g. 48
	 * @return string
	 */
	protected function _getHoursByText($hours)
	{
		$hours = intval($hours);

		return Core_Inflection::available(CURRENT_LNG)
			? ($hours % 24 == 0
				? ($hours / 24) . ' ' . Core_Inflection::getPlural(Core::_('Admin.day'), $hours / 24, CURRENT_LNG)
				: $hours . ' ' . Core_Inflection::getPlural(Core::_('Admin.hour'), $hours, CURRENT_LNG)
			)
			: Core::_('Admin.hours', $this->ban_hours);
	}

	/**
	 * Get Times by text
	 * @param int $times e.g. 2
	 * @return string
	 */
	protected function _getTimesByText($times)
	{
		$times = intval($times);

		return /*Core_Inflection::available(CURRENT_LNG)
			? $times . ' ' . Core_Inflection::getPlural(Core::_('Admin.time'), $times, CURRENT_LNG)
			: */$times . ' ' . Core::_('Ipaddress_Visitor_Filter.times');
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
				<span class="badge badge-round badge-max-width margin-right-5 green"><?php echo htmlspecialchars(Core::_('Ipaddress_Filter.' . $type) . ($type == 'get' ? ' ' . Core_Array::get($aCondition, 'get', '', 'trim')  : '') . ($type == 'header' ? ' ' . Core_Array::get($aCondition, 'header', '', 'trim') : ''))?></span>

				<?php
				if ($type != 'delta_mobile_resolution')
				{
				?>
					<span class="badge badge-round badge-max-width margin-right-5 gray"><?php echo htmlspecialchars(Core::_('Ipaddress_Filter.condition_' . Core_Array::get($aCondition, 'condition', '', 'trim')))?></span>
					<span class="badge badge-round badge-max-width margin-right-5 blue"><?php echo htmlspecialchars(Core_Array::get($aCondition, 'value', '', 'trim'))?></span>

				<?php
				}

				if ($type != 'header')
				{
					$times = Core_Array::get($aCondition, 'times', 1, 'int');
					$hours = Core_Array::get($aCondition, 'hours', 1, 'int');
					?>
					<span class="badge badge-round badge-max-width margin-right-5 orange"><?php echo $this->_getTimesByText($times)?> <?php echo Core::_('Ipaddress_Visitor_Filter.in')?> <?php echo $this->_getHoursByText($hours)?></span>

				<?php
				}

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
	 * @hostcms-event ipaddress_visitor_filter.onAfterRedeclaredCopy
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
	 * @hostcms-event ipaddress_visitor_filter.onBeforeMove
	 * @hostcms-event ipaddress_visitor_filter.onAfterMove
	 */
	public function move($ipaddress_visitor_filter_dir_id)
	{
		Core_Event::notify($this->_modelName . '.onBeforeMove', $this, array($ipaddress_visitor_filter_dir_id));

		$this->ipaddress_visitor_filter_dir_id = $ipaddress_visitor_filter_dir_id;
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
		return '<a target="_blank" href="' . $oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'exportFilters', NULL, 1, intval($this->id), 'ipaddress_visitor_filter_dir_id=' . Core_Array::getGet('ipaddress_visitor_filter_dir_id')) . '"><i class="fa fa-upload"></i></a>';
	}
}