<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Information System. Backend's Index Pages and Widget.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Module_Informationsystem_Module extends Informationsystem_Module
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
	protected $_moduleName = 'informationsystem';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_adminPages = array(
			77 => array('title' => Core::_('Informationsystem.widget_title'))
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
		$oUser = Core_Entity::factory('User')->getCurrent();

		$oComments = Core_Entity::factory('Comment');

		$oComments->queryBuilder()
			->straightJoin()
			->join('comment_informationsystem_items', 'comments.id', '=', 'comment_informationsystem_items.comment_id')
			->join('informationsystem_items', 'comment_informationsystem_items.informationsystem_item_id', '=', 'informationsystem_items.id')
			->join('informationsystems', 'informationsystem_items.informationsystem_id', '=', 'informationsystems.id')
			->where('informationsystem_items.deleted', '=', 0)
			->where('informationsystems.deleted', '=', 0)
			->where('site_id', '=', CURRENT_SITE)
			->orderBy('comments.datetime', 'DESC')
			->limit(3);

		// Права доступа пользователя к комментариям
		if ($oUser->superuser == 0 && $oUser->only_access_my_own == 1)
		{
			$oComments->queryBuilder()->where('comments.user_id', '=', $oUser->id);
		}

		$aComments = $oComments->findAll();

		$windowId = 'modalInformationsystemComments';
		$shortcutImg = "/modules/skin/{$this->_skinName}/images/module/{$this->_moduleName}.png";
		$shortcutTitle = Core::_('Informationsystem.widget_title');

		if (count($aComments) > 0)
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

			$iAdmin_Form_Id = 52;
			$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);
			$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);

			$sInformationsystemHref = '/admin/informationsystem/item/comment/index.php';

			foreach ($aComments as $key => $oComment)
			{
				$oCore_Html_Entity_Div_Action = Core::factory('Core_Html_Entity_Div')
					->class('action')
					->add(
						Core::factory('Core_Html_Entity_A')
						->href(
							$oAdmin_Form_Controller->getAdminActionLoadHref($sInformationsystemHref, 'changeActive', NULL, 0, $oComment->id)
						)
						->title(
							Core::_('Comment.change_active')
						)
						->onclick(
							"$.widgetRequest({path: '" . $oAdmin_Form_Controller->getAdminActionLoadHref($sInformationsystemHref, 'changeActive', NULL, 0, $oComment->id) . "', context: $('#{$windowId}')}); return false"
						)
						->add(
							Core::factory('Core_Html_Entity_Img')
								->src('/modules/skin/' . $this->_skinName . '/images/action/' . ($oComment->active ? 'checked' : 'unchecked') . '.png')
						)
					)
					->add(
						Core::factory('Core_Html_Entity_A')
						->href(
							$oAdmin_Form_Controller->getAdminActionLoadHref($sInformationsystemHref, 'edit', NULL, 0, $oComment->id)
						)
						->title(
							Core::_('Comment.edit')
						)
						->onclick(
							"$.openWindowAddTaskbar({path: '" . $oAdmin_Form_Controller->getAdminActionLoadHref($sInformationsystemHref, 'edit', NULL, 0, $oComment->id) . "', shortcutImg: '{$shortcutImg}', shortcutTitle: '{$shortcutTitle}', Minimize: true, Closable: true}); return false"
						)
						->add(
							Core::factory('Core_Html_Entity_Img')
								->src('/modules/skin/' . $this->_skinName . '/images/action/edit.png')
							)
					)
					->add(
						Core::factory('Core_Html_Entity_A')
						->href(
							$oAdmin_Form_Controller->getAdminActionLoadHref($sInformationsystemHref, 'markDeleted', NULL, 0, $oComment->id)
						)
						->title(
							Core::_('Comment.delete')
						)
						->onclick(
							"res = confirm('".Core::_('Admin_Form.confirm_dialog', htmlspecialchars(Core::_('Admin_Form.delete')))."'); if (res) { $.widgetRequest({path: '" . $oAdmin_Form_Controller->getAdminActionLoadHref($sInformationsystemHref, 'markDeleted', NULL, 0, $oComment->id) . "', context: $('#{$windowId}')});} return false"
						)
						->add(
							Core::factory('Core_Html_Entity_Img')
								->src('/modules/skin/' . $this->_skinName . '/images/action/delete.png')
						)
					);

				if ($oComment->active)
				{
					$oStructure = $oComment->Informationsystem_Item->Informationsystem->Structure;

					$oCurrentAlias = Core_Entity::factory('Site', CURRENT_SITE)->getCurrentAlias();

					if ($oCurrentAlias)
					{
						$oCore_Html_Entity_Div_Action->add(
							Core::factory('Core_Html_Entity_A')
								->href(
									($oStructure->https ? 'https://' : 'http://' ) . $oCurrentAlias->name . $oStructure->getPath() . $oComment->Informationsystem_Item->getPath() . '#comment' . $oComment->id
								)
								->target('_blank')
								->title(
									Core::_('Comment.view_comment')
								)
								->add(
									Core::factory('Core_Html_Entity_Img')
										->src('/modules/skin/' . $this->_skinName . '/images/action/external-link.png')
								)
						);
					}
				}

				$oModalWindowSub
					->add(
						Core::factory('Core_Html_Entity_Div')
							->class('comment comment' . ($key % 2 == 0 ? 0 : 1))
							->add(
								Core::factory('Core_Html_Entity_Div')
									->class('corner')
							)
							->add(
								Core::factory('Core_Html_Entity_Span')
									->value(trim(htmlspecialchars(Core_Str::cut(strip_tags(html_entity_decode($oComment->text, ENT_COMPAT, 'UTF-8')), 70))))
							)
							->add(
								Core::factory('Core_Html_Entity_Div')
									->class('clear')
							)
							->add($oCore_Html_Entity_Div_Action)
							->add(
								Core::factory('Core_Html_Entity_Div')
									->class('date')
									->value(htmlspecialchars(Core_Date::sql2date($oComment->datetime)))
							)
					);
			}


			$oModalWindowSub->add(
				Core::factory('Core_Html_Entity_Div')
					->class('widgetDescription')
					->add(
						Core::factory('Core_Html_Entity_Img')
							->src('/modules/skin/' . $this->_skinName . '/images/widget/comment.png')
					)->add(
						Core::factory('Core_Html_Entity_Div')
							->add(
								Core::factory('Core_Html_Entity_A')
									->id('widgetInformationsystemCommentOther')
									->href($sInformationsystemHref)
									->value(Core::_('Informationsystem.widget_other_comments'))
							)
							->add(
								Core::factory('Core_Html_Entity_Script')
									->type('text/javascript')
									->value("$('#widgetInformationsystemCommentOther').linkShortcut({path: '{$sInformationsystemHref}', shortcutImg: '{$shortcutImg}', shortcutTitle: '{$shortcutTitle}', Minimize: true, Closable: true});")
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