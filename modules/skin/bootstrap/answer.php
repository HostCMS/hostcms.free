<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Skin.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Skin_Bootstrap_Answer extends Admin_Answer
{
	/**
	 * Send header and HTML answer
	 * @return self
	 */
	protected function _sendHtml()
	{
		$this->_showHeader();

		//echo $this->message;

		?><div id="id_content"><?php echo $this->content?></div><?php

		// При смене статуса в новом окне сообщения задваиваются, при удалении в новом не выводятся
		?><script>$('#id_message').html('<?php echo Core_Str::escapeJavascriptVariable($this->message)?>');</script><?php

		$this
			->_afterLoad()
			->_showFooter();

		return $this;
	}

	/**
	 * Execute afterload logic
	 * @return self
	 */
	protected function _afterLoad()
	{
		parent::_afterLoad();
		?><script>$.currentMenu('<?php echo Core_Str::escapeJavascriptVariable($this->module)?>');</script><?php

		return $this;
	}
}