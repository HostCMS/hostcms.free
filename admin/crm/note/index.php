<?php
/**
 * Crm notes.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		if ($bAvailable)
		{
			$oCrm_Note_Attachment
				->setDir($oObject->getPath());

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
		$src = '/admin/crm/note/index.php?&crm_note_attachment_id=' . $oCrm_Note_Attachment->id . '&' . $params;

		ob_start();
		?>
		<div class="modal fade" id="crmNoteAttachmentModal<?php echo $oCrm_Note_Attachment->id?>" tabindex="-1" role="dialog" aria-labelledby="crmNoteAttachmentModalLabel">
			<div class="modal-dialog " role="document">
				<div class="modal-content no-padding-bottom">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><?php echo htmlspecialchars($oCrm_Note_Attachment->file_name)?></h4>
					</div>
					<div class="modal-body">
						<img style="max-width: 100%;" src="<?php echo $src?>"/>
					</div>
					<div class="modal-footer">
						<a target="_blank" href="<?php echo $src . '&download'?>" class="btn btn-palegreen btn-sm">
							<i class="fa fa-download"></i> <?php echo Core::_('Crm_Note.download')?>
						</a>
					</div>
				</div>
			</div>
		</div>
		<?php
		$aJSON['html'] = ob_get_clean();
	}

	Core::showJson($aJSON);
}