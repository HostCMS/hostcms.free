<?php
if (!Core::moduleIsActive('helpdesk'))
{
	?>
	<h1>Системы обработки запросов &madsh; HelpDesk</h1>
	<p>Функционал недоступен, приобретите более старшую редакцию.</p>
	<p>Модуль &laquo;<a href="http://www.hostcms.ru/hostcms/modules/helpdesk/">Системы обработки запросов</a>&raquo; доступен в редакции &laquo;<a href="http://www.hostcms.ru/hostcms/editions/corporation/">Корпорация</a>&raquo;.</p>
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

$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

if (is_null($oSiteuser))
{
	return ;
}

$Helpdesk_Controller_Show = Core_Page::instance()->object;

$oHelpdesk = $Helpdesk_Controller_Show->getEntity();

if ($Helpdesk_Controller_Show->worktime)
{
	$xslName = Core_Array::get(Core_Page::instance()->libParams, 'workingHoursXsl');
	$Helpdesk_Controller_Show->criticalityLevels(FALSE);
}
else
{
	$xslName = $Helpdesk_Controller_Show->ticket
		? Core_Array::get(Core_Page::instance()->libParams, 'ticketXsl')
		: Core_Array::get(Core_Page::instance()->libParams, 'helpdeskXsl');
	$Helpdesk_Controller_Show->criticalityLevels(TRUE);
}

$Helpdesk_Controller_Show->addEntity(
	Core::factory('Core_Xml_Entity')
		->name('ТекущаяКатегория')
		->value($Helpdesk_Controller_Show->category)
);

$Helpdesk_Controller_Show->ticketsForbiddenTags(array('datetime'));

// Добавление тикета
if (Core_Array::getPost('add_ticket'))
{
	$oHelpdesk_Ticket = Core_Entity::factory('Helpdesk_Ticket');
	$oHelpdesk_Ticket->helpdesk_criticality_level_id = intval(Core_Array::getPost('criticality_level_id'));
	$oHelpdesk_Ticket->helpdesk_category_id = intval(Core_Array::getPost('helpdesk_category_id'));
	$oHelpdesk_Ticket->siteuser_id = $oSiteuser->id;
	$oHelpdesk_Ticket->email = $oSiteuser->email;
	$oHelpdesk_Ticket->helpdesk_account_id = $oHelpdesk->helpdesk_account_id;
	$oHelpdesk_Ticket->datetime = date('Y-m-d H:i:s');
	$oHelpdesk_Ticket->notify_change_status = 0;
	$oHelpdesk_Ticket->send_email = Core_Array::getPost('send_email', 0) ? 1 : 0;
	$oHelpdesk_Ticket->source = 0;
	$oHelpdesk->add($oHelpdesk_Ticket);

	$helpdesk_ticket_mask = $oHelpdesk->ticket_mask != ''
		? $oHelpdesk->ticket_mask
		// Маска формирования имени тикета по умолчанию
		: Core::_('helpdesk.template_ticket_name');

	$oHelpdesk_Ticket->number = sprintf($helpdesk_ticket_mask, $oHelpdesk_Ticket->id);
	$oHelpdesk_Ticket->save();

	$oHelpdesk_Message = Core_Entity::factory('Helpdesk_Message');
	$oHelpdesk_Message->helpdesk_status_id = $oHelpdesk_Ticket->Helpdesk->helpdesk_status_new_id;
	$oHelpdesk_Message->subject = Core_Str::stripTags(strval(Core_Array::getPost('subject')));
	$oHelpdesk_Message->message = strval(Core_Array::getPost('text'));
	$oHelpdesk_Message->datetime = $oHelpdesk_Ticket->datetime;
	$oHelpdesk_Message->modification_datetime = $oHelpdesk_Ticket->datetime;
	$oHelpdesk_Message->inbox = 1;
	$oHelpdesk_Message->type = $oHelpdesk->message_type;
	$oHelpdesk_Message->grade = 0;
	$oHelpdesk_Message->sorting = 1;
	$oHelpdesk_Ticket->add($oHelpdesk_Message);
	$oHelpdesk_Ticket->recountAnswerDateTime();

	$aAttachments = Core_Array::getFiles('attachment', array());

	if (is_array($aAttachments) && isset($aAttachments['name']))
	{
		if (!is_null($oHelpdesk_Message))
		{
			$iCount = count($aAttachments['name']);

			for ($i = 0; $i < $iCount; $i++)
			{
				$aFile = array(
					'name' => $aAttachments['name'][$i],
					'tmp_name' => $aAttachments['tmp_name'][$i],
					'size' => $aAttachments['size'][$i]
				);

				if(intval($aFile['size']) > 0)
				{
					//if (Core_File::isValidExtension($aFile['name'], Core::$mainConfig['availableExtension']))
					//{
						$oHelpdesk_Attachment = Core_Entity::factory('Helpdesk_Attachment');
						$oHelpdesk_Attachment->helpdesk_message_id = $oHelpdesk_Message->id;
						$oHelpdesk_Attachment->saveFile($aFile['tmp_name'], $aFile['name']);
					//}
				}
			}
		}
	}

	// Отправлять отчет о получении запроса.
	if ($oHelpdesk->notify)
	{
		$oHelpdesk_Message->sendNotification();

		if ($oHelpdesk_Message->sorting == 1)
		{
			// Поиск и отправка автоматического ответа
			$oHelpdesk_Message->sendAutoAnswer();
		}
	}

	if (Core::moduleIsActive('search'))
	{
		Search_Controller::indexingSearchPages(
			array($oHelpdesk_Message->indexing())
		);
	}

	$path = "./";
	?>
	<h1>Добавление запроса в службу техподдержки</h1>
	<p>Ваш запрос принят. Через 3 секунды Вы вернетесь к списку тикетов.</p>
	<p>Если Вы не хотите ждать, перейдите по <a href="<?php echo $path?>">ссылке</a>.</p>
	<script type="text/javascript">setTimeout(function(){ location = '<?php echo $path?>' }, 3000);</script>
	<?php

	return;
}

// Добавление сообщения
if ($Helpdesk_Controller_Show->ticket && Core_Array::getPost('send_message'))
{
	$oHelpdesk_Ticket = Core_Entity::factory('Helpdesk_Ticket')->find($Helpdesk_Controller_Show->ticket);

	if ($oHelpdesk_Ticket->siteuser_id == $oSiteuser->id)
	{
		$oHelpdesk_Message = Core_Entity::factory('Helpdesk_Message');
		$oHelpdesk_Message->helpdesk_status_id = $oHelpdesk_Ticket->Helpdesk->helpdesk_status_new_id;
		$oHelpdesk_Message->parent_id = intval(Core_Array::getPost('parent_id'));
		$oHelpdesk_Message->subject = Core_Str::stripTags(strval(Core_Array::getPost('message_subject')));
		$oHelpdesk_Message->message = strval(Core_Array::getPost('message_text'));
		$oHelpdesk_Message->datetime = $oHelpdesk_Message->modification_datetime = Core_Date::timestamp2sql(time());
		$oHelpdesk_Message->inbox = 1;
		$oHelpdesk_Message->helpdesk_status_id = $oHelpdesk_Ticket->Helpdesk->helpdesk_status_new_id;
		$oHelpdesk_Message->sorting = $oHelpdesk_Ticket->Helpdesk_Messages->getCount() + 1;
		$oHelpdesk_Message->type = $oHelpdesk_Ticket->Helpdesk->message_type;
		$oHelpdesk_Ticket->add($oHelpdesk_Message);

		$oHelpdesk_Ticket->open();

		$aAttachments = Core_Array::getFiles("attachment", array());
		if (is_array($aAttachments) && isset($aAttachments['name']))
		{
			if (!is_null($oHelpdesk_Message))
			{
				$iCount = count($aAttachments['name']);

				for ($i = 0; $i < $iCount; $i++)
				{
					$aFile = array(
						'name' => $aAttachments['name'][$i],
						'tmp_name' => $aAttachments['tmp_name'][$i],
						'size' => $aAttachments['size'][$i]
					);

					if(intval($aFile['size']) > 0)
					{
						//if (Core_File::isValidExtension($aFile['name'], Core::$mainConfig['availableExtension']))
						//{
							$oHelpdesk_Attachment = Core_Entity::factory('Helpdesk_Attachment');
							$oHelpdesk_Attachment->helpdesk_message_id = $oHelpdesk_Message->id;
							$oHelpdesk_Attachment->saveFile($aFile['tmp_name'], $aFile['name']);
						//}
					}
				}
			}
		}

		if (Core::moduleIsActive('search'))
		{
			Search_Controller::indexingSearchPages(
				array($oHelpdesk_Message->indexing())
			);
		}

		$path = "./";
		?>
		<h1>Добавление сообщения в запрос</h1>
		<p>Ваш запрос принят. Через 3 секунды Вы вернетесь к списку сообщений.</p>
		<p>Если Вы не хотите ждать, перейдите по <a href="<?php echo $path?>">ссылке</a>.</p>
		<script type="text/javascript">setTimeout(function(){ location = '<?php echo $path?>' }, 3000);</script>
		<?php

		return;
	}
}

// Закрытие тикета
if (!is_null($ticket_id = Core_Array::getGet('close_ticket')))
{
	$oHelpdesk_Ticket = Core_Entity::factory('Helpdesk_Ticket', $ticket_id);
	if ($oHelpdesk_Ticket->siteuser_id == $oSiteuser->id)
	{
		$oHelpdesk_Ticket->close();
	}
}

if (!is_null($ticket_id = Core_Array::getGet('open_ticket')))
{
	$oHelpdesk_Ticket = Core_Entity::factory('Helpdesk_Ticket', $ticket_id);
	if ($oHelpdesk_Ticket->siteuser_id == $oSiteuser->id)
	{
		$oHelpdesk_Ticket->open();
	}
}

// Фильтр по статусу тикетов
$ticketStatus = Core_Array::getGet('status');
if (!is_null($ticketStatus) && $ticketStatus != -1)
{
	$Helpdesk_Controller_Show->addEntity(
		Core::factory('Core_Xml_Entity')
			->name('apply_filter')
			->value($ticketStatus)
	);

	$Helpdesk_Controller_Show->helpdeskTickets()
		->queryBuilder()
		->where('open', '=', intval($ticketStatus));
}

$Helpdesk_Controller_Show
	->xsl(
		Core_Entity::factory('Xsl')->getByName($xslName)
	)
	->show();