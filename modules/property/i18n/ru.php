<?php
/**
 * Properties.
 *
 * @package HostCMS
 * @subpackage Property
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
return array(
	'model_name' => 'Дополнительные свойства',
	'title' => 'Дополнительные свойства',
	'menu' => 'Свойство',
	'add' => 'Добавить',
	'parent_dir' => 'Дополнительные свойства',

	'tab_format' => 'Форматы',

	'add_title' => 'Добавление свойства',
	'edit_title' => 'Редактирование свойства "%s"',

	'name' => 'Название',
	'description' => 'Описание',
	'type' => 'Тип',

	'type0' => 'Целое число',
	'type1' => 'Строка',
	'type2' => 'Файл',
	'type3' => 'Список',
	'type4' => 'Большое текстовое поле',
	'type5' => 'Элемент информационной системы',
	'type6' => 'Визуальный редактор',
	'type7' => 'Флажок',
	'type8' => 'Дата',
	'type9' => 'Дата-время',
	'type10' => 'Скрытое поле',
	'type11' => 'Число с плавающей запятой',
	'type12' => 'Товар интернет-магазина',
	'type13' => 'Группа информационной системы',
	'type14' => 'Группа интернет-магазина',

	'list_id' => '<acronym title="Выберите список значений для дополнительного свойства">Список</acronym>',
	'informationsystem_id' => '<acronym title="Информационная система, задаваемая в качестве свойства">Информационная система</acronym>',
	'shop_id' => '<acronym title="Интернет-магазин, задаваемый в качестве свойства">Интернет-магазин</acronym>',

	'sorting' => 'Порядок сортировки',
	'default_value' => 'Значение по умолчанию',
	'tag_name' => '<acronym title="Название XML-тега, который будет содержать значение свойства">Название XML-тега</acronym>',

	'image_large_max_width' => 'Максимальная ширина большого изображения',
	'image_large_max_height' => 'Максимальная высота большого изображения',
	'image_small_max_width' => 'Максимальная ширина малого изображения',
	'image_small_max_height' => 'Максимальная высота малого изображения',

	'hide_small_image' => 'Скрыть поле малого изображения',
	'guid' => '<acronym title="Уникальный идентификатор элемента">GUID</acronym>',
	'id' => 'Идентификатор',
	'edit_success' => 'Дополнительное свойство успешно добавлено.',
	'markDeleted_success' => 'Дополнительное свойство успешно удалено!',
	'copy_success' => 'Дополнительное свойство успешно скопировано.',
	'apply_success' => 'Дополнительное свойство успешно изменено.',
	'type_does_not_exist' => 'Тип свойства %d не существует',
	'deletePropertyValue_success' => 'Значение свойства удалено успешно.',
	'value_other_owner' => 'Значение принадлежит другому объекту!',
	'value_not_found' => 'Значение не найдено!',
	'property_not_found' => 'Свойство не найдено',
	'small_file_caption' => 'Малое изображение свойства "%s"',
	'delete_success' => 'Элемент удален!',
	'undelete_success' => 'Элемент восстановлен!',
	'changeAccess_success' => 'Доступ изменен.',
	'allowAccess_success' => 'Доступ разрешен.',
	'denyAccess_success' => 'Доступ запрещен.',
	'allowAccessChildren_success' => 'Доступ разрешен.',
	'denyAccessChildren_success' => 'Доступ запрещен.',
	'multiple' => 'Разрешить множественные значения для свойства',
	'preserve_aspect_ratio' => 'Сохранять пропорции изображения',
	'preserve_aspect_ratio_small' => 'Сохранять пропорции малого изображения',
	'changeMultiple_success' => 'Множественность свойства изменена!',
	'watermark_default_use_large_image' => 'Использовать водяной знак',
	'watermark_default_use_small_image' => 'Использовать водяной знак для малых изображений',
	'move_success' => 'Свойства перенесены',
	'obligatory' => 'Обязательное для заполнения',
	'merge_success' => 'Свойства объединены',
	'merge_error_type' => 'Нельзя объединять свойства разных типов!',

	'move_title' => 'Перенос свойств',
	'move_dir_id' => 'Родительский раздел',

	'option_recursive_properties' => 'Разрешить рекурсивные свойства',
	'option_add_list_items' => 'Добавлять в XML значения для списочных свойств',

	'indexing' => 'Индексировать',
	'changeIndexing_success' => 'Статус индексирования свойства успешно изменен.',
	'changeIndexing_error' => 'Ошибка при изменении статуса индексирования свойства.',
	'use_trailing_punctuation' => '<acronym title="Оптическое выравнивание текста перемещает символы пунктуации за границу набора">Оптическое выравнивание</acronym>',
	'use_typograph' => 'Типографировать',
);