<?php

if (Core::moduleIsActive('helpdesk') && Core::moduleIsActive('siteuser'))
{
	$oHelpdesk = Core_Entity::factory('Helpdesk',
		Core_Array::get(Core_Page::instance()->libParams, 'helpdeskId')
	);

	$Helpdesk_Controller_Show = new Helpdesk_Controller_Show($oHelpdesk);

	$Helpdesk_Controller_Show
		->limit(Core_Array::get(Core_Page::instance()->libParams, 'itemsOnPage'))
		->parseUrl();

	// Текстовая информация для указания номера страницы, например "страница"
	$pageName = Core_Array::get(Core_Page::instance()->libParams, 'page')
		? Core_Array::get(Core_Page::instance()->libParams, 'page')
		: 'страница';

	// Разделитель в заголовке страницы
	$pageSeparator = Core_Array::get(Core_Page::instance()->libParams, 'separator')
		? Core_Page::instance()->libParams['separator']
		: ' / ';

	$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
	if (is_null($oSiteuser))
	{
		return ;
	}

	$aTitle = array($oHelpdesk->name);
	$aDescription = array($oHelpdesk->name);
	$aKeywords = array($oHelpdesk->name);

	// Просмотр прикрепленного файла
	if (($attachment_id = intval(Core_Array::getGet('get_attachment_id')))
		&& Core_Entity::factory('Helpdesk_Ticket', $Helpdesk_Controller_Show->ticket)->siteuser_id == $oSiteuser->id
		&& Core_Entity::factory('Helpdesk_Attachment', $attachment_id)->Helpdesk_Message->Helpdesk_Ticket->id = $Helpdesk_Controller_Show->ticket
	)
	{
		$oHelpdesk_Attachment =	Core_Entity::factory('Helpdesk_Attachment', $attachment_id);
		Core_File::download($oHelpdesk_Attachment->getFilePath(), $oHelpdesk_Attachment->file_name, array('content_disposition' => 'attachment'));
		exit();
	}

	if ($Helpdesk_Controller_Show->category)
	{
		$oHelpdesk_Category = Core_Entity::factory('Helpdesk_Category', $Helpdesk_Controller_Show->category);

		do {
			$aTitle[] = $oHelpdesk_Category->name;
			$aDescription[] = $oHelpdesk_Category->name;
			$aKeywords[] = $oHelpdesk_Category->name;

		} while($oHelpdesk_Category = $oHelpdesk_Category->getParent());
	}

	if ($Helpdesk_Controller_Show->ticket)
	{
		$oHelpdesk_Ticket = Core_Entity::factory('Helpdesk_Ticket', $Helpdesk_Controller_Show->ticket);

		$oHelpdesk_Message = $oHelpdesk_Ticket->Helpdesk_Messages->getFirstMessage();

		if (!is_null($oHelpdesk_Message))
		{
			$aTitle[] = $oHelpdesk_Message->subject;
			$aDescription[] = $oHelpdesk_Message->subject;
			$aKeywords[] = $oHelpdesk_Message->subject;
		}
	}

	if ($Helpdesk_Controller_Show->page)
	{
		array_unshift($aTitle, $pageName . ' ' . ($Helpdesk_Controller_Show->page + 1));
	}

	if (count($aTitle) > 1)
	{
		$aTitle = array_reverse($aTitle);
		$aDescription = array_reverse($aDescription);
		$aKeywords = array_reverse($aKeywords);

		Core_Page::instance()->title(implode($pageSeparator, $aTitle));
		Core_Page::instance()->description(implode($pageSeparator, $aDescription));
		Core_Page::instance()->keywords(implode($pageSeparator, $aKeywords));
	}

	// AJAX-установка оценки
	if (Core_Array::getPost('ajaxGrade'))
	{
		$oHelpdesk_Message = Core_Entity::factory('Helpdesk_Message', intval(Core_Array::getPost('id')));

		if ($oHelpdesk_Message->Helpdesk_Ticket->helpdesk_id == $oHelpdesk->id
			&& $oHelpdesk_Message->Helpdesk_Ticket->siteuser_id == $oSiteuser->id)
		{
			$grade = intval(Core_Array::getPost('value'));
			if ($grade >= 0 && $grade <= 5)
			{
				$oHelpdesk_Message->grade = $grade;
				$oHelpdesk_Message->save();
			}
		}

		echo json_encode(array());
		die();
	}

	Core_Page::instance()->object = $Helpdesk_Controller_Show;
}