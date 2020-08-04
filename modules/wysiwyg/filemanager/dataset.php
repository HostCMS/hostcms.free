<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Wysiwyg Filemanager Dataset.
 *
 * @package HostCMS
 * @subpackage Wysiwyg
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	 * @var array
	 */
	protected $_aTypes = NULL;

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
		$this->_aTypes = is_array($type) ? $type : array($type);
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
		if (!$this->_count)
		{
			/*is_null($this->_objects) && */$this->_loadFiles();
			$this->_count = count($this->_objects);
		}

		return $this->_count;
	}

	/**
	 * Dataset objects list
	 * @var array|NULL
	 */
	protected $_objects = NULL;

	/**
	 * Load objects
	 * @return array
	 */
	public function load()
	{
		return is_array($this->_objects)
			? array_slice($this->_objects, $this->_offset, $this->_limit)
			: array();
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
		if (is_dir($this->_path) /*&& !is_link($this->_path)*/)
		{
			if ($dh = opendir($this->_path))
			{
				// Читаем файлы и каталоги из данного каталога
				while (($file = readdir($dh)) !== FALSE)
				{
					$filePath = $this->_path . DIRECTORY_SEPARATOR . $file;
					if ($file != '.' && $file != '..' && file_exists($filePath))
					{
						$filetype = @filetype($filePath);

						if (in_array($filetype, $this->_aTypes))
						{
							$stat = stat($filePath);

							$Wysiwyg_Filemanager_File = $this->_newObject();
							$Wysiwyg_Filemanager_File->setSortField($sortField);
							$Wysiwyg_Filemanager_File->path = $this->_path;
							$Wysiwyg_Filemanager_File->name = @iconv(mb_detect_encoding($file, mb_detect_order(), TRUE), "UTF-8", $file);
							$Wysiwyg_Filemanager_File->datetime = Core_Date::timestamp2sql($stat[9]);
							$Wysiwyg_Filemanager_File->type = $filetype;
							$Wysiwyg_Filemanager_File->size = $filetype == 'dir'
								? '<DIR>'
								: ($filetype == 'link'
									? '<LINK>'
									: number_format($stat['size'], 0, '.', ' ')
								);
							$Wysiwyg_Filemanager_File->mode = Core_File::getFilePerms($filePath, TRUE);
							$Wysiwyg_Filemanager_File->owner = Core_File::getFileOwners($filePath);
							$Wysiwyg_Filemanager_File->hash = sha1($Wysiwyg_Filemanager_File->name);

							$bAdd = TRUE;
							foreach ($this->_conditions as $condition)
							{
								foreach ($condition as $operator => $args)
								{
									if ($operator == 'where' && $args[0] == 'name')
									{
										$value = $Wysiwyg_Filemanager_File->{$args[0]};

										if ($args[1] == 'LIKE')
										{
											if (strpos($args[2], '%') === FALSE && strpos($args[2], '_') === FALSE)
											{
												$value !== $args[2]
													&& $bAdd = FALSE;
											}
											else
											{
												$pattern = preg_quote($args[2], '/');
												$pattern = str_replace(array('%', '_'), array('.*?', '.'), $pattern);

												!preg_match('/^' . $pattern . '$/', $value)
													&& $bAdd = FALSE;
											}
										}
									}
								}
							}
							$bAdd && $this->_objects[$Wysiwyg_Filemanager_File->hash] = $Wysiwyg_Filemanager_File;
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
		if ($primaryKey === 0)
		{
			return $this->_newObject();
		}
		else
		{
			is_null($this->_objects) && $this->_loadFiles();

			if (isset($this->_objects[$primaryKey]))
			{
				return $this->_objects[$primaryKey];
			}
		}

		return NULL;
	}
}