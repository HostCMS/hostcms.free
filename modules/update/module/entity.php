<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Updates.
 *
 * @package HostCMS
 * @subpackage Update
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Update_Module_Entity extends Core_Entity
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
	public $img = 1;

	/**
	 * Backend property
	 * @var string
	 */
	public $name = NULL;

	/**
	 * Backend property
	 * @var string
	 */
	public $path = NULL;

	/**
	 * Backend property
	 * @var string
	 */
	public $description = NULL;

	/**
	 * Backend property
	 * @var string
	 */
	public $number = NULL;

	/**
	 * Backend property
	 * @var string
	 */
	public $file = NULL;
	
	/**
	 * Backend property
	 * @var string
	 */
	public $beta = NULL;

	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'update_module';

	/**
	 * Load columns list
	 * @return self
	 */
	protected function _loadColumns()
	{
		return $this;
	}

	/**
	 * Get primary key name
	 * @return string
	 */
	public function getPrimaryKeyName()
	{
		return 'id';
	}

	/**
	 * Table columns
	 * @var array
	 */
	protected $_tableColums = array();

	/**
	 * Set table columns
	 * @param array $tableColums columns
	 * @return self
	 */
	public function setTableColums($tableColums)
	{
		$this->_tableColums = $tableColums;
		return $this;
	}

	/**
	 * Get table colums
	 * @return array
	 */
	public function getTableColumns()
	{
		return $this->_tableColums;
	}

	/**
	 * Install updates
	 * @hostcms-event Update_Module_Entity.onBeforeInstall
	 * @hostcms-event Update_Module_Entity.onAfterInstall
	 */
	public function install()
	{
		// Устанавливаем время выполнения
		(!defined('DENY_INI_SET') || !DENY_INI_SET)
			&& function_exists('set_time_limit')
			&& ini_get('safe_mode') != 1
			&& @set_time_limit(3600);

		Core_Event::notify(get_class($this) . '.onBeforeInstall', $this);

		if ($this->file != '')
		{
			$oMarket_Controller = Market_Controller::instance();
			$oMarket_Controller->setMarketOptions();
			$oMarket_Controller->tmpDir = CMS_FOLDER . 'hostcmsfiles/tmp/install' . DIRECTORY_SEPARATOR . $this->id;

			is_dir($oMarket_Controller->tmpDir)
				&& Core_File::deleteDir($oMarket_Controller->tmpDir);

			// Создаем директорию снова
			Core_File::mkdir($oMarket_Controller->tmpDir, CHMOD, TRUE);

			$Core_Http = $oMarket_Controller->getModuleFile($this->file);

			// Сохраняем tar.gz
			$source_file = $oMarket_Controller->tmpDir . DIRECTORY_SEPARATOR . 'tmpfile.tar.gz';
			Core_File::write($source_file, $Core_Http->getDecompressedBody());

			// Распаковываем файлы
			$Core_Tar = new Core_Tar($source_file);
			if (!$Core_Tar->extractModify($oMarket_Controller->tmpDir, $oMarket_Controller->tmpDir))
			{
				// Возникла ошибка распаковки
				throw new Core_Exception(
					Core::_('Update.update_files_error')
				);
			}

			// Копируем файлы из ./files/ в папку системы
			$sFilesDir = $oMarket_Controller->tmpDir . DIRECTORY_SEPARATOR . 'files';
			if (is_dir($sFilesDir))
			{
				Core_File::copyDir($sFilesDir, CMS_FOLDER);
			}

			// Размещаем SQL из описания обновления
			$sSqlFilename = $oMarket_Controller->tmpDir . '/update.sql';
			if (is_file($sSqlFilename))
			{
				$sSqlCode = Core_File::read($sSqlFilename);
				Sql_Controller::instance()->execute($sSqlCode);
			}

			// Размещаем PHP из описания обновления
			$sPhpFilename = $oMarket_Controller->tmpDir . '/update.php';
			if (is_file($sPhpFilename))
			{
				include($sPhpFilename);
			}

			clearstatcache();

			// Удаляем папку с файлами в случае с успешной установкой
			is_dir($oMarket_Controller->tmpDir) && Core_File::deleteDir($oMarket_Controller->tmpDir);

			$message = Core::_('Update.install_success', $this->name);

			Core_Log::instance()->clear()
				->status(Core_Log::$SUCCESS)
				->write($message);

			Core_Message::show($message);
		}

		Core_Event::notify(get_class($this) . '.onAfterInstall', $this);

		// Load new updates list
		// $this->loadUpdates();

		Update_Controller::instance()->deleteUpdateFile();

		return NULL;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$this->beta && Core::factory('Core_Html_Entity_Span')
			->class('badge badge-darkorange')
			->value('β')
			->execute();
	}
}