<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Delete
 *
 * @package HostCMS
 * @subpackage Trash
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		$timeout = Core::getmicrotime();

		$iCount = 0;

		do {
			$iDeleted = $this->_object->chunkDelete(100);
			$iCount += $iDeleted;

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
				<?php echo $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'delete', NULL, 0, $this->_object->id)?>
			}
			setTimeout ('set_location()', <?php echo $iDelay * 1000?>);
			</script><?php
		}
		else
		{
			Core_Message::show(Core::_('Trash.deleted_complete'));

			Core_Log::instance()->clear()
				->status(Core_Log::$SUCCESS)
				->write('All items have been completely deleted from Trash');
		}
	}
}