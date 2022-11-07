<?php

return array(
	'error_file_write' => 'Помилка відкриття файлу для запису %s, перевірте права доступу до директорії.',
	'error_resize' => 'Помилка зменшення малого зображення до максимально допустимого розміру. Ймовірною причиною є вказівка ​​розміру зображення менше 0.',
	'error_upload' => 'Файл не завантажено!',

	'error_log_message_stack' => "Файл: %s, рядок %s",
	'error_log_message_short' => "<strong>%s:</strong> %s у файлі %s (рядок %s)",
	'error_log_message' => "<strong>%s:</strong> %s у файлі %s (рядок %s)\nСтек виклик:\n%s",

	'info_cms' => 'Система управління сайтом',
	'info_cms_site_support' => 'Технічна підтримка сайту: ',
	'info_cms_site' => 'Офіційний сайт: ',
	'info_cms_support' => 'Служба технічної підтримки: ',
	'info_cms_sales' => 'Відділ продажу: ',

	'purchase_commercial_version' => 'Купити повну версію',

	'administration_center' => 'Центр адміністрування',
	'debug_information' => 'Налагоджувальна інформація',
	'sql_queries' => 'SQL-запити',
	'sql_statistics' => 'Час: <strong>%.3f</strong> сек. <a onclick="hQuery(\'#sql_h%d\').toggle()" class="sqlShowStack">Виклики</a>',
	'sql_debug_backtrace' => '%s, рядок %d<br/>',
	'show_xml' => 'Показати XML/XSL',
	'hide_xml' => 'Приховати XML/XSL',
	'lock' => 'Зафіксувати панель',
	'logout' => 'Вихід',

	'total_time' => 'Час виконання: <strong>%.3f</strong> с, із них',
	'time_load_modules' => "завантаження модулів: <strong>%.3f</strong> с",
	'time_page' => "макета і сторінки: <strong>%.3f</strong> с",
	'time_page_config' => "налаштувань сторінки: <strong>%.3f</strong> с",
	'time_database_connection' => "з'єднання із СУБД: <strong>%.3f</strong> с",
	'time_database_select' => "вибору БД: <strong>%.3f</strong> с",
	'time_sql_execution' => "виконання запитів: <strong>%.3f</strong> с",
	'time_xml_execution' => "обробки XML: <strong>%.3f</strong> с",
	'time_tpl_execution' => "обробки TPL: <strong>%.3f</strong> с",
	'memory_usage' => "Використано пам\'яті: <strong>%.2f</strong> Мб.",
	'number_of_queries' => "Кількість запитів: <strong>%d</strong>.",
	'compression' => 'Компресія: <strong>%s</strong>.',
	'cache' => 'Кешування: <strong>%s</strong>.',

	'cache_insert_time' => 'час запису в кеш: <strong>%.3f</strong> с',
	'cache_read_time' => 'час читання кеш-пам\'яті: <strong>%.3f</strong> с',

	'cache_write_requests' => 'запитів на запис: <strong>%d</strong>',
	'cache_read_requests' => 'запитів на читання: <strong>%d</strong>',

	'error_create_log' => "Неможливо створити log-файл <b>%s</b><br /><b>Перевірте наявність зазначеної директорії і встановіть необхідні права доступу для неї.</b>",
	'error_log_level_0' => "Нейтральне",
	'error_log_level_1' => "Успішне",
	'error_log_level_2' => "Низький рівень критичності",
	'error_log_level_3' => "Средній рівень критичності",
	'error_log_level_4' => "Найвищий рівень критичності",

	'error_log_attempt_to_access' => "Спроба доступу до модуля %s незареєстрованим користувачем",
	'error_log_several_modules' => "Помилка! Знайдено кілька примірників однакових модулів!",
	'error_log_access_was_denied' => "Доступ до модуля %s заборонений",
	'error_log_module_disabled' => "Модуль %s відключений",
	'error_log_module_access_allowed' => "Доступ до модуля \"%s\" дозволений",
	'error_log_action_access_allowed' => "Виконано дію \"%s\", форми \"%s\"",
	'error_log_logged' => "Вхід в систему управління",
	'error_log_authorization_error' => 'Невірні дані для аутентифікації',
	'error_log_exit' => 'Вихід із системи управління',
	'session_destroy_error' => 'Помилка закриття сеансу',
	'error_log_add_message' => "<strong>Помилка!</strong> Повідомлення про помилку занесено в журнал.",

	'error_message' => "Вітаємо!\n"
	. "Тільки що на сайті відбулася подія, інформація про яку представлена ​​нижче:\n"
	. "Дата: %s\n"
	. "Подія: %s\n"
	. "Статус події: %s\n"
	. "Користувач: %s\n"
	. "Сайт: %s\n"
	. "Сторінка: %s\n"
	. "IP-адреса: %s\n"
	. "Система управління сайтом %s,\n"
	. "http://%s/\n",

	'E_ERROR' => "Помилка",
	'E_WARNING' => "Попередження",
	'E_PARSE' => "Parse Error",
	'E_NOTICE' => "Зауваження",
	'E_CORE_ERROR' => "Core Error",
	'E_CORE_WARNING' => "Core Warning",
	'E_COMPILE_ERROR' => "Compile Error",
	'E_COMPILE_WARNING' => "Compile Warning",
	'E_USER_ERROR' => "Помилка",
	'E_USER_WARNING' => "Попередження",
	'E_USER_NOTICE' => "Зауваження",
	'E_STRICT' => "Strict",
	'E_DEPRECATED' => "Deprecated",

	'default_form_name' => 'Основна',
	'default_event_name' => 'Перегляд',

	'widgets' => 'Віджети',
	'addNote' => 'Додати замітку',
	'deleteNote' => 'Видалити замітку',

	'key_not_found' => 'Не вдалося знайти ліцензійний ключ!',
	'getting_key' => '<div style="margin-top: 20px; overflow: auto; z-index: 9999; background-color: rgba(255, 255, 255, .8); padding: 0 20px; text-shadow: 1px 1px 0 rgba(255, 255, 255, .4)">

	<h2>Отримання номера ліцензії та PIN-коду <a href="https://www.hostcms.ru/documentation/introduction/licenses/licenses/" target="_blank"><i class="fa fa-external-link"></i></a></h2>

	<p>Після установки системи управління необхідно зареєструватися на нашому сайті в розділі «<a href="https://www.hostcms.ru/users/" target="_blank">Особистий кабінет</a>»</p>
	<p>Після підтвердження реєстрації користувача і входу в особистий кабінет, в розділі «Ліцензії» доступний список виданих ліцензій:</p>

	<p align="center">
	<img src="//www.hostcms.ru/images/documentation/guide/site/licenses-list.png" class="img-responsive" />
	</p>

	<p>Комерційні користувачі можуть дізнатися свій номер ліцензії та PIN-код з таблиці в розділі «Ліцензії» особистого кабінету, користувачі HostCMS.Халява можуть додати нову ліцензію.</p>
	<p>Дізнавшись номер ліцензії та PIN-код можна повернутися в <a href="/admin/" target="_blank">центр адміністрування</a> і ввести ці дані в розділі «Система» → «Сайти» → пункт меню «Налаштування» → «Реєстраційні дані».</p>

	<h2>Отримання ключа <a href="https://www.hostcms.ru/documentation/introduction/key/key/" target="_blank"><i class="fa fa-external-link"></i></a></h2>

	<p>Далі можна отримувати ключі у <a href="/admin/" target="_blank">центрі адміністрування</a> системи управління, перейшовши в розділ «Система» → «Сайти», вибрати глобус для відповідного сайту в стовпці «Домени»:</p>

	<p align="center">
	<img src="//www.hostcms.ru/images/documentation/introduction/install/step-by-step/key/key-2.png" class="img-responsive" />
	</p>

	<p>При нажатии на пиктограмму «Ключ» система запросит ключ для выбранного домена по вашей лицензии и внесет его в список ключей сайта.</p>

	<p align="center">
	<img src="//www.hostcms.ru/images/documentation/introduction/install/step-by-step/key/key-3.png" class="img-responsive" /></p>

	<h2>Вхід в центр адміністрування</h2>
	<p>Перейдіть в <a href="/admin/" target="_blank">центр адміністрування</a>, далі дійте за інструкцією.</p>
	</div>',

	'access_forbidden_title' => 'Доступ до сайту заборонений',
	'access_forbidden' => 'ДДоступ до сайту заборонений. Зверніться до адміністратора.',

	'extension_does_not_allow' => 'Завантажувати файл з розширенням "%s" заборонено.',
	'delete_success' => 'Елемент видалений!',
	'undelete_success' => 'Елемент відновлено!',
	'redaction0' => 'Халява',
	'redaction1' => 'Мій сайт',
	'redaction3' => 'Малий бізнес',
	'redaction5' => 'Бізнес',
	'redaction7' => 'Корпорація',

	'byte' => 'Байт',
	'kbyte' => 'КБ',
	'mbyte' => 'МБ',
	'gbyte' => 'ГБ',

	'timePeriodSeconds' => '%s сек. тому',
	'timePeriodMinutes' => '%s хв. тому',
	'timePeriodHours' => '%s год. тому',
	'timePeriodDays' => '%s дн. тому',
	'timePeriodYesterday' => 'вчора',
	'timePeriodMonths' => '%s міс. тому',
	'timePeriodYears' => '%s р. тому',
	'timePeriodYearMonths' => '%s р. %s міс. тому',

	'shortTitleSeconds' => 'с.',
	'shortTitleSeconds_1' => 'сек.',
	'shortTitleMinutes' => 'м.',
	'shortTitleHours' => 'ч.',
	'shortTitleDays' => 'д.',
	'shortTitleYears' => 'г.',

	'now' => 'Зараз',
	'ago' => '%1$s %2$s назад',
	'today' => 'Cьогодні',
	'yesterday' => 'Вчора',
	'tomorrow' => 'Завтра',
	'estimate_date' => '%1$d %2$s',
	'estimate_date_year' => '%1$d %2$s %3$d р.',

	'time_postfix' => ' в %s',

	'month1' => 'січня',
	'month2' => 'лютого',
	'month3' => 'березня',
	'month4' => 'квітня',
	'month5' => 'травня',
	'month6' => 'червня',
	'month7' => 'липня',
	'month8' => 'серпня',
	'month9' => 'вересня',
	'month10' => 'жовтня',
	'month11' => 'листопада',
	'month12' => 'грудня',

	'capitalMonth1' => 'Січень',
	'capitalMonth2' => 'Лютий',
	'capitalMonth3' => 'Березень',
	'capitalMonth4' => 'Квітень',
	'capitalMonth5' => 'Травень',
	'capitalMonth6' => 'Червень',
	'capitalMonth7' => 'Липень',
	'capitalMonth8' => 'Серпень',
	'capitalMonth9' => 'Вересень',
	'capitalMonth10' => 'Жовтень',
	'capitalMonth11' => 'Листопад',
	'capitalMonth12' => 'Грудень',

	'hour_nominative' => 'годину',
	'hour_genitive_singular' => 'години',
	'hour_genitive_plural' => 'годин',
	'minute_nominative' => 'хвилину',
	'minute_genitive_singular' => 'хвилини',
	'minute_genitive_plural' => 'хвилин',

	'day' => 'День',
	'month' => 'Місяц',
	'year' => 'Рік',
	'random' => 'Випадковий',
	'generateChars' => 'Символи',

	'title_no_access_to_page' => 'У Вас недостатньо прав доступу до даної сторінки!',
	'message_more_info' => 'За більш детальною інформацією звертайтеся до адміністратора сайту.',
	'title_domain_must_be_added' => 'Необхідно додати домен %s в список підтримуваних системою управління сайтом HostCMS!',
	'message_domain_must_be_added' => 'Домен <b>%s</b> необхідно додати в список підтримуваних системою управління сайтом <b>HostCMS</b>!',
	'add_domain_instruction1' => 'Для додавання домену перейдіть в <b><a href="/admin/site/index.php" target="_blank">"Розділ адміністрування" → "Система" → "Сайти"</a></b>.',
	'add_domain_instruction2' => 'Виберіть піктограму <b>"Домени"</b> для необхідного сайту. На сторінці, натисніть в меню <b>"Додати"</b>.',
	'home_page_not_found' => 'Чи не знайдена головна сторінка сайту',
	'message_home_page_must_be_added' => 'Вам необхідно додати головну сторінку в <b>"Розділ адміністрування" → "Структура сайту"</b>. <br/> <b> "Назва розділу"</b> для головної сторінки повинно бути "<b>/</b>".',
	'site_disabled_by_administrator' => 'Сайт %s відключений адміністратором і в даний момент недоступний!',
	'site_activation_instruction' => 'Для включення сайту перейдіть в розділ «Сайти» і встановіть значення «Активність» необхідного сайту.',
	'title_limit_available_sites_exceeded' => 'Перевищено ліміт доступних сайтів в системі!',
	'message_limit_available_sites_exceeded' => 'Перевищено ліміт доступних активних сайтів в системі управління сайтом HostCMS!',
	'message_remove_unnecessary_sites' => 'Видаліть зайві сайти з системи (<b>"Розділ адміністрування" → "Сайти"</b>) або придбайте версію без обмеження Многосайтовий.',
	'missing_template_for_page' => 'Відсутня макет для сторінки!',
	'change template instruction' => 'Вам необхідно змінити макет для даної сторінки в <b>"Розділ адміністрування" → "Структура сайту"</b>. Для статичних сторінок макет вказуєте в <b>"Розділ адміністрування" → "Сторінки і документи"</b>.',
	'hosting_mismatch_system_requirements' => 'Невідповідність хостингу системним вимогам!',
	'requires_php5' => 'Для роботи системи управління сайтом HostCMS необхідний PHP 5 з встановленою підтримкою <a href="https://www.hostcms.ru/documentation/server/ibxslt/" target="_blank">Libxslt</a>.',
	'list_tested_hosting' => 'На нашому сайті також розміщений <a href="https://www.hostcms.ru/hosting/" target="_blank"> список протестованих хостингів</a>, придатних для роботи HostCMS.',

	'show_title' => 'Показувати',
	'data_show_title' => 'Показувати на сайті',
);