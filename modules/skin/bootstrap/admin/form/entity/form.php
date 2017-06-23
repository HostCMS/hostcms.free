<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Entity_Form extends Skin_Default_Admin_Form_Entity_Form {
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		// Warning: fieldType, fieldMessage, fieldsStatus (ниже !) переделать на JS-класс + заполнение в модели input-а
		?><div id="box0">
		<script type="text/javascript">
		var fieldType = new Array(), fieldMessage = new Array(), fieldsStatus = new Array();
		</script>
		<form <?php echo implode(' ', $aAttr) ?>><?php

		$this->executeChildren();

		?></form>
		<?php
		if (!is_null($this->id))
		{
		?><script type="text/javascript">$(function() { CheckAllField('<?php echo $windowId?>', "<?php echo htmlspecialchars($this->id)?>"); });</script><?php
		}
		?>
		</div><?php
	}
}