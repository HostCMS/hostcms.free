<?php

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
return array(
	'model_name' => 'Поля формы центра администрирования',
	'form_forms_field_lng_name' => '<acronym title="Название поля формы центра администрирования">Название поля</acronym>',
	'form_forms_field_lng_description' => '<acronym title="Описание поля формы центра администрирования">Описание поля</acronym>',

	// Форма редактирования полей формы центра администрирования.
	'form_add_forms_field_title' => 'Добавление поля формы центра администрирования',
	'form_edit_forms_field_title' => 'Редактирование поля формы центра администрирования "%s"',

	'show_form_fields_menu_add_new_top' => 'Поле формы',

	'show_form_fields_menu_add_new' => 'Добавить',


	'admin_form_tab_0' => 'Название',
	'admin_form_tab_3' => 'Вид',
	'name' => '<acronym title="Ключевое поле таблицы">Ключевое поле</acronym>',
	'sorting' => '<acronym title="Порядок сортировки поля">Порядок сортировки</acronym>',
	'type' => '<acronym title="Тип поля (поле ввода, выпадающий список, флажок и т.д.)">Тип поля</acronym>',
	'view' => '<acronym title="Параметр, определяющий отображение поля - в виде столбца таблицы или в виде элемента фильтра">Отображение</acronym>',
	'format' => '<acronym title="Строка формата отображения данных. Строка формата состоит из директив: обычных символов (за исключением %), которые копируются в результирующую строку, и описатели преобразований, каждый из которых заменяется на один из параметров">Формат отображения</acronym>',
	'allow_sorting' => '<acronym title="Разрешить сортировку по алфавиту">Разрешить сортировку</acronym>',
	'allow_filter' => '<acronym title="Разрешить фильтр для значений поля">Разрешить фильтр</acronym>',
	'editable' => '<acronym title="Разрешить редактирование поля без открытия формы">Редактировать на форме</acronym>',
	'width' => '<acronym title="Ширина поля в пикселах, процентах и т.д., например, 45px">Ширина поля</acronym>',
	'ico' => '<acronym title="Изображение для поля, указывается классом для тега &lt;i&gt;, например &quot;fa fa-comment&quot;">Изображение для поля</acronym>',
	'class' => '<acronym title="CSS-класс отображения поля">CSS-класс</acronym>',
	'attributes' => '<acronym title="Список атрибутов поля">Атрибуты</acronym>',
	'image' => '<acronym title="Соответствие изображений значениям поля. Задается в формате <Значение поля>=<Путь к изображению>">Соответствие изображений значениям поля</acronym>',
	'link' => '<acronym title="Ссылка с подстановкой">Ссылка</acronym>',
	'onclick' => '<acronym title="Действия, выполняемые при событии onclick">Onclick</acronym>',
	'list' => '<acronym title="Соответствие значений элементам списка. Задается в формате <Значение поля>=<Элемент списка>">Соответствие значений элементам списка</acronym>',

	'admin_form_id' => 'Идентификатор формы центра администрирования',
	'id' => 'Идентификатор поля формы',

	'edit_success' => 'Информации о поле формы добавлена!',
	'edit_error' => 'Ошибка! Информации о поле формы не добавлена!',

	'form_fields_menu_admin_form_fields' => 'Список полей формы "%s"',

	// Тип поля.
	'field_type_text' => 'Текст',
	'field_type_text_as_is' => 'Текст "AS IS"',
	'field_type_input' => 'Поле ввода',
	'field_type_checkbox' => 'Флажок',
	'field_type_link' => 'Ссылка',
	'field_type_date_time' => 'Дата-время',
	'field_type_date' => 'Дата',
	'field_type_image_link' => 'Картинка-ссылка',
	'field_type_image_list' => 'Список',
	'field_type_image_callback_function' => 'Вычисляемое поле (Используется обратный вызов функции)',
	
	// Отображение	
	'field_view_column' => 'Столбец',
	'field_view_filter_element' => 'Элемент фильтра',

	// Список полей.
	'show_form_fields_title' => 'Поля формы центра администрирования "%s"',

	'apply_success' => 'Данные для поля успешно обновлены.',
	'apply_error' => 'Ошибка! Данные для поля не изменены!',

	'markDeleted_success' => 'Поле формы успешно удалено!',
	'markDeleted_error' => 'Ошибка! Поле формы не удалено!',

	'copy_success' => 'Поле формы успешно скопировано!',
	'copy_error' => 'Ошибка! Поле формы не скопировано!',

	'filter_type' => '<acronym title="При фильтрации по свойствам модели, указанной для Dataset, используется WHERE, в остальных случаях HAVING">Тип фильтрации</acronym>',
	'filter_where' => 'WHERE',
	'filter_having' => 'HAVING',
	'delete_success' => 'Элемент удален!',
	'undelete_success' => 'Элемент восстановлен!',
);