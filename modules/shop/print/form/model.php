<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Print_Form_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
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
		return Core_File::isFile($path) ? Core_File::read($path) : NULL;
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
	 * @hostcms-event shop_print_form.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();

		try
		{
			Core_File::copy($this->getPrintFormFilePath(), $newObject->getPrintFormFilePath());
		} catch (Exception $e) {}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_print_form.onBeforeGetRelatedSite
	 * @hostcms-event shop_print_form.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}