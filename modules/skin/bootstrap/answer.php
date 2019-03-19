<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Skin.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		// При смене статуса в новом окне сообщения задваиваются
		/*<script>$('#id_message').append('<?php echo Core_Str::escapeJavascriptVariable($this->message)?>');</script>*/

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