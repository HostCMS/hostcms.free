<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Site_Favicon_Model
 *
 * @package HostCMS
 * @subpackage Site
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Site_Favicon_Model extends Core_Entity
{
	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'filename';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'site' => array(),
		'user' => array()
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
			$this->_preloadValues['site_id'] = defined('CURRENT_SITE') ? CURRENT_SITE : 0;
		}
	}

	/**
	 * Get favicon path
	 */
	protected function _getFaviconPath()
	{
		return CMS_FOLDER . $this->Site->uploaddir . "favicon";
	}

	/**
	 * Get favicon file path
	 * @return string
	 */
	public function getFaviconPath()
	{
		return $this->_getFaviconPath() . '/' . $this->filename;
	}

	/**
	 * Get favicon file href
	 * @return string
	 */
	public function getFaviconHref()
	{
		return '/' . $this->Site->uploaddir . "favicon/" . $this->filename;
	}

	/**
	 * Specify favicon file
	 * @param string $fileSourcePath source file path
	 * @return self
	 */
	public function saveFavicon($name, $fileSourcePath)
	{
		$this->deleteFavicon();

		$this->type = Core_Mime::getFileMime($name);
		$this->filename = '';
		$this->save();

		$this->filename = 'favicon' . $this->site_id . '-' . $this->id . '.' . Core_File::getExtension($name);
		$this->save();

		$faviconPath = $this->_getFaviconPath();

		if (!Core_File::isDir($faviconPath))
		{
			try
			{
				Core_File::mkdir($faviconPath);
			} catch (Exception $e) {}
		}

		Core_File::upload($fileSourcePath, $this->getFaviconPath());

		return $this;
	}

	/**
	 * Delete favicon file
	 * @return self
	 * @hostcms-event site_favicon.onAfterDeleteFavicon
	 */
	public function deleteFavicon()
	{
		if ($this->filename != '')
		{
			try
			{
				Core_File::delete($this->getFaviconPath());
			} catch (Exception $e) {}

			Core_Event::notify($this->_modelName . '.onAfterDeleteFavicon', $this);

			$this->filename = '';
			$this->type = '';
			$this->save();
		}

		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event site_favicon.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->deleteFavicon();

		return parent::delete($primaryKey);
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event site_favicon.onBeforeGetRelatedSite
	 * @hostcms-event site_favicon.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}