<?php
/**
 * Information systems.
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	'information_items_edit_form_title' => 'Редактирование информационного элемента',

	'markDeleted' => 'Удалить информационный элемент',

	'id' => 'Идентификатор',
	'informationsystem_id' => 'Идентификатор информационной системы',
	'shortcut_id' => 'Идентификатор родительского элемента',

	'name' => '<acronym title="Название информационного элемента">Название информационного элемента</acronym>',
	'informationsystem_group_id' => '<acronym title="Группа, к которой принадлежит информационный элемент">Группа</acronym>',
	'datetime' => '<acronym title="Дата добавления/редактирования информационного элемента">Дата</acronym>',
	'start_datetime' => '<acronym title="Дата публикации информационного элемента">Дата публикации</acronym>',
	'end_datetime' => '<acronym title="Дата завершения публикации информационного элемента">Дата завершения публикации</acronym>',
	'description' => '<acronym title="Описание информационного элемента">Описание информационного элемента</acronym>',
	'exec_typograph_description' => '<acronym title="Применить типографирование к описанию">Типографировать описание</acronym>',
	'use_trailing_punctuation' => '<acronym title="Оптическое выравнивание текста перемещает символы пунктуации за границу набора">Оптическое выравнивание</acronym>',
	'active' => '<acronym title="Статус активности информационного элемента">Активен</acronym>',
	'sorting' => '<acronym title="Порядок сортировки информационного элемента">Порядок сортировки</acronym>',
	'ip' => '<acronym title="IP-адрес компьютера отправителя информационного элемента, например XXX.XXX.XXX.XXX, где XXX - число от 0 до 255">IP-адрес</acronym>',
	'showed' => '<acronym title="Число просмотров информационного элемента">Число просмотров</acronym>',
	'siteuser_id' => '<acronym title="Идентификатор пользователя сайта, создавшего информационный элемент">Пользователь сайта</acronym>',
	'image_large' => '<acronym title="Большое изображение для информационного элемента">Большое изображение</acronym>',
	'image_small' => '<acronym title="Малое изображение для информационного элемента">Малое изображение</acronym>',

	'path' => '<acronym title="Название элемента в URL">Название элемента в URL</acronym>',
	'maillist' => '<acronym title="Элемент информационной системы можно добавить как выпуск в рассылку">Разместить в рассылке</acronym>',
	'maillist_default_value' => '-- Не рассылать --',

	'siteuser_group_id' => '<acronym title="Группа, имеющая права доступа к информационному элементу">Группа доступа</acronym>',

	'indexing' => '<acronym title="Флаг, указывающий индексировать элемент информационной системы или нет">Индексировать</acronym>',
	'text' => '<acronym title="Текст информационного элемента">Текст</acronym>',
	'exec_typograph_for_text' => '<acronym title="Применить типографирование к тексту">Типографировать текст</acronym>',
	'use_trailing_punctuation_for_text' => '<acronym title="Оптическое выравнивание текста перемещает символы пунктуации за границу набора">Оптическое выравнивание</acronym>',

	'tab_1' => 'Описание',
	'tab_2' => 'SEO',
	'tab_3' => 'Метки',
	'tab_4' => 'Дополнительные свойства',

	'seo_title' => '<acronym title="Значение мета-тега <title> для информационного элемента">Заголовок (Title)</acronym>',
	'seo_description' => '<acronym title="Значение мета-тега <description> для информационного элемента">Описание (Description)</acronym>',
	'seo_keywords' => '<acronym title="Значение мета-тега <keywords> для информационного элемента">Ключевые слова (Keywords)</acronym>',

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
	'move_items_groups_information_groups_id' => '<acronym title="Группа, в которую будут перенесены элементы и группы">Родительская группа</acronym>',

	// Ярлыки информационных элементов
	'add_information_item_shortcut_title' => 'Создание ярлыка',
	'add_item_shortcut_information_groups_id' => '<acronym title="Группа, в которой размещается ярлык информационного элемента">Родительская группа</acronym>',
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
	'import_small_images' => "Малое изображение для ",
	'import' => "Импорт",
	'import_list_file' => "<acronym title=\"Выберите файл с компьютера\">Выберите файл с компьютера</acronym>",
	'alternative_file_pointer_form_import' => "<acronym title=\"Задайте относительный путь к файлу от директории системы, например, tmp/myfile.csv\">или укажите путь к файлу на сервере</acronym>",
	'import_list_name_field_f' => "<acronym title=\"Флаг, указывающий на то, содержит ли первая строка имена полей\">Первая строка содержит имена полей</acronym>",
	'import_separator' => "<acronym title=\"Разделитель для столбцов\">Разделитель</acronym>",
	'import_separator1' => "Запятая",
	'import_separator2' => "Точка с запятой",
	'import_separator3' => "Табуляция",
	'import_separator4' => 'Другой',
	'import_stop' => "<acronym title=\"Ограничитель для полей\">Ограничитель</acronym>",
	'import_stop1' => "Кавычки",
	'import_stop2' => 'Другой',
	'import_encoding' => "Кодировка",
	'import_parent_group' => "<acronym title=\"Вы можете выгружать элементы из указанного каталога, включая все подкаталоги\">Родительская группа для выгрузки элементов</acronym>",
	'import_images_path' => "<acronym title=\"Путь для внешних файлов, например /upload_images/\">Путь для внешних файлов</acronym>",
	'import_action_items' => "<acronym title=\"Действие для существующих элементов\">Действие для существующих элементов</acronym>",
	'import_action_items0' => "Удалить существующие элементы во всех группах",
	'import_action_items1' => "Обновить существующие элементы",
	'import_action_items2' => "Оставить без изменений",
	'import_action_delete_image' => "<acronym title=\"Установка данного флага позволяет удалять изображения для элементов, если эти изображения не переданы или пусты\">Удалять изображения для элементов при обновлении</acronym>",
	'search_event_indexation_import' => "Использовать событийную индексацию при вставке групп элементов и элементов",
	'import_max_time' => "<acronym title=\"Максимальное время выполнения (в секундах)\">Максимальное время выполнения</acronym>",
	'import_max_count' => "<acronym title=\"Максимальное количество импортируемых за шаг элементов\">Максимальное кол-во импортируемых за шаг</acronym>",
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