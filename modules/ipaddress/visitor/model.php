<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress_Visitor_Model
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Ipaddress_Visitor_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'ipaddress_visitor_filter' => array()
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
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
		}
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function resultBackend()
	{
		$title = Core_Date::timestamp2datetime($this->result_expired);

		switch ($this->result)
		{
			case 0:
				$color = 'red';
				$icon = 'fa-times-circle-o';
			break;
			case 1:
				$color = 'green';
				$icon = 'fa-check-circle-o';
			break;
			case 2:
				$color = 'blue';
				$icon = 'fa-clock';
			break;
		}

		return '<i class="fa ' . $icon . ' ' . $color . '" title="' . $title . '">';
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if (Core::moduleIsActive('counter'))
		{
			$additionalParam = 'admin_form_filter_2319=' . $this->id;

			$href = $oAdmin_Form_Controller->getAdminLoadHref('/{admin}/counter/session/index.php', NULL, NULL, $additionalParam);
			$onclick = $oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/counter/session/index.php', NULL, NULL, $additionalParam);

			return '<a href="' . $href . '" onclick="' . $onclick . '">' . htmlspecialchars($this->id) . '</a>';
		}

		return htmlspecialchars($this->id);
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function ipBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		Ipaddress_Controller::instance()->isBlocked($this->ip, FALSE) && Core_Html_Entity::factory('I')
			->class('fa-solid fa-ban darkorange')
			->execute();

		if ($this->ipaddress_visitor_filter_id)
		{
			$this->_filterBadge();
		}
	}

	/**
	 * Show filter name badge
	 */
	protected function _filterBadge()
	{
		Core_Html_Entity::factory('Span')
			->class('badge badge-round gray')
			->value(htmlspecialchars((string) $this->Ipaddress_Visitor_Filter->name))
			->title((string) $this->Ipaddress_Visitor_Filter->name)
			->execute();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function ipaddress_visitor_filter_idBackend()
	{
		$this->_filterBadge();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function useragentBackend()
	{
		ob_start();

		if ($this->useragent != '')
		{
			?><span title="<?php echo htmlspecialchars($this->useragent)?>"><?php

				$browser = htmlspecialchars(Core_Browser::getBrowser($this->useragent));

				/*if ($browser != '-')
				{*/
					$ico = Core_Browser::getBrowserIco($browser);

					!is_null($ico)
						&& $browser = '<i class="' . $ico . '"></i> ' . $browser;

					echo $browser . ' ';
				//}
			?></span>
			<span class="label label-sm label-success"><?php echo htmlspecialchars(Core_Browser::getOs($this->useragent));?></span><?php
		}

		if ($this->headers != '')
		{
			$aHeaders = json_decode($this->headers, TRUE);
			if (is_array($aHeaders))
			{
				foreach ($aHeaders as $hKey => $hValue)
				{
					echo '<br><b>' . htmlspecialchars($hKey) . '</b>: ' . htmlspecialchars($hValue);
				}
			}
		}

		return ob_get_clean();
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event ipaddress_visitor.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		if (Core::moduleIsActive('counter'))
		{
			$aCounter_Sessions = Core_Entity::factory('Counter_Session')->getAllByTag($this->id, FALSE);
			foreach ($aCounter_Sessions as $oCounter_Session)
			{
				$oCounter_Session->delete();
			}
		}

		return parent::delete($primaryKey);
	}
}