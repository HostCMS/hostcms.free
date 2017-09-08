<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Updates.
 *
 * @package HostCMS
 * @subpackage Update
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Update_Entity extends Core_Entity
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
	public $name = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $description = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $beta = NULL;

	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'update';

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
	public function getTableColums()
	{
		return $this->_tableColums;
	}

	/**
	 * Processing update description
	 * @return string
	 */
	public function description()
	{
		return nl2br(
			str_replace(array(
				'[+]',
				'[*]',
				'[!]',
			), array(
				'<img src="/admin/images/add.gif" class="img_line" />',
				'<img src="/admin/images/edited.gif" class="img_line" />',
				'<img src="/admin/images/error.gif" class="img_line" />',
			), htmlspecialchars($this->description))
		);
	}

	/**
	 * Load updates
	 * @return self
	 */
	public function loadUpdates()
	{
		Update_Controller::instance()
			->setUpdateOptions()
			->getUpdates();

		return $this;
	}

	/**
	 * Install updates
	 * @hostcms-event Update_Entity.onBeforeInstall
	 * @hostcms-event Update_Entity.onAfterInstall
	 */
	public function install()
	{
		// Устанавливаем время выполнения
		(!defined('DENY_INI_SET') || !DENY_INI_SET)
			&& function_exists('set_time_limit')
			&& ini_get('safe_mode') != 1
			&& @set_time_limit(3600);

		// Удаляем gz-файлы
		/*$wysiwyg_path = CMS_FOLDER . 'admin/wysiwyg/';

		// Удаление gz-файлов с кэшем виз. редактора
		if (is_dir($wysiwyg_path) && !is_link($wysiwyg_path))
		{
			clearstatcache();

			if ($dh = opendir($wysiwyg_path))
			{
				// Читаем файлы и каталоги
				while (($file = readdir($dh)) !== FALSE)
				{
					if ($file != '.' && $file != '..')
					{
						$file_path = $wysiwyg_path . $file;

						if (file_exists($file_path) && filetype($file_path) == "file")
						{
							// Если файл архива
							preg_match('/tiny_mce_[0-9a-f]*\.gz$/', $file_path) && Core_File::delete($file_path);
						}
					}
				}
				closedir($dh);
			}
		}*/

		Core_Event::notify(get_class($this) . '.onBeforeInstall', $this);

		$data = Update_Controller::instance()
			->setUpdateOptions()
			->getUpdate($this->id);

		if (empty($data))
		{
			throw new Core_Exception(Core::_('Update.server_return_empty_answer'));
		}

		// обрабатываем ошибки, которые, возможно, вернул сервер
		$oXml = @simplexml_load_string($data);

		if (is_object($oXml))
		{
			$update_dir = Update_Controller::instance()->getPath();
			!is_dir($update_dir) && Core_File::mkdir($update_dir);

			$error = (int)$oXml->error;

			// 5 - истек период поддержки
			if (!$error || $error == 5)
			{
				foreach ($oXml->update as $value)
				{
					$current_update_id = (int)$value->attributes()->id;

					$current_update_name = (string)$value->update_name;

					Core_Log::instance()->clear()
						->status(Core_Log::$MESSAGE)
						->write(Core::_('Update.msg_update_required', $current_update_name));

					// по умолчанию ошибок обновления нет
					$error_update = FALSE;

					$current_update_dir = $update_dir . '/' . $current_update_id;
					!is_dir($current_update_dir) && Core_File::mkdir($current_update_dir);

					$aUpdateItems = array();

					$aModules = $value->xpath('modules/module');
					foreach ($aModules as $key => $module)
					{
						$aTmpUpdateItem = array();

						$current_update_list_id = (int)$module->attributes()->id;

						Core_Log::instance()->clear()
							->status(Core_Log::$MESSAGE)
							->write(Core::_('Update.msg_installing_package', $current_update_list_id));

						$update_list_file = (string)$module->update_list_file;
						if ($update_list_file != '')
						{
							$Core_Http = Update_Controller::instance()
								->setUpdateOptions()
								->getUpdateFile(
									html_entity_decode($update_list_file, ENT_COMPAT, 'UTF-8')
								);

							$sHeaders = $Core_Http->getHeaders();

							// Получаем оригинальное имя файла, учитывем пробел после символа "="
							$first_point = mb_strpos($sHeaders, '=', mb_strpos($sHeaders, 'Content-Disposition:')) + 1;
							$second_point = mb_strpos($sHeaders, ';', $first_point);

							$original_filename = mb_substr($sHeaders, $first_point, $second_point - $first_point);

							// Убираем кавычки
							$original_filename = trim(str_replace('"', '', $original_filename));

							if (empty($original_filename))
							{
								$original_filename = $key . 'tar.gz';
							}

							$source_file = $current_update_dir . '/' . $current_update_list_id . '_' . $original_filename;

							$content = $Core_Http->getBody();

							if (strlen($content) > 100)
							{
								Core_File::write($source_file, $content);
							}
							else
							{
								throw new Core_Exception(Core::_('Update.update_files_error'));
							}

							$aTmpUpdateItem['tar'] = $source_file;
						}
						else
						{
							Core_Log::instance()->clear()
								->status(Core_Log::$MESSAGE)
								->write('Empty update_list_file');
						}

						$update_list_sql = (string)$module->update_list_sql;
						// Размещаем файлы обновления в директорию
						if (!$error_update)
						{
							$filename = $current_update_dir . '/' . $current_update_list_id . '.sql';

							Core_File::write($filename, html_entity_decode($update_list_sql, ENT_COMPAT, 'UTF-8'));

							$aTmpUpdateItem['sql'] = $filename;
						}

						$update_list_php = (string)$module->update_list_php;
						if (!$error_update)
						{
							$filename = $current_update_dir . '/' . $current_update_list_id . '.php';

							Core_File::write($filename, html_entity_decode($update_list_php, ENT_COMPAT, 'UTF-8'));

							$aTmpUpdateItem['file'] = $filename;
						}

						$aUpdateItems[] = $aTmpUpdateItem;
					}

					// Extract and execute after load
					foreach ($aUpdateItems as $aTmpUpdateItem)
					{
						if (isset($aTmpUpdateItem['tar']))
						{
							$Core_Tar = new Core_Tar($aTmpUpdateItem['tar']);

							Core_Log::instance()->clear()
								->status(Core_Log::$MESSAGE)
								->write(Core::_('Update.msg_unpack_package', basename($aTmpUpdateItem['tar'])));

							// Распаковываем файлы
							if (!$Core_Tar->extractModify(CMS_FOLDER, CMS_FOLDER))
							{
								$error_update = TRUE;

								$message = Core::_('Update.update_files_error');

								Core_Log::instance()->clear()
									->status(Core_Log::$MESSAGE)
									->write($message);

								// Возникла ошибка распаковки
								Core_Message::show($message, 'error');

								try {
									Core_File::copy($aTmpUpdateItem['tar'], tempnam(CMS_FOLDER . TMP_DIR, 'update'));
								} catch (Exception $e) {}
							}
						}

						if (isset($aTmpUpdateItem['sql']))
						{
							$sql_code = Core_File::read($aTmpUpdateItem['sql']);

							// Если версия MySQL меньше 4.1.0
							/*if (version_compare($kernel->GetDBVersion(), '4.1.0', "<"))
							{
								$search = array('CHARACTER SET cp1251', 'COLLATE cp1251_general_ci', 'ENGINE=MyISAM', 'DEFAULT CHARSET=cp1251',
								'CHARACTER SET utf8', 'COLLATE utf8_general_ci', 'ENGINE=MyISAM', 'DEFAULT CHARSET=utf8');

								$sql_code = str_replace($search, ' ', $sql_code);
							}*/

							Sql_Controller::instance()->execute($sql_code);
						}

						// Clear Core_ORM_ColumnCache
						Core_ORM::clearColumnCache();

						if (isset($aTmpUpdateItem['file']))
						{
							include($aTmpUpdateItem['file']);
						}
					}

					// Удаляем папку с файлами
					is_dir($current_update_dir) && Core_File::deleteDir($current_update_dir);
					// Удаляем XML обновления
					$update_file = Update_Controller::instance()->getFilePath();
					is_file($update_file) && Core_File::delete($update_file);

					// Clear Core_ORM_ColumnCache
					Core_ORM::clearColumnCache();

					/*$aCore_Orm_Config = Core::$config->get('core_orm') + array(
						'cache' => 'memory',
						'columnCache' => 'memory'
					);
					$oCore_Cache = Core_Cache::instance($aCore_Orm_Config['columnCache']);
					$oCore_Cache->deleteAll('Core_ORM_ColumnCache');*/

					// Если не было ошибок
					if (!$error_update)
					{
						$oHOSTCMS_UPDATE_NUMBER = Core_Entity::factory('Constant')->getByName('HOSTCMS_UPDATE_NUMBER');
						!is_null($oHOSTCMS_UPDATE_NUMBER) && $oHOSTCMS_UPDATE_NUMBER->value($current_update_id)->save();

						$message = Core::_('Update.install_success', $this->name);

						Core_Log::instance()->clear()
							->status(Core_Log::$SUCCESS)
							->write($message);

						Core_Message::show($message);
					}
				}
			}

			if ($error > 0)
			{
				Core_Message::show(Core::_('Update.server_error_respond_' . $error, $this->name), 'error');
			}
		}
		else
		{
			Core_Message::show(Core::_('Update.server_error_xml'), 'error');
		}

		Core_Event::notify(get_class($this) . '.onAfterInstall', $this);

		// Load new updates list
		$this->loadUpdates();

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
			->class('badge badge-hostcms badge-square')
			->value('β')
			->execute();
	}
}