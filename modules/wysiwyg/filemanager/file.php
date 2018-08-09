<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Filemanager.
 *
 * @package HostCMS
 * @subpackage Wysiwyg
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Wysiwyg_Filemanager_File extends Core_Entity
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
	 * Load columns list
	 * @return self
	 */
	protected function _loadColumns()
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

		$this->type == 'file'
			? Core_File::delete($filePath)
			: Core_File::deleteDir($filePath);

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
			$oCore_Html_Entity_Img = Core::factory('Core_Html_Entity_A')
				->add(
					Core::factory('Core_Html_Entity_I')
						->class('fa fa-download')
					/*Core::factory('Core_Html_Entity_Img')
						->src('/admin/images/disk.gif')*/
				)
				->href("/admin/filemanager/index.php?hostcms[action]=download&cdir=" . rawurlencode(Core_File::pathCorrection(Core_Array::getRequest('cdir'))) . "&dir=" . rawurlencode(Core_File::pathCorrection(Core_Array::getRequest('dir'))) ."&hostcms[checked][1][{$this->hash}]=1")
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
	public function getTableColums()
	{
		return array_flip(
			array('hash', 'name', 'type', 'datetime', 'size', 'mode', 'user_id')
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
	 * Get file image
	 */
	public function image()
	{
		$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')
			->class('fm_preview');

		$oChild = Core::factory('Core_Html_Entity_I')
			->class('fa fa-file-text-o');

		if ($this->type == 'file')
		{
			$aExt = array('JPG', 'JPEG', 'GIF', 'PNG');

			// Ассоциированные иконки
			$ext = Core_File::getExtension($this->name);

			if (isset(Core::$mainConfig['fileIcons'][$ext]))
			{
				$oChild = Core::factory('Core_Html_Entity_Img')
					->src('/admin/images/icons/' . Core::$mainConfig['fileIcons'][$ext]);
			}

			/*$icon_file = '/admin/images/icons/' . (
				isset(Core::$mainConfig['fileIcons'][$ext])
					? Core::$mainConfig['fileIcons'][$ext]
					: 'file.gif'
			);*/

			try
			{
				if (self::$aConfig['thumbnails'] && Core_File::isValidExtension($this->name, $aExt))
				{
					$sImgContent = NULL;

					$filePath = $this->_getFullPath();

					/// ------
					$maxWidth = 48;
					$maxHeight = 48;

					$picsize = Core_Image::getImageSize($filePath);

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

							// в $destX и $destY теперь хранятся размеры оригинального изображения после уменьшения
							// от них рассчитываем размеры для обрезания на втором шаге
							$destX_step2 = $maxWidth;
							// Масштабируем сначала по X
							if ($destX > $maxWidth && $maxWidth != 0)
							{
								// Позиции, с которых необходимо вырезать
								$src_x = ceil(($destX - $maxWidth) / 2);
							}
							else
							{
								$src_x = 0;
							}

							// Масштабируем по Y
							if ($destY > $maxHeight && $maxHeight != 0)
							{
								$destY_step2 = $maxHeight;
								$destX_step2 = $destX;

								// Позиции, с которых необходимо вырезать
								$src_y = ceil(($destY - $maxHeight) / 2);
							}
							else
							{
								$destY_step2 = $destY;
								$src_y = 0;
							}

							$targetResourceStep1 = imagecreatetruecolor($destX, $destY);

							$iImagetype = Core_Image::instance()->exifImagetype($filePath);

							//$ext = Core_File::getExtension($this->name);
							if ($iImagetype == IMAGETYPE_JPEG)
							{
								$sourceResource = imagecreatefromjpeg($filePath);

								if ($sourceResource)
								{
									// Изменяем размер оригинальной картинки и копируем в созданую картинку
									imagecopyresampled($targetResourceStep1, $sourceResource, 0, 0, 0, 0, $destX, $destY, $sourceX, $sourceY);

									ob_start();
									imagejpeg($targetResourceStep1);
									$sImgContent = ob_get_clean();

									imagedestroy($sourceResource);
								}
							}
							elseif ($iImagetype == IMAGETYPE_PNG)
							{
								$sourceResource = imagecreatefrompng($filePath);

								if ($sourceResource)
								{
									imagealphablending($targetResourceStep1, FALSE);
									imagesavealpha($targetResourceStep1, TRUE);

									imagecopyresized($targetResourceStep1, $sourceResource, 0, 0, 0, 0, $destX, $destY, $sourceX, $sourceY);

									ob_start();
									imagepng($targetResourceStep1);
									$sImgContent = ob_get_clean();

									imagedestroy($sourceResource);
								}
							}
							elseif ($iImagetype == IMAGETYPE_GIF)
							{
								$sourceResource = imagecreatefromgif ($filePath);

								if ($sourceResource)
								{
									Core_Image_Gd::setTransparency($targetResourceStep1, $sourceResource);

									imagecopyresampled($targetResourceStep1, $sourceResource, 0, 0, 0, 0, $destX, $destY, $sourceX, $sourceY);

									ob_start();
									imagegif ($targetResourceStep1);
									$sImgContent = ob_get_clean();

									imagedestroy($sourceResource);
								}
							}
							else
							{
								imagedestroy($targetResourceStep1);

								return FALSE;
							}

							imagedestroy($targetResourceStep1);
						}
						else
						{
							$sImgContent = Core_File::read($filePath);
						}

						$oChild = Core::factory('Core_Html_Entity_Img')
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
			$oChild = Core::factory('Core_Html_Entity_I')
					->class('fa fa-folder-open-o');

			/*$icon_file = $this->name == '..'
				? '/admin/images/top_point.gif'
				: '/admin/images/folder.gif';*/
		}

		/*?><div class=""><img src="<?php echo $icon_file?>" /></div><?php*/
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
}