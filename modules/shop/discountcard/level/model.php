<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Discountcard_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
 class Shop_Discountcard_Level_Model extends Core_Entity
{
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_discountcard' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'user' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shop_discountcard_levels.level' => 'ASC',
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
		}
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function nameBackend($oAdmin_Form_Field)
	{
		return '<i class="fa fa-circle" style="margin-right: 5px; color: ' . ($this->color ? htmlspecialchars($this->color) : '#aebec4') . '"></i> '
			. '<span class="editable" id="apply_check_0_' . $this->id . '_fv_' . $oAdmin_Form_Field->id . '">' . htmlspecialchars($this->name) . '</span>';
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function amountBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return $this->Shop->Shop_Currency->formatWithCurrency($this->amount);
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function roundBackend()
	{
		return $this->round
			? '<i class="fa fa-check-circle-o green">'
			: '<i class="fa fa-times-circle-o red">';
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function discountBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return Core_Str::hideZeros($this->discount) . '%';
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function levelBackend()
	{
		ob_start();

		Core_Html_Entity::factory('Div')
			->class('text-align-center')
			->add(
				Core_Html_Entity::factory('Span')
					->class('badge badge-hostcms badge-square')
					->value($this->level)
			)
			->execute();

		return ob_get_clean();
	}

	/**
	 * Backend badge
	 */
	public function nameBadge()
	{
		$this->apply_max_discount && Core_Html_Entity::factory('Span')
			->class('label label-yellow label-sm')
			->value('MAX')
			->execute();
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
     * @hostcms-event shop_discountcard.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		// ????
		$this->Shop_Discountcards->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_discountcard_level.onBeforeGetRelatedSite
	 * @hostcms-event shop_discountcard_level.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}