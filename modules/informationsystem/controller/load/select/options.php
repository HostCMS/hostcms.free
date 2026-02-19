<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 *
 * Контроллер загрузки значений списка инф. элементов для select доп. св-в
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Informationsystem_Controller_Load_Select_Options extends Admin_Form_Action_Controller_Type_Load_Select_Options
{
    /**
     * Get Informationsystem_Item option name
     * @param Informationsystem_Item_Model $oInformationsystem_Item
     * @return array|bool|float|int|mixed|string
     * @hostcms-event Informationsystem_Controller_Load_Select_Options.onGetOptionName
     */
	static public function getOptionName(Informationsystem_Item_Model $oInformationsystem_Item)
	{
		Core_Event::notify('Informationsystem_Controller_Load_Select_Options.onGetOptionName', $oInformationsystem_Item);

		$eventResult = Core_Event::getLastReturn();

		return !is_null($eventResult)
			? $eventResult
			: $oInformationsystem_Item->name;
	}

	/**
	 * Add value
	 * @return self
	 */
	public function addValues()
	{
		foreach ($this->_objects as $Object)
		{
			$oTmp = new stdClass();
			$oTmp->value = $Object->id;
			$oTmp->name = self::getOptionName(!$Object->shortcut_id ? $Object : $Object->Informationsystem_Item);

			$this->_values[] = $oTmp;
		}

		return $this;
	}

	/**
	 * Get count of objects
	 * @return int
     */
	protected function _getCount()
	{
		return $this->_model->getCount();
	}

	/**
	 * Find objects by $this->_model
	 * @return self
	 * @hostcms-event Informationsystem_Controller_Load_Select_Options.onBeforeFindObjects
	 */
	protected function _findObjects()
	{
		$oInformationsystem = $this->_model->Informationsystem;

		$offset = 0;
		$limit = 1000;

		$this->_model
			->queryBuilder()
			->clearOrderBy()
			->clearSelect()
			->select('id', 'shortcut_id', 'name');

		switch ($oInformationsystem->items_sorting_direction)
		{
			case 1:
				$items_sorting_direction = 'DESC';
			break;
			case 0:
			default:
				$items_sorting_direction = 'ASC';
		}

		// Определяем поле сортировки информационных элементов
		switch ($oInformationsystem->items_sorting_field)
		{
			case 1:
				$this->_model
					->queryBuilder()
					->orderBy('informationsystem_items.name', $items_sorting_direction)
					->orderBy('informationsystem_items.sorting', $items_sorting_direction);
				break;
			case 2:
				$this->_model
					->queryBuilder()
					->orderBy('informationsystem_items.sorting', $items_sorting_direction)
					->orderBy('informationsystem_items.name', $items_sorting_direction);
				break;
			case 0:
			default:
				$this->_model
					->queryBuilder()
					->orderBy('informationsystem_items.datetime', $items_sorting_direction)
					->orderBy('informationsystem_items.sorting', $items_sorting_direction);
		}

		$this->_objects = array();

		Core_Event::notify('Informationsystem_Controller_Load_Select_Options.onBeforeFindObjects', $this, array($this->_model));

		do {
			$this->_model
				->queryBuilder()
				->offset($offset)
				->limit($limit);

			$aTmpObjects = $this->_model->findAll(FALSE);

			count($aTmpObjects)
				&& $this->_objects = array_merge($this->_objects, $aTmpObjects);

			$offset += $limit;
		}
		while (count($aTmpObjects));

		return $this;
	}
}