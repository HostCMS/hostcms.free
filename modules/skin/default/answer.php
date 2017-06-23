<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Skin.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Answer extends Admin_Answer
{
	/**
	 * Open window
	 * @return self
	 */
	protected function _openWindow()
	{
		if ($this->_openWindow)
		{
			$windowId = $this->_getWindowId();

			?><script type="text/javascript">
			$(function() {
			<?php
				/*if (!is_null($this->title))
				{
					?>document.title = '<?php echo str_replace("'", "\'", $this->title)?>';<?php
				}*/
				$aUri = explode('/', trim(Core_Array::get($_SERVER, 'REQUEST_URI'), '/'));

				$moduleName = isset($aUri[1])
					? Core_Str::escapeJavascriptVariable($aUri[1]) . '.png'
					: 'default.png';

				$windowSettings = $this->_windowSettings + array(
					'title' => $this->title,
					'Minimize' => TRUE,
				);

				$mode = Core_Skin::instance()->getMode();
				if ($mode != 'blank')
				{
					$aTmp = array();
					foreach ($windowSettings as $key => $value)
					{
						if ($value === TRUE)
						{
							$value = 'true';
						}
						elseif ($value === FALSE)
						{
							$value = 'false';
						}
						else
						{
							$value = "'" . Core_Str::escapeJavascriptVariable($value) . "'";
						}
						$aTmp[] = $key . ': ' . $value;
					}

					?>var jDivWin = $("#<?php echo htmlspecialchars($windowId)?>");
					jDivWin.HostCMSWindow($.windowSettings({<?php echo implode(',', $aTmp)?>})).HostCMSWindow('open');
					<?php if ($this->_addTaskbar)
					{
						?>$.addTaskbar(jDivWin, {shortcutImg: '/modules/skin/default/images/module/<?php echo $moduleName?>', shortcutTitle: '<?php echo Core_Str::escapeJavascriptVariable($this->title)?>'});<?php
					}
					?>
					$.afterContentLoad(jDivWin);
				<?php
				}
				?>
			});</script><?php
		}

		return $this;
	}

	/**
	 * Send html content
	 */
	protected function _sendHtml()
	{
		// Before show index()
		$mode = Core_Skin::instance()->getMode();

		$this->_showHeader();

		try
		{
			$this->skin && is_null($mode) && defined('CURRENT_SITE') && Core_Skin::instance()->index();

			if ($mode != 'index')
			{
				$windowId = $this->_getWindowId();

				?><div id="<?php echo htmlspecialchars($windowId)?>" class="hostcmsWindow"><?php
				?><div id="id_message"><?php echo $this->message?></div><?php /*echo $this->content*/?><?php
				?></div><?php
				?><script type="text/javascript"><?php
				?>$(function() {<?php
				// Fix bug with duplicated execution Javascript
				?>$('#<?php echo htmlspecialchars($windowId)?>').append('<?php echo str_replace('script', "scr' + 'ipt", Core_Str::escapeJavascriptVariable($this->content))?>');<?php
				?>});</script><?php
			}
			else
			{
				echo $this->content;
			}

			// Open in new window
			is_null($mode) && $this->openWindow(TRUE);

			$this->_openWindow();
		}
		catch (Exception $e){
			Core::factory('Core_Html_Entity_Div')
				->class('indexMessage')
				->value(Core_Message::get($e->getMessage(), 'error'))
				->execute();
		}

		$this->skin && Core_Skin::instance()->footer();
	}
}