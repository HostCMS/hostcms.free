<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Syntaxhighlighter_Model
 *
 * @package HostCMS
 * @subpackage Syntaxhighlighter
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Syntaxhighlighter_Model extends Core_Entity
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

		$oSyntaxhighlighters = Core_Entity::factory('Syntaxhighlighter');
		$oSyntaxhighlighters
			->queryBuilder()
			->where('syntaxhighlighters.default', '=', 1);

		$aSyntaxhighlighters = $oSyntaxhighlighters->findAll();

		foreach ($aSyntaxhighlighters as $oSyntaxhighlighter)
		{
			$oSyntaxhighlighter->default = 0;
			$oSyntaxhighlighter->update();
		}

		$this->default = 1;

		return $this->save();
	}

	/**
	 * Get default syntaxhighlighter
	 * @param boolean $bCache cache mode
	 * @return self|NULL
	 */
	public function getDefault($bCache = TRUE)
	{
		$this->queryBuilder()
			//->clear()
			->where('syntaxhighlighters.default', '=', 1)
			->limit(1);

		$aSyntaxhighlighters = $this->findAll($bCache);

		return isset($aSyntaxhighlighters[0])
			? $aSyntaxhighlighters[0]
			: NULL;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event syntaxhighlighter.onBeforeRedeclaredDelete
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
			$dir = CMS_FOLDER . 'modules/syntaxhighlighter/driver/' . $this->driver . '/';

			if (Core_File::isDir($dir))
			{
				Core_File::deleteDir($dir);
			}
		}

		return parent::delete($primaryKey);
	}
}