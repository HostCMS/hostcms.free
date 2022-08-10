<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Controller
 *
 * @package HostCMS
 * @subpackage User
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Controller
{
	/**
	 * Show popover
	 * @param object $object
	 * @param array $args
	 * @param array $options
	 */
	static public function onAfterShowContentPopover($object, $args, $options)
	{
		//$windowId = $object->getWindowId();
		$windowId = $options[0]->getWindowId();

		?><script>
		$('#<?php echo $windowId?> [data-popover="hover"]').showUserPopover('<?php echo $windowId?>');
		</script><?php
	}
}