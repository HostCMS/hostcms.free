<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Wysiwyg_Controller
 *
 * @package HostCMS
 * @subpackage Wysiwyg
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Wysiwyg_Controller
{
	/**
	 * Get tmp dir href
	 * @return string
	 */
	static public function getTmpDirHref()
	{
		return UPLOADDIR . 'wysiwyg/';
	}

	/**
	 * Get Path and Href
	 * @param Core_Entity $oEntity
	 * @return array|NULL array('href' => '', 'path' => '')
	 */
	static public function getPathAndHref($oEntity)
	{
		$aMethods = array(
			'getItemPath' => 'getItemHref',
			'getGroupPath' => 'getGroupHref',
			'getDirPath' => 'getDirHref',
			'getPath' => 'getHref'
		);

		$aReturn = NULL;

		foreach ($aMethods as $pathMethod => $hrefMethod)
		{
			if (method_exists($oEntity, $pathMethod) && strpos($oEntity->$hrefMethod(), '/private/') === FALSE)
			{
				$aReturn['href'] = rtrim($oEntity->$hrefMethod(), '/') . '/';
				$aReturn['path'] = rtrim($oEntity->$pathMethod(), '\/') . DIRECTORY_SEPARATOR;

				break;
			}
		}

		return $aReturn;
	}

	/**
	 * Upload images
	 * @param array $aValues
	 * @param Core_Entity $oEntity
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string|NULL
	 */
	static public function uploadImages($aValues, $oEntity, $oAdmin_Form_Controller)
	{
		if (isset($aValues['wysiwyg_images']) && is_array($aValues['wysiwyg_images']))
		{
			clearstatcache();

			$aConform = array();
			foreach ($aValues['wysiwyg_images'] as $filepath)
			{
				$realFilepath = Core_File::pathCorrection($filepath);

				// Перемещаем файл из директории Wysiwyg_Controller::getTmpDirHref()
				if (strpos($realFilepath, Core_File::pathCorrection('/' . Wysiwyg_Controller::getTmpDirHref())) === 0)
				{
					$aTmp = Wysiwyg_Controller::getPathAndHref($oEntity);
					if (is_array($aTmp))
					{
						$newHref = $aTmp['href'];
						$newPath = $aTmp['path'];
					}
					else
					{
						$newHref = Wysiwyg_Controller::getTmpDirHref();
						$newPath = CMS_FOLDER . ltrim($newHref, DIRECTORY_SEPARATOR);
					}

					try {
						if (method_exists($oEntity, 'createDir'))
						{
							$oEntity->createDir();
						}
						else
						{
							Core_File::mkdir($newPath, TRUE);
						}

						Core_File::rename(CMS_FOLDER . ltrim($realFilepath, DIRECTORY_SEPARATOR), $newPath . basename($realFilepath));

						$aConform[$filepath] = '/' . ltrim($newHref, '/') . basename($filepath);
					} catch (Exception $e) {
						Core_Message::show($e->getMessage(), 'error');
					}
				}
			}

			if (count($aConform))
			{
				// Заменяем на новые пути в сохраненных данных
				$aFields = array('description', 'text', 'comment');
				foreach ($aFields as $fieldName)
				{
					if (isset($oEntity->$fieldName))
					{
						$oEntity->$fieldName = str_replace(array_keys($aConform), array_values($aConform), $oEntity->$fieldName);
					}
				}
				$oEntity->save();

				// Дополнительные свойства
				if (Core::moduleIsActive('property') && method_exists($oEntity, 'getPropertyValues'))
				{
					$aProperty_Values = $oEntity->getPropertyValues(FALSE);
					foreach($aProperty_Values as $oProperty_Value)
					{
						// Визуальный редактор
						if ($oProperty_Value->Property->type == 6)
						{
							$oProperty_Value->value = str_replace(array_keys($aConform), array_values($aConform), $oProperty_Value->value);
							$oProperty_Value->save();
						}
					}
				}

				// Пользовательские поля
				if (Core::moduleIsActive('field') && method_exists($oEntity, 'getFields'))
				{
					$aField_Values = $oEntity->getFields(FALSE);
					foreach($aField_Values as $oField_Value)
					{
						// Визуальный редактор
						if ($oField_Value->Field->type == 6)
						{
							$oField_Value->value = str_replace(array_keys($aConform), array_values($aConform), $oField_Value->value);
							$oField_Value->save();
						}
					}
				}

				// Обновляем новые пути в редакторах
				$windowId = $oAdmin_Form_Controller->getWindowId();
				ob_start();
				?>
				<script type="text/javascript">
				$(function(){
					// Удаляем скрытые инпуты
					var $form = $('#<?php echo $windowId?> form.adminForm').find('input[name ^= wysiwyg_images]').remove();

					var aConformities = [];
					<?php
					foreach ($aConform as $source => $destination)
					{
						?>aConformities.push({
							source: '<?php echo Core_Str::escapeJavascriptVariable($source)?>',
							destination: '<?php echo Core_Str::escapeJavascriptVariable($destination)?>'
						});<?php
					}
					?>
					wysiwyg.replaceWysiwygImages(aConformities);
				});
				</script>
				<?php

				return $oAdmin_Form_Controller->addMessage(ob_get_clean());
			}
		}

		return NULL;
	}
}