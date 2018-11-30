<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
return array(
	'model_name' => 'Заказы магазина',
	'orders' => "Заказы",
	'shops_link_order' => "Заказ",
	'order_edit' => "Редактирование заказа №%s",
	'shops_link_order_add' => "Добавить",
	'show_order_title' => "Заказы магазина \"%s\"",
	'order_add_form_title' => "Добавление информации о заказе",
	'order_edit_form_title' => 'Редактирование информации о заказе "%s"',
	'tab2' => 'Контактные данные',
	'tab3' => 'Описание заказа',
	'tab4' => 'Документы',
	'tab5' => 'Метки',
	'invoice' => "Номер заказа",
	'datetime' => "Дата заказа",
	'siteuser_id' => "Клиент",
	'source_id' => "<acronym title=\"Идентификатор URL метки (UTM, OpenStat и т.п.)\">Идентификатор URL метки</acronym>",
	'cond_of_delivery_add_form_price_order' => "Сумма заказа",
	'order_currency' => "Валюта",
	'paid' => 'Оплачен',
	'canceled' => 'Отменен',
	'order_items_link' => "Товары",
	'payment_datetime' => "Дата оплаты",
	'system_of_pay' => "Платежная система",
	'show_order_status' => 'Статус заказа',
	'print' => "Печать",
	'order_card' => "Карточка заказа",
	'company_id' => 'Организация продавца',
	'status_datetime' => 'Дата изменения статуса заказа',
	'ip' => 'IP-адрес заказчика',
	'type_of_delivery' => "Типы доставок",
	'shop_delivery_condition_id' => "Условия доставки",
	'postcode' => "Индекс",
	'address' => "Улица",
	'house' => "Дом, корпус",
	'flat' => "Квартира (офис)",
	'surname' => 'Фамилия',
	'name' => 'Имя',
	'patronymic' => 'Отчество',
	'company' => 'Компания',
	'phone' => "Телефон",
	'fax' => 'Факс',
	'email' => 'E-mail',
	'description' => 'Описание заказа',
	'system_information' => 'Информация о заказе',
	'delivery_information' => 'Информация об отправлении',
	'shop_id' => 'Идентификатор магазина',
	'id' => 'Идентификатор',
	'markDeleted_success' => "Информация о заказе успешно удалена!",
	'recalc_order_delivery_sum' => "Пересчитать стоимость доставки",
	'recalc_delivery_success' => "Cтоимость доставки пересчитана успешно!",
	'order_card_dt' => "Карточка заказа %s от %s",
	'order_card_supplier' => "Поставщик",
	'order_card_inn_kpp' => "ИНН/КПП",
	'order_card_ogrn' => 'ОГРН',
	'order_card_address' => "Адрес",
	'order_card_phone' => 'Телефон',
	'order_card_fax' => "Факс",
	'order_card_email' => "E-mail",
	'order_card_site' => "Сайт",
	'order_card_paymentsystem' => "Способ оплаты",
	'payer' => "Компания",
	'order_card_contact_person' => "Контактное лицо",
	'order_card_site_user' => "Пользователь",
	'order_card_site_user_id' => "код",
	'table_description' => "Наименование",
	'table_mark' => "Артикул",
	'table_mesures' => "Ед. изм.",
	'table_price' => "Цена",
	'table_amount' => "Кол-во",
	'table_nds_tax' => "Ставка налога",
	'table_nds_value' => "Налог",
	'table_amount_value' => "Сумма",
	'table_nds' => "В том числе налог:",
	'table_all_to_pay' => "Всего к оплате:",
	'order_card_system_of_pay' => "Платежная система",
	'order_card_status_of_pay' => "Оплачен",
	'order_card_cancel' => "Отменен",
	'order_card_order_status' => "Статус заказа",
	'order_card_type_of_delivery' => "Тип доставки",
	'order_card_description' => "Описание заказа",
	'cond_of_delivery_duplicate' => "Внимание при выборе доставки было выбрано несколько одинаковых условий доставки для типа \"%s\". Было оставлено условие доставки (код %s) с наименьшей ценой, рекомендуем проверить условия доставки для данного типа.",
	'shop_order_admin_subject' => 'Заказ N %1$s от %3$s в "%2$s"',
	'delete_success' => 'Элемент удален!',
	'undelete_success' => 'Элемент восстановлен!',
	'changeStatusPaid_success' => 'Статус заказа успешно изменен',
	'edit_success' => "Информация о заказе успешно добавлена!",
	'order_card_system_info' => 'Информация о заказе',
	'show_order_status_link' => 'Справочник статусов заказа',
	'changeStatusCanceled_success' => 'Статус отмены заказа изменен',
	'apply_success' => "Информация успешно изменена.",
	'confirm_admin_subject' => 'Подтверждение оплаты, заказ N %1$s от %3$s в магазине "%2$s"',
	'confirm_user_subject' => 'Подтверждение оплаты, заказ N %1$s от %3$s',
	'cancel_admin_subject' => 'Отмена заказа N %1$s от %3$s в магазине "%2$s"',
	'cancel_user_subject' => 'Отмена заказа N %1$s от %3$s',
	'acceptance_report_form' => 'Акт',
	'acceptance_report_invoice' => 'Счёт-фактура',
	'document_number' => 'Номер реализации',
	'document_datetime' => 'Дата реализации',
	'vat_number' => 'Номер счет-фактуры',
	'vat_datetime' => 'Дата счет-фактуры',
	'tin' => 'ИНН',
	'kpp' => 'КПП',
	'property_menu' => 'Свойства заказа',
	'property_menu_add' => 'Добавить',
	'property_title' => 'Дополнительные свойства',
	'show_list_of_properties_title' => "Список свойств заказа интернет-магазина \"%s\"",
	'tab_properties' => "Дополнительные свойства",
	'prefix' => 'Префикс',
	'display' => 'Способ отображения свойства',
	'properties_show_kind_none' => 'Не отображать',
	'properties_show_kind_text' => 'Поле ввода',
	'properties_show_kind_list' => 'Список - списком',
	'properties_show_kind_radio' => 'Список - переключателями',
	'properties_show_kind_checkbox' => 'Список - флажками',
	'properties_show_kind_checkbox_one' => 'Флажок',
	'properties_show_kind_from_to' => 'От.. до..',
	'properties_show_kind_listbox' => 'Список - список с множественным выбором',
	'properties_show_kind_textarea' => 'Большое текстовое поле',
	'copy_success' => "Заказ успешно скопирован!",
	'guid' => "GUID",
	'send_mail' => "Отправить письмо о заказе",
	'most_ordered' => "Популярные товары за %s дней (в единицах)",
	'popular_brands' => "Популярные бренды за %s дней (в единицах)",
	'popover_title' => "Заказ № %s",
	'notification_new_order' => 'Новый заказ №%s',
	'notification_paid_order' => 'Оплачен заказ №%s',
	'notification_new_order_description' => '%1$s на сумму %2$s',
);