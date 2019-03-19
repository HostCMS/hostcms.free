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
		<script>
		fieldType['<?php echo $windowId?>'] = [], fieldMessage['<?php echo $windowId?>'] = [], fieldsStatus['<?php echo $windowId?>'] = [];
		</script>
		<form <?php echo implode(' ', $aAttr) ?>><?php

		$this->executeChildren();

		?></form>
		<?php
		if (!is_null($this->id))
		{
		?><script>$(function() { CheckAllField('<?php echo $windowId?>', "<?php echo htmlspecialchars($this->id)?>"); });</script><?php
		}
		?>
		</div><?php
	}
}