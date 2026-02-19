<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lib_Property_Model
 *
 * @package HostCMS
 * @subpackage Lib
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Lib_Property_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'lib_property' => array('foreign_key' => 'parent_id'),
		'lib' => array(),
		'user' => array()
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'lib_property' => array('foreign_key' => 'parent_id'),
		'lib_property_list_value' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'default_value' => '',
		'sorting' => 0
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'lib_properties.sorting' => 'ASC'
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
	public function typeBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$color = Core_Str::createColor($this->type);

		return '<span class="badge badge-round badge-max-width margin-left-5" style="border-color: ' . $color . '; color: ' . Core_Str::hex2darker($color, 0.2) . '; background-color: ' . Core_Str::hex2lighter($color, 0.88) . '">'
			. Core::_('Lib_Property.lib_property_type_' . $this->type)
			. '</span>';
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function valuesBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		switch ($this->type)
		{
			case 3:
				$link = $oAdmin_Form_Field->link;
				$onclick = $oAdmin_Form_Field->onclick;
			break;
			case 10:
				$link = '/{admin}/lib/property/index.php?lib_id={lib_id}&lib_dir_id={lib_dir_id}&parent_id={id}';
				$onclick = "$.adminLoad({path: '/{admin}/lib/property/index.php',additionalParams: 'lib_id={lib_id}&lib_dir_id={lib_dir_id}&parent_id={id}', windowId: '{windowId}'}); return false";
			break;
			default:
				$link = $onclick = NULL;
		}

		if (!is_null($link) && !is_null($onclick))
		{
			$link = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $link);
			$onclick = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $onclick);

			return '<a href="' . $link . '" onclick="' . $onclick . '"><i class="fa fa-list-ul" title="' . $oAdmin_Form_Field->name . '"></i></a>';
		}

		return '—';
	}

	/**
	 * Backend badge
	 */
	public function valuesBadge()
	{
		$count = $this->Lib_Properties->getCount(FALSE);
		$count && Core_Html_Entity::factory('Span')
			->class('badge badge-ico badge-azure white')
			->value($count < 100 ? $count : '∞')
			->title($count)
			->execute();
	}

	/**
	 * Save object.
	 *
	 * @return Core_Entity
	 */
	public function save()
	{
		if ($this->type == 6)
		{
			$this->type = 0;
			$this->multivalue = 1;
		}

		return parent::save();
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event lib_property.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();

		$aLib_Property_List_Values = $this->Lib_Property_List_Values->findAll(FALSE);
		foreach ($aLib_Property_List_Values as $oLib_Property_List_Value)
		{
			$newObject->add(clone $oLib_Property_List_Value);
		}

		$aLib_Properties = $this->Lib_Properties->findAll(FALSE);
		foreach ($aLib_Properties as $oLib_Property)
		{
			$subLibProperty = $oLib_Property->copy();
			$subLibProperty->parent_id = $newObject->id;
			$subLibProperty->save();
		}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
     * @hostcms-event lib_property.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Lib_Property_List_Values->deleteAll(FALSE);

		$this->Lib_Properties->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}
}