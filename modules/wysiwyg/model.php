<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Wysiwyg_Model
 *
 * @package HostCMS
 * @subpackage Wysiwyg
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Wysiwyg_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
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
		}
	}


	/**
	 * Switch default status
	 * @return self
	 */
	public function changeDefaultStatus()
	{
		// $this->save();

		$oWysiwygs = Core_Entity::factory('Wysiwyg');
		$oWysiwygs
			->queryBuilder()
			->where('wysiwygs.default', '=', 1);

		$aWysiwygs = $oWysiwygs->findAll();

		foreach ($aWysiwygs as $oWysiwyg)
		{
			$oWysiwyg->default = 0;
			$oWysiwyg->update();
		}

		$this->default = 1;

		return $this->save();
	}

	/**
	 * Get default wysiwyg
	 * @param boolean $bCache cache mode
	 * @return self|NULL
	 */
	public function getDefault($bCache = TRUE)
	{
		$this->queryBuilder()
			//->clear()
			->where('wysiwygs.default', '=', 1)
			->limit(1);

		$aWysiwygs = $this->findAll($bCache);

		return isset($aWysiwygs[0])
			? $aWysiwygs[0]
			: NULL;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event wysiwyg.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		if ($this->driver != '')
		{
			$dir = CMS_FOLDER . 'modules/wysiwyg/driver/' . $this->driver . '/';

			if (Core_File::isDir($dir))
			{
				Core_File::deleteDir($dir);
			}
		}

		return parent::delete($primaryKey);
	}
}