<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Wallpaper_Model
 *
 * @package HostCMS
 * @subpackage User
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
		}
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function nameBackend()
	{
		return '<i class="fa fa-circle" style="margin-right: 5px; color: ' . ($this->color ? htmlspecialchars($this->color) : '#aebec4') . '"></i> '
			. '<span class="editable" id="apply_check_0_' . $this->id . '_fv_1135">' . htmlspecialchars($this->name) . '</span>';
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
			Core_File::isFile($this->getLargeImageFilePath()) && Core_File::delete($this->getLargeImageFilePath());
		} catch (Exception $e) {}

		try
		{
			Core_File::isFile($this->getSmallImageFilePath()) && Core_File::delete($this->getSmallImageFilePath());
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
		$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div')
			->class('fm_preview');

		if ($this->image_small != '')
		{
			$oCore_Html_Entity_Div
				->add(
					Core_Html_Entity::factory('Img')
						->src($this->getSmallImageFileHref())
				);
		}

		$oCore_Html_Entity_Div->execute();
	}
}