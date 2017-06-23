<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core command controller.
 * Основной контроллер показа при использовании совместимости с HostCMS v. 5
 * Будет полностью исключен в HostCMS v. 7
 *
 * @package HostCMS
 * @subpackage Core\Command
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Command_Controller_Hostcms5_Default extends Core_Command_Controller_Default
{
	/**
	 * Default controller action
	 * @return Core_Response
	 */
	public function showAction()
	{
		Core_Event::notify(get_class($this) . '.onBeforeShowAction', $this);

		$user_ip = Core_Array::get($_SERVER, 'REMOTE_ADDR', '127.0.0.1');
		define('USER_IP', $user_ip);

		$GLOBALS['URL_QUERY'] = isset(Core::$url['query'])
			? Core::$url['query']
			: '';

		// Определяем поизицю последнего слэша
		$last_slash = mb_strrpos(Core::$url['path'], '/');

		$url_path = Core::$url['path'];

		// Получение элементов строки -> массив, первый '/' удаляем
		$GLOBALS['URL_ARRAY'] = defined('OPTIONAL_LAST_SLASH') && OPTIONAL_LAST_SLASH
			? explode('/', mb_substr($url_path, 1))
			: explode('/', mb_substr($url_path, 1, $last_slash));

		$count = count($GLOBALS['URL_ARRAY']);

		/* Строка содержит подстроку от последнего слэша до конца строки */
		$GLOBALS['URL_LAST_STR'] = mb_substr($url_path, $last_slash);

		/*
		// IIS при запросе главной - /index.php
		// С /index.php делаем 301 редирект на главную страницу
		if ($count == 1 && mb_strtolower($last_str) == '/index.php')
		{
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: /");
			exit();
		}
		*/

		// Удаляем последний пустой элемент, если он есть и это не единственный элемент
		if (empty ($GLOBALS['URL_ARRAY'][$count - 1]) && $count != 1)
		{
			array_pop($GLOBALS['URL_ARRAY']);
		}

		// полученное количество элементов и элемент - пустой
		if ($count == 1 && (empty ($GLOBALS['URL_ARRAY'][0]) || mb_strtolower($GLOBALS['URL_ARRAY'][0]) == 'index.php'))
		{
			$GLOBALS['URL_ARRAY'][0] = '/';
		}

		// В index.php используем $SYSTEM_URL_ARRAY, т.к. пользоватлеи своими действиями могут менять $GLOBALS['URL_ARRAY']
		$SYSTEM_URL_ARRAY
			= $GLOBALS['param'] // Оставлено для совместимости
			= $GLOBALS['URL_ARRAY'];

		define('CURRENT_URL_PATH', Core::$url['path']);

		!defined('PAGE_DIR') && define('PAGE_DIR', CMS_FOLDER . 'hostcmsfiles/documents/');

		$kernel = & singleton('kernel');

		/**
		 * Break frontend generation
		 * @param array $SYSTEM_URL_ARRAY
		 * @param string $title
		 * @param string $error_text
		 * @param boolean $allow_panel
		 */
		function exit_index($SYSTEM_URL_ARRAY = array(), $title = '', $error_text = '', $allow_panel = TRUE)
		{
			if (defined('OB_START'))
			{
				$sContent = ob_get_clean();
				$oSite = Core_Entity::factory('Site', CURRENT_SITE);

				if (strtoupper($oSite->coding) != 'UTF-8')
				{
					// Delete BOM (EF BB BF)
					//$sContent = str_replace(chr(0xEF).chr(0xBB).chr(0xBF), '', $sContent);
					$sContent = @iconv('UTF-8', $oSite->coding . '//IGNORE//TRANSLIT', $sContent);
				}

				echo $sContent;
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterShowAction', $this);

		return parent::showAction();
	}

	/**
	 * Set lib params
	 */
	protected function _setLibParams()
	{
		$oCore_Page = Core_Page::instance();
		$GLOBALS['LA'] = $oCore_Page->libParams;
	}
}
