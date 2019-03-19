<?php
/**
 * Backend users.
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
return array(
	'model_name' => 'Сотрудники',
	'menu' => 'Сотрудники',
	'ua_add_user_link' => 'Добавить',
	'ua_link_user_access' => 'Права доступа',
	'ua_link_user_modules_access' => 'К модулям',
	'ua_link_user_forms_access' => 'К действиям',
	'ua_add_user_form_title' => 'Добавление информации о сотруднике',
	'ua_edit_user_form_title' => 'Редактирование информации о сотруднике "%s"',
	'ua_show_users_title' => 'Сотрудники',
	'id' => 'Идентификатор',
	'login' => "Логин",
	'password' => "Пароль",
	'password_second' => "Подтверждение пароля",
	'ua_add_edit_user_form_password_second_message' => "Введенные пароли не совпадают.",
	'superuser' => "Администратор",
	'users_type_form_tab_2' => 'Личные данные',
	'users_type_form_tab_3' => 'Расписание',

	'name' => 'Имя',
	'patronymic' => 'Отчество',
	'surname' => 'Фамилия',
	'caption' => 'Сотрудник',
	'head' => 'Начальник отдела',
	'head_title' => 'Начальник отдела',

	'sex' => 'Пол',
	'male' => 'Мужской',
	'female' => 'Женский',
	'dismissed' => 'Уволен',
	'freelance' => 'Внештатный',
	'birthday' => 'День рождения',
	'address' => 'Адрес',
	'image' => 'Фотография',
	'description' => 'Характеристика',

	'active' => "Активен",
	'position' => 'Должность',
	'only_access_my_own' => 'Доступ только к элементам сотрудника',
	'read_only' => '<acronym title="Доступ только для чтения для непривилегированных сотрудникей">Только чтение</acronym>',
	'markDeleted_success' => 'Сотрудник успешено удален',
	'markDeleted_error' => 'Ошибка! Сотрудник не удален!',
	'edit_success' => 'Информация о сотруднике изменена!',
	'changeActive_success' => "Информация успешно изменена",
	'apply_success' => 'Информация изменена!',
	'error_superuser' => 'Ошибка! В системе должен быть хотя бы один superuser!',
	'user_has_already_registered' => 'Ошибка! Сотрудник с данным логином уже зарегистрирован!',
	'error_superuser' => 'Ошибка! В системе должен быть хотя бы один superuser!',
	'error_changes_superuser' => 'Ошибка изменения статуса привилегированного сотрудника для сотрудника %s',
	'error_delete_end_users' => 'Ошибка! Удаление последнего сотрудника',
	'error_row_delete_users' => 'Ошибка! Вы не имеете полномочий на совершение данной операции!',
	'demo_mode' => 'Демонстрационный режим, доступ запрещен!',
	'delete_success' => 'Элемент удален!',
	'undelete_success' => 'Элемент восстановлен!',

	'chat_message' => 'Напишите сообщение',
	'chat_messages_none' => 'Нет сообщений',
	'chat_count_unread_message' => 'Непрочитанные сообщения: %s',
	'chat_count_new_message' => 'Новые сообщения: ',
	'new_message_from' => 'Новое сообщение от %s %s',
	'wallpaper' => 'Обои',
	'backend-field-caption' => 'Сотрудник',

	'choosing_site' => 'Выбор сайта',
	'ua_show_user_form_events_access_title' => 'Доступ к действиям формы "%s"',

	'root_dir' => '<acronym title="Корневая директория, выше которой сотрудник не может подниматься">Корневая директория для сотрудника</acronym>',

	'view_sex' => 'Пол:',
	'view_age' => 'Возраст:',
	'view_phones' => 'Телефоны',
	'view_emails' => 'Электронные адреса',
	'view_socials' => 'Социальные сети',
	'view_messengers' => 'Мессенджеры',
	'view_websites' => 'Сайты',
	'error_object_owned_another_user' => 'Объект принадлежит другому сотруднику, доступ запрещен.',
	'break' => 'Перерыв',
	'timesheet_title' => 'Учет рабочего времени',
	'select_user' => 'Укажите сотрудника',
	'user_active' => 'Онлайн %s',
	'user_last_activity' => 'Последняя активность %s',
);