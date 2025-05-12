<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_View
 *
 * @package HostCMS
 * @subpackage Dms
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Event_View extends Skin_Bootstrap_Admin_Form_Controller_List
{
	/**
	 * Top menu bar
	 */
	protected function _topMenuBar()
	{
		?><div class="table-toolbar">
			<?php
			Core_Event::notify('Admin_Form_Controller.onBeforeShowMenu', $this->_Admin_Form_Controller, array($this));
			?>
			<?php $this->_Admin_Form_Controller->showFormMenus()?>
			<div class="btn-group"><?php echo Event_Controller::showCrmProjectFilter()?></div>
			<div class="table-toolbar-right pull-right">
				<?php $this->showPageSelector && $this->_pageSelector()?>
				<?php $this->showChangeViews && $this->_Admin_Form_Controller->showChangeViews()?>
			</div>
			<div class="clear"></div>
		</div>
		<?php
	}
}