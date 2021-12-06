<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Entity_Form extends Skin_Default_Admin_Form_Entity_Form {
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		?><div id="box0">
		<form <?php echo implode(' ', $aAttr) ?>><?php
		$this->executeChildren();
		?></form>
		<?php
		if (!is_null($this->id))
		{
		?><script>$(function() {
			mainFieldChecker.checkAll('<?php echo Core_Str::escapeJavascriptVariable($windowId)?>', "<?php echo htmlspecialchars($this->id)?>"); });
			$.showAutosave($('#<?php echo $windowId?> form.adminForm'));
		</script><?php
		}
		?>
		</div><?php
	}
}