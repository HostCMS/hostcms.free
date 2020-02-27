<?php

/**
 * Install.
 *
 * @package HostCMS
 * @subpackage Install
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
return array(
	'changeLanguage' => 'Выберите язык:',
	'constant_on' => 'Включено',
	'constant_off' => 'Отключено',
	'undefined' => 'Не определено',
	'not_installed' => 'Не установлен',

	'supported' => 'Поддерживается',
	'unsupported' => 'Не поддерживается',

	'success' => 'Успешно',
	'error' => 'Ошибка',

	'start' => 'Запуск',
	'back' => 'Назад',
	'next' => 'Далее',

	'yes' => 'Да',
	'seconds' => 'сек.',
	'megabytes' => 'M',

	'title' => 'Установка системы управления сайтом HostCMS',
	'menu_0' => 'Выбор языка',
	'menu_1' => 'Лицензионное соглашение',
	'menu_2' => 'Проверка параметров',
	'menu_3' => 'Опции установки',
	'menu_4' => 'Результат конфигурирования',
	'menu_5' => 'Данные лицензии',
	'menu_6' => 'Выбор макета сайта',
	'menu_7' => 'Настройки макета',
	'menu_8' => 'Завершение установки',
	
	'license-caption' => 'Регистрационные данные',
	'login' => 'Логин в личном кабинете',
	'login-placeholder' => 'Логин в личном кабинете на сайте www.hostcms.ru',
	'license' => 'Номер лицензии',
	'pin' => 'PIN-код',
	
	'step_0' => 'Выбор языка',
	'step_1' => 'Шаг 1: Лицензионное соглашение',
	'step_2' => 'Шаг 2: Проверка соответствия параметров сервера',
	'step_3' => 'Шаг 3: Параметры',
	'step_4' => 'Шаг 4: Результат предварительного конфигурирования',
	'step_5' => 'Шаг 5: Лицензионные данные',
	'step_6' => 'Шаг 6: Выбор макета сайта',
	'step_7' => 'Шаг 7: Настройки макета',
	'step_8' => 'Шаг 8: Завершение установки',

	'step_5_warning1' => 'Номер лицензии и PIN-код можно узнать в <a href="http://www.hostcms.ru/users/" target="_blank">личном кабинете</a> на нашем сайте в разделе <a href="http://www.hostcms.ru/users/licence/" target="_blank">Лицензии</a>.',
	'step_5_warning2' => 'Вы можете не заполнять лицензионные данные на этом этапе, просто нажмите "Далее".',
	'step_5_warning3' => 'У только что зарегистрированных пользователей список лицензий будет пуст, вы можете <a href="http://www.hostcms.ru/shop/" target="_blank">приобрести лицензию</a> <i class="fa fa-external-link"></i> или самостоятельно <a href="http://www.hostcms.ru/users/licence/add-free/" target="_blank">создать лицензию</a> <i class="fa fa-external-link"></i> для бесплатной редакции &laquo;Халява&raquo;, нажав на кнопку &laquo;+ HostCMS.Халява&raquo;.',
	
	'write_error' => 'Ошибка записи в файл %s.',
	'template_data_information' => 'Внесенные данные будут использованы в макете.',
	'allowed_extension' => 'Разрешенные расширения файла: %s',
	'max_file_size' => 'Максимальный размер файла: %s x %s',
	'empty_settings' => 'Макет не имеет настроек.',
	'file_not_found' => 'Файл %s не найден.',
	'template_install_success' => 'Установка макета выполнена.',
	'template_files_copy_error' => 'Ошибка копирования файлов макета!',
	'file_copy_error' => 'Ошибка копирования файла в %s!',
	'file_disabled_extension' => 'Файл для %s имеет запрещенное расширение!',

	'templates_dont_exist' => 'Недоступен список шаблонов. Система управления будет установлена с шаблоном по умолчанию.',

	'license_agreement_error' => 'Для продолжения установки Вам необходимо принять условия лицензионного соглашения!',
	'license_agreement' => 'Я согласен с условиями лицензионного договора.',

	'table_field_param' => 'Параметр',
	'table_field_need' => 'Требуется',
	'table_field_thereis' => 'Имеется',
	'table_field_value' => 'Значение',

	'table_field_php_version' => 'Версия PHP:',
	'table_field_mbstring' => 'Multibyte String:',
	'table_field_json' => 'JSON:',
	'table_field_simplexml' => 'SimpleXML:',
	'table_field_iconv' => 'Iconv:',
	'table_field_gd_version' => 'Версия GD:',
	'table_field_pcre_version' => 'Версия PCRE:',
	'table_field_mysql' => 'MySQL:',
	'table_field_maximum_upload_data_size' => 'Максимальный размер загружаемых данных:',
	'table_field_maximum_execution_time' => 'Максимальное время исполнения:',
	'table_field_disc_space' => 'Дисковое пространство:',
	'table_field_ram_space' => 'Объем памяти:',
	'table_field_safe_mode' => 'Защищённый режим PHP:',
	'table_field_register_globals' => 'Глобальные переменные:',
	'table_field_magic_quotes_gpc' => 'Магические кавычки:',
	'table_field_xslt_support' => 'Поддержка XSLT:',

	'parameter_corresponds' => 'Параметр соответствует.',
	'parameter_not_corresponds_but_it_is_safe' => 'Несоответствие, не влияющее на функционирование системы.',
	'parameter_not_corresponds' => 'Параметр не соответствует.',

	'access_parameter' => 'Параметры доступа',
	'file_access' => 'Права доступа к файлам',
	'directory_access' => 'Права доступа к директориям',
	'example' => 'например, %s',
	'database_params' => 'Параметры базы данных',
	'mysql_server' => 'MySQL cервер',
	'database_login' => 'Логин для базы данных',
	'database_pass' => 'Пароль для базы данных',
	'database_mysql' => 'Название базы данных',
	'database_storage_engine' => 'Тип таблиц',
	'database_driver' => 'Драйвер MySQL',
	'create_database' => 'Создать базу данных',
	'create_database_flag' => 'Не устанавливайте этот флажок, если база данных уже создана!',
	'clear_database' => 'Очистить базу данных',
	'clear_database_caution' => 'При очистке базы данных все данные из нее будут удалены!',

	'action' => 'Действие',
	'result' => 'Результат',
	'comment' => 'Комментарий',
	
	'empty_color_scheme' => 'Макет не имеет цветовых схем. Нажмите <strong>"Далее"</strong>.',

	'store_database_params' => 'Запись параметров БД',
	'not_enough_rights_error' => 'Ошибка записи файла <b>%s</b>. Установите необходимые права доступа для директории.',
	'database_connection' => 'Соединение с базой данных',
	'database_connection_check_params' => 'Проверьте правильность данных для соединения с БД.',
	'database_creation' => 'Создание базы данных',
	'attention_message' => 'У пользователя БД, с помощью которого происходит соединение, должно быть достаточно прав для создания БД. На большинстве виртуальных хостингов таких прав у пользователей БД нет. В таком случае рекомендуется создать базу данных из панели управления хостингом и не устанавливать галочку "Создать базу данных".',
	'attention_message2' => '<p>В случае повторной инсталляции рекомендуется производить установку в новую базу данных, в противном случае все данные в БД будут потеряны.</p><p>Для повторной установки системы управления сайтом HostCMS нажмите кнопку <strong>"Далее"</strong>, для начала работы нажмите кнопку <strong>"Запуск"</strong>.</p><p>При невозможности автоматически удалить инсталлятор, для продолжения работы удалите вручную с сайта директорию <b>/install/</b>.</p>',
	'attention_message3' => '<p>Для продолжения установки соединитесь с сервером по протоколу FTP и <strong>удалите файл install.php</strong>, размещенный в корне сайта.</p>',

	'database_selection' => 'Выбор базы данных',
	'database_selection_access_denied' => 'Пользователь не имеет права на доступ к указанной БД или БД не существует.',
	'database_clearing' => 'Очистка базы данных',
	'sql_dump_loading' => 'Загрузка и выполнение дампа SQL',
	'sql_dump_loading_error' => 'Ошибка. Версия MySQL %s',
	'domen_installing' => 'Установка домена',
	'lng_installing' => 'Установка языка',
	'sql_error' => 'Ошибка %s',

	'error_system_already_install' => 'Система управления HostCMS уже установлена!',
	'delete_install_file' => 'Удалите файл install.php',
	'attention_complete_install' => '<p>Для завершения установки, перехода на главную страницу сайта и удаления системы инсталляции нажмите кнопку <strong>"Запуск"</strong>.</p><p>Для перехода в раздел администрирования введите в адресную строку браузера <a href="/admin/" target="_blank">http://[ваш_сайт]/admin/</a>, предварительно заменив [ваш_сайт] на адрес сайта.</p><p>Для входа в раздел администрирования используйте: <br />Пользователь: <strong>admin</strong> <br />Пароль: <strong>admin</strong></p><p>Благодарим за выбор системы управления сайтом HostCMS!</p>',
);