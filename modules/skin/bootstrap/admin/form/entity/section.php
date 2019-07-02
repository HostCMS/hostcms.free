<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Entity_Section extends Skin_Default_Admin_Form_Entity_Section {

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$this->class = $this->class . ' panel panel-default';
		$aAttr = $this->getAttrsString();

		?><div class="panel-group accordion">
			<div <?php echo implode(' ', $aAttr)?>>
				<div class="panel-heading">
					<h4 class="panel-title">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#<?php echo $this->id?>" href="#collapse<?php echo $this->id?>">
							<?php echo $this->caption ?>
						</a>
					</h4>
				</div>
				<div id="collapse<?php echo $this->id?>" class="panel-collapse collapse in">
					<div class="panel-body">
						<?php $this->executeChildren()?>
					</div>
				</div>
			</div>
		</div><?php
	}
}