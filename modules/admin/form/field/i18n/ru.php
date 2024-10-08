<?php

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
return array(
	'model_name' => 'Поля формы центра администрирования',
	'form_forms_field_lng_name' => 'Название поля',
	'form_forms_field_lng_description' => 'Описание поля',

	'form_add_forms_field_title' => 'Добавление поля формы центра администрирования',
	'form_edit_forms_field_title' => 'Редактирование поля формы центра администрирования "%s"',

	'show_form_fields_menu_add_new_top' => 'Поле формы',

	'admin_form_tab_0' => 'Название',
	'admin_form_tab_3' => 'Вид',
	'name' => 'Ключевое поле из таблицы или модели',
	'sorting' => 'Порядок сортировки',
	'type' => 'Тип поля',
	'view' => 'Отображение',
	'format' => '<acronym title="Строка формата (за исключением %), по умолчанию %s">Формат отображения</acronym>',
	'allow_sorting' => 'Разрешить сортировку',
	'allow_filter' => 'Разрешить фильтр',
	'editable' => 'Редактировать прямо на форме',
	'width' => 'Ширина поля (px, %)',
	'ico' => '<acronym title="Изображение для поля, указывается классом для тега &lt;i&gt;, например &quot;fa fa-comment&quot;">Изображение для поля</acronym>',
	'class' => '<acronym title="CSS-класс отображения поля">CSS-класс</acronym>',
	'attributes' => '<acronym title="Список атрибутов поля">Атрибуты поля</acronym>',
	'image' => '<acronym title="Соответствие изображений значениям поля. Задается в формате <Значение поля>=<Путь к изображению>">Соответствие изображений значениям поля</acronym>',
	'link' => 'Ссылка',
	'onclick' => 'Onclick',
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
	'field_type_textarea' => 'Большое текстовое поле',

	// Отображение
	'field_view0' => 'Столбец и фильтр',
	'field_view1' => 'Элемент фильтра',
	'field_view2' => 'Столбец',

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
	'filter_condition' => 'Условие фильтрации',

	'show_by_default' => 'Показывать по умолчанию',
	'not_show_by_default' => 'Не показывать по умолчанию',
);