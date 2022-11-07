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
		$windowId = $options[0]->getWindowId();

		?><script>
		$('#<?php echo $windowId?> [data-popover="hover"]').showUserPopover('<?php echo $windowId?>');
		</script><?php
	}

	/**
	 * Show popover
	 * @param object $object
	 * @param array $args
	 * @param array $options
	 */
	static public function onAfterRedeclaredPrepareForm($controller, $args)
	{
		list($object, $Admin_Form_Controller) = $args;

		$windowId = $Admin_Form_Controller->getWindowId();

		$controller->issetTab('main')
			&& $controller->getTab('main')->add(Admin_Form_Entity::factory('Script')->value("$('#{$windowId} [data-popover=\"hover\"]').showUserPopover('{$windowId}');"));
	}
}