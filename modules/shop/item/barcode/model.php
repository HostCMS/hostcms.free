<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Barcode_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Item_Barcode_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_item' => array(),
	);

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'value';

	/**
	 * Set barcode type
	 * @return int
	 */
	public function setType()
	{
		$this->type = 0;

		$bNumeric = is_numeric($this->value);

		if ($bNumeric)
		{
			$lenght = strlen($this->value);

			// EAN-8
			if ($lenght == 8)
			{
				if (Core_Barcode::isEAN8($this->value))
				{
					$this->type = 1;
				}
			}
			// EAN-13
			elseif ($lenght == 13)
			{
				if (Core_Barcode::isEAN13($this->value))
				{
					$this->type = 2;
				}
			}
			// ITF-14
			elseif ($lenght == 14)
			{
				if (Core_Barcode::isITF14($this->value))
				{
					$this->type = 3;
				}
			}
		}
		else
		{
			// EAN-128/GS1-128
			if (Core_Barcode::isEAN128($this->value))
			{
				$this->type = 4;
			}
			// CODE39
			elseif (Core_Barcode::isCODE39($this->value))
			{
				$this->type = 5;
			}
		}

		return $this;
	}

	/*
	 * Check EAN-8 barcode
	 * @param string $value barcode
	 * @return bool
	 */
	public function isEAN8($value)
	{
		return Core_Barcode::isEAN8($value);
	}

	/*
	 * Check EAN-13 barcode
	 * @param string $value barcode
	 * @return bool
	 */
	public function isEAN13($value)
	{
		return Core_Barcode::isEAN13($value);
	}

	/*
	 * Check ITF-14 barcode
	 * @param string $value barcode
	 * @return bool
	 */
	public function isITF14($value)
	{
		return Core_Barcode::isITF14($value);
	}

	/*
	 * Check ITF-14 barcode
	 * @param string $value barcode
	 * @return bool
	 */
	public function isCODE39($value)
	{
		return Core_Barcode::isCODE39($value);
	}

	/*
	 * Check EAN-128/GS1-128 barcode
	 * @param string $value barcode
	 * @return bool
	 */
	public function isEAN128($value)
	{
		return Core_Barcode::isEAN128($value);
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_item_barcode.onBeforeGetRelatedSite
	 * @hostcms-event shop_item_barcode.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Item->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}