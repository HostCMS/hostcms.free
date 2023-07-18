<?php

return array(
	'error_file_write' => 'Ошибка открытия файла для записи %s, проверьте права доступа к директории.',
	'error_resize' => 'Ошибка уменьшения малого изображения до максимально допустимого размера. Вероятной причиной является указание размера изображения меньше 0.',
	'error_upload' => 'Файл не загружен!',

	'error_log_message_stack' => "Файл: %s, строка %s",
	'error_log_message_short' => "<strong>%s:</strong> %s в файле %s (строка %s)",
	'error_log_message' => "<strong>%s:</strong> %s в файле %s (строка %s)\nСтек вызовов:\n%s",
	'error_log_add_message' => "<strong>Ошибка!</strong> Сообщение об ошибке занесено в журнал.",

	'info_cms' => 'Система управления сайтом',
	'info_cms_site_support' => 'Техническая поддержка сайта: ',
	'info_cms_site' => 'Официальный сайт: ',
	'info_cms_support' => 'Служба технической поддержки: ',
	'info_cms_sales' => 'Отдел продаж: ',

	'purchase_commercial_version' => 'Купить полную версию',

	'administration_center' => 'Центр администрирования',
	'debug_information' => 'Отладочная информация',
	'sql_queries' => 'SQL-запросы',
	'sql_statistics' => 'Время: <strong>%.3f</strong> сек. <a onclick="hQuery(\'#sql_h%d\').toggle()" class="sqlShowStack">Вызовы</a>',
	'sql_debug_backtrace' => '%s, строка %d<br/>',
	'show_xml' => 'Показать XML/XSL',
	'hide_xml' => 'Скрыть XML/XSL',
	'lock' => 'Зафиксировать панель',
	'logout' => 'Выход',

	'total_time' => 'Время выполнения: <strong>%.3f</strong> с, из них',
	'time_load_modules' => "загрузки модулей: <strong>%.3f</strong> с",
	'time_template' => "макета и страницы: <strong>%.3f</strong> с",
	'time_page' => "кода страницы: <strong>%.3f</strong> с",
	'time_page_config' => "настроек страницы: <strong>%.3f</strong> с",
	'time_database_connection' => "соединения с СУБД: <strong>%.3f</strong> с",
	'time_database_select' => "выбора БД: <strong>%.3f</strong> с",
	'time_sql_execution' => "выполнения запросов: <strong>%.3f</strong> с",
	'time_xml_execution' => "обработки XML: <strong>%.3f</strong> с",
	'time_tpl_execution' => "обработки TPL: <strong>%.3f</strong> с",
	'memory_usage' => "Использовано памяти: <strong>%.2f</strong> Мб.",
	'number_of_queries' => "Количество запросов: <strong>%d</strong>.",
	'compression' => 'Компрессия: <strong>%s</strong>.',
	'cache' => 'Кэширование: <strong>%s</strong>.',

	'cache_insert_time' => 'время записи в кэш: <strong>%.3f</strong> с',
	'cache_read_time' => 'время чтения из кэша: <strong>%.3f</strong> с',

	'cache_write_requests' => 'запросов на запись: <strong>%d</strong>',
	'cache_read_requests' => 'запросов на чтение: <strong>%d</strong>',

	'error_create_log' => "Невозможно создать log-файл <b>%s</b><br /><b>Проверьте наличие указанной директории и установите необходимые права доступа для нее.</b>",
	'error_log_level_0' => "Нейтральное",
	'error_log_level_1' => "Успешное",
	'error_log_level_2' => "Низкий уровень критичности",
	'error_log_level_3' => "Средний уровень критичности",
	'error_log_level_4' => "Наивысший уровень критичности",

	'error_log_attempt_to_access' => "Попытка доступа к модулю %s незарегистрированным пользователем",
	'error_log_several_modules' => "Ошибка! Найдено несколько экземпляров одинаковых модулей!",
	'error_log_access_was_denied' => "Доступ к модулю '%s' запрещен",
	'error_log_module_disabled' => "Модуль '%s' отключен",
	'error_log_module_access_allowed' => "Доступ к модулю \"%s\" разрешен",
	'error_log_action_access_allowed' => "Действие \"%s\" формы \"%s\"",
	'error_log_logged' => "Вход в систему управления",
	'error_log_authorization_error' => 'Неверные данные для аутентификации',
	'error_log_exit' => 'Выход из системы управления',
	'session_destroy_error' => 'Ошибка закрытия сеанса',
	'session_change_ip' => 'Попытка использования сессии %s с IP %s',

	'error_message' => "Здравствуйте!\n\n"
	. "Только что на сайте произошло событие, информация о котором представлена ниже:\n"
	. "Дата: %s\n"
	. "Событие: %s\n"
	. "Статус события: %s\n"
	. "Пользователь: %s\n"
	. "Сайт: %s\n"
	. "Страница: %s\n"
	. "User Agent: %s\n"
	. "IP-адрес: %s\n\n"
	. "Система управления сайтом HostCMS,\n"
	. "https://www.hostcms.ru",

	'E_ERROR' => "Ошибка",
	'E_WARNING' => "Предупреждение",
	'E_PARSE' => "Parse Error",
	'E_NOTICE' => "Замечание",
	'E_CORE_ERROR' => "Core Error",
	'E_CORE_WARNING' => "Core Warning",
	'E_COMPILE_ERROR' => "Compile Error",
	'E_COMPILE_WARNING' => "Compile Warning",
	'E_USER_ERROR' => "Ошибка",
	'E_USER_WARNING' => "Предупреждение",
	'E_USER_NOTICE' => "Замечание",
	'E_STRICT' => "Strict",
	'E_DEPRECATED' => "Deprecated",

	'default_form_name' => 'Основная',
	'default_event_name' => 'Просмотр',

	'widgets' => 'Виджеты',
	'addNote' => 'Добавить заметку',
	'deleteNote' => 'Удалить заметку',

	'key_not_found' => 'Не найден лицензионный ключ!',
	'getting_key' => '<div style="margin-top: 20px; overflow: auto; z-index: 9999; background-color: rgba(255, 255, 255, .8); padding: 0 20px; text-shadow: 1px 1px 0 rgba(255, 255, 255, .4)">

	<h2>Получение номера лицензии и PIN-кода <a href="https://www.hostcms.ru/documentation/introduction/licenses/licenses/" target="_blank"><i class="fa fa-external-link"></i></a></h2>

	<p>После установки системы управления необходимо зарегистрироваться на нашем сайте в разделе «<a href="https://www.hostcms.ru/users/" target="_blank">Личный кабинет</a>»</p>
	<p>После подтверждения регистрации пользователя и входа в личный кабинет, в разделе «Лицензии» доступен список выданных лицензий:</p>

	<p align="center">
	<img src="//www.hostcms.ru/images/documentation/guide/site/licenses-list.png" class="img-responsive" />
	</p>

	<p>Коммерческие пользователи могут узнать свой номер лицензии и PIN-код из таблицы в разделе «Лицензии» личного кабинета, пользователи HostCMS.Старт могут добавить новую лицензию.</p>
	<p>Узнав номер лицензии и PIN-код можно вернуться в <a href="/admin/" target="_blank">центр администрирования</a> и ввести эти данные в разделе «Система» → «Сайты» → пункт меню «Настройки» → «Регистрационные данные».</p>

	<h2>Получение ключа <a href="https://www.hostcms.ru/documentation/introduction/key/key/" target="_blank"><i class="fa fa-external-link"></i></a></h2>

	<p>Далее можно получать ключи в <a href="/admin/" target="_blank">центре администрирования</a> системы управления, перейдя в раздел «Система» → «Сайты», выбрать глобус для соответствующего сайта в столбце «Домены»:</p>

	<p align="center">
	<img src="//www.hostcms.ru/images/documentation/introduction/install/step-by-step/key/key-2.png" class="img-responsive" />
	</p>

	<p>При нажатии на пиктограмму «Ключ» система запросит ключ для выбранного домена по вашей лицензии и внесет его в список ключей сайта.</p>

	<p align="center">
	<img src="//www.hostcms.ru/images/documentation/introduction/install/step-by-step/key/key-3.png" class="img-responsive" /></p>

	<h2>Вход в центр администрирования</h2>
	<p>Перейдите в <a href="/admin/" target="_blank">центр администрирования</a>, далее действуйте по инструкции.</p>
	</div>',

	'access_forbidden_title' => 'Доступ к сайту запрещен',
	'access_forbidden' => 'Доступ к сайту запрещен. Обратитесь к администратору.',

	'extension_does_not_allow' => 'Загружать файл с расширением "%s" запрещено.',
	'delete_success' => 'Элемент удален!',
	'undelete_success' => 'Элемент восстановлен!',
	'redaction0' => 'Старт',
	'redaction1' => 'Мой сайт',
	'redaction3' => 'Малый бизнес',
	'redaction5' => 'Бизнес',
	'redaction7' => 'Корпорация',

	'byte' => 'Байт',
	'kbyte' => 'КБ',
	'mbyte' => 'МБ',
	'gbyte' => 'ГБ',

	'timePeriodSeconds' => '%s сек. назад',
	'timePeriodMinutes' => '%s мин. назад',
	'timePeriodHours' => '%s час. назад',
	'timePeriodDays' => '%s дн. назад',
	'timePeriodYesterday' => 'вчера',
	'timePeriodMonths' => '%s мес. назад',
	'timePeriodYears' => '%s г. назад',
	'timePeriodYearMonths' => '%s г. %s мес. назад',

	'shortTitleSeconds' => 'с.',
	'shortTitleSeconds_1' => 'сек.',
	'shortTitleMinutes' => 'м.',
	'shortTitleHours' => 'ч.',
	'shortTitleDays' => 'д.',
	'shortTitleYears' => 'г.',

	'now' => 'Сейчас',
	'ago' => '%1$s %2$s назад',
	'today' => 'Cегодня',
	'yesterday' => 'Вчера',
	'tomorrow' => 'Завтра',
	'estimate_date' => '%1$d %2$s',
	'estimate_date_year' => '%1$d %2$s %3$d г.',

	'time_postfix' => ' в %s',

	'month1' => 'января',
	'month2' => 'февраля',
	'month3' => 'марта',
	'month4' => 'апреля',
	'month5' => 'мая',
	'month6' => 'июня',
	'month7' => 'июля',
	'month8' => 'августа',
	'month9' => 'сентября',
	'month10' => 'октября',
	'month11' => 'ноября',
	'month12' => 'декабря',

	'capitalMonth1' => 'Январь',
	'capitalMonth2' => 'Февраль',
	'capitalMonth3' => 'Март',
	'capitalMonth4' => 'Апрель',
	'capitalMonth5' => 'Май',
	'capitalMonth6' => 'Июнь',
	'capitalMonth7' => 'Июль',
	'capitalMonth8' => 'Август',
	'capitalMonth9' => 'Сентябрь',
	'capitalMonth10' => 'Октябрь',
	'capitalMonth11' => 'Ноябрь',
	'capitalMonth12' => 'Декабрь',

	'hour_nominative' => 'час',
	'hour_genitive_singular' => 'часа',
	'hour_genitive_plural' => 'часов',
	'minute_nominative' => 'минуту',
	'minute_genitive_singular' => 'минуты',
	'minute_genitive_plural' => 'минут',

	'day' => 'День',
	'month' => 'Месяц',
	'year' => 'Год',
	'quarter' => 'Квартал',
	
	'random' => 'Случайный',
	'generateChars' => 'Символы',

	'title_no_access_to_page' => 'У Вас недостаточно прав доступа к данной странице!',
	'message_more_info' => 'За более подробной информацией обратитесь к администратору сайта.',
	'title_domain_must_be_added' => 'Необходимо добавить домен %s в список поддерживаемых системой управления сайтом HostCMS!',
	'message_domain_must_be_added' => 'Домен <b>%s</b> необходимо добавить в список поддерживаемых системой управления сайтом <b>HostCMS</b>!',
	'add_domain_instruction1' => 'Для добавления домена перейдите в <b><a href="/admin/site/index.php" target="_blank">"Раздел администрирования" → "Система" → "Сайты"</a></b>.',
	'add_domain_instruction2' => 'Выберите пиктограмму <b>"Домены"</b> для требуемого сайта. На открывшейся странице нажмите в меню <b>"Добавить"</b>.',
	'home_page_not_found' => 'Не найдена главная страница сайта',
	'message_home_page_must_be_added' => 'Вам необходимо добавить главную страницу в <b>"Раздел администрирования" → "Структура сайта"</b>. <br /><b>"Путь"</b> для главной страницы должно быть "<b>/</b>".',
	'site_disabled_by_administrator' => 'Сайт %s отключен администратором и в данный момент недоступен!',
	'site_activation_instruction' => 'Для включения сайта перейдите в раздел «Сайты» и установите значение «Активность» требуемого сайта.',
	'title_limit_available_sites_exceeded' => 'Превышен лимит доступных сайтов в системе!',
	'message_limit_available_sites_exceeded' => 'Превышен лимит доступных активных сайтов в системе управления сайтом HostCMS!',
	'message_remove_unnecessary_sites' => 'Удалите лишние сайты из системы (<b>"Раздел администрирования" → "Система" → "Сайты"</b>) или приобретите версию без ограничения многосайтовости.',
	'missing_template_for_page' => 'Отсутствует макет для страницы!',
	'change template instruction' => 'Вам необходимо изменить макет для данной страницы в <b>"Раздел администрирования" → "Структура сайта" → "Структура сайта"</b>.',
	'hosting_mismatch_system_requirements' => 'Несоответствие хостинга системным требованиям!',
	'requires_php5' => 'Для работы системы управления сайтом HostCMS необходим PHP 5 или PHP 7 с установленной поддержкой <a href="https://www.hostcms.ru/documentation/server/ibxslt/" target="_blank">Libxslt</a>.',
	'list_tested_hosting' => 'На нашем сайте также размещен <a href="https://www.hostcms.ru/hosting/" target="_blank">список протестированных хостингов</a>, подходящих для HostCMS.',

	'show_title' => 'Показывать',
	'data_show_title' => 'Показывать на сайте',
	
	'unpack_wrong_crc' => 'Ошибка расчета контрольной суммы %s: %d рассчитана, %d фактически указана',
	'unpack_file_already_exists_and_directory' => 'Файл %s уже существует и является директорией',
	'unpack_dir_already_exists_and_file' => 'Директория %s уже существует и является файлом',
	'unpack_file_already_exists_and_protected' => 'Файл %s уже существует и защищен от записи! Установите права доступа к файлу в соответствии с руководством по установке.',
	'unpack_error_creating_dir' => 'Ошибка создания директории для %s',
	'unpack_error_opening_binary_mode' => 'Ошибка открытия файла %s в бинарном режиме',
	'unpack_file_incorrect_size' => 'Извлеченный файл %s имеет некорректный размер %d, ожидается %d. Архив может быть поврежден.',
);