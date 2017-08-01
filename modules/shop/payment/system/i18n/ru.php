<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
return array(
	'model_name' => 'Платежные системы',
	'show_system_of_pay_link' => "Справочник платежных систем",
	'system_of_pay_menu' => "Платежная система",
	'system_of_pay_menu_add' => "Добавить",
	'name' => "<acronym title=\"Название платежной системы\">Название</acronym>",
	'sorting' => "<acronym title=\"Порядок сортировки платежной системы\">Порядок сортировки</acronym>",
	'description' => "<acronym title=\"Описание платежной системы\">Описание</acronym>",
	'active' => "<acronym title=\"Активна ли платежная система\">Активность</acronym>",
	'shop_id' => '<acronym title="Интернет магазин">Интернет магазин</acronym>',
	'shop_currency_id' => "<acronym title=\"Валюта, в которой проводится расчет в данной платежной системе\">Валюта</acronym>",
	'system_of_pay_edit_form_title' => "Редактирование информации о платежной системе",
	'system_of_pay_add_form_title' => "Добавление информации о платежной системе",
	'system_of_pay_add_form_handler' => "<acronym title=\"Обработчик платежной системы\">Обработчик</acronym>",
	'changeStatus_success' => "Активность изменена",
	'apply_success' => 'Данные для действия успешно обновлены.',
	'apply_error' => 'Ошибка! Данные для действия не изменены.',
	'markDeleted_success' => 'Действие успешно удалено!',
	'markDeleted_error' => 'Ошибка! Действие формы не удалено!',
	'edit_success' => "Данные о платежной системе успешно добавлены!",
	'delete_success' => 'Элемент удален!',
	'undelete_success' => 'Элемент восстановлен!',
	'id' => 'Идентификатор',
	'attention' => 'Внимание! Имя класса зависит от идентификатора платежной системы, например, для платежной системы 17 имя будет<br/><b>class Shop_Payment_System_Handler17 extends Shop_Payment_System_Handler</b>',
	'file_error' => 'Ошибка записи файла %s. Проверьте права доступа к директории!'
);