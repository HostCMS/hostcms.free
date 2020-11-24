<?php

if (!Core::moduleIsActive('forum'))
{
	?>
	<h1>Форумы</h1>
	<p>Функционал недоступен, приобретите более старшую редакцию.</p>
	<p>Модуль &laquo;<a href="http://www.hostcms.ru/hostcms/modules/forums/">Форумы</a>&raquo; доступен в редакции &laquo;<a href="http://www.hostcms.ru/hostcms/editions/corporation/">Корпорация</a>&raquo;.</p>
	<?php
	return ;
}

if (!Core::moduleIsActive('siteuser'))
{
	?>
	<h1>Пользователи сайта</h1>
	<p>Функционал недоступен, приобретите более старшую редакцию.</p>
	<p>Модуль &laquo;<a href="http://www.hostcms.ru/hostcms/modules/users/">Пользователи сайта</a>&raquo; доступен в редакциях &laquo;<a href="http://www.hostcms.ru/hostcms/editions/corporation/">Корпорация</a>&raquo; и &laquo;<a href="http://www.hostcms.ru/hostcms/editions/business/">Бизнес</a>&raquo;.</p>
	<?php
	return ;
}

$Forum_Controller_Show = Core_Page::instance()->object;

$Forum_Controller_Show->addMessageUserNotificationXsl(
	Core_Entity::factory('Xsl')->getByName(
		Core_Array::get(Core_Page::instance()->libParams, 'addMessageUserNotificationXsl')
	)
)->addMessageAdminNotificationXsl(
	Core_Entity::factory('Xsl')->getByName(
		Core_Array::get(Core_Page::instance()->libParams, 'addMessageAdminNotificationXsl')
	)
)->editMessageUserNotificationXsl(
	Core_Entity::factory('Xsl')->getByName(
		Core_Array::get(Core_Page::instance()->libParams, 'editMessageUserNotificationXsl')
	)
)->editMessageAdminNotificationXsl(
	Core_Entity::factory('Xsl')->getByName(
		Core_Array::get(Core_Page::instance()->libParams, 'editMessageAdminNotificationXsl')
	)
)->addTopicAdminNotificationXsl(
	Core_Entity::factory('Xsl')->getByName(
		Core_Array::get(Core_Page::instance()->libParams, 'addTopicAdminNotificationXsl')
	)
);

$oForum = $Forum_Controller_Show->getEntity();

$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

if (is_null($oSiteuser))
{
	$Forum_Controller_Show->addEntity(
		Core::factory('Core_Xml_Entity')
			->name('captcha_id')
			->value(Core_Captcha::getCaptchaId())
		);
}

$Forum_Controller_Show->category && $oForum_Category = Core_Entity::factory('Forum_Category', $Forum_Controller_Show->category);

// Показ списка тем
if ($Forum_Controller_Show->myPosts)
{
	if ($oSiteuser)
	{
		$xslName = Core_Array::get(Core_Page::instance()->libParams, 'messagesXsl');
	}
	else
	{
		$path = '/' . $oForum->Structure->path . '/'  . $Forum_Controller_Show->category . '/';
		?>
		<h1>Необходимо авторизироваться</h1>
		<p>Вам необходимо авторизироваться. Через 3 секунды Вы вернетесь в форум.</p>
		<p>Если Вы не хотите ждать, перейдите по <a href="<?php echo $path?>">ссылке</a>.</p>
		<script type="text/javascript">setTimeout(function(){ location = '<?php echo $path?>' }, 3000);</script>
		<?php
		return;
	}
}
elseif ($Forum_Controller_Show->category && !$Forum_Controller_Show->topic)
{
	$xslName = Core_Array::get(Core_Page::instance()->libParams, 'topicsXsl');

	$isModerator = $oForum_Category->isModerator($oSiteuser);
	if ($isModerator)
	{
		$forum_topic_id = intval(Core_Array::getGet('visible_topic_id', 0));
		if ($forum_topic_id)
		{
			$oForum_Topic = Core_Entity::factory('Forum_Topic', $forum_topic_id);
			$oForum_Topic->visible = 1 - $oForum_Topic->visible;
			$oForum_Topic->save();
		}

		$forum_topic_id = intval(Core_Array::getGet('notice_topic_id', 0));
		if ($forum_topic_id)
		{
			$oForum_Topic = Core_Entity::factory('Forum_Topic', $forum_topic_id);
			$oForum_Topic->announcement = 1 - $oForum_Topic->announcement;
			$oForum_Topic->save();
		}

		$forum_topic_id = intval(Core_Array::getGet('close_topic_id', 0));
		if ($forum_topic_id)
		{
			$oForum_Topic = Core_Entity::factory('Forum_Topic', $forum_topic_id);
			$oForum_Topic->closed = 1 - $oForum_Topic->closed;
			$oForum_Topic->save();
		}

		$forum_topic_id = intval(Core_Array::getGet('delete_topic_id', 0));
		if ($forum_topic_id)
		{
			$oForum_Topic = Core_Entity::factory('Forum_Topic', $forum_topic_id);
			$oForum_Topic->markDeleted();
		}
	}

	// Обработка добавления/редактирования темы
	if (Core_Array::getPost('add_edit_topic')
		&& (!$Forum_Controller_Show->editTopic
			|| ($oForum_Topic = Core_Entity::factory('Forum_Topic', $Forum_Controller_Show->editTopic))
		&& ($oForum_Category = $oForum_Topic->Forum_Category)
		&& ($oForum_Category->id == $Forum_Controller_Show->category)))
	{
		$topic_subject = Core_Str::removeEmoji(Core_Str::stripTags(strval(Core_Array::getPost('topic_subject'))));
		$topic_text = Core_Str::removeEmoji(strval(Core_Array::getPost('topic_text')));

		$status = 0;

		// Проверяем на доступность пользователю добавления/редактирования темы
		// Добавление темы
		if (!$Forum_Controller_Show->editTopic)
		{
			$oForum_Category = Core_Entity::factory('Forum_Category', $Forum_Controller_Show->category);
			!$oForum_Category->hasAddTopicPermission($oSiteuser) && $status = -2;
		}
		else
		{
			$oForum_Topic_Post = $oForum_Topic->Forum_Topic_Posts->getFirstPost();

			if (!is_null($oForum_Topic_Post))
			{
				!$oForum_Topic_Post->hasEditPermission($oSiteuser) && $status = -2;
			}
			else
			{
				$status = -2;
			}
		}

		if ($status == 0)
		{
			if ((mb_strlen($topic_subject) < 2 || mb_strlen($topic_text) < 2))
			{
				$status = -1;
			}
			elseif (is_null($oSiteuser) && $oForum_Category->use_captcha && !Core_Captcha::valid(Core_Array::getPost('captcha_id'), Core_Array::getPost('captcha')))
			{
				$status = -3;
			}
		}

		$path = '/' . $oForum->Structure->path . '/'  . $Forum_Controller_Show->category . '/';

		switch($status)
		{
			case -1:
				$Forum_Controller_Show->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('error')
						->value('Длина темы и сообщения должна быть не менее 2-х символов.')
				)
				->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('error_topic_title')
						->value($topic_subject)
				)->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('error_topic_text')
						->value($topic_text)
				);

				if ($isModerator)
				{
					$Forum_Controller_Show->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('error_topic_announcement')
							->value(intval(Core_Array::getPost('announcement')))
					)->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('error_topic_closed')
							->value(intval(Core_Array::getPost('closed')))
					)->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('error_topic_visible')
							->value(intval(Core_Array::getPost('visible')))
					);
				}
			break;

			case -2:
				?>
				<h1>У Вас недостаточно прав для совершения операции или Вы слишком часто добавляете сообщения</h1>
				<p> Через 3 секунды Вы вернетесь в форум.</p>
				<p>Если Вы не хотите ждать, перейдите по <a href="<?php echo $path?>">ссылке</a>.</p>
				<script type="text/javascript">setTimeout(function(){ location = '<?php echo $path?>' }, 3000);</script>
				<?php
				return ;

			case -3:
				$Forum_Controller_Show->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('error')
						->value('Вы неверно ввели число подтверждения отправки формы!')
				)
				->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('error_topic_title')
						->value($topic_subject)
				)->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('error_topic_text')
						->value($topic_text)
				);

				if ($isModerator)
				{
					$Forum_Controller_Show->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('error_topic_announcement')
							->value(intval(Core_Array::getPost('announcement')))
					)->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('error_topic_closed')
							->value(intval(Core_Array::getPost('closed')))
					)->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('error_topic_visible')
							->value(intval(Core_Array::getPost('visible')))
					);
				}
			break;

			default:
				// Antispam
				if (Core::moduleIsActive('antispam'))
				{
					$Antispam_Controller = new Antispam_Controller();
					$bAntispamAnswer = $Antispam_Controller
						->addText($topic_subject)
						->addText($topic_text)
						->execute();
				}
				else
				{
					$bAntispamAnswer = TRUE;
				}

				if ($bAntispamAnswer)
				{
					// Добавление темы
					if (!$Forum_Controller_Show->editTopic)
					{
						$oForum_Topic = Core_Entity::factory('Forum_Topic');

						$bFlood = FALSE;

						if ($oForum_Category->isModerator($oSiteuser))
						{
							$oForum_Topic->visible = intval(Core_Array::getPost('visible', 0));
							$oForum_Topic->announcement = intval(Core_Array::getPost('announcement', 0));
							$oForum_Topic->closed = intval(Core_Array::getPost('closed', 0));
						}
						else
						{
							// Проверка на флуд
							$oForum_Topic_Posts = Core_Entity::factory('Forum_Topic_Post');
							$oForum_Topic_Posts->queryBuilder()
								->select('forum_topic_posts.*')
								->join('forum_topics', 'forum_topics.id', '=', 'forum_topic_posts.forum_topic_id')
								->where('forum_topics.forum_category_id', '=', $oForum_Category->id)
								->where('forum_topic_posts.ip', '=', Core_Array::get($_SERVER, 'REMOTE_ADDR', '127.0.0.1'));

							if (!is_null($oSiteuser))
							{
								$oForum_Topic_Posts->queryBuilder()
									->setOr()
									->where('forum_topic_posts.siteuser_id', '=', $oSiteuser->id);
							}
							$oForum_Topic_Posts->queryBuilder()
								->clearOrderBy()
								->orderBy('forum_topic_posts.id', 'DESC')
								->limit(1);

							$aForum_Topic_Posts = $oForum_Topic_Posts->findAll(FALSE);
							if (count($aForum_Topic_Posts))
							{
								$bFlood = time() - Core_Date::sql2timestamp($aForum_Topic_Posts[0]->datetime) < $oForum->flood_protection_time;
							}

							// Если постмодерировать, то тема видима
							$oForum_Topic->visible = $oForum_Category->postmoderation;
						}

						if (!$bFlood)
						{
							$oForum_Category->add($oForum_Topic);

							$oForum_Topic_Post = Core_Entity::factory('Forum_Topic_Post');
							$oForum_Topic_Post->siteuser_id = !is_null($oSiteuser) ? $oSiteuser->id : 0;
							$oForum_Topic_Post->subject = $topic_subject;
							$oForum_Topic_Post->text = $topic_text;
							$oForum_Topic->add($oForum_Topic_Post);

							// Пересчитываем количество сообщений
							if (!is_null($oSiteuser))
							{
								$oForum_Siteuser_Count = $Forum_Controller_Show->getCountMessageProperty();
								$oForum_Siteuser_Count->count = $oForum_Siteuser_Count->count + 1;
								$oForum_Siteuser_Count->save();
							}

							// Подписываем создателя темы
							if ($oSiteuser && Core_Array::getPost('subscribe'))
							{
								$oForum_Topic_Subscriber = Core_Entity::factory('Forum_Topic_Subscriber');
								$oForum_Topic_Subscriber->siteuser_id = $oSiteuser->id;
								$oForum_Topic->add($oForum_Topic_Subscriber);
							}

							// Событийная индексация
							if (Core::moduleIsActive('search'))
							{
								// $oForum_Topic->indexing() возвращает массив с страницами темы
								Search_Controller::indexingSearchPages($oForum_Topic->indexing());
							}

							// Отправляем письмо куратору форума
							// addTopicAdminNotificationXsl
							if (strlen($oForum_Category->email))
							{
								$oForum_Topic->clearEntities();
								$oForum_Category
									->clearEntities()
									->showXmlModerators(TRUE);
								$oForum->clearEntities()
									->addEntity(
										$oForum->Site->clearEntities()->showXmlAlias()
									)
									->addEntity(
										$oForum_Category->addEntity(
											$oForum_Topic
												->showXmlFirstPost(TRUE)
												->addEntity($oForum_Topic_Post->clearEntities()->showXmlSiteuser())
										)
									);

								$sXml = $oForum->getXml();

								$mail_text = Xsl_Processor::instance()
									->xml($sXml)
									->xsl($Forum_Controller_Show->addTopicAdminNotificationXsl)
									->process();

								$aEmails = array_map('trim', explode(',', $oForum_Category->email));

								if (count($aEmails))
								{
									$oCore_Mail = Core_Mail::instance()
										->clear()
										->to(array_shift($aEmails))
										->from(EMAIL_TO)
										->subject(Core::_('Forum.add_topic'))
										->message(trim($mail_text))
										->contentType('text/plain')
										->header('X-HostCMS-Reason', 'Forum');

									count($aEmails)
										&& $oCore_Mail->header('Bcc', implode(',', $aEmails));

									$oCore_Mail->send();
								}
							}
							?>
							<h1>Создание темы</h1>
							<p>Спасибо, тема успешно создана. Через 3 секунды Вы вернетесь в форум.</p>
							<p>Если Вы не хотите ждать, перейдите по <a href="<?php echo $path?>">ссылке</a>.</p>
							<script type="text/javascript">setTimeout(function(){ location = '<?php echo $path?>' }, 3000);</script>
							<?php
						}
						else
						{
							?>
							<h1>Вы слишком часто добавляете сообщения</h1>
							<p>Через 3 секунды Вы вернетесь в форум.</p>
							<p>Если Вы не хотите ждать, перейдите по <a href="<?php echo $path?>">ссылке</a>.</p>
							<script type="text/javascript">setTimeout(function(){ location = '<?php echo $path?>' }, 3000);</script>
							<?php
						}
					}
					else // Редактирование темы
					{
						$oForum_Topic_Original = clone $oForum_Topic;
						if ($oForum_Category->isModerator($oSiteuser))
						{
							$oForum_Topic->visible = intval(Core_Array::getPost('visible', 0));
							$oForum_Topic->announcement = intval(Core_Array::getPost('announcement', 0));
							$oForum_Topic->closed = intval(Core_Array::getPost('closed', 0));
							$oForum_Topic->save();

							// Событийная индексация
							if (Core::moduleIsActive('search'))
							{
								// $oForum_Topic->indexing() возвращает массив с страницами темы
								Search_Controller::indexingSearchPages($oForum_Topic->indexing());
							}
						}

						$oForum_Topic_Post = $oForum_Topic->Forum_Topic_Posts->getFirstPost();

						// Отправляем письмо куратору  категории
						if (strlen($oForum_Category->email))
						{
							$oForum_Topic->clearEntities();
							$oForum_Topic_Post->clearEntities()->showXmlSiteuser();

							$oForum_Topic_Post_Original = clone $oForum_Topic_Post;

							$oForum_Topic_Post->subject = $topic_subject;
							$oForum_Topic_Post->text = $topic_text;
							$oForum_Topic_Post->save();

							$oForum_Topic
								->showXmlFirstPost(TRUE)
								->addEntity(Core::factory('Core_Xml_Entity')
									->name('old')
									->addEntity($oForum_Topic_Post_Original)
								)->addEntity(Core::factory('Core_Xml_Entity')
									->name('new')
									->addEntity($oForum_Topic_Post)
								)->addEntity(Core::factory('Core_Xml_Entity')
									->name('original_topic')
									->addEntity($oForum_Topic_Original->clearEntities()->showXmlFirstPost(FALSE)->showXmlLastPost(FALSE))
								);

							$oForum_Category
								->clearEntities()
								->showXmlModerators(TRUE);
							$oForum->clearEntities()
								->addEntity(
									$oForum->Site->clearEntities()->showXmlAlias()
								)
								->addEntity($oSiteuser)
								->addEntity(
									$oForum_Category->addEntity($oForum_Topic)
								);

							$sXml = $oForum->getXml();

							$mail_text = Xsl_Processor::instance()
								->xml($sXml)
								->xsl($Forum_Controller_Show->editMessageAdminNotificationXsl)
								->process();

							$aEmails = array_map('trim', explode(',', $oForum_Category->email));

							if (count($aEmails))
							{
								$oCore_Mail = Core_Mail::instance()
									->clear()
									->to(array_shift($aEmails))
									->from(EMAIL_TO)
									->subject(Core::_('Forum.edit_topic'))
									->message(trim($mail_text))
									->contentType('text/plain')
									->header('X-HostCMS-Reason', 'Forum');

								count($aEmails)
									&& $oCore_Mail->header('Bcc', implode(',', $aEmails));

								$oCore_Mail->send();
							}
						}

						$page_count = intval(Core_Array::getPost('current_page')) > 1
							? 'page-' . intval(Core_Array::getPost('current_page')) . '/'
							: '';

						$path = '/' . $oForum->Structure->path . '/'  . $Forum_Controller_Show->category . '/' . $page_count;
						?>
						<h1>Редактирование темы</h1>
						<p>Тема изменена. Через 3 секунды Вы вернетесь в форум.</p>
						<p>Если Вы не хотите ждать, перейдите по <a href="<?php echo $path?>">ссылке</a>.</p>
						<script type="text/javascript">setTimeout(function(){ location = '<?php echo $path?>' }, 3000);</script>
						<?php
					}
				}
				else
				{
					?>
					<h1>Добавление темы запрещено!</h1>
					<p>Через 3 секунды Вы вернетесь в форум.</p>
					<p>Если Вы не хотите ждать, перейдите по <a href="<?php echo $path?>">ссылке</a>.</p>
					<script type="text/javascript">setTimeout(function(){ location = '<?php echo $path?>' }, 3000);</script>
					<?php
				}

				return;
		}
	}

	// Форма добавления/редактирования темы
	if ($Forum_Controller_Show->editTopic
	&& ($oForum_Topic = Core_Entity::factory('Forum_Topic', $Forum_Controller_Show->editTopic))
	&& $oForum_Topic->Forum_Category->id == $Forum_Controller_Show->category
	&& $oForum_Topic->hasEditPermission($oSiteuser)
	|| $Forum_Controller_Show->addTopic && $oForum_Category->hasAddTopicPermission($oSiteuser))
	{
		$xslName = Core_Array::get(Core_Page::instance()->libParams, 'newTopicXsl');

		$Forum_Controller_Show->limit(0);

		if ($Forum_Controller_Show->editTopic)
		{
			$Forum_Controller_Show->addEntity($oForum_Topic->showXmlFirstPost(TRUE));
		}
	}
}
elseif ($Forum_Controller_Show->topic)
{
	$xslName = Core_Array::get(Core_Page::instance()->libParams, 'topicMessagesXsl');

	if (($forum_topic_post_id = intval(Core_Array::getGet('del_post_id', 0)))
		&& ($oForum_Topic_Post = Core_Entity::factory('Forum_Topic_Post', $forum_topic_post_id))
		&& ($oForum_Topic = $oForum_Topic_Post->Forum_Topic)
		&& ($oForum_Topic->id == $Forum_Controller_Show->topic)
		)
	{
		$canDelete = $isModerator = $oForum_Category->isModerator($oSiteuser);

		// Пользователь - не модератор, но автор темы
		if (!$isModerator && $oForum_Topic_Post->siteuser_id == $oSiteuser->id)
		{
			$oForum = $oForum_Topic_Post->Forum_Topic->Forum_Category->Forum_Group->Forum;

			$canDelete = time() < (Core_Date::sql2timestamp($oForum_Topic_Post->datetime) + $oForum->allow_delete_time);

			if (!$canDelete)
			{
				$Forum_Controller_Show->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('error')
						->value('Время, втечение которого Вы могли удалять свои сообщения, истекло.')
				);
			}
		}

		if ($canDelete)
		{
			$oForum_Topic_Post->markDeleted();

			// При удалении единственного сообщения темы, удаляем и тему
			$oForum_Topic = $oForum_Topic_Post->Forum_Topic;

			// Удалили не единственное сообщение
			if ($oForum_Topic->Forum_Topic_Posts->getCount(FALSE))
			{
				$path = '/' . $oForum->Structure->path . '/'  . $Forum_Controller_Show->category . '/' .  $Forum_Controller_Show->topic . '/';

				$page = ceil($oForum_Topic->Forum_Topic_Posts->getCount() / $oForum->posts_on_page);

				$path .= $page > 1 ? "page-{$page}/" : '';
				?>
				<h1>Удаление сообщения</h1>
				<p>Удаление сообщения успешно произведено. Через 3 секунды Вы вернетесь в форум.</p>
				<p>Если Вы не хотите ждать, перейдите по <a href="<?php echo $path?>">ссылке</a>.</p>
				<script type="text/javascript">setTimeout(function(){ location = '<?php echo $path?>' }, 3000);</script>
				<?php
			}
			else
			{
				$oForum_Topic->markDeleted();

				$path = '/' . $oForum->Structure->path . '/' . $Forum_Controller_Show->category . '/';

				?><script type="text/javascript">setTimeout(function(){ location = '<?php echo $path?>' }, 0);</script><?php
			}

			// Пересчитываем количество сообщений
			if (!is_null($oSiteuser))
			{
				$oForum_Siteuser_Count = $Forum_Controller_Show->getCountMessageProperty();
				$oForum_Siteuser_Count->count = $oForum_Siteuser_Count->count - 1;
				$oForum_Siteuser_Count->save();
			}

			return;
		}
	}

	// Подписка на тему
	if (Core_Array::getGet('action') == 'topic_subscribe' && !is_null($oSiteuser)
		&& ($oForum_Topic = Core_Entity::factory('Forum_Topic', $Forum_Controller_Show->topic))
		&& !$oForum_Topic->isSubscribed($oSiteuser))
	{
		$oForum_Topic_Subscriber = Core_Entity::factory('Forum_Topic_Subscriber');
		$oForum_Topic_Subscriber->siteuser_id = $oSiteuser->id;
		$oForum_Topic->add($oForum_Topic_Subscriber);
	}

	// Удаление подписки
	if (Core_Array::getGet('action') == 'topic_unsubscribe' && !is_null($oSiteuser)
		&& ($oForum_Topic = Core_Entity::factory('Forum_Topic', $Forum_Controller_Show->topic))
		&& $oForum_Topic->isSubscribed($oSiteuser))
	{
		$oForum_Topic_Subscriber = $oForum_Topic->Forum_Topic_Subscribers->getBySiteuserId($oSiteuser->id);
		!is_null($oForum_Topic_Subscriber) && $oForum_Topic_Subscriber->delete();
	}

	// Добавление/редактирование сообщения
	if (Core_Array::getPost('add_post')
		&& (!$Forum_Controller_Show->editPost ||
		($oForum_Topic_Post = Core_Entity::factory('Forum_Topic_Post', $Forum_Controller_Show->editPost))
		&& ($oForum_Topic = $oForum_Topic_Post->Forum_Topic)
		&& ($oForum_Topic->id == $Forum_Controller_Show->topic))
	)
	{
		$post_title = Core_Str::removeEmoji(strval(Core_Array::getPost('post_title')));
		$post_text = Core_Str::removeEmoji(strval(Core_Array::getPost('post_text')));

		$status = 0;

		// Проверяем на доступность пользователю добавления/редактирования сообщения
		// Добавление темы
		if (!$Forum_Controller_Show->editPost)
		{
			$oForum_Topic = Core_Entity::factory('Forum_Topic', $Forum_Controller_Show->topic);
			!$oForum_Topic->hasAddPermission($oSiteuser) && $status = -2;
		}
		else
		{
			!$oForum_Topic_Post->hasEditPermission($oSiteuser) && $status = -2;
		}

		if ($status == 0)
		{
			if (mb_strlen($post_title) < 2 || mb_strlen($post_text) < 2)
			{
				$status = -1;
			}
			elseif (is_null($oSiteuser) && $oForum_Category->use_captcha && !Core_Captcha::valid(Core_Array::getPost('captcha_id'), Core_Array::getPost('captcha')))
			{
				$status = -3;
			}
		}

		$path = '/' . $oForum->Structure->path . '/'  . $Forum_Controller_Show->category . '/' . $Forum_Controller_Show->topic . '/';

		switch($status)
		{
			case -1:
				$Forum_Controller_Show->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('error')
						->value('Длина темы и сообщения должна быть не менее 2-х символов.')
				)
				->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('error_post_title')
						->value($post_title)
				)->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('error_post_text')
						->value($post_text)
				);
			break;
			case -2:
				$page = ceil($oForum_Topic->Forum_Topic_Posts->getCount() / $oForum->posts_on_page);
				$path .= $page > 1 ? "page-{$page}/" : '';

				?>
				<h1>У Вас недостаточно прав для совершения операции или Вы слишком часто добавляете сообщения</h1>
				<p> Через 3 секунды Вы вернетесь в форум.</p>
				<p>Если Вы не хотите ждать, перейдите по <a href="<?php echo $path?>">ссылке</a>.</p>
				<script type="text/javascript">setTimeout(function(){ location = '<?php echo $path?>' }, 3000);</script>
				<?php
				return ;

			case -3:
				$Forum_Controller_Show->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('error')
						->value('Вы неверно ввели число подтверждения отправки формы!')
				)
				->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('error_post_title')
						->value($post_title)
				)->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('error_post_text')
						->value($post_text)
				);
			break;

			default:
				// Antispam
				if (Core::moduleIsActive('antispam'))
				{
					$Antispam_Controller = new Antispam_Controller();
					$bAntispamAnswer = $Antispam_Controller
						->addText($post_title)
						->addText($post_text)
						->execute();
				}
				else
				{
					$bAntispamAnswer = TRUE;
				}

				if ($bAntispamAnswer)
				{
					// Добавление сообщения
					if (!$Forum_Controller_Show->editPost)
					{
						$oForum_Topic_Post = Core_Entity::factory('Forum_Topic_Post');
						$oForum_Topic_Post->siteuser_id = !is_null($oSiteuser) ? $oSiteuser->id : 0;
						$oForum_Topic_Post->subject = $post_title;
						$oForum_Topic_Post->text = $post_text;
						$oForum_Topic->add($oForum_Topic_Post);

						// Пересчитываем количество сообщений
						if (!is_null($oSiteuser))
						{
							$oForum_Siteuser_Count = $Forum_Controller_Show->getCountMessageProperty();
							$oForum_Siteuser_Count->count = $oForum_Siteuser_Count->count + 1;
							$oForum_Siteuser_Count->save();
						}

						$oForum_Topic->clearEntities();
						$oForum_Category
							->clearEntities()
							->showXmlModerators(TRUE);

						$oForum->clearEntities()
							->addEntity(
								$oForum->Site->clearEntities()->showXmlAlias()
							)
							->addEntity(
								$oForum_Category->addEntity(
									$oForum_Topic
										->showXmlFirstPost(TRUE)
										->addEntity(Core::factory('Core_Xml_Entity')
											->name('new')
											->addEntity(
												$oForum_Topic_Post->clearEntities()->showXmlSiteuser()
											)
										)
								)
							);

						$sXml = $oForum->getXml();

						$mail_text = Xsl_Processor::instance()
							->xml($sXml)
							->xsl($Forum_Controller_Show->addMessageUserNotificationXsl)
							->process();

						$aEmails = array();

						// Отправка писем всем подписчикам темы
						$aForum_Topic_Subscribers = $oForum_Topic->Forum_Topic_Subscribers->findAll();

						foreach($aForum_Topic_Subscribers as $oForum_Topic_Subscriber)
						{
							$oSubscriber = Core_Entity::factory('Siteuser', $oForum_Topic_Subscriber->siteuser_id);

							// Пользователь не является куратором категории
							if ($oSubscriber->email != $oForum_Category->email)
							{
								$aEmails[] = $oSubscriber->email;
							}
						}

						Core_Session::close();

						// Отправляем письма подписчикам
						if (count($aEmails))
						{
							Core_Mail::instance()
								->clear()
								->header('Bcc', implode(',', $aEmails))
								->from(EMAIL_TO)
								->subject(Core::_('Forum.add_post'))
								->message(trim($mail_text))
								->contentType('text/plain')
								->header('X-HostCMS-Reason', 'Forum')
								->send();
						}

						Core_Session::start();

						// Отправляем письмо куратору форума
						// addMessageAdminNotificationXsl

						if (strlen($oForum_Category->email))
						{
							$mail_text = Xsl_Processor::instance()
								->xml($sXml)
								->xsl($Forum_Controller_Show->addMessageAdminNotificationXsl)
								->process();

							$aEmails = array_map('trim', explode(',', $oForum_Category->email));

							if (count($aEmails))
							{
								$oCore_Mail = Core_Mail::instance()
									->clear()
									->to(array_shift($aEmails))
									->from(EMAIL_TO)
									->subject(Core::_('Forum.add_post'))
									->message(trim($mail_text))
									->contentType('text/plain')
									->header('X-HostCMS-Reason', 'Forum');

								count($aEmails)
									&& $oCore_Mail->header('Bcc', implode(',', $aEmails));

								$oCore_Mail->send();
							}
						}

						// Событийная индексация
						if (Core::moduleIsActive('search'))
						{
							// $oForum_Topic->indexing() возвращает массив с страницами темы
							Search_Controller::indexingSearchPages($oForum_Topic->indexing());
						}

						$page = ceil($oForum_Topic->Forum_Topic_Posts->getCount() / $oForum->posts_on_page);
						$path .= $page > 1 ? "page-{$page}/" : '';

						?>
						<h1>Добавление сообщения</h1>
						<p>Сообщение успешно добавлено. Через 3 секунды Вы вернетесь в форум.</p>
						<p>Если Вы не хотите ждать, перейдите по <a href="<?php echo $path?>">ссылке</a>.</p>
						<script type="text/javascript">setTimeout(function(){ location = '<?php echo $path?>' }, 3000);</script>
						</script>
						<?php
					}
					else // Редактирование сообщения
					{
						$oForum_Topic->clearEntities();
						$oForum_Topic_Post->clearEntities()->showXmlSiteuser();

						$oForum_Topic_Post_Old = clone $oForum_Topic_Post;

						$oForum_Topic_Post->subject = $post_title;
						$oForum_Topic_Post->text = $post_text;
						$oForum_Topic_Post->save();

						$oForum_Topic
							->showXmlFirstPost(TRUE)
							->addEntity(Core::factory('Core_Xml_Entity')
								->name('old')
								->addEntity($oForum_Topic_Post_Old)
							)->addEntity(Core::factory('Core_Xml_Entity')
								->name('new')
								->addEntity($oForum_Topic_Post)
							);

						$oForum_Category
							->clearEntities()
							->showXmlModerators(TRUE);

						$oForum->clearEntities()
							->addEntity(
								$oForum->Site->clearEntities()->showXmlAlias()
							)
							->addEntity($oSiteuser)
							->addEntity(
								$oForum_Category->addEntity($oForum_Topic)
							);

						$sXml = $oForum->getXml();

						$mail_text = Xsl_Processor::instance()
							->xml($sXml)
							->xsl($Forum_Controller_Show->editMessageUserNotificationXsl)
							->process();

						$aEmails = array();

						// Отправка писем всем подписчикам темы
						$aForum_Topic_Subscribers = $oForum_Topic->Forum_Topic_Subscribers->findAll();

						foreach($aForum_Topic_Subscribers as $oForum_Topic_Subscriber)
						{
							$oSubscriber = Core_Entity::factory('Siteuser', $oForum_Topic_Subscriber->siteuser_id);

							// Пользователь не является куратором категории
							if ($oSubscriber->email != $oForum_Category->email)
							{
								$aEmails[] = $oSubscriber->email;
							}
						}

						Core_Session::close();

						// Отправляем письма подписчикам
						if (count($aEmails))
						{
							// Отправляем письмо подписчику
							Core_Mail::instance()
								->clear()
								->header('Bcc', implode(',', $aEmails))
								->from(EMAIL_TO)
								->subject(Core::_('Forum.edit_post'))
								->message(trim($mail_text))
								->contentType('text/plain')
								->header('X-HostCMS-Reason', 'Forum')
								->send();
						}

						Core_Session::start();

						// Отправляем письмо куратору категории
						if (strlen($oForum_Category->email))
						{
							$mail_text = Xsl_Processor::instance()
								->xml($sXml)
								->xsl($Forum_Controller_Show->editMessageAdminNotificationXsl)
								->process();

							$aEmails = array_map('trim', explode(',', $oForum_Category->email));

							if (count($aEmails))
							{
								$oCore_Mail = Core_Mail::instance()
									->clear()
									->to(array_shift($aEmails))
									->from(EMAIL_TO)
									->subject(Core::_('Forum.edit_post'))
									->message(trim($mail_text))
									->contentType('text/plain')
									->header('X-HostCMS-Reason', 'Forum');

								count($aEmails)
									&& $oCore_Mail->header('Bcc', implode(',', $aEmails));

								$oCore_Mail->send();
							}
						}

						$page_count = Core_Array::getPost('current_page')
							? 'page-' . intval(Core_Array::getPost('current_page')) . '/'
							: '';

						// Редактирование выполнено
						$path = '/' . $oForum->Structure->path . '/'  . $Forum_Controller_Show->category . '/' . $Forum_Controller_Show->topic . '/' . $page_count;
						?>
						<h1>Редактирование сообщения</h1>
						<p>Сообщение успешно отредактированно. Через 3 секунды Вы вернетесь в форум.</p>
						<p>Если Вы не хотите ждать, перейдите по <a href="<?php echo $path?>">ссылке</a>.</p>
						<script type="text/javascript">setTimeout(function(){ location = '<?php echo $path?>' }, 3000);</script>
						<?php
					}
				}
				else
				{
					?>
					<h1>Добавление сообщения запрещено!</h1>
					<p>Через 3 секунды Вы вернетесь в форум.</p>
					<p>Если Вы не хотите ждать, перейдите по <a href="<?php echo $path?>">ссылке</a>.</p>
					<script type="text/javascript">setTimeout(function(){ location = '<?php echo $path?>' }, 3000);</script>
					<?php
				}
				return;
		}
	}

	// Показ формы редактирования сообщения
	if (($show_form_edit_post = $Forum_Controller_Show->editPost /*($forum_topic_post_id = Core_Array::getRequest('edit_post_id', 0))*/
		&& ($oForum_Topic_Post = Core_Entity::factory('Forum_Topic_Post', $Forum_Controller_Show->editPost))
		&& $oForum_Topic_Post->Forum_Topic->id == $Forum_Controller_Show->topic)
		&& $oForum_Topic_Post->hasEditPermission($oSiteuser)
		)
	{
		$Forum_Controller_Show->post($Forum_Controller_Show->editPost);
		$xslName = Core_Array::get(Core_Page::instance()->libParams, 'editMessageXsl');
	}
	elseif($show_form_edit_post)
	{
		$Forum_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('error')
				->value('Время, втечение которого Вы могли редактировать свои сообщения истекло.')
		);
	}

}
else
{
	// Список групп форума
	$xslName = Core_Array::get(Core_Page::instance()->libParams, 'forumXsl');
}

$Forum_Controller_Show
	->xsl(
		Core_Entity::factory('Xsl')->getByName($xslName)
	)
	->show();