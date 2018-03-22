<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Siteuser. Backend's Index Pages and Widget.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Module_Siteuser_Module extends Siteuser_Module
{
	/**
	 * Name of the skin
	 * @var string
	 */
	protected $_skinName = 'default';

	/**
	 * Name of the module
	 * @var string
	 */
	protected $_moduleName = 'siteuser';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_adminPages = array(
			77 => array('title' => Core::_('Siteuser.widget_title'))
		);
	}

	/**
	 * Show admin widget
	 * @param int $type
	 * @param boolean $ajax
	 * @return self
	 */
	public function adminPage($type = 0, $ajax = FALSE)
	{
		$oSiteusers = Core_Entity::factory('Site', CURRENT_SITE)->Siteusers;
		$oSiteusers->queryBuilder()
			->orderBy('siteusers.datetime', 'DESC')
			->limit(3);

		$aSiteusers = $oSiteusers->findAll();

		$windowId = 'modalSiteusers';
		$shortcutImg = "/modules/skin/{$this->_skinName}/images/module/{$this->_moduleName}.png";
		$shortcutTitle = Core::_('Siteuser.widget_title');

		if (count($aSiteusers) > 0)
		{
			if (!$ajax)
			{
				$oModalWindow = Core::factory('Core_Html_Entity_Div')
					->id($windowId)
					->class('widget')
					->title($shortcutTitle)
					->add($oModalWindowSub = Core::factory('Core_Html_Entity_Div')
						->class('sub')
					);
			}
			else
			{
				$oModalWindowSub = Core::factory('Core_Html_Entity_Div');
			}

			$iAdmin_Form_Id = 188;
			$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);
			$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);

			$sSiteuserHref = '/admin/siteuser/index.php';

			foreach ($aSiteusers as $key => $oSiteuser)
			{
				$oCore_Html_Entity_Div_Action = Core::factory('Core_Html_Entity_Div')
					->class('action')
					->add(
							Core::factory('Core_Html_Entity_A')
							->href(
								$oAdmin_Form_Controller->getAdminActionLoadHref($sSiteuserHref, 'changeActive', NULL, 0, $oSiteuser->id)
							)
							->title(
								Core::_('Siteuser.change_active')
							)
							->onclick(
								"$.widgetRequest({path: '" . $oAdmin_Form_Controller->getAdminActionLoadHref($sSiteuserHref, 'changeActive', NULL, 0, $oSiteuser->id) . "', context: $('#{$windowId}')}); return false"
							)
							->add(
								Core::factory('Core_Html_Entity_Img')
									->src('/modules/skin/' . $this->_skinName . '/images/action/' . ($oSiteuser->active ? 'checked' : 'unchecked') . '.png')
							)
						)
					->add(
						Core::factory('Core_Html_Entity_A')
						->href(
							$oAdmin_Form_Controller->getAdminActionLoadHref($sSiteuserHref, 'edit', NULL, 0, $oSiteuser->id)
						)
						->title(
							Core::_('Siteuser.edit')
						)
						->onclick(
							"$.openWindowAddTaskbar({path: '" . $oAdmin_Form_Controller->getAdminActionLoadHref($sSiteuserHref, 'edit', NULL, 0, $oSiteuser->id) . "', shortcutImg: '{$shortcutImg}', shortcutTitle: '{$shortcutTitle}', Minimize: true, Closable: true}); return false"
						)
						->add(
							Core::factory('Core_Html_Entity_Img')
								->src('/modules/skin/' . $this->_skinName . '/images/action/edit.png')
							)
					)
					->add(
						Core::factory('Core_Html_Entity_A')
						->href(
							$oAdmin_Form_Controller->getAdminActionLoadHref($sSiteuserHref, 'markDeleted', NULL, 0, $oSiteuser->id)
						)
						->title(
							Core::_('Siteuser.delete')
						)
						->onclick(
							"res = confirm('".Core::_('Admin_Form.confirm_dialog', htmlspecialchars(Core::_('Admin_Form.delete')))."'); if (res) { $.widgetRequest({path: '" . $oAdmin_Form_Controller->getAdminActionLoadHref($sSiteuserHref, 'markDeleted', NULL, 0, $oSiteuser->id) . "', context: $('#{$windowId}')});} return false"
						)
						->add(
							Core::factory('Core_Html_Entity_Img')
								->src('/modules/skin/' . $this->_skinName . '/images/action/delete.png')
						)
					);

				$oDiv = Core::factory('Core_Html_Entity_Div')
					->class('event event' . ($oSiteuser->active == 1 ? 1 : 4))
					->add(
						Core::factory('Core_Html_Entity_Div')
							->class('corner')
					)
					->add(
						Core::factory('Core_Html_Entity_Span')
							->value(Core::_('Siteuser.login') . trim(htmlspecialchars(Core_Str::cut(strip_tags(html_entity_decode($oSiteuser->login, ENT_COMPAT, 'UTF-8')), 70))))
					)
					->add(
						Core::factory('Core_Html_Entity_Div')
							->class('clear')
					);

				$sEmail = strip_tags(html_entity_decode($oSiteuser->email, ENT_COMPAT, 'UTF-8'));
					
				strlen($oSiteuser->email) && $oDiv
					->add(
						Core::factory('Core_Html_Entity_Span')
							->value(Core::_('Siteuser.email'))
					)
					->add(
						Core::factory('Core_Html_Entity_A')
							->href('mailto:' . $sEmail)
							->value(trim(htmlspecialchars(Core_Str::cut($sEmail, 70))))
					)
					
					->add(
						Core::factory('Core_Html_Entity_Div')
						->class('clear')
					);

				$oDiv->add($oCore_Html_Entity_Div_Action)
					->add(
						Core::factory('Core_Html_Entity_Div')
							->class('widget_date')
							->value(htmlspecialchars(Core_Date::sql2date($oSiteuser->datetime)))
					);

				$oModalWindowSub->add($oDiv);
			}


			$oModalWindowSub->add(
				Core::factory('Core_Html_Entity_Div')
					->class('widgetDescription')
					->add(
						Core::factory('Core_Html_Entity_Img')
							->src('/modules/skin/' . $this->_skinName . '/images/widget/counter.png')
					)->add(
						Core::factory('Core_Html_Entity_Div')
							->add(
								Core::factory('Core_Html_Entity_A')
									->id('widgetSiteuserOther')
									->href($sSiteuserHref)
									->value(Core::_('Siteuser.widget_other_users'))
							)
							->add(
								Core::factory('Core_Html_Entity_Script')
									->type('text/javascript')
									->value("$('#widgetSiteuserOther').linkShortcut({path: '{$sSiteuserHref}', shortcutImg: '{$shortcutImg}', shortcutTitle: '{$shortcutTitle}', Minimize: true, Closable: true});")
							)
					)
			);

			if (!$ajax)
			{
				$oUser = Core_Entity::factory('User')->getCurrent();

				$oModule = Core_Entity::factory('Module')->getByPath($this->_moduleName);
				$module_id = $oModule->id;
				$oUser_Setting = $oUser->User_Settings->getByModuleIdAndTypeAndEntityId($module_id, 77, 0);

				if (is_null($oUser_Setting))
				{
					$oUser_Setting = Core_Entity::factory('User_Setting');
					$oUser_Setting->position_x = "'right'";
					$oUser_Setting->position_y = 85;
					$oUser_Setting->width = 250;
					$oUser_Setting->height = 260;
				}

				$oModalWindow
					->add(
						Core::factory('Core_Html_Entity_Script')
							->type('text/javascript')
							->value("$(function(){
								$('#{$windowId}').widgetWindow({
									position: [{$oUser_Setting->position_x}, {$oUser_Setting->position_y}],
									width: {$oUser_Setting->width},
									height: {$oUser_Setting->height},
									moduleId: '{$module_id}',
									path: '/admin/index.php?ajaxWidgetLoad&moduleId={$module_id}&type=0'
								});
							});")
					)
					->execute();
			}
			else
			{
				$oModalWindowSub->execute();
			}
		}

		return $this;
	}
}