<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Delivery_Interval_Controller_Tab
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Delivery_Interval_Controller_Tab extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'shop_delivery_id'
	);

	/**
	 * Form controller
	 * @var Admin_Form_Controller
	 */
	protected $_Admin_Form_Controller = NULL;

	/**
	 * Constructor.
	 * @param Admin_Form_Controller $Admin_Form_Controller controller
	 */
	public function __construct(Admin_Form_Controller $Admin_Form_Controller)
	{
		parent::__construct();

		$this->_Admin_Form_Controller = $Admin_Form_Controller;
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$aShop_Delivery_Intervals = $this->_getShopDeliveryIntervals();

		$oDeliveryIntervalDiv = Admin_Form_Entity::factory('Div');

		$oDivOpen = Admin_Form_Entity::factory('Code')->html('<div class="delivery_intervals item_div clear" width="600">');
		$oDivClose = Admin_Form_Entity::factory('Code')->html('</div>');

		$oFromTime = Admin_Form_Entity::factory('Input')
			->id("timepicker_from")
			->caption(Core::_('Shop_Delivery_Interval.from_time'))
			->name('deliveryIntervalFrom_[]')
			->value('00:00')
			->divAttr(array('class' => 'form-group col-xs-6 col-sm-6 col-md-2'));

		$oToTime = Admin_Form_Entity::factory('Input')
			->id("timepicker_to")
			->caption(Core::_('Shop_Delivery_Interval.to_time'))
			->name('deliveryIntervalTo_[]')
			->value('00:00')
			->divAttr(array('class' => 'form-group col-xs-6 col-sm-6 col-md-2'));

		$htmlInit = "<script>
			$(function(){
				$('#{$windowId} #timepicker_from').wickedpicker({
					now: '00:00',
					twentyFour: true, //Display 24 hour format, defaults to false
					upArrow: 'wickedpicker__controls__control-up', //The up arrow class selector to use, for custom CSS
					downArrow: 'wickedpicker__controls__control-down', //The down arrow class selector to use, for custom CSS
					close: 'wickedpicker__close', //The close class selector to use, for custom CSS
					hoverState: 'hover-state', //The hover state class to use, for custom CSS
					title: '" . Core::_('User_Worktime.time') . "', //The Wickedpicker's title,
					showSeconds: false, //Whether or not to show seconds,
					timeSeparator: ' : ', // The string to put in between hours and minutes (and seconds)
					secondsInterval: 1, //Change interval for seconds, defaults to 1,
					minutesInterval: 1, //Change interval for minutes, defaults to 1
					clearable: false //Make the picker's input clearable (has clickable 'x')
				});

				$('#{$windowId} #timepicker_to').wickedpicker({
					now: '00:00',
					twentyFour: true, //Display 24 hour format, defaults to false
					upArrow: 'wickedpicker__controls__control-up', //The up arrow class selector to use, for custom CSS
					downArrow: 'wickedpicker__controls__control-down', //The down arrow class selector to use, for custom CSS
					close: 'wickedpicker__close', //The close class selector to use, for custom CSS
					hoverState: 'hover-state', //The hover state class to use, for custom CSS
					title: '" . Core::_('User_Worktime.time') . "', //The Wickedpicker's title,
					showSeconds: false, //Whether or not to show seconds,
					timeSeparator: ' : ', // The string to put in between hours and minutes (and seconds)
					secondsInterval: 1, //Change interval for seconds, defaults to 1,
					minutesInterval: 1, //Change interval for minutes, defaults to 1
					clearable: false //Make the picker's input clearable (has clickable 'x')
				});
			})
		</script>";

		if (count($aShop_Delivery_Intervals))
		{
			foreach ($aShop_Delivery_Intervals as $oShop_Delivery_Interval)
			{
				$oFromTime = clone $oFromTime;
				$oToTime = clone $oToTime;

				$oDeliveryIntervalDiv
					->add($oDivOpen)
					->add(
						$oFromTime
							->value($oShop_Delivery_Interval->from_time)
							->name("deliveryIntervalFrom_{$oShop_Delivery_Interval->id}")
							->id("deliveryIntervalFrom_{$oShop_Delivery_Interval->id}")
					)
					->add(
						$oToTime
							->value($oShop_Delivery_Interval->to_time)
							->name("deliveryIntervalTo_{$oShop_Delivery_Interval->id}")
							->id("deliveryIntervalTo_{$oShop_Delivery_Interval->id}")
					)
					->add($this->imgBox())
					->add($oDivClose)
				;

				$html = "<script>
					$(function(){
						$('#{$windowId} #deliveryIntervalFrom_{$oShop_Delivery_Interval->id}').wickedpicker({
							now: '{$oShop_Delivery_Interval->from_time}',
							twentyFour: true, //Display 24 hour format, defaults to false
							upArrow: 'wickedpicker__controls__control-up', //The up arrow class selector to use, for custom CSS
							downArrow: 'wickedpicker__controls__control-down', //The down arrow class selector to use, for custom CSS
							close: 'wickedpicker__close', //The close class selector to use, for custom CSS
							hoverState: 'hover-state', //The hover state class to use, for custom CSS
							title: '" . Core::_('User_Worktime.time') . "', //The Wickedpicker's title,
							showSeconds: false, //Whether or not to show seconds,
							timeSeparator: ' : ', // The string to put in between hours and minutes (and seconds)
							secondsInterval: 1, //Change interval for seconds, defaults to 1,
							minutesInterval: 1, //Change interval for minutes, defaults to 1
							clearable: false //Make the picker's input clearable (has clickable 'x')
						});

						$('#{$windowId} #deliveryIntervalTo_{$oShop_Delivery_Interval->id}').wickedpicker({
							now: '{$oShop_Delivery_Interval->to_time}',
							twentyFour: true, //Display 24 hour format, defaults to false
							upArrow: 'wickedpicker__controls__control-up', //The up arrow class selector to use, for custom CSS
							downArrow: 'wickedpicker__controls__control-down', //The down arrow class selector to use, for custom CSS
							close: 'wickedpicker__close', //The close class selector to use, for custom CSS
							hoverState: 'hover-state', //The hover state class to use, for custom CSS
							title: '" . Core::_('User_Worktime.time') . "', //The Wickedpicker's title,
							showSeconds: false, //Whether or not to show seconds,
							timeSeparator: ' : ', // The string to put in between hours and minutes (and seconds)
							secondsInterval: 1, //Change interval for seconds, defaults to 1,
							minutesInterval: 1, //Change interval for minutes, defaults to 1
							clearable: false //Make the picker's input clearable (has clickable 'x')
						});
					})
				</script>";

				$oDeliveryIntervalDiv->add(Admin_Form_Entity::factory('Code')->html($html));
			}
		}
		else
		{
			$oDeliveryIntervalDiv
				->add($oDivOpen)
				->add($oFromTime)
				->add($oToTime)
				->add($this->imgBox())
				->add($oDivClose)
				->add(Admin_Form_Entity::factory('Code')->html($htmlInit))
			;
		}

		return $oDeliveryIntervalDiv;
	}

	/**
	 * Get delivery intervals
	 * @return array
	 */
	protected function _getShopDeliveryIntervals()
	{
		return $this->shop_delivery_id
			? Core_Entity::factory('Shop_Delivery_Interval')->getAllByShop_delivery_id($this->shop_delivery_id, FALSE)
			: array();
	}

	/**
	 * Apply object property
	 */
	public function applyObjectProperty()
	{
		// Интервалы, установленные значения
		$aShop_Delivery_Intervals = $this->_getShopDeliveryIntervals();

		foreach ($aShop_Delivery_Intervals as $oShop_Delivery_Interval)
		{
			$from_time = Core_Array::getPost("deliveryIntervalFrom_{$oShop_Delivery_Interval->id}");

			if (!is_null($from_time) && $from_time !== '')
			{
				$from_time = Core_Array::getPost("deliveryIntervalFrom_{$oShop_Delivery_Interval->id}", '00:00', 'str');
				$to_time = Core_Array::getPost("deliveryIntervalTo_{$oShop_Delivery_Interval->id}", '00:00', 'str');

				$from_time = str_replace(' ', '', $from_time);
				$to_time = str_replace(' ', '', $to_time);

				$oShop_Delivery_Interval
					->shop_delivery_id(intval($this->shop_delivery_id))
					->from_time($from_time . ':00')
					->to_time($to_time . ':00')
					->save();
			}
			else
			{
				$oShop_Delivery_Interval->delete();
			}
		}

		// Интервалы, новые значения
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$aDeliveryIntervals = Core_Array::getPost('deliveryIntervalFrom_');

		if ($aDeliveryIntervals)
		{
			$aDeliveryIntervalTo = Core_Array::getPost('deliveryIntervalTo_');

			foreach ($aDeliveryIntervals as $key => $deliveryInterval)
			{
				if ($deliveryInterval !== '')
				{
					$deliveryInterval = str_replace(' ', '', $deliveryInterval);

					$deliveryIntervalTo = Core_Array::get($aDeliveryIntervalTo, $key);
					$deliveryIntervalTo = str_replace(' ', '', $deliveryIntervalTo);

					$oShop_Delivery_Interval = Core_Entity::factory('Shop_Delivery_Interval')
						->shop_delivery_id(intval($this->shop_delivery_id))
						->from_time($deliveryInterval . ':00')
						->to_time($deliveryIntervalTo . ':00')
						->save();

					ob_start();
					Core_Html_Entity::factory('Script')
						->value("$(\"#{$windowId} input[name='deliveryIntervalFrom_\\[\\]']\").eq(0).prop('name', 'deliveryIntervalFrom_{$oShop_Delivery_Interval->id}');
						$(\"#{$windowId} input[name='deliveryIntervalTo_\\[\\]']\").eq(0).prop('name', 'deliveryIntervalTo_{$oShop_Delivery_Interval->id}');
						")
						->execute();

					$this->_Admin_Form_Controller->addMessage(ob_get_clean());
				}
			}
		}
	}

	public function imgBox($addFunction = '$.cloneDeliveryInterval', $deleteOnclick = '$.deleteNewDeliveryInterval(this)')
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		ob_start();
			Admin_Form_Entity::factory('Div')
				->class('no-padding add-remove-property margin-top-20 pull-left')
				->add(
					Admin_Form_Entity::factory('Div')
						->class('btn btn-palegreen')
						->add(Admin_Form_Entity::factory('Code')->html('<i class="fa fa-plus-circle close"></i>'))
						->onclick("{$addFunction}('{$windowId}', this);")
				)
				->add(
					Admin_Form_Entity::factory('Div')
						->class('btn btn-darkorange btn-delete')
						->add(Admin_Form_Entity::factory('Code')->html('<i class="fa fa-minus-circle close"></i>'))
						->onclick($deleteOnclick)
				)
				->execute();

		return Admin_Form_Entity::factory('Code')->html(ob_get_clean());
	}
}