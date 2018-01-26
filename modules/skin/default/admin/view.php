<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Default view.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Admin_View extends Admin_View
{
	public function showTitle()
	{
		// Заголовок формы
		strlen($this->pageTitle) && Admin_Form_Entity::factory('Title')
			->name($this->pageTitle)
			->execute();

		return $this;
	}
	
	/**
	 * Show children elements
	 * @return self
	 */
	public function showChildren()
	{
		// Связанные с формой элементы (меню, строка навигации и т.д.)
		foreach ($this->_children as $oAdmin_Form_Entity)
		{
			$oAdmin_Form_Entity->execute();
		}

		return $this;
	}
	
	public function show()
	{
		$this->showTitle();
		
		// Перенесено в Admin_Answer для этого скина
		/*?><div id="id_message"><?php echo $this->message?></div><?php*/
		
		$this->showChildren();
		
		echo $this->content;
	}
}