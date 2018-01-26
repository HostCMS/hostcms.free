<?php
/**
 * Backend users.
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
return array(
	'model_name' => 'Сотрудники',
	'menu' => 'Сотрудники',
	'ua_link_users' => 'Сотрудник',
	'ua_link_users_site' => 'Сотрудники',
	'ua_add_user_link' => 'Добавить',
	'ua_link_user_access' => 'Права доступа',
	'ua_link_user_modules_access' => 'К модулям',
	'ua_link_user_forms_access' => 'К действиям',
	'ua_add_user_form_title' => 'Добавление информации о сотруднике',
	'ua_edit_user_form_title' => 'Редактирование информации о сотруднике',
	'ua_show_users_title' => 'Сотрудники',
	'id' => 'Идентификатор',
	'login' => "<acronym title=\"Логин сотрудника\">Логин</acronym>",
	'users_type_form' => 'Группа сотрудникей',
	'password' => "<acronym title=\"Поле ввода пароля\">Пароль</acronym>",
	'password_second' => "<acronym title=\"Поле подтверждения пароля\">Подтверждение пароля</acronym>",
	'ua_add_edit_user_form_password_second_message' => "Введенные пароли не совпадают.",
	'superuser' => "<acronym title=\"Сотрудник, обладающий полным комплексом прав\">Администратор</acronym>",
	'siteusers_parent_site_id' => "<acronym title=\"Родительский сайт элемента\">Сайт</acronym>",
	'users_type_form_tab_2' => 'Личные данные',

	'name' => '<acronym title="Имя сотрудника">Имя</acronym>',
	'patronymic' => '<acronym title="Отчество сотрудника">Отчество</acronym>',
	'surname' => '<acronym title="Фамилия сотрудника">Фамилия</acronym>',
	'caption' => '<acronym title="ФИО сотрудника">Сотрудник</acronym>',
	'head' => '<acronym title="Назначить сотрудника начальником отдела">Начальник отдела</acronym>',
	'head_title' => 'Начальник отдела',

	'sex' => '<acronym title="Пол сотрудника">Пол</acronym>',
	'male' => 'Мужской',
	'female' => 'Женский',
	'dismissed' => '<acronym title="Сотрудник уволен">Уволен</acronym>',
	'freelance' => '<acronym title="Работает вне штата компании">Внештатный</acronym>',
	'birthday' => '<acronym title="Дата рождения">День рождения</acronym>',
	'address' => '<acronym title="Адрес проживания">Адрес</acronym>',
	'image' => '<acronym title="Фотография">Фотография</acronym>',
	'description' => '<acronym title="Характеристика, дополнительная информация">Характеристика</acronym>',

	//'icq' => '<acronym title="Номер ICQ сотрудника">ICQ</acronym>',
	'active' => "<acronym title=\"Активность сотрудника\">Активен</acronym>",
	'position' => '<acronym title="Должность сотрудника">Должность</acronym>',
	'only_access_my_own' => '<acronym title="Доступ только к созданным сотрудникем элементам">Доступ только к элементам сотрудника </acronym>',
	'read_only' => '<acronym title="Доступ только для чтения для непривилегированных сотрудникей">Только чтение</acronym>',
	'markDeleted_success' => 'Пользователь успешено удален',
	'markDeleted_error' => 'Ошибка! Пользователь не удален!',
	'edit_success' => 'Информация о сотруднике изменена!',
	'changeActive_success' => "Информация успешно изменена",
	'apply_success' => 'Информация изменена!',
	'error_superuser' => 'Ошибка! В системе должен быть хотя бы один superuser!',
	'user_has_already_registered' => 'Ошибка! Пользователь с данным именем уже зарегистрирован!',
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
	'backend-field-caption' => 'Пользователь',

	'choosing_site' => 'Выбор сайта',
	'ua_show_user_form_events_access_title' => 'Access rights for actions "%s"',

	'root_dir' => '<acronym title="Корневая директория, выше которой пользователь не может подниматься">Корневая директория для сотрудника</acronym>',

	'view_sex' => 'Пол:',
	'view_age' => 'Возраст:',
	'view_phones' => 'Телефоны',
	'view_emails' => 'Электронные адреса',
	'view_socials' => 'Социальные сети',
	'view_messengers' => 'Мессенджеры',
	'view_websites' => 'Сайты',
);