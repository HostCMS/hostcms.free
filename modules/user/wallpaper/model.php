<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Wallpaper_Model
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Wallpaper_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'user_wallpapers.sorting' => 'ASC'
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'sorting' => 0
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
	 * Get user wallpaper href
	 * @return string
	 */
	public function getHref()
	{
		return 'upload/user/wallpaper/';
	}

	/**
	 * Get user wallpaper path
	 * @return string
	 */
	public function getPath()
	{
		return CMS_FOLDER . $this->getHref();
	}

	/**
	 * Get large image file path
	 * @return string|NULL
	 */
	public function getLargeImageFilePath()
	{
		return $this->image_large != ''
			? $this->getPath() . $this->image_large
			: NULL;
	}

	/*
	 * Get large image href
	 * @return string
	 */
	public function getLargeImageFileHref()
	{
		return '/' . $this->getHref() . $this->image_large;
	}

	/**
	 * Get small image file path
	 * @return string|NULL
	 */
	public function getSmallImageFilePath()
	{
		return $this->image_small != ''
			? $this->getPath() . $this->image_small
			: NULL;
	}

	/*
	 * Get small image href
	 * @return string
	 */
	public function getSmallImageFileHref()
	{
		return '/' . $this->getHref() . $this->image_small;
	}

	/**
	 * Delete image file
	 * @return self
	 */
	public function deleteImageFile()
	{
		try
		{
			is_file($this->getLargeImageFilePath()) && Core_File::delete($this->getLargeImageFilePath());
		} catch (Exception $e) {}

		try
		{
			is_file($this->getSmallImageFilePath()) && Core_File::delete($this->getSmallImageFilePath());
		} catch (Exception $e) {}

		$this->image_large = '';
		$this->image_small = '';

		$this->save();

		return $this;
	}

	/**
	 * Backend
	 * @return self
	 */
	public function smallImage()
	{
		$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')
			->class('fm_preview');

		if (strlen($this->image_small))
		{
			$oCore_Html_Entity_Div
				->add(
					Core::factory('Core_Html_Entity_Img')
						->src($this->getSmallImageFileHref())
				);
		}

		$oCore_Html_Entity_Div->execute();
	}
}