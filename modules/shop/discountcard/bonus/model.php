<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Discountcard_Bonus_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
 class Shop_Discountcard_Bonus_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_order' => array(),
		'shop_discountcard' => array(),
		'shop_discountcard_bonus_type' => array(),
		'user' => array()
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_discountcard_bonus_transaction' => array()
	);

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'id';

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
		}
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function balanceBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$datetime = Core_Date::timestamp2sql(time());

		if ($this->expired <= $datetime) // Бонус истек
		{
			return '—';
		}

		$value = Shop_Controller::instance()->round($this->amount - $this->written_off);

		if ($this->datetime >= $datetime) // Бонус еще не доступен
		{
			$color = 'default';
		}
		// Бонус доступен и НЕ потрачен
		elseif ($this->datetime <= $datetime && $this->expired >= $datetime && floatval($value))
		{
			$color = 'palegreen';
		}
		// Бонус доступен и потрачен
		else
		{
			$color = 'pink';
		}

		Core_Html_Entity::factory('Span')
			->class('label label-' . $color)
			->value($value)
			->execute();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function shop_discountcard_bonus_type_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		ob_start();

		$path = $oAdmin_Form_Controller->getPath();

		$oCore_Html_Entity_Dropdownlist = new Core_Html_Entity_Dropdownlist();

		$additionalParams = Core_Str::escapeJavascriptVariable(
			str_replace(array('"'), array('&quot;'), $oAdmin_Form_Controller->additionalParams)
		);

		Core_Html_Entity::factory('Span')
			->class('padding-left-10')
			->add(
				$oCore_Html_Entity_Dropdownlist
					->value($this->shop_discountcard_bonus_type_id)
					->options(Shop_Discountcard_Bonus_Type_Controller_Edit::getDropdownlistOptions())
					->onchange("$.adminLoad({path: '{$path}', additionalParams: '{$additionalParams}', action: 'apply', post: { 'hostcms[checked][0][{$this->id}]': 0, apply_check_0_{$this->id}_fv_{$oAdmin_Form_Field->id}: $(this).find('li[selected]').prop('id') }, windowId: '{$oAdmin_Form_Controller->getWindowId()}'});")
					->data('change-context', 'true')
				)
			->execute();

		return ob_get_clean();
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function orderBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->shop_order_id)
		{
			$oShop_Order = $this->Shop_Order;

			return '<a href="/admin/shop/order/index.php?hostcms[action]=edit&hostcms[checked][0][' . $oShop_Order->id . ']=1&shop_id=' . $oShop_Order->Shop->id . '" onclick="$.adminLoad({path: \'/admin/shop/order/index.php\', action: \'edit\', operation: \'\', additionalParams: \'hostcms[checked][0][' . $oShop_Order->id . ']=1&shop_id=' . $oShop_Order->Shop->id . '\', windowId: \'id_content\'}); return false">' . htmlspecialchars($oShop_Order->invoice) . '</a>';
		}
	}

	/**
	 * Change active
	 */
	public function changeActive()
	{
		$this->active = 1 - $this->active;
		return $this->save();
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event shop_discountcard_bonus.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Shop_Discountcard_Bonus_Transactions->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_discountcard_bonus.onBeforeGetRelatedSite
	 * @hostcms-event shop_discountcard_bonus.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Discountcard->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}