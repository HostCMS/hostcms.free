<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin answer.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Admin_Answer extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'module',
		'ajax',
		'title',
		'message',
		'content',
		'skin'
	);

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->module = NULL;
		$this->skin = TRUE;
		$this->title = $this->message = $this->content = '';
	}

	/**
	 * Send AJAX answer with headers
	 */
	protected function _sendAjax()
	{
		Core::showJson(array(
			'form_html' => $this->content,
			// Content already consists message. В хуках сообщения теряются
			//'error' => $this->content == '' ? $this->message : '',
			'error' => $this->message,
			'title' => $this->title,
			'module' => $this->module
		));
	}

	/**
	 * Show header
	 * @return self
	 */
	protected function _showHeader()
	{
		$this->skin && Core_Skin::instance()
			->setMode(Core_Array::getRequest('hostcmsMode', Core_Skin::instance()->getMode()))
			->title($this->title)
			->header();

		return $this;
	}

	/**
	 * Show footer
	 * @return self
	 */
	protected function _showFooter()
	{
		$this->skin && Core_Skin::instance()->footer();

		return $this;
	}

	/**
	 * Execute afterload logic
	 * @return self
	 */
	protected function _afterLoad()
	{
		?><script><?php
		if (!is_null($this->title))
		{
			?>document.title = '<?php echo Core_Str::escapeJavascriptVariable($this->title)?>';<?php
		}
		?>$.afterContentLoad($("#id_content"));<?php
		?></script><?php

		return $this;
	}

	/**
	 * Send header and HTML answer
	 * @return self
	 */
	protected function _sendHtml()
	{
		$this->_showHeader();

		?><div id="id_content"><?php
			?><div id="id_message"><?php echo $this->message?></div><?php
			echo $this->content;
		?></div><?php

		$this
			->_afterLoad()
			->_showFooter();

		return $this;
	}

	/**
	 * Send answer (AJAX or HTML)
	 * @return self
	 */
	public function execute()
	{
		$this->ajax
			? $this->_sendAjax()
			: $this->_sendHtml();

		return $this;
	}

	/**
	 * Get current window id
	 * @return int
	 */
	protected function _getWindowId()
	{
		$aHostCMS = Core_Array::getGet('hostcms', array());
		return Core_Array::get($aHostCMS, 'window', 'id_content');
	}

	/**
	 * Open window, default FALSE
	 */
	protected $_openWindow = FALSE;

	/**
	 * Open window, default FALSE
	 * @param boolean $openWindow open mode
	 * @return self
	 */
	public function openWindow($openWindow)
	{
		$this->_openWindow = $openWindow;
		return $this;
	}

	/**
	 * Window's settings
	 */
	protected $_windowSettings = array();

	/**
	 * Set window's settings
	 * @param array $windowSettings settings
	 * @return self
	 */
	public function windowSettings($windowSettings)
	{
		$this->_windowSettings = $windowSettings;
		return $this;
	}

	/**
	 * Add into taskbar
	 */
	protected $_addTaskbar = TRUE;

	/**
	 * Add into taskbar, default TRUE
	 * @param boolean $addTaskbar TRUE/FALSE
	 * @return self
	 */
	public function addTaskbar($addTaskbar)
	{
		$this->_addTaskbar = $addTaskbar;
		return $this;
	}
}