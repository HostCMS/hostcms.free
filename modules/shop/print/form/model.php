<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Print_Form_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Print_Form_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'user' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'sorting' => 0,
		'active' => 1,
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shop_print_forms.sorting' => 'ASC'
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
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
		}
	}

	/**
	 * Get the path to the print form
	 * @return string
	 */
	public function getPrintFormFilePath()
	{
		return CMS_FOLDER . "hostcmsfiles/shop/print/handler" . intval($this->id) . ".php";
	}

	/**
	 * Get content of the print form file
	 * @return string|NULL
	 */
	public function loadPrintFormFile()
	{
		$path = $this->getPrintFormFilePath();
		return is_file($path) ? Core_File::read($path) : NULL;
	}

	/**
	 * Specify content of the print form file
	 * @param string $content content
	 * @return self
	 */
	public function savePrintFormFile($content)
	{
		$this->save();

		$sPrintFormFilePath = $this->getPrintFormFilePath();
		Core_File::mkdir(dirname($sPrintFormFilePath), CHMOD, TRUE);
		Core_File::write($sPrintFormFilePath, trim($content));

		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event shop_print_form.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		try
		{
			Core_File::delete($this->getPrintFormFilePath());
		} catch (Exception $e) {}

		return parent::delete($primaryKey);
	}

	/**
	 * Change status of activity for print form
	 * @return self
	 */
	public function changeStatus()
	{
		$this->active = 1 - $this->active;
		return $this->save();
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 */
	public function copy()
	{
		$newObject = parent::copy();

		try
		{
			Core_File::copy($this->getPrintFormFilePath(), $newObject->getPrintFormFilePath());
		} catch (Exception $e) {}

		return $newObject;
	}
}