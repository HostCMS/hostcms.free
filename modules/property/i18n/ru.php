<?php
/**
 * Properties.
 *
 * @package HostCMS
 * @subpackage Property
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	'type5' => 'Информационная система',
	'type6' => 'Визуальный редактор',
	'type7' => 'Флажок',
	'type8' => 'Дата',
	'type9' => 'Дата-время',
	'type10' => 'Скрытое поле',
	'type11' => 'Число с плавающей запятой',
	'type12' => 'Интернет-магазин',

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
);