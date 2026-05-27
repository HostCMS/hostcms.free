<?php
/**
 * Crm notes.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'crm');

// File download
if (Core_Array::getGet('crm_note_attachment_id'))
{
	$oCrm_Note_Attachment = Core_Entity::factory('Crm_Note_Attachment')->getById(Core_Array::getGet('crm_note_attachment_id', 0, 'int'));

	if (!is_null($oCrm_Note_Attachment))
	{
		$bAvailable = FALSE;
		$event_id = Core_Array::getGet('event_id', 0, 'int');
		$lead_id = Core_Array::getGet('lead_id', 0, 'int');
		$crm_project_id = Core_Array::getGet('crm_project_id', 0, 'int');
		$deal_id = Core_Array::getGet('deal_id', 0, 'int');
		$siteuser_id = Core_Array::getGet('siteuser_id', 0, 'int');
		$timeline_id = Core_Array::getGet('timeline_id', 0, 'int');

		if ($event_id)
		{
			$oObject = Core_Entity::factory('Event')->getById($event_id);
			$bAvailable = !is_null($oObject) && $oObject->id == $oCrm_Note_Attachment->Crm_Note->Event_Crm_Note->event_id;
		}
		elseif ($lead_id)
		{
			$oObject = Core_Entity::factory('Lead')->getById($lead_id);
			$bAvailable = !is_null($oObject) && $oObject->id == $oCrm_Note_Attachment->Crm_Note->Lead_Crm_Note->lead_id;
		}
		elseif ($crm_project_id)
		{
			$oObject = Core_Entity::factory('Crm_Project')->getById($crm_project_id);
			$bAvailable = !is_null($oObject) && $oObject->id == $oCrm_Note_Attachment->Crm_Note->Crm_Project_Crm_Note->crm_project_id;
		}
		elseif ($deal_id)
		{
			$oObject = Core_Entity::factory('Deal')->getById($deal_id);
			$bAvailable = !is_null($oObject) && $oObject->id == $oCrm_Note_Attachment->Crm_Note->Deal_Crm_Note->deal_id;
		}
		elseif ($siteuser_id)
		{
			$oObject = Core_Entity::factory('Siteuser')->getById($siteuser_id);
			$bAvailable = !is_null($oObject) && $oObject->id == $oCrm_Note_Attachment->Crm_Note->Siteuser_Crm_Note->siteuser_id;
		}
		elseif ($timeline_id)
		{
			$oObject = Core_Entity::factory('Timeline')->getById($timeline_id);
			$bAvailable = !is_null($oObject) && $oObject->id == $oCrm_Note_Attachment->Crm_Note->Timeline->id;
		}

		if ($bAvailable)
		{
			$path = $oObject->getModelName() == 'siteuser'
				? $oObject->getDirPath()
				: $oObject->getPath();

			$oCrm_Note_Attachment
				->setDir($path);

			$filePath = is_null(Core_Array::getGet('preview'))
				? $oCrm_Note_Attachment->getFilePath()
				: $oCrm_Note_Attachment->getSmallFilePath();

			if (!is_null($filePath))
			{
				$content_disposition = !is_null(Core_Array::getGet('download'))
					? array('content_disposition' => 'attachment')
					: array();

				Core_File::download($filePath, $oCrm_Note_Attachment->file_name, $content_disposition);
			}
			else
			{
				throw new Core_Exception('Wrong file path');
			}
		}
	}
	else
	{
		throw new Core_Exception('Access denied');
	}

	exit();
}

if (Core_Array::getPost('showCrmNoteAttachment'))
{
	$aJSON = array(
		'html' => ''
	);

	$crm_note_attachment_id = Core_Array::getPost('crm_note_attachment_id', 0, 'int');

	$oCrm_Note_Attachment = Core_Entity::factory('Crm_Note_Attachment')->getById($crm_note_attachment_id);
	if (!is_null($oCrm_Note_Attachment))
	{
		$params = Core_Array::getPost('params', 0, 'strval');
		$src = Admin_Form_Controller::correctBackendPath('/{admin}/crm/note/index.php?&crm_note_attachment_id=') . $oCrm_Note_Attachment->id . '&' . $params;

		ob_start();
		?>
		<div class="modal fade" id="crmNoteAttachmentModal<?php echo $oCrm_Note_Attachment->id?>" tabindex="-1" role="dialog" aria-labelledby="crmNoteAttachmentModalLabel">
			<div class="modal-dialog " role="document">
				<div class="modal-content no-padding-bottom">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<a target="_blank" href="<?php echo $src . '&download'?>" class="palegreen btn-sm pull-right">
							<i class="fa-solid fa-download"></i>
						</a>
						<h4 class="modal-title"><?php echo htmlspecialchars($oCrm_Note_Attachment->file_name)?></h4>
					</div>
					<div class="modal-body padding-bottom-15">
						<img style="max-width: 100%;" src="<?php echo $src?>"/>
					</div>
					<!-- <div class="modal-footer">
						<a target="_blank" href="<?php echo $src . '&download'?>" class="btn btn-palegreen btn-sm">
							<i class="fa-solid fa-download"></i> <?php echo Core::_('Crm_Note.download')?>
						</a>
					</div> -->
				</div>
			</div>
		</div>
		<?php
		$aJSON['html'] = ob_get_clean();
	}

	Core::showJson($aJSON);
}

if (Core_Array::getPost('sendReaction'))
{
	$aJSON = array(
		'success' => FALSE
	);

	$crm_note_id = Core_Array::getPost('crm_note_id', 0, 'int');
	$user_id = Core_Array::getPost('user_id', 0, 'int');
	$emoji = Core_Array::getPost('emoji', '', 'trim');
	$action = Core_Array::getPost('action', '', 'trim');

	if (!$crm_note_id || !$user_id || $emoji == '' || $action == '')
	{
		$aJSON['error'] = 'Переданы не все данные';
		Core::showJson($aJSON);
	}

	$oCrm_Note = Core_Entity::factory('Crm_Note')->getById($crm_note_id, FALSE);
	if (!is_null($oCrm_Note))
	{
		$oCrm_Note_Reaction = $oCrm_Note->Crm_Note_Reactions->getByUser_id($user_id, FALSE);

		switch ($action)
		{
			case 'add':
				if (is_null($oCrm_Note_Reaction))
				{
					$oCrm_Note_Reaction = Core_Entity::factory('Crm_Note_Reaction');
					$oCrm_Note_Reaction->crm_note_id = $oCrm_Note->id;
					$oCrm_Note_Reaction->user_id = $user_id;
					$oCrm_Note_Reaction->emoji = $emoji;
					$oCrm_Note_Reaction->save();

					$aJSON['success'] = TRUE;
				}
			break;
			case 'remove':
				if (!is_null($oCrm_Note_Reaction))
				{
					$oCrm_Note_Reaction->delete();

					$aJSON['success'] = TRUE;
				}
			break;
			case 'replace':
				$oCrm_Note_Reaction->delete();

				$oCrm_Note_Reaction = Core_Entity::factory('Crm_Note_Reaction');
				$oCrm_Note_Reaction->crm_note_id = $oCrm_Note->id;
				$oCrm_Note_Reaction->user_id = $user_id;
				$oCrm_Note_Reaction->emoji = $emoji;
				$oCrm_Note_Reaction->save();

				$aJSON['success'] = TRUE;
			break;
		}
	}

	Core::showJson($aJSON);
}

if (Core_Array::getPost('toggleSubscription'))
{
	$aJSON = array(
		'success' => FALSE
	);

	$crm_note_id = Core_Array::getPost('crm_note_id', 0, 'int');
	$user_id = Core_Array::getPost('user_id', 0, 'int');
	$action = Core_Array::getPost('action', '', 'trim');

	if (!$crm_note_id || !$user_id || $action == '')
	{
		$aJSON['error'] = 'Переданы не все данные';
		Core::showJson($aJSON);
	}

	$oCrm_Note = Core_Entity::factory('Crm_Note')->getById($crm_note_id, FALSE);
	if (!is_null($oCrm_Note))
	{
		$oTimeline = $oCrm_Note->Timeline;

		if ($oTimeline)
		{
			switch ($action)
			{
				case 'subscribe':
					$oTimeline_User = Core_Entity::factory('Timeline_User');
					$oTimeline_User->user_id = $user_id;
					$oTimeline->add($oTimeline_User);

					$aJSON['success'] = TRUE;
				break;
				case 'unsubscribe':
					$oTimeline_User = $oTimeline->Timeline_Users->getByUser_id($user_id);

					if (!is_null($oTimeline_User))
					{
						$oTimeline_User->delete();

						$aJSON['success'] = TRUE;
					}
				break;
			}
		}
	}

	Core::showJson($aJSON);
}

if (Core_Array::getPost('getReactionUsers'))
{
	$aJSON = array(
		'success' => FALSE,
		'html' => ''
	);

	$crm_note_id = Core_Array::getPost('crm_note_id', 0, 'int');

	if ($crm_note_id)
	{
		$oCrm_Note = Core_Entity::factory('Crm_Note')->getById($crm_note_id, FALSE);

		if (!is_null($oCrm_Note))
		{
			$emoji = Core_Array::getPost('emoji', '', 'trim');

			$aJSON = array(
				'success' => TRUE,
				'html' => Crm_Note_Controller::getReactionUsers($oCrm_Note, $emoji)
			);
		}
	}

	Core::showJson($aJSON);
}

if (Core_Array::getPost('sendAiRequest'))
{
	$aJSON = array(
		'success' => FALSE,
		'html' => ''
	);

	$messageId = Core_Array::getPost('messageId', 0, 'int');

	if ($messageId)
	{
		$model = Core_Array::getPost('model', '', 'trim');

		switch ($model)
		{
			case 'crm_note':
				$oEntity = Core_Entity::factory('Crm_Note')->getById($messageId, FALSE);
				if (!is_null($oEntity))
				{
					$text = $oEntity->text;
				}
			break;
			case 'helpdesk_message':
				$oEntity = Core_Entity::factory('Helpdesk_Message')->getById($messageId, FALSE);

				if (!is_null($oEntity))
				{
					$text = $oEntity->message;
				}
			break;
			default:
				$oEntity = NULL;
				$text = '';
		}

		if (Core::moduleIsActive('ai') && !is_null($oEntity) && $text != '')
		{
			$ai_prompt_id = Core_Array::getPost('ai_prompt_id', 0, 'int');

			$oAi_Prompt = Core_Entity::factory('Ai_Prompt')->getById($ai_prompt_id, FALSE);

			if (!is_null($oAi_Prompt) && $oAi_Prompt->model == $model)
			{
				$oAi = Core_Entity::factory('Ai')->getDefault();

				$oAi_Controller = Ai_Controller::instance($oAi->driver);
				$oAi_Controller->setAi($oAi);
				$oAi_Controller->setModel($oAi->model);

				$response = $oAi_Controller->query($text);

				if (!is_null($response))
				{
					$aJSON = array(
						'success' => TRUE,
						'html' => Ai_Markdown::parse($response)
					);
				}
				else
				{
					$aJSON['error'] = Core::_('Crm_Note.wrong_answer');
					Core::showJson($aJSON);
				}
			}
			else
			{
				$aJSON['error'] = Core::_('Crm_Note.empty_prompt');
				Core::showJson($aJSON);
			}
		}
		else
		{
			$aJSON['error'] = Core::_('Crm_Note.empty_object');
			Core::showJson($aJSON);
		}
	}

	Core::showJson($aJSON);
}

/*if (Core_Array::getPost('sendUploadVideo'))
{
	$action = Core_Array::getPost('action', '', 'trim');

	switch ($action)
	{
		case 'upload_chunk':
			$fileId = preg_replace('/[^a-zA-Z0-9_]/', '', Core_Array::getPost('file_id')); // Защита имени файла

			if (!$fileId || !isset($_FILES['video_chunk'])) {
				echo json_encode(['success' => false, 'error' => 'Нет данных']);
				exit;
			}

			// Директория для временных файлов HostCMS
			$tempDir = CMS_FOLDER . 'hostcmsfiles/tmp/videos/';
			if (!is_dir($tempDir)) {
				mkdir($tempDir, 0755, true);
			}

			$tempFilePath = $tempDir . $fileId . '.webm';

			// Читаем чанк и ДОПИСЫВАЕМ (FILE_APPEND) в конец файла
			$chunkData = file_get_contents($_FILES['video_chunk']['tmp_name']);
			file_put_contents($tempFilePath, $chunkData, FILE_APPEND);

			echo json_encode(['success' => true]);
			exit;
		break;
		case 'finalize_video':
			$fileId = preg_replace('/[^a-zA-Z0-9_]/', '', Core_Array::getPost('file_id'));

			// Получаем расширение от JS (оставляем только безопасные варианты)
			$ext = Core_Array::getPost('extension') === 'mp4' ? 'mp4' : 'webm';

			$tempDir = CMS_FOLDER . 'hostcmsfiles/tmp/videos/';
			$finalDir = CMS_FOLDER . 'upload/crm_videos/' . date('Y/m/');

			if (!is_dir($finalDir)) {
				mkdir($finalDir, 0755, true);
			}

			// Временный файл мы сохраняли как .webm, так его и ищем
			$tempFile = $tempDir . $fileId . '.webm';

			// А вот финальный файл сохраняем с тем расширением, которое реально прислал браузер
			$finalFile = $finalDir . $fileId . '.' . $ext;

			// Относительный URL для вставки в редактор
			$videoUrl = '/upload/crm_videos/' . date('Y/m/') . $fileId . '.' . $ext;

			if (!file_exists($tempFile)) {
				echo json_encode(['success' => false, 'error' => 'Файл не найден на сервере']);
				exit;
			}

			// Просто перемещаем файл и меняем ему расширение на правильное
			if (rename($tempFile, $finalFile)) {
				echo json_encode(['success' => true, 'video_url' => $videoUrl]);
			} else {
				echo json_encode(['success' => false, 'error' => 'Не удалось сохранить видеофайл']);
			}
			exit;
		break;
	}
}*/