<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Tag_Informationsystem_Item_Model
 *
 * @package HostCMS
 * @subpackage Tag
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Tag_Informationsystem_Item_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'tag' => array(),
		'informationsystem_item' => array(),
		'site' => array()
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
			$this->_preloadValues['site_id'] = defined('CURRENT_SITE') ? CURRENT_SITE : 0;
		}
	}

	/**
	 * Get tag by ID of information system item
	 * @param int $informationsystem_item_id 
	 * @return mixed
	 */
	public function getByInformationsystemItem($informationsystem_item_id)
	{
		$this->queryBuilder()
			//->clear()
			->where('informationsystem_item_id', '=', $informationsystem_item_id)
			->limit(1);

		$aTag_Informationsystem_Items = $this->findAll();

		if (isset($aTag_Informationsystem_Items[0]))
		{
			return $aTag_Informationsystem_Items[0];
		}

		return NULL;
	}
}