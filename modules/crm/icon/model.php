<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Icon_Model
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Crm_Icon_Model extends Core_Entity
{
	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'value';

	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'crm_project' => array(),
		'dms_class' => array()
	);

	/**
	 * Get icon
	 * @return string
	 */
	public function getIcon()
	{
		$value = $this->value !== ''
			? $this->value
			: 'fa-solid fa-tasks';

		return '<i class="' . htmlspecialchars((string) $value) . ' fa-fw"></i>';
	}

	public function getRandom()
	{
		$oCrm_Icons = Core_Entity::factory('Crm_Icon');
		$oCrm_Icons->queryBuilder()
			->limit(1)
			->clearOrderBy()
			->orderBy('RAND()');

		$aCrm_Icons = $oCrm_Icons->findAll(FALSE);

		return isset($aCrm_Icons[0])
			? $aCrm_Icons[0]
			: NULL;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event crm_icon.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		Core_QueryBuilder::update('crm_projects')
			->set('crm_icon_id', 0)
			->where('crm_icon_id', '=', $this->id)
			->execute();

		return parent::delete($primaryKey);
	}
}