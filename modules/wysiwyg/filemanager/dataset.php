<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Wysiwyg Filemanager Dataset.
 *
 * @package HostCMS
 * @subpackage Wysiwyg
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Wysiwyg_Filemanager_Dataset extends Admin_Form_Dataset
{
	/**
	 * Items count
	 * @var int
	 */
	protected $_count = NULL;

	/**
	 * Directory path
	 * @var string
	 */
	protected $_path = NULL;

	/**
	 * File type
	 * @var string
	 */
	protected $_type = NULL;

	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'Wysiwyg_Filemanager_File';

	/**
	 * Constructor.
	 * @param int $type type
	 */
	public function __construct($type)
	{
		$this->_type = $type;
		$this->_path = rtrim(CMS_FOLDER, DIRECTORY_SEPARATOR);
	}

	/**
	 * Set model name
	 * @param string $modelName
	 * @return self
	 */
	public function modelName($modelName)
	{
		$this->_modelName = $modelName;
		return $this;
	}

	/**
	 * Set path
	 * @param string $path path
	 */
	public function setPath($path)
	{
		$this->_path = rtrim($path, DIRECTORY_SEPARATOR);
		return $this;
	}

	/**
	 * Get count of finded objects
	 * @return int
	 */
	public function getCount()
	{
		if (!count($this->_count))
		{
			$this->_loadFiles();
			$this->_count = count($this->_objects);
		}

		return $this->_count;
	}

	/**
	 * Dataset objects list
	 * @var array|NULL
	 */
	protected $_objects = array();

	/**
	 * Load objects
	 * @return array
	 */
	public function load()
	{
		return array_slice($this->_objects, $this->_offset, $this->_limit);
	}

	/**
	 * Load data
	 * @return self
	 */
	protected function _loadFiles()
	{
		$this->_objects = array();

		// Default sorting field
		$sortField = 'name';
		$sortDirection = 'ASC';

		foreach ($this->_conditions as $condition)
		{
			foreach ($condition as $operator => $args)
			{
				if ($operator == 'orderBy')
				{
					$sortField = $args[0];
					$sortDirection = strtoupper($args[1]);
				}
			}
		}

		// Директория существует
		if (is_dir($this->_path) && !is_link($this->_path))
		{
			if ($dh = opendir($this->_path))
			{
				$i = 0;
				// Читаем файлы и каталоги из данного каталога
				while (($file = readdir($dh)) !== FALSE)
				{
					$filePath = $this->_path . DIRECTORY_SEPARATOR . $file;
					if ($file != '.' && $file != '..' && file_exists($filePath))
					{
						if (filetype($filePath) == $this->_type)
						{
							$isDir = filetype($filePath) == 'dir';
							$stat = stat($filePath);

							$Wysiwyg_Filemanager_File = $this->_newObject();
							$Wysiwyg_Filemanager_File->setSortField($sortField);
							$Wysiwyg_Filemanager_File->path = $this->_path;
							//$Wysiwyg_Filemanager_File->name = mb_convert_encoding($file, 'UTF-8');
							$Wysiwyg_Filemanager_File->name = @iconv(mb_detect_encoding($file, mb_detect_order(), TRUE), "UTF-8", $file);
							$Wysiwyg_Filemanager_File->datetime = Core_Date::timestamp2sql($stat[9]);
							$Wysiwyg_Filemanager_File->type = $this->_type;
							$Wysiwyg_Filemanager_File->size = $isDir
								? '<DIR>'
								: number_format($stat['size'], 0, '.', ' ');
							$Wysiwyg_Filemanager_File->mode = Core_File::getFilePerms($filePath, TRUE);
							$Wysiwyg_Filemanager_File->hash = sha1($Wysiwyg_Filemanager_File->name);

							$this->_objects[$Wysiwyg_Filemanager_File->hash] = $Wysiwyg_Filemanager_File;
						}
					}
				}
				closedir($dh);

				uasort($this->_objects, array($this, $sortDirection == 'ASC' ? '_sortAsc' : '_sortDesc'));
			}
		}
		else
		{
			Core_Message::show(Core::_('Wysiwyg_Filemanager.dir_does_not_exist', $this->_path), 'error');
		}

		return $this;
	}

	/**
	 * Get new object
	 * @return object
	 */
	protected function _newObject()
	{
		$modelName = $this->_modelName;
		return new $modelName();
	}

	/**
	 * Get entity
	 * @return object
	 */
	public function getEntity()
	{
		return $this->_newObject();
	}

	/**
	 * Get object
	 * @param int $primaryKey ID
	 * @return object
	 */
	public function getObject($primaryKey)
	{
		!count($this->_objects) && $this->_loadFiles();

		if (isset($this->_objects[$primaryKey]))
		{
			return $this->_objects[$primaryKey];
		}
		elseif ($primaryKey == 0)
		{
			return $this->_newObject();
		}

		return NULL;
	}
}