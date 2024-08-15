<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Delete
 *
 * @package HostCMS
 * @subpackage Trash
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Trash_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		$oAdmin_Form_Controller = $this->getController();

		$iDelay = 1;
		$iMaxTime = (!defined('DENY_INI_SET') || !DENY_INI_SET)
			? ini_get('max_execution_time')
			: 25;

		$iMaxTime > 30 && $iMaxTime = 30;

		$timeout = Core::getmicrotime();

		$iCount = 0;

		$offset = Core_Array::getGet('offset', 0, 'int');
		$limit = 100;

		do {
			$iDeleted = $this->_object->chunkDelete($offset, $limit);

			$iCount += $iDeleted;

			$offset += ($limit - $iDeleted);

			if (Core::getmicrotime() - $timeout + 3 > $iMaxTime)
			{
				break;
			}

		} while ($iDeleted);

		$bRedirect = $iCount > 0;

		if ($bRedirect)
		{
			Core_Message::show(Core::_('Trash.deleted_elements', $iCount));

			?>
			<script type="text/javascript">
			function set_location()
			{
				<?php echo $oAdmin_Form_Controller
					//->addAdditionalParam('secret_csrf', Core_Security::getCsrfToken())
					->getAdminActionLoadAjax(
						array(
							'path' => $oAdmin_Form_Controller->getPath(),
							'action' => 'delete',
							'datasetKey' => 0,
							'datasetValue' => $this->_object->id,
							'additionalParams' => "offset={$offset}&secret_csrf=" . Core_Security::getCsrfToken()
						)
					)?>
			}
			setTimeout('set_location()', <?php echo $iDelay * 1000?>);
			</script><?php
		}
		else
		{
			$oAdmin_Form_Controller->additionalParams('');

			Core_Message::show(Core::_('Trash.deleted_complete'));

			Core_Log::instance()->clear()
				->status(Core_Log::$SUCCESS)
				->write('All items have been completely deleted from Trash');
		}
	}
}