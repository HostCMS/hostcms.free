<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Property_Value_File_Model
 *
 * @package HostCMS
 * @subpackage Property
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Property_Value_File_Model extends Property_Value_Abstract
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'property_value_file';

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'file' => '',
		'file_name' => '',
		'file_description' => '',
		'file_small' => '',
		'file_small_name' => '',
		'file_small_description' => ''
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'entity_id',
		'file',
		'file_small',
		'size',
		'width',
		'height',
		'small_size',
		'small_width',
		'small_height'
	);

	/**
	 * File directory
	 * @var string
	 */
	protected $_dir = NULL;

	/**
	 * Set file directory
	 * @param string $dir directory path
	 * @return self
	 */
	public function setDir($dir)
	{
		$this->_dir = $dir;
		return $this;
	}

	/**
	 * File href
	 * @var string
	 */
	protected $_href = NULL;

	/**
	 * Set file href
	 * @param string $href href
	 * @return self
	 */
	public function setHref($href)
	{
		$this->_href = $href;
		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event property_value_file.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		if ($this->_dir)
		{
			$this
				->deleteLargeFile()
				->deleteSmallFile();
		}

		return parent::delete($primaryKey);
	}

	/**
	 * Get large file path
	 * @return string
	 */
	public function getLargeFilePath()
	{
		return $this->_dir . $this->file;
	}

	/**
	 * Get large file href
	 * @return string
	 */
	public function getLargeFileHref()
	{
		return $this->_href . $this->file;
	}

	/**
	 * Delete large file
	 * @return self
	 * @hostcms-event property_value_file.onAfterDeleteLargeFile
	 */
	public function deleteLargeFile()
	{
		$path = $this->getLargeFilePath();

		if ($this->file != '')
		{
			try
			{
				Core_File::delete($path);
			} catch (Exception $e) {}

			Core_Event::notify($this->_modelName . '.onAfterDeleteLargeFile', $this);

			$this->file = '';
			$this->file_name = '';
			//$this->file_description = '';
			$this->save();
		}

		return $this;
	}

	/**
	 * Get small file path
	 * @return string
	 */
	public function getSmallFilePath()
	{
		return $this->_dir . $this->file_small;
	}

	/**
	 * Get small file href
	 * @return string
	 */
	public function getSmallFileHref()
	{
		return $this->_href . $this->file_small;
	}

	/**
	 * Delete small file
	 * @return self
	 * @hostcms-event property_value_file.onAfterDeleteSmallFile
	 */
	public function deleteSmallFile()
	{
		$path = $this->getSmallFilePath();

		if ($this->file_small != '')
		{
			try
			{
				Core_File::delete($path);
			} catch (Exception $e) {}

			Core_Event::notify($this->_modelName . '.onAfterDeleteSmallFile', $this);

			$this->file_small = '';
			$this->file_small_name = '';
			//$this->file_small_description = '';
			$this->save();
		}

		return $this;
	}

	/**
	 * Prepare entity and children entities
	 * @return self
	 */
	protected function _prepareData()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		if (!is_null($this->_href))
		{
			$oFile_Entity = Core::factory('Core_Xml_Entity')
				->name('file')
				->value(rawurlencode($this->file));

			if ($this->file)
			{
				if ($this->size == 0)
				{
					$path = $this->getLargeFilePath();

					if (Core_File::isFile($path) && is_readable($path))
					{
						$this->size = filesize($path);

						if ($this->size > 12 && Core_Image::instance()->exifImagetype($path))
						{
							$picsize = @getimagesize($path);
							if ($picsize)
							{
								$this->width = $picsize[0];
								$this->height = $picsize[1];
							}
						}

						$this->save();
					}
				}

				$oFile_Entity
					->addAttribute('size', $this->size)
					->addAttribute('width', $this->width)
					->addAttribute('height', $this->height);
			}

			$oFile_Small_Entity = Core::factory('Core_Xml_Entity')
				->name('file_small')
				->value(rawurlencode($this->file_small));

			if ($this->file_small)
			{
				if ($this->small_size == 0)
				{
					$path = $this->getSmallFilePath();

					if (Core_File::isFile($path) && is_readable($path))
					{
						$this->small_size = filesize($path);

						if ($this->small_size > 12 && Core_Image::instance()->exifImagetype($path))
						{
							$picsize = @getimagesize($path);
							if ($picsize)
							{
								$this->small_width = $picsize[0];
								$this->small_height = $picsize[1];
							}
						}

						$this->save();
					}
				}

				$oFile_Small_Entity
					->addAttribute('size', $this->small_size)
					->addAttribute('width', $this->small_width)
					->addAttribute('height', $this->small_height);
			}

			$this
				->addEntity($oFile_Entity)
				->addEntity($oFile_Small_Entity);

			if (Core::moduleIsActive('cdn'))
			{
				if ($this->file !== '')
				{
					$link = Core::moduleIsActive('cdn')
						? Cdn_Controller::link($this->getLargeFileHref())
						: NULL;

					$this->_isTagAvailable('cdn_file')
						&& $this->addXmlTag('cdn_file', !is_null($link) ? $link : $this->getLargeFileHref());
				}

				if ($this->file_small !== '')
				{
					$link = Core::moduleIsActive('cdn')
						? Cdn_Controller::link($this->getSmallFileHref())
						: NULL;

					$this->_isTagAvailable('cdn_file_small')
						&& $this->addXmlTag('cdn_file_small', !is_null($link) ? $link : $this->getSmallFileHref());
				}
			}
		}

		$this
			->addXmlTag('property_dir_id', $this->Property->property_dir_id)
			->addXmlTag('tag_name', $this->Property->tag_name);

		return $this;
	}
}