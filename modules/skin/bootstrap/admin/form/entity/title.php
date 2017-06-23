<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Entity_Title extends Skin_Default_Admin_Form_Entity_Title
{
	/**
	 * Executes the business logic.
	 */

	public function execute()
	{
		?><div class="page-header position-relative">
			<div class="header-title">
				<h1>
					<?php echo htmlspecialchars($this->name)?>
				</h1>
			</div>
			<?php
			if (Core_Auth::logged())
			{
			?><div class="header-buttons">
				<a href="#" class="sidebar-toggler">
					<i class="fa fa-arrows-h"></i>
				</a>
				<a href="" id="refresh-toggler" class="refresh">
					<i class="glyphicon glyphicon-refresh"></i>
				</a>
				<a href="#" id="fullscreen-toggler" class="fullscreen">
					<i class="glyphicon glyphicon-fullscreen"></i>
				</a>
			</div><?php
			}
			?>
		</div><?php
	}
}