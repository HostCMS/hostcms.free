<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Trash.
 *
 * @package HostCMS
 * @subpackage Trash
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Trash_Entity extends Core_Empty_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $id = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $table_name = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $name = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $count = NULL;

	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'trash';

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 */
	public function delete($primaryKey = NULL)
	{
		if ($this->table_name != '')
		{
			$Trash_Table_Dataset = new Trash_Table_Dataset($this->table_name);

			$totalCount = $Trash_Table_Dataset->getCount();

			$offset = 0;
			$limit = 100;

			while ($totalCount > 0)
			{
				$iDeleted = $this->chunkDelete($offset, $limit);

				if (!$iDeleted)
				{
					break;
				}

				$offset += ($limit - $iDeleted);

				$totalCount -= $limit;
			}
		}

		return $this;
	}

	/**
	 * Delete object from database by chunk
	 * @param int $limit
	 * @return int
	 */
	public function chunkDelete($offset, $limit)
	{
		$Trash_Table_Dataset = new Trash_Table_Dataset($this->table_name);

		$aTrash_Table_Items = $Trash_Table_Dataset
			->offset($offset)
			->limit($limit)
			->clear()
			->getObjects();

		$oUser = Core_Auth::getCurrentUser();

		$iCount = 0;

		foreach ($aTrash_Table_Items as $oTrash_Table_Item)
		{
			if (!$oUser || $oUser->checkObjectAccess($oTrash_Table_Item))
			{
				$oTrash_Table_Item->delete();
				$iCount++;
			}
		}

		return $iCount;
	}

	/**
	 * Turn off deleted status
	 * @return self
	 */
	public function undelete()
	{
		if ($this->table_name != '')
		{
			$Trash_Table_Dataset = new Trash_Table_Dataset($this->table_name);

			$offset = 0;
			$limit = 100;

			$oUser = Core_Auth::getCurrentUser();

			do {
				$aTrash_Table_Items = $Trash_Table_Dataset
					->offset($offset)
					->limit($limit)
					->clear()
					->getObjects();

				foreach ($aTrash_Table_Items as $oTrash_Table_Item)
				{
					if (!$oUser || $oUser->checkObjectAccess($oTrash_Table_Item))
					{
						$oTrash_Table_Item->undelete();
					}
					else
					{
						$offset++;
					}
				}
			}
			while (count($aTrash_Table_Items));
		}

		return $this;
	}
}