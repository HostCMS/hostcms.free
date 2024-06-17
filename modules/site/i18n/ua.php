<?php

return array(
	'model_name' => 'Сайт',
	'name' => 'Назва сайту',
	'active' => '<acronym title="Статус сайту. Неактивні сайти не відображаються відвідувачам">Активність</acronym>',
	'sorting' => 'Порядок сортування',
	'locale' => '<acronym title="Локаль для сайту. Наприклад «ua_UA.utf8»">Локаль</acronym>',
	'coding' => '<acronym title="Кодування сайту, наприклад, UTF-8">Кодування</acronym>',
	'timezone' => '<acronym title="Часовий пояс, в якому знаходиться сайт. Встановлюється при відмінності часового поясу сайту від часового поясу хостингу">Часовий пояс</acronym>',
	'max_size_load_image' => 'Максимальний розмір малого зображення',
	'max_size_load_image_big' => 'Максимальний розмір великого зображення',
	'admin_email' => '<acronym title="Електронна адреса адміністратора сайту">E-mail</acronym>',
	'send_attendance_report' => '<acronym title="При виборі даного параметра на електронну скриньку адміністратора сайту щодня буде приходити лист, що містить статистику відвідуваності сайту">Відправляти щоденний звіт про відвідуваність сайту</acronym>',
	'date_format' => '<acronym title="Формат дати. Наприклад «d.m.Y.»">Формат дати</acronym>',
	'date_time_format' => '<acronym title="Формат дати та часу. Наприклад «d.m.Y H:i:s.»">Формат дати та часу</acronym>',
	'error' => '<acronym title="Режим виведення помилок. Наприклад «E_ERROR» або «E_ALL»">Режим виведення помилок</acronym>',
	'error404' => '<acronym title="Сторінка, яка відображається при виникненні 404 помилки (сторінка не знайдена), якщо сторінка не вказана - при виникненні 404 помилки проводиться редирект на головну сторінку">Сторінка для &quot;Помилка 404&quot; (сторінка не знайдена)</acronym>',
	'error403' => '<acronym title="Сторінка, яка відображається при спробі доступу до розділу сайту користувачем, який не має права доступу">Сторінка для &quot;Помилка 403&quot; (доступ заборонено)</acronym>',
	'robots' => '<acronym title="Вміст файлу robots.txt для даного сайту">robots.txt</acronym>',
	'key' => '<acronym title="Ліцензійні ключі для сайту">Ліцензійний ключ</acronym>',
	'closed' => '<acronym title="Сторінка, яка відображається якщо сайт відключений адміністратором">Сторінка, яка відображається при відключенні сайту</acronym>',
	'safe_email' => '<acronym title="Параметр, що визначає захищати e-mail на сторінках сайту від спам-ботів чи ні">Захищати e-mail на сторінках сайту</acronym>',
	'html_cache_use' => 'Кешувати сторінки сайту в статичні файли',
	'html_cache_with' => 'Включати сторінки в кеш',
	'html_cache_without' => 'Не вмикати сторінки в кеш',
	'css_left' => '<acronym title="Призначений для користувача стиль елементів, що виносяться зліва за кордон набору, якщо не вказано, стиль прописується явно">CSS-стиль для лівого оптичного вирівнювання</acronym>',
	'css_right' => '<acronym title="Призначений для користувача стиль елементів, що виносяться справа за кордон набору, якщо не вказано, стиль прописується явно">CSS-стиль для правого оптичного вирівнювання</acronym>',
	'html_cache_clear_probability' => '<acronym title="Визначає ймовірність, з якою буде відбуватися очищення кешу в статичних файлах для поточного сайту. Наприклад, при вказівці числа 10000 очищення кеша буде відбуватися раз в 10000 звернень до сайту. Якщо вказано 0, то автоматичне очищення кешу в статичних файлах не використовуватиметься">Число, що визначає ймовірність очищення кешу.</acronym>',
	'uploaddir' => '<acronym title="Відносний шлях до директорії для зберігання завантажених файлів. Шлях повинен закінчуватися символом /. Наприклад, upload/">Директорія для збереження завантажених файлів</acronym>',
	'nesting_level' => '<acronym title="Число рівнів вкладеності директорій (мінімум 1) для зберігання файлів сутностей системи (основних і додаткових властивостей типу \'Файл\' інформаційних елементів, основних і додаткових властивостей типу \'Файл\' інформаційних груп, додаткових властивостей типу \'Файл\' вузлів структури і т.д.)">Рівень вкладеності</acronym>',
	'id' => 'Ідентифікатор',
	'site_add_site_form_title' => 'Додавання інформації про сайт',
	'site_edit_site_form_title' => 'Редагування інформації про сайт "%s"',
	'site_dates' => 'Формати',
	'site_errors' => 'Помилки',
	'site_robots_txt' => 'robots.txt',
	'site_licence' => 'Ключі',
	'site_cache_options' => ' Кешування',
	'edit_success' => 'Сайт успішно доданий!',
	'edit_error' => 'Помилка! Сайт не доданий!',
	'markDeleted_success' => 'Сайт успішно видалений!',
	'markDeleted_error' => 'Помилка видалення сайту!',
	'changeStatus_success' => 'Активність успішно змінена!',
	'changeStatus_error' => 'Помилка зміни активності!',
	'apply_success' => 'Інформація успішно змінена!',
	'apply_error' => 'Помилка зміни інформації!',
	'notes' => 'Нотатки',
	'menu' => 'Сайти',
	'save_notes' => 'Зберегти',
	'site_show_site_title_list' => 'Список сайтів',
	'site_show_site_title' => 'Сайт',
	'site_link_add' => 'Додати',
	'copy_success' => 'Інформація про сайт успішно скопійована',
	'copy_error' => 'Помилка копіювання інформації про сайт',
	'favicon' => '<acronym title="Favicon-файл для сайту, підтримує розширення .ico, .png, .svg">Favicon</acronym>',
	'deleteFavicon_success' => 'Favicon успішно видалено',
	'default' => 'За замовчуванням',

	'menu2_caption' => 'Налаштування',
	'menu2_sub_caption' => 'Реєстраційні дані',

	'accountinfo_title' => 'Редагування реєстраційних даних',
	'accountinfo_login' => 'Логін користувача в особистому кабінеті на сайті www.hostcms.ru',
	'accountinfo_contract_number' => 'Номер ліцензії',
	'accountinfo_pin_code' => 'PIN-код',
	'accountInfo_success' => 'Реєстраційні дані успішно змінено.',

	'delete_success' => 'Елемент видалений!',
	'undelete_success' => 'Елемент відновлено!',

	'add_site_with_template' => 'Додати з шаблоном дизайну',
	'choose_site_template' => 'Вибір макета сайту',
	'choose_color_scheme' => 'Вибір колірної схеми',
	'template_settings' => 'Налаштування макета',
	'delete_current_site' => 'Заборонено видалення поточного сайту, попередньо перейдіть на інший сайт!',
	'delete_last_site' => 'Заборонено видалення останнього сайту!',
	'delete_site_all_superusers_belongs' => 'Неможливо видалити сайт, так як всі суперкористувачі належать йому!',

	'lng' => 'Мова сайту',
	'lng_default' => 'ua',
	'error_email' => '<acronym title="Технічний e-mail (помилки і т.д.)">Технічний e-mail</acronym>',
	'https' => 'HTTPS',
	'set_https' => 'Встановити HTTPS',

	'site_csp' => 'CSP',
	'csp-header-default-src' => 'Default',
	'csp-header-script-src' => 'Script',
	'csp-header-style-src' => 'Style',
	'csp-header-img-src' => 'Image',
	'csp-header-font-src' => 'Font',
	'csp-header-connect-src' => 'Connect',
	'csp-header-media-src' => 'Media',
	'csp-header-object-src' => 'Object',
	'csp-header-frame-src' => 'Frame',

	'none' => 'Заборонити доступ до всього',
	'all' => 'Повний доступ, виключаючи схеми data: blob: filesystem:',
	'self' => 'Поточний джерело (виключаючи його піддомени)',
	'blob' => 'Дозволити blob:',
	'data' => 'Розширені дані, наприклад, зображення в base64',
	'inline' => 'Дозволити використовувати інлайн-суті, наприклад, "script"',
	'eval' => 'Дозволити використовувати eval()',
	'hosts' => 'Розділені пробілами, * для піддоменів, можна вказати тільки схему, наприклад, https:',

	'protect' => 'Захист від атак',
	'sender_name' => 'Ім\'я відправника',
	'filter_placeholder' => 'Фільтр за назвою',

	'site_protect' => 'Захист',
	'protect_header' => 'Фрейми',
	'protect_frame' => 'Захист від кадрів',
	'protect_frame_exclusions' => 'Не захищати сторінки',
	'error_bot' => 'Сторінка для перевірки ботів',
);