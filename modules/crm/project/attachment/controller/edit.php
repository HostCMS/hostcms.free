<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Project_Attachment_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Crm_Project_Attachment_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('file_name')
			->addSkipColumn('file')
			->addSkipColumn('datetime')
			;

		if (!$object->id)
		{
			$object->crm_project_id = Core_Array::getGet('crm_project_id');
		}

		return parent::setObject($object);
	}

	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$object = $this->_object;

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		ob_start();
		?>

		<div class="row margin-top-10 dms-document-attachments-dropzone">
			<div class="col-xs-12">
				<div id="dropzone">
					<div class="dz-message needsclick"><i class="fa fa-arrow-circle-o-up"></i> <?php echo Core::_('Admin_Form.upload_file')?></div>
				</div>
			</div>
		</div>

		<script>
			$(function() {
				$("#<?php echo $windowId?> #dropzone").dropzone({
					url: hostcmsBackend + '/crm/project/attachment/index.php?hostcms[action]=uploadFiles&hostcms[checked][0][0]=1&crm_project_id=<?php echo $object->crm_project_id?>',
					parallelUploads: 10,
					maxFilesize: <?php echo Core::$mainConfig['dropzoneMaxFilesize']?>,
					paramName: 'file',
					uploadMultiple: true,
					autoProcessQueue: false,
					autoDiscover: false,
					init: function() {
						var dropzone = this;

						$(".formButtons #action-button-apply").on("click", function(e) {
							e.preventDefault();
							e.stopPropagation();

							if (dropzone.getQueuedFiles().length)
							{
								dropzone.processQueue();
							}
						});
					},
					success : function(file, response){
						$.adminLoad({ path: hostcmsBackend + '/crm/project/entity/index.php', additionalParams: 'crm_project_id=<?php echo $object->crm_project_id?>', windowId: 'id_content' });
						bootbox.hideAll();
					}
				});
			});
		</script>

		<?php

		$oHtml = Admin_Form_Entity::factory('Code')
			->html(ob_get_clean());

		$oMainRow1->add(
			Admin_Form_Entity::factory('Div')
				->class('col-xs-12')
				->add($oHtml)
		);

		return $this;
	}

	/**
	 * Get save button
	 * @return Admin_Form_Entity_Buttons
	 */
	protected function _getSaveButton()
	{
		return NULL;
	}

	/**
	 * Get apply button
	 * @return Admin_Form_Entity_Buttons
	 */
	protected function _getApplyButton()
	{
		$oButton = parent::_getApplyButton();

		$oButton->onclick = '';

		return $oButton;
	}
}