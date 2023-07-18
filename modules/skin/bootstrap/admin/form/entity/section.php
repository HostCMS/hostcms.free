<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Entity_Section extends Skin_Default_Admin_Form_Entity_Section
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		// Add label propery
		$this->_allowedProperties[] = 'opened';

		$this->_skipProperties[] = 'opened';

		parent::__construct();

		$this->opened(TRUE);
	}

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
						<a class="accordion-toggle<?php echo $this->opened ? '' : ' collapsed'?>" data-toggle="collapse" data-parent="#<?php echo htmlspecialchars((string) $this->id)?>" href="#collapse<?php echo htmlspecialchars((string) $this->id)?>">
							<?php echo $this->caption?>
						</a>
					</h4>
				</div>
				<div id="collapse<?php echo htmlspecialchars((string) $this->id)?>" class="panel-collapse collapse<?php echo $this->opened ? ' in' : ''?>">
					<div class="panel-body">
						<?php $this->executeChildren()?>
					</div>
				</div>
			</div>
		</div><?php
	}
}