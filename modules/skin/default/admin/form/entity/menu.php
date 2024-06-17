<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_Menu extends Admin_Form_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'name',
		'img',
		'href',
		'onclick',
		'target',
		'icon'
	);

	/**
	 * Show menu item
	 */
	protected function _showMenuItem()
	{
		$href = $this->href;
		$onclick = $this->onclick;

		if ($href && $onclick)
		{
			?><a href="<?php echo htmlspecialchars((string) $href)?>" onclick="<?php echo htmlspecialchars((string) $onclick)?>"><?php
		}
		else
		{
			?><span><?php
		}

		if ($this->img)
		{
			?><img align="absmiddle" src="<?php echo htmlspecialchars((string) $this->img)?>" /><?php
		}

		echo htmlspecialchars((string) $this->name);

		if ($href && $onclick)
		{
			?></a><?php
		}
		else
		{
			?></span><?php
		}
	}

	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Menu.onBeforeExecute
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Menu.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		$menu_id = rand(1, 999999);

		$li_id = "id_menu_item_{$menu_id}";

		$menu_id++;

		echo '<td valign="bottom" id="'.$li_id.'" OnMouseOver="HostCMSMenuOver(\'' . $li_id . '\','. 1 .", '" . 'id_' . $menu_id . '\');" OnMouseOut="HostCMSMenuOut(\'' . $li_id . "', 1, '" . 'id_' . $menu_id .'\');" class="li_lev_1">';

		$this->_showMenuItem();

		if (!empty($this->_children))
		{
			?><div id="id_<?php echo $menu_id?>" class="shadowed" style="display: none"><ul><?php
			// Вывод подменю
			foreach ($this->_children as $key => $subMenu)
			{
				$li_id = "id_menu_item_{$menu_id}_{$key}";
				?><li id="<?php echo htmlspecialchars($li_id)?>"><?php
				$subMenu->_showMenuItem();
				?></li><?php
			}
			?></ul></div><?php
		}

		?></td><?php

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}