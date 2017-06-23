<?php

if (!Core::moduleIsActive('siteuser'))
{
	?>
	<h1>Пользователи сайта</h1>
	<p>Функционал недоступен, приобретите более старшую редакцию.</p>
	<p>Модуль &laquo;<a href="http://www.hostcms.ru/hostcms/modules/users/">Пользователи сайта</a>&raquo; доступен в редакциях &laquo;<a href="http://www.hostcms.ru/hostcms/editions/corporation/">Корпорация</a>&raquo; и &laquo;<a href="http://www.hostcms.ru/hostcms/editions/business/">Бизнес</a>&raquo;.</p>
	<?php
	return ;
}

$xslRestorePasswordMailXsl = Core_Array::get(Core_Page::instance()->libParams, 'xslRestorePasswordMailXsl');

if (!is_null(Core_Array::getPost('apply')))
{
	$login = strval(Core_Array::getPost('login'));
	$email = strval(Core_Array::getPost('email'));
	$oSiteuser = Core_Entity::factory('Site', CURRENT_SITE)->Siteusers->getByLoginAndEmail($login, $email);

	if (!is_null($oSiteuser) && $oSiteuser->active)
	{
		$Siteuser_Controller_Restore_Password = new Siteuser_Controller_Restore_Password(
			$oSiteuser
		);
		$Siteuser_Controller_Restore_Password
			->subject('Восстановление пароля')
			->xsl(
				Core_Entity::factory('Xsl')->getByName($xslRestorePasswordMailXsl)
			)
			->sendNewPassword();

		$path = '../';
		?>
		<h1>Восстановление пароля прошло успешно</h1>
		<p>В Ваш адрес отправлено письмо, содержащее Ваш новый пароль.</p>
		<p>Если Ваш браузер поддерживает автоматическое перенаправление через 3 секунды Вы перейдёте на страницу <a href="../">идентификации пользователя</a>. Если Вы не хотите ждать перейдите по соответствующей ссылке.</p>
		<script type="text/javascript">setTimeout(function(){ location = '<?php echo $path?>' }, 3000);</script>
		<?php

		return;
	}
	else
	{
		$error = 'Пользователь с такими параметрами не зарегистрирован или на указанный e-mail не может быть отправлено письмо.';
	}
}

?>
<h1>Восстановление пароля</h1>
<?php
if (!empty($error))
{
	?><div id="error"><?php echo $error?></div><?php
}
?>
<form action="/users/restore_password/" method="post">
<p>
	Пользователь:
	<br />
	<input name="login" type="text" size="30" class="large">
</p>
<p>
	E-mail:
	<br />
	<input name="email" type="text" size="30" class="large">
</p>
<p>
<input name="apply" type="submit" value="Восстановить" class="button" />
</p>
</form>