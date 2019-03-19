<?php
/**
 * Information systems.
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
return array(
	'model_name' => 'Информационные элементы',
	'show_information_groups_title' => 'Информационная система "%s"',
	'information_system_top_menu_items' => 'Информационный элемент',
	'show_information_groups_link2' => 'Добавить',
	'show_information_groups_link3' => 'Дополнительные свойства',
	'show_all_comments_top_menu' => 'Комментарии',
	'show_comments_link_show_all_comments' => 'Все комментарии',

	'information_items_add_form_title' => 'Добавление информационного элемента',
	'information_items_edit_form_title' => 'Редактирование информационного элемента "%s"',

	'markDeleted' => 'Удалить информационный элемент',

	'id' => 'Идентификатор',
	'informationsystem_id' => 'Идентификатор информационной системы',
	'shortcut_id' => 'Идентификатор родительского элемента',

	'name' => 'Название информационного элемента',
	'informationsystem_group_id' => 'Группа',
	'datetime' => 'Дата',
	'start_datetime' => 'Дата публикации',
	'end_datetime' => 'Дата завершения публикации',
	'description' => 'Описание информационного элемента',
	'exec_typograph_description' => 'Типографировать описание',
	'use_trailing_punctuation' => '<acronym title="Оптическое выравнивание текста перемещает символы пунктуации за границу набора">Оптическое выравнивание</acronym>',
	'active' => 'Активен',
	'sorting' => 'Порядок сортировки',
	'ip' => 'IP-адрес',
	'showed' => 'Число просмотров',
	'siteuser_id' => 'Клиент',
	'image_large' => 'Большое изображение',
	'image_small' => 'Малое изображение',

	'path' => 'Название элемента в URL',
	'maillist' => '<acronym title="Элемент информационной системы можно добавить как выпуск в рассылку">Разместить в рассылке</acronym>',
	'maillist_default_value' => '-- Не рассылать --',

	'siteuser_group_id' => 'Группа доступа',

	'indexing' => 'Индексировать',
	'text' => 'Текст',
	'exec_typograph_for_text' => 'Типографировать текст',
	'use_trailing_punctuation_for_text' => '<acronym title="Оптическое выравнивание текста перемещает символы пунктуации за границу набора">Оптическое выравнивание</acronym>',

	'tab_1' => 'Описание',
	'tab_2' => 'SEO',
	'tab_3' => 'Метки',
	'tab_4' => 'Дополнительные свойства',

	'seo_title' => 'Заголовок (Title)',
	'seo_description' => 'Описание (Description)',
	'seo_keywords' => 'Ключевые слова (Keywords)',

	'tags' => '<acronym title="Метки (теги) информационного элемента, разделяются запятой, например процессоры, AMD, Athlon64">Метки (теги)</acronym>',
	'type_tag' => 'Введите тэг ...',

	'error_information_group_URL_item' => 'В группе уже существует информационный элемент с таким названием в URL!',
	'error_information_group_URL_item_URL' => 'В группе существует подгруппа с URL, совпадающим с названием элемента в URL!',

	'edit_success' => 'Информационный элемент изменен.',
	'apply_success' => 'Информация изменена.',
	'copy_success' => 'Информационный элемент скопирован!',

	'changeActive_success' => 'Активность информационного элемента изменена.',
	'changeIndexation_success' => 'Индексация информационной группы изменена.',

	// Перенос информационных элементов и групп
	'move_items_groups_title' => 'Перенос групп и элементов',
	'move_items_groups_information_groups_id' => 'Родительская группа',

	// Ярлыки информационных элементов
	'add_information_item_shortcut_title' => 'Создание ярлыка',
	'add_item_shortcut_information_groups_id' => 'Родительская группа',
	'shortcut_success' => 'Ярлык успешно создан.',
	'markDeleted_success' => 'Информационный элемент удален.',
	'markDeleted_error' => 'Информационный элемент не удален!',
	'move_success' => 'Информационные элементы перенесены.',

	'show_comments_title' => 'Комментарии к информационному элементу "%s"',
	'shortcut_success' => 'Ярлык элемента успешно добавлен',

	'show_information_propertys_title' => 'Дополнительные свойства элементов информационной системы "%s"',
	'delete_success' => 'Элемент удален!',
	'undelete_success' => 'Элемент восстановлен!',
	'root' => 'Корень информационной системы',
	'shortcut_group_tags' => "<acronym title=\"Группы в которых располагаются ярлыки текущего элемента\">Дополнительные группы</acronym>",
	'select_group' => 'Выберите группу',
	'export' => 'Экспорт',
	'export_list_separator' => "<acronym title=\"Разделитель для столбцов\">Разделитель</acronym>",
	'export_list_separator1' => "Запятая",
	'export_list_separator2' => "Точка с запятой",
	'export_encoding' => "Кодировка",
	'input_file_encoding0' => 'Windows-1251',
	'input_file_encoding1' => 'UTF-8',
	'export_parent_group' => "<acronym title=\"Вы можете выгружать элементы из указанного каталога, включая все подкаталоги\">Родительская группа для выгрузки элементов</acronym>",
	'export_external_properties_allow_items' => "Экспортировать дополнительные свойства элементов",
	'export_external_properties_allow_groups' => "Экспортировать дополнительные свойства групп",
	'tab_export' => 'Экспорт/Импорт',
	'guid' => '<acronym title="Уникальный идентификатор элемента, например ID00029527">GUID</acronym>',
	'import_small_images' => "Малое изображение для %s",
	'import_file_description' => "Описание файла для %s",
	'import' => "Импорт",
	'import_list_file' => "Выберите файл с компьютера",
	'alternative_file_pointer_form_import' => "<acronym title=\"Задайте относительный путь к файлу от директории системы, например, tmp/myfile.csv\">или укажите путь к файлу на сервере</acronym>",
	'import_list_name_field_f' => "Первая строка содержит имена полей",
	'import_separator' => "Разделитель",
	'import_separator1' => "Запятая",
	'import_separator2' => "Точка с запятой",
	'import_separator3' => "Табуляция",
	'import_separator4' => 'Другой',
	'import_stop' => "Ограничитель",
	'import_stop1' => "Кавычки",
	'import_stop2' => 'Другой',
	'import_encoding' => "Кодировка",
	'import_parent_group' => "Родительская группа для выгрузки элементов",
	'import_images_path' => "<acronym title=\"Путь для внешних файлов, например /upload_images/\">Путь для внешних файлов</acronym>",
	'import_action_items' => "Действие для существующих элементов",
	'import_action_items0' => "Удалить существующие элементы во всех группах",
	'import_action_items1' => "Обновить существующие элементы",
	'import_action_items2' => "Оставить без изменений",
	'import_action_delete_image' => "<acronym title=\"Установка данного флага позволяет удалять изображения для элементов, если эти изображения не переданы или пусты\">Удалять изображения для элементов при обновлении</acronym>",
	'search_event_indexation_import' => "Использовать событийную индексацию при вставке групп элементов и элементов",
	'import_max_time' => "<acronym title=\"Максимальное время выполнения (в секундах)\">Максимальное время выполнения</acronym>",
	'import_max_count' => "Максимальное кол-во импортируемых за шаг",
	'import_button_load' => "Загрузить",
	'root_folder' => 'Корневая группа',
	'count_insert_item' => 'Загружено элементов',
	'count_update_item' => 'Обновлено элементов',
	'create_catalog' => 'Создано разделов',
	'update_catalog' => 'Обновлено разделов',
	'msg_download_complete' => "Импорт завершен!",
	'information_items_copy_form_title' => 'Копировать элемент',
	'add_value' => 'Добавить отсутствующие значения свойства по умолчанию у элементов',
);