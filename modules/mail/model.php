<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Mail_Model
 *
 * @package HostCMS
 * @subpackage Mail
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Mail_Model extends Core_Entity
{
	/**
	 * Delete messages
	 * @var boolean
	 */
	protected $_deleteMessages = FALSE;

	/**
	 * Set delete messages
	 * @param boolean $deleteMessages
	 * @return self
	 */
	public function deleteMessages($deleteMessages = TRUE)
	{
		$this->_deleteMessages = $deleteMessages;
		return $this;
	}

	/**
	 * Search messages
	 * @var boolean
	 */
	protected $_search = NULL;

	/**
	 * Set search messages
	 * @param boolean $deleteMessages
	 * @return self
	 */
	public function search($search)
	{
		$this->_search = $search;
		return $this;
	}

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'site' => array(),
		'crm_source' => array(),
		'user' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'ssl' => 0,
		'sorting' => 0,
		'active' => 0,
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'mails.sorting' => 'ASC'
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
			$this->_preloadValues['site_id'] = defined('CURRENT_SITE') ? CURRENT_SITE : 0;
		}
	}

	/**
	 * Change mail status
	 * @return Mail_Model
	 * @hostcms-event mail.onBeforeChangeActive
	 * @hostcms-event mail.onAfterChangeActive
	 */
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$this->active = 1 - $this->active;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterChangeActive', $this);

		return $this;
	}

	/**
	 * Get default mail
	 * @return Mail_Model|NULL
	 */
	public function getDefault()
	{
		$this->queryBuilder()
			->clear()
			->where('default', '=', 1)
			->limit(1);

		$aObjects = $this->findAll();

		return isset($aObjects[0]) ? $aObjects[0] : NULL;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->ssl)
		{
			$sslColor = '#a0d468';

			?><span class="badge badge-round badge-max-width margin-left-5" title="<?php echo Core::_('Mail.ssl')?>" style="border-color: <?php echo $sslColor?>; color: <?php echo Core_Str::hex2darker($sslColor, 0.2)?>; background-color:<?php echo Core_Str::hex2lighter($sslColor, 0.88)?>">SSL</span><?php
		}

		if ($this->default)
		{
			$defaultColor = '#57b5e3';

			?><span class="badge badge-round badge-max-width margin-left-5" title="<?php echo Core::_('Mail.default')?>" style="border-color: <?php echo $defaultColor?>; color: <?php echo Core_Str::hex2darker($defaultColor, 0.2)?>; background-color:<?php echo Core_Str::hex2lighter($defaultColor, 0.88)?>">SMTP</span><?php
		}

		if (Core::moduleIsActive('lead') && $this->create_leads)
		{
			$leadColor = '#edc051';

			?><span class="badge badge-round badge-max-width margin-left-5" title="<?php echo Core::_('Mail.create_leads')?>" style="border-color: <?php echo $leadColor?>; color: <?php echo Core_Str::hex2darker($leadColor, 0.2)?>; background-color:<?php echo Core_Str::hex2lighter($leadColor, 0.88)?>"><i class="fa-solid fa-user"></i></span><?php
		}
	}

	/**
	 * Receive messages
	 * @return array $aMessages
	 */
	public function receive()
	{
		$Core_Mail_Imap = new Core_Mail_Imap();

		$Core_Mail_Imap
			->login($this->login)
			->password($this->password)
			->delete($this->_deleteMessages)
			->ssl($this->ssl)
			->type($this->imap != '' ? 'imap' : 'pop3')
			->server($this->imap != '' ? $this->imap : $this->pop3);

		!is_null($this->_search)
			&& $Core_Mail_Imap->search($this->_search);

		$Core_Mail_Imap->execute();

		if (count($Core_Mail_Imap->getErrors()) && is_array($Core_Mail_Imap->getErrors()))
		{
			throw new Core_Exception(implode("\n", $Core_Mail_Imap->getErrors()));
		}

		$aMessages = $Core_Mail_Imap->getMessages();

		// Создавать лид для входящих писем
		if ($this->create_leads && Core::moduleIsActive('lead'))
		{
			foreach ($aMessages as $aMessage)
			{
				Lead_Controller::createLeadFromEmail($aMessage, $this->crm_source_id);
			}
		}

		return $aMessages;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event mail.onBeforeGetRelatedSite
	 * @hostcms-event mail.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}