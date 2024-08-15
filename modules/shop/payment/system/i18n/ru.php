<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
return array(
	'model_name' => 'Платежные системы',
	'show_system_of_pay_link' => "Справочник платежных систем",
	'system_of_pay_menu' => "Платежная система",
	'system_of_pay_menu_add' => "Добавить",
	'name' => "Название",
	'sorting' => "Порядок сортировки",
	'description' => "Описание",
	'active' => "Активность",
	'shop_id' => 'Интернет магазин',
	'shop_currency_id' => "Валюта",
	'system_of_pay_edit_form_title' => 'Редактирование информации о платежной системе "%s"',
	'system_of_pay_add_form_title' => "Добавление информации о платежной системе",
	'system_of_pay_add_form_handler' => "Обработчик",
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
	'file_error' => 'Ошибка записи файла %s. Проверьте права доступа к директории!',
	'type' => 'Тип',
	'type0' => 'Наличные',
	'type1' => 'Онлайн',
	'type2' => 'Банк',
	'type3' => 'Счет',
	'shop_order_status_id' => 'Статус заказа при оплате',
	'siteuser_groups' => "Группа доступа",
	'all' => 'Все',
);