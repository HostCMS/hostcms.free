<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Updates.
 *
 * @package HostCMS
 * @subpackage Update
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Update_Entity extends Core_Empty_Entity
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
	public $img = 0;

	/**
	 * Backend property
	 * @var int
	 */
	public $name = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $number = NULL;

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
				'<i class="fa-solid fa-circle-plus success"></i>',
				'<i class="fa-solid fa-circle-check warning"></i>',
				'<i class="fa-solid fa-circle-exclamation danger"></i>',
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
		if (Core_File::isDir($wysiwyg_path) && !Core_File::isLink($wysiwyg_path))
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
			!Core_File::isDir($update_dir) && Core_File::mkdir($update_dir);

			$error = (int)$oXml->error;

			// 5 - истек период поддержки
			if (!$error || $error == 5)
			{
				Core_Database::instance()->query('SET SESSION wait_timeout = 600');

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
					!Core_File::isDir($current_update_dir) && Core_File::mkdir($current_update_dir);

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

							$content = $Core_Http->getDecompressedBody();

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

						if (!$error_update)
						{
							// SQL
							$sqlContent = (string)$module->update_list_sql;
							// XML convert "\r\n" to "\r"
							$aTmpUpdateItem['sql'] = html_entity_decode($sqlContent, ENT_COMPAT, 'UTF-8');

							// File
							$fileContent = (string)$module->update_list_php;

							if ($fileContent != '')
							{
								$filename = $current_update_dir . '/' . $current_update_list_id . '.php';

								Core_File::write($filename, html_entity_decode($fileContent, ENT_COMPAT, 'UTF-8'));

								$aTmpUpdateItem['file'] = $filename;
							}
						}

						$aUpdateItems[] = $aTmpUpdateItem;
					}


					// Check permitions
					$aNotWritable = array();
					foreach ($aUpdateItems as $aTmpUpdateItem)
					{
						if (isset($aTmpUpdateItem['tar']))
						{
							$Core_Tar = new Core_Tar($aTmpUpdateItem['tar'], 'gz');
							$aListContents = $Core_Tar->listContent();

							foreach ($aListContents as $aListContent)
							{
								if (isset($aListContent['filename']))
								{
									$filename = CMS_FOLDER . $aListContent['filename'];

									if (file_exists($filename) && !is_writable($filename))
									{
										$aNotWritable[] = $aListContent['filename'];
									}
								}
							}
						}
					}

					if (!count($aNotWritable))
					{
						// Extract and execute after load
						foreach ($aUpdateItems as $aTmpUpdateItem)
						{
							if (isset($aTmpUpdateItem['tar']))
							{
								$Core_Tar = new Core_Tar($aTmpUpdateItem['tar'], 'gz');
								$Core_Tar->addReplace('admin/', Core::$mainConfig['backend'] . '/');

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

							if (isset($aTmpUpdateItem['sql']) && strlen($aTmpUpdateItem['sql']))
							{
								Core_Log::instance()->clear()
									->status(Core_Log::$MESSAGE)
									->write(Core::_('Update.msg_execute_sql'));

								Sql_Controller::instance()->executeByString($aTmpUpdateItem['sql']);
							}

							// Clear Core_ORM_ColumnCache, Core_ORM_RelationCache
							Core_ORM::clearColumnCache();
							Core_ORM::clearRelationModelCache();

							method_exists('Core_Cache', 'opcacheReset') && Core_Cache::opcacheReset();
							
							if (function_exists('opcache_reset'))
							{
								opcache_reset();
							}

							if (isset($aTmpUpdateItem['file']))
							{
								Core_Log::instance()->clear()
									->status(Core_Log::$MESSAGE)
									->write(Core::_('Update.msg_execute_file'));

								include($aTmpUpdateItem['file']);
							}
						}

						// Clear Core_ORM_ColumnCache, Core_ORM_RelationCache
						Core_ORM::clearColumnCache();
						Core_ORM::clearRelationModelCache();

						// Rebuild Shortcodes
						if (Core::moduleIsActive('shortcode'))
						{
							Shortcode_Controller::instance()->rebuild();
						}

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
					else
					{
						Core_Message::show(Core::_('Update.not_writable', implode(', ', $aNotWritable)), 'error');
					}

					// Удаляем папку с файлами
					Core_File::isDir($current_update_dir) && Core_File::deleteDir($current_update_dir);
					// Удаляем XML обновления
					$update_file = Update_Controller::instance()->getFilePath();
					Core_File::isFile($update_file) && Core_File::delete($update_file);
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
		//$this->loadUpdates();

		Update_Controller::instance()->deleteUpdateFile();

		return NULL;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function numberBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$this->beta && Core_Html_Entity::factory('Span')
			->class('badge badge-darkorange')
			->value('β')
			->execute();
	}
}