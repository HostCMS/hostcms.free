<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Filemanager.
 *
 * @package HostCMS
 * @subpackage Wysiwyg
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Wysiwyg_Filemanager_File extends Core_Empty_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'wysiwyg_filemanager';

	/**
	 * Backend property
	 * @var string
	 */
	public $hash = NULL;

	/**
	 * Backend property
	 * @var string
	 */
	public $name = NULL;

	/**
	 * Backend property
	 * @var string
	 */
	public $nameEncoded = NULL;

	/**
	 * Backend property
	 * @var string
	 */
	public $type = NULL;

	/**
	 * Backend property
	 * @var string
	 */
	public $datetime = NULL;

	/**
	 * Backend property
	 * @var string
	 */
	public $size = NULL;

	/**
	 * Backend property
	 * @var string
	 */
	public $mode = NULL;

	/**
	 * Backend property
	 * @var string
	 */
	public $owner = NULL;

	/**
	 * Backend property
	 * @var string
	 */
	public $path = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $user_id = 0;

	/**
	 * Backend property
	 * @var int
	 */
	public $download = 0;

	/**
	 * Set preload values from _preloadValues
	 * @return Core_ORM
	 */
	protected function _setPreloadValues()
	{
		return $this;
	}

	/**
	 * Sorting field
	 * @var string
	 */
	protected $_sortField = NULL;

	/**
	 * Set sorting field
	 * @param string $sortField
	 */
	public function setSortField($sortField)
	{
		$this->_sortField = $sortField;
	}

	/**
	 * Get sorting field
	 * @return string
	 */
	public function getSortField()
	{
		return $this->_sortField;
	}

	/**
	 * Get primary key name
	 * @return string
	 */
	public function getPrimaryKeyName()
	{
		return 'hash';
	}

	/**
	 * Get full file path for current file
	 * @return string
	 */
	protected function _getFullPath()
	{
		return $this->path . DIRECTORY_SEPARATOR . /*Core_File::convertfileNameToLocalEncoding(*/$this->name/*)*/;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 */
	public function delete($primaryKey = NULL)
	{
		$filePath = $this->_getFullPath();

		$this->type == 'dir'
			? Core_File::deleteDir($filePath)
			// file or link
			: Core_File::delete($filePath);

		return $this;
	}

	/**
	 * Extract files
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 */
	public function extract($primaryKey = NULL)
	{
		$filePath = $this->_getFullPath();
		$dirname = dirname($filePath);

		if ($this->_availableExtractCoreTar())
		{
			$Core_Tar = new Core_Tar($filePath);
			$Core_Tar->addReplace('admin/', Core::$mainConfig['backend'] . '/');
			$Core_Tar->extractModify($dirname, $dirname);
		}
		elseif ($this->_availableExtractCoreZip())
		{
			$Core_Zip = new Core_Zip($filePath);
			$Core_Zip->addReplace('admin/', Core::$mainConfig['backend'] . '/');
			$Core_Zip->extract($dirname);
		}

		return $this;
	}

	/**
	 * Callback function
	 * @return string
	 */
	public function adminDownload()
	{
		if ($this->type == 'file')
		{
			ob_start();
			Core_Html_Entity::factory('A')
				->add(
					Core_Html_Entity::factory('I')
						->class('fa fa-download palegreen')
				)
				->href(Admin_Form_Controller::correctBackendPath("/{admin}/filemanager/index.php") . "?hostcms[action]=download&cdir=" . rawurlencode(Core_File::pathCorrection(Core_Array::getRequest('cdir'))) . "&dir=" . rawurlencode(Core_File::pathCorrection(Core_Array::getRequest('dir'))) ."&hostcms[checked][1][{$this->hash}]=1")
				->target('_blank')
				->execute();
			return ob_get_clean();
		}
	}

	/**
	 * Download file
	 */
	public function download()
	{
		Core_Session::close();

		$filePath = $this->_getFullPath();

		Core_File::download($filePath, $this->name, array('content_disposition' => 'attachment'));
		exit();
	}

	/**
	 * Get table columns
	 * @return array
	 */
	public function getTableColumns()
	{
		return array_flip(
			array('hash', 'name', 'nameEncoded', 'type', 'datetime', 'size', 'mode', 'owner', 'user_id')
		);
	}

	/**
	 * Module config
	 */
	static public $aConfig = NULL;

	/**
	 * Constructor.
	 * @param string $primaryKey
	 */
	public function __construct($primaryKey = NULL)
	{
		parent::__construct($primaryKey);

		if (is_null(self::$aConfig))
		{
			self::$aConfig = Core_Config::instance()->get('wysiwyg_filemanager_config', array()) + array(
				'thumbnails' => TRUE,
			);
		}
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->type == 'link')
		{
			Core_Html_Entity::factory('I')
				->class('fa-solid fa-link fa-small')
				->execute();
		}
	}

	/**
	 * Get file image
	 */
	public function image()
	{
		$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div')
			->class('fm-preview-' . $this->type);

		$oChild = Core_Html_Entity::factory('I')
			->class('fa fa-file-text-o');

		if ($this->type == 'file')
		{
			$aExt = array('JPG', 'JPEG', 'GIF', 'PNG');

			$oChild = Core_Html_Entity::factory('I')
				->class(Core_File::getIcon($this->name));

			try
			{
				if (self::$aConfig['thumbnails'] && Core_File::isValidExtension($this->name, $aExt))
				{
					$sImgContent = NULL;

					$filePath = $this->_getFullPath();

					$maxWidth = $maxHeight = 48;

					$picsize = Core_Image::instance()->getImageSize($filePath);

					if ($picsize)
					{
						$sourceX = $picsize['width'];
						$sourceY = $picsize['height'];

						/* Если размеры исходного файла больше максимальных, тогда масштабируем*/
						if (($sourceX > $maxWidth || $sourceY > $maxHeight))
						{
							$destX = $sourceX;
							$destY = $sourceY;

							// Масштабируем сначала по X
							if ($destX > $maxWidth && $maxWidth != 0)
							{
								$coefficient = $sourceY / $sourceX;
								$destX = $maxWidth;
								$destY = ceil($maxWidth * $coefficient);
							}

							// Масштабируем по Y
							if ($destY > $maxHeight && $maxHeight != 0)
							{
								$coefficient = $sourceX / $sourceY;
								$destX = ceil($maxHeight * $coefficient);
								$destY = $maxHeight;
							}

							$targetResourceStep1 = imagecreatetruecolor($destX, $destY);

							$iImagetype = Core_Image::instance()->exifImagetype($filePath);

							//$ext = Core_File::getExtension($this->name);
							if ($iImagetype == IMAGETYPE_JPEG)
							{
								$sourceResource = @imagecreatefromjpeg($filePath);

								if ($sourceResource)
								{
									// Изменяем размер оригинальной картинки и копируем в созданую картинку
									imagecopyresampled($targetResourceStep1, $sourceResource, 0, 0, 0, 0, $destX, $destY, $sourceX, $sourceY);

									ob_start();
									imagejpeg($targetResourceStep1);
									$sImgContent = ob_get_clean();

									PHP_VERSION_ID < 80500 && imagedestroy($sourceResource);
									unset($sourceResource);
								}
							}
							elseif ($iImagetype == IMAGETYPE_PNG)
							{
								$sourceResource = @imagecreatefrompng($filePath);

								if ($sourceResource)
								{
									imagealphablending($targetResourceStep1, FALSE);
									imagesavealpha($targetResourceStep1, TRUE);

									imagecopyresized($targetResourceStep1, $sourceResource, 0, 0, 0, 0, $destX, $destY, $sourceX, $sourceY);

									ob_start();
									imagepng($targetResourceStep1);
									$sImgContent = ob_get_clean();

									PHP_VERSION_ID < 80500 && imagedestroy($sourceResource);
									unset($sourceResource);
								}
							}
							elseif ($iImagetype == IMAGETYPE_GIF)
							{
								$sourceResource = @imagecreatefromgif ($filePath);

								if ($sourceResource)
								{
									Core_Image::instance('gd')->setTransparency($targetResourceStep1, $sourceResource);

									imagecopyresampled($targetResourceStep1, $sourceResource, 0, 0, 0, 0, $destX, $destY, $sourceX, $sourceY);

									ob_start();
									imagegif($targetResourceStep1);
									$sImgContent = ob_get_clean();

									PHP_VERSION_ID < 80500 && imagedestroy($sourceResource);
									unset($sourceResource);
								}
							}
							else
							{
								PHP_VERSION_ID < 80500 && imagedestroy($targetResourceStep1);
								unset($targetResourceStep1);

								return FALSE;
							}

							PHP_VERSION_ID < 80500 && imagedestroy($targetResourceStep1);
							unset($targetResourceStep1);
						}
						else
						{
							$sImgContent = Core_File::read($filePath);
						}

						$oChild = Core_Html_Entity::factory('Img')
							->src(
								"data:" . Core_Mime::getFileMime($filePath) .
								";base64," . base64_encode($sImgContent)
							);

						//$icon_file = "data:" . Core_Mime::getFileMime($filePath) . ";base64," . base64_encode($sImgContent);
					}
				}
			}
			catch (Exception $e) { }
		}
		else
		{
			$oChild = Core_Html_Entity::factory('I')->class('fa-regular fa-folder-open wysiwyg-folder');
		}

		$oCore_Html_Entity_Div
			->add($oChild)
			->execute();
	}

	/**
	 * Get file datetime
	 * @return string
	 */
	public function datetime()
	{
		return Core_Date::sql2datetime($this->datetime);
	}

	/**
	 * Available Core_Tar
	 * @return bool
	 */
	protected function _availableExtractCoreTar()
	{
		if ((substr($this->name, -7) === '.tar.gz' || substr($this->name, -4) === '.tgz') && extension_loaded('zlib'))
		{
			return TRUE;
		}

		$ext = Core_File::getExtension($this->name);

		return $ext == 'tar'
			|| $ext == 'bz2' && extension_loaded('bz2');
	}

	/**
	 * Available Core_Zip
	 * @return bool
	 */
	protected function _availableExtractCoreZip()
	{
		$ext = Core_File::getExtension($this->name);

		return $ext == 'zip';
	}

	/**
	 * Check user access to admin form action
	 * @param string $actionName admin form action name
	 * @param User_Model $oUser user object
	 * @return bool
	 */
	public function checkBackendAccess($actionName, $oUser)
	{
		switch ($actionName)
		{
			case 'extract':
				return $this->_availableExtractCoreTar() || $this->_availableExtractCoreZip();
			break;
			case 'edit':
				$ext = Core_File::getExtension($this->name);

				$aForbiddenExtensions = array(
					'gif', 'png', 'jpg', 'jpeg', 'bmp', 'webp',
					'pdf', 'odt', 'xls', 'xslx', 'doc', 'docx', 'ppt', 'pptx', 'rtf',
					'zip', 'gz', 'tar', 'bz2', 'rar',
					'exe', 'sig'
				);

				if (in_array(mb_strtolower($ext), $aForbiddenExtensions))
				{
					return FALSE;
				}
			break;
		}

		return TRUE;
	}
}