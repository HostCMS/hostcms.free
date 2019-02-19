<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Skin.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default extends Core_Skin
{
	/**
	 * Name of the skin
	 * @var string
	 */
	protected $_skinName = 'default';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$lng = $this->getLng();

		$this
			->addJs('/modules/skin/default/js/jquery/jquery.elastic.js')
			->addJs('/modules/skin/default/js/jquery/jquery.tools.js')
			->addJs('/modules/skin/bootstrap/js/jquery.form.js')
			->addJs('/modules/skin/bootstrap/js/main.js')
			->addJs('/modules/skin/default/js/ui/jquery-ui.js')
			->addJs('/modules/skin/default/js/default-hostcms.js')
			->addJs('/modules/skin/default/js/ui/i18n/jquery.ui.datepicker-' . $lng . '.js')
			->addJs('/modules/skin/default/js/ui/jquery-HostCMSWindow.js')
			->addJs('/modules/skin/default/js/ui/timepicker/timepicker.js')
			->addJs('/modules/skin/default/js/ui/timepicker/i18n/jquery-ui-timepicker-' . $lng . '.js')
			->addJs('/modules/skin/default/js/ui/stars/jquery.ui.stars.js')
			->addJs('/modules/skin/default/js/fusionchart/FusionCharts.js')
			// context menu
			->addJs('/modules/skin/' . $this->_skinName . '/js/mb.menu/mbMenu.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/mb.menu/jquery.metadata.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/mb.menu/jquery.hoverIntent.js')
			->addJs('/modules/skin/bootstrap/js/typeahead-bs2.min.js')
			->addJs('/modules/skin/bootstrap/js/bootstrap-tag.js')
			->addJs('/modules/skin/bootstrap/js/ace.js')
			->addJs('/modules/skin/bootstrap/js/codemirror/lib/codemirror.js')
			->addJs('/modules/skin/bootstrap/js/codemirror/mode/css/css.js')
			->addJs('/modules/skin/bootstrap/js/codemirror/mode/htmlmixed/htmlmixed.js')
			->addJs('/modules/skin/bootstrap/js/codemirror/mode/javascript/javascript.js')
			->addJs('/modules/skin/bootstrap/js/codemirror/mode/clike/clike.js')
			->addJs('/modules/skin/bootstrap/js/codemirror/mode/php/php.js')
			->addJs('/modules/skin/bootstrap/js/codemirror/mode/xml/xml.js')
			->addJs('/modules/skin/bootstrap/js/codemirror/addon/selection/active-line.js')
			->addJs('/modules/skin/bootstrap/js/codemirror/addon/search/searchcursor.js')
			->addJs('/modules/skin/bootstrap/js/codemirror/addon/search/search.js')
			->addJs('/modules/skin/bootstrap/js/codemirror/addon/dialog/dialog.js')

			->addJs('/modules/skin/bootstrap/js/charts/flot/jquery.flot.js')
			->addJs('/modules/skin/bootstrap/js/charts/flot/jquery.flot.time.js')
			->addJs('/modules/skin/bootstrap/js/charts/flot/jquery.flot.tooltip.js')
			->addJs('/modules/skin/bootstrap/js/charts/flot/jquery.flot.crosshair.js')
			->addJs('/modules/skin/bootstrap/js/charts/flot/jquery.flot.resize.js')
			->addJs('/modules/skin/bootstrap/js/charts/flot/jquery.flot.selection.js')
			->addJs('/modules/skin/bootstrap/js/jquery.slimscroll.js')

			;

		/*if (defined('USE_HOSTCMS_5') && USE_HOSTCMS_5)
		{
			$this
				->addJs('/admin/js/JsHttpRequest.js')
				->addJs('/admin/js/hostcms5.js');
		}*/

		$this
			->addCss('/modules/skin/' . $this->_skinName . '/js/mb.menu/menu.css')
			->addCss('/modules/skin/' . $this->_skinName . '/css/style.css')
			->addCss('/modules/skin/' . $this->_skinName . '/css/external.css')
			->addCss('/modules/skin/default/js/ui/themes/base/jquery.ui.all.css')
			->addCss('/modules/skin/default/js/ui/stars/ui.stars.css')
			->addCss('/modules/skin/default/js/ui/ui.css')
			->addCss('/modules/skin/bootstrap/js/codemirror/lib/codemirror.css')
			->addCss('/modules/skin/default/js/codemirror/addon/dialog/dialog.css')

			->addCss('/modules/skin/bootstrap/css/font-awesome.min.css')
			;
	}

	/**
	 * Show HTML head
	 */
	public function showHead()
	{
		$timestamp = $this->_getTimestamp();

		Core_Browser::check();

		$lng = $this->getLng();

		$aCss = $this->getCss();
		foreach ($aCss as $sPath)
		{
			?><link type="text/css" href="<?php echo $sPath . '?' . $timestamp?>" rel="stylesheet" /><?php
			echo PHP_EOL;
		}?>
		<script>if (!window.jQuery) {document.write('<scri'+'pt src="/modules/skin/default/js/jquery/jquery.js"></scr'+'ipt>');}</script>

		<?php
		$this->addJs("/modules/skin/bootstrap/js/lng/{$lng}/{$lng}.js");
		$aJs = $this->getJs();
		foreach ($aJs as $sPath)
		{
			Core::factory('Core_Html_Entity_Script')
				->src($sPath . '?' . $timestamp)
				->execute();
		}
		?>

		<script><?php
		if (Core_Auth::logged())
		{
			?>var HostCMSFileManager = new HostCMSFileManager();<?php
		}
		?>

		$(function() {
			$.datepicker.setDefaults({showAnim: 'slideDown', dateFormat: 'dd.mm.yy', showButtonPanel: true});

			$('#topMenu').buildMenu({
				menuWidth:200,
				iconPath:'',
				hasImages:true,
				fadeInTime:100,
				fadeOutTime:300,
				adjustLeft:2,
				minZindex:"auto",
				adjustTop:10,
				shadow:false,
				hoverIntent:0,
				openOnClick:true,
				closeOnMouseOut:true,
				closeAfter:100,
				submenuHoverIntent:200
			});

			$('#hostCmsLogoImg')
				.click(function(){$.hideAllWindow(); return false})
				.mousedown(function(){$('#hostCmsLogo').animate({top: '2px'})})
				.mouseup(function(){$('#hostCmsLogo').animate({top: 0})});
		});
		</script>
		<script src="/admin/wysiwyg/jquery.tinymce.js"></script>
		<?php

		return $this;
	}

	/**
	 * Show header
	 */
	public function header()
	{
		?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title><?php echo $this->_title?></title>
	<meta content="text/html; charset=UTF-8" http-equiv="Content-Type"></meta>
	<link rel="apple-touch-icon" href="/modules/skin/bootstrap/ico/icon-iphone-retina.png" />
	<link rel="shortcut icon" type="image/x-icon" href="/modules/skin/bootstrap/ico/favicon.ico" />
	<link rel="icon" type="image/png" href="/modules/skin/bootstrap/ico/favicon.png" />
	<?php $this->showHead()?>
</head>
<body class="body-<?php echo htmlspecialchars($this->_mode)?> hostcms6 backendBody">
<?php
if ($this->_mode != 'blank')
{
?>
<div id="top">
	<div id="hostCmsLogo">
		<a href="/admin/"><img id="hostCmsLogoImg" src="<?php echo $this->getImageHref()?>logo.png" alt="(^) HostCMS" title="HostCMS<?php echo Core_Auth::logged() ? ' v. ' . htmlspecialchars(CURRENT_VERSION) : ''?>"></img></a>
	</div>
	<?php if (Core_Auth::logged() && defined('CURRENT_SITE'))
	{ ?>
	<div id="topMenu" class="disableSelect">
		<div id="selectSite" class="rootVoice {menu: 'submenuSites'}">
			<span><?php $oCurrentSite = Core_Entity::factory('Site', CURRENT_SITE);
			echo Core_Str::escapeJavascriptVariable(
				Core_Str::cut($oCurrentSite->name, 25)
			)?></span> ▼
		</div>

		<div id="selectWidget" class="rootVoice {menu: 'submenuWidgets'}">
			<span><?php echo Core::_('Core.widgets')?></span> ▼
		</div>

		<div id="viewSite">
			<a target="_blank"><img src="<?php echo $this->getImageHref()?>external-link.png" alt="<?php echo Core::_('Admin.viewSite')?>" title="<?php echo Core::_('Admin.viewSite')?>"></img></a>
		</div>
	</div>
	<?php
	}
	?><div id="taskBar">
		<div id="subTaskBar">
			<div class="nav" onclick="$.tasksScroll(-42)">
				<img src="<?php echo $this->getImageHref()?>taskbar_left.png"></img>
			</div>
			<div id="tasks">
				<div id="tasksScroll"></div>
			</div>
			<div class="nav" onclick="$.tasksScroll(42)">
				<img src="<?php echo $this->getImageHref()?>taskbar_right.png"></img>
			</div>
		</div>
	</div>

	<script>
	(function($){
		$.UpdateTaskbar();

		// Check CPU performance
		var cookieFxOff = $.cookie('hostcms6FxOff');
		if (cookieFxOff == null) {
			var start = (new Date).getTime();
			for (var i=1, x = 0;i<99999;i++) { x += Math.floor(Math.random() * 1000 * i) / (Math.random() + 1)}
			var diff = (new Date).getTime() - start;
			cookieFxOff = diff < 20 ? 0 : 1;
			$.cookie('hostcms6FxOff', cookieFxOff, {expires: 1});
		}

		$.fx.off = cookieFxOff == 1;
	})(jQuery);
	</script>

	<?php if (Core_Auth::logged())
	{
	?><div id="logout">
		<div><a href="/admin/logout.php"><img src="<?php echo $this->getImageHref()?>logout.png"></img></a></div>
		<div id="login"><a href="/admin/logout.php"><?php echo Core_Entity::factory('User')->getCurrent()->login?></a></div>
	</div><?php
	}
	?></div><?php
		}

		return $this;
	}

	/**
	 * Show theme selector
	 */
	protected function _themeSelector()
	{
		?><div id="themeSelector"><?php
			$aConfig = Core_Config::instance()->get('skin_config');
			foreach ($aConfig as $sSkinName => $aSkinConfig)
			{
				$aSkinConfig += array('cover' => 'cover.jpg');
				Core::factory('Core_Html_Entity_A')
					->href('./index.php?skinName=' . $sSkinName)
					->add(
						Core::factory('Core_Html_Entity_Img')
							->src('/modules/skin/' . $sSkinName . '/images/' . $aSkinConfig['cover'])
							->alt(Core::_("Skin_{$sSkinName}.name"))
							->title(Core::_("Skin_{$sSkinName}.name"))
							->width(70)->height(70)
					)
					->execute();
			}
		?></div>
		<script>
		(function($){
			$('#theme').on('click', function(){
				var object = $('#themeSelector'), position = $(this).find('span').offset();
				object.HostCMSWindow({
					title: '<?php echo Core::_('Admin.themes')?>',
					autoOpen: true,
					Maximize: false,
					Minimize: false,
					Reload: false,
					width: 250,
					height: 100,
					minWidth: 250,
					minHeight: 100,
					position: [position.left, position.top + 30],
					beforeClose: function(event, ui){
						ui.options.WindowStatus = 'minimized'
					}
				});
			});

			$('#login').focus();
			$('#languages img').each(function(index, object){
				$(object).on('click', function(){
					$(this).setLanguage();
				});
			});
			$("#languages img[alt='<?php echo Core_I18n::instance()->getLng()?>']").setLanguage();
		})(jQuery);
		</script><?php
		return $this;
	}

	/**
	 * Show authorization form
	 */
	public function authorization()
	{
		$this->_mode = 'authorization';

		$message = Core_Skin::instance()->answer()->message;
		if ($message)
		{
			Core::factory('Core_Html_Entity_Div')
				->id('authorizationError')
				->value($message)
				->execute();

			// Reset message
			Core_Skin::instance()->answer()->message('');
		}

		?><div id="authorization">
			<div id="form">
				<form name="authorization" action="/admin/index.php" method="post">
					<div>
						<p><?php echo Core::_('Admin.authorization_form_login')?>:</p>
						<p><input name="login" type="text"></input></p>
					</div>
					<div>
						<p><?php echo Core::_('Admin.authorization_form_password')?>:</p>
						<p><input name="password" type="password"></input></p>
					</div>
					<div id="languages">
						<?php

						$aAdmin_Languages = Core_Entity::factory('Admin_Language')->findAll();
						foreach ($aAdmin_Languages as $oAdmin_Language)
						{
							if ($oAdmin_Language->active)
							{
								$oCore_Html_Entity_Img = Core::factory('Core_Html_Entity_Img')
									->src($this->getImageHref() . "/flags/{$oAdmin_Language->shortname}.png")
									->id("{$oAdmin_Language->shortname}Lng")
									->alt($oAdmin_Language->shortname)
									->title($oAdmin_Language->name)
									->execute();
							}
						}
						?>
						<input type="text" name="lng_value" id="language" />
					</div>
					<div>
						<p><label><input name="ip" type="checkbox" checked="checked"></input> <?php echo Core::_('Admin.authorization_form_ip')?></label></p>
					</div>
					<div>
						<p><input value="<?php echo Core::_('Admin.authorization_form_button')?>" name="submit" type="submit"></input></p>
					</div>
				</form>
			</div>
			<div id="rightImage">
				<img src="<?php echo $this->getImageHref()?>lock_authorization.png"></img>

				<div id="theme" class="disableSelect"><p><span><?php echo Core::_('Admin.themes')?></span> ▼</p></div>
			</div>
		</div>
		<?php
		$this->_themeSelector();
	}

	/**
	 * Change language
	 */
	public function changeLanguage()
	{
		$this->_mode = 'changeLanguage';

		/*$message = Core_Skin::instance()->answer()->message;
		if ($message)
		{
			Core::factory('Core_Html_Entity_Div')
				->id('authorizationError')
				->value($message)
				->execute();

			// Reset message
			Core_Skin::instance()->answer()->message('');
		}*/

		?><div id="authorization">
			<div id="form">
				<form name="authorization" action="./index.php" method="post">
					<div id="changeLanguage">
						<p><?php echo Core::_('Install.changeLanguage')?></p>
					</div>
					<div id="languages">
						<?php
						$aInstallConfig = Core_Config::instance()->get('install_config');
						$aLng = Core_Array::get($aInstallConfig, 'lng', array());

						foreach ($aLng as $shortname => $name)
						{
							$oCore_Html_Entity_Img = Core::factory('Core_Html_Entity_Img')
								->src($this->getImageHref() . "/flags/{$shortname}.png")
								->id("{$shortname}Lng")
								->alt($shortname)
								->title($name)
								->execute();
						}
						?>
						<input type="text" name="lng_value" id="language" />
					</div>
					<div>
						<p><input value="<?php echo Core::_('Install.next')?>" name="step_0" type="submit"></input></p>
					</div>
				</form>
			</div>
			<div id="rightImage" class="globe">
				<div id="theme" class="disableSelect"><p><span><?php echo Core::_('Admin.themes')?></span> ▼</p></div>
			</div>
		</div>
		<?php
		$this->_themeSelector();
	}

	/**
	 * Show back-end index page
	 * @return self
	 */
	public function index()
	{
		if ($this->_mode == 'blank')
		{
			return $this;
		}
		$this->_mode = 'index';

		$oUser = Core_Entity::factory('User')->getCurrent();

		if (is_null($oUser))
		{
			throw new Core_Exception('Undefined user.', array(), 0, FALSE, 0, FALSE);
		}

		$bAjax = Core_Array::getRequest('_', FALSE);

		// Widget settings
		if (!is_null(Core_Array::getGet('userSettings')))
		{
			if (!is_null(Core_Array::getGet('moduleId')))
			{
				$moduleId = intval(Core_Array::getGet('moduleId', 0));
				$type = intval(Core_Array::getGet('type', 0));
				$entity_id = intval(Core_Array::getGet('entity_id', 0));

				$oUser_Setting = $oUser->User_Settings->getByModuleIdAndTypeAndEntityId($moduleId, $type, $entity_id);
				is_null($oUser_Setting) && $oUser_Setting = Core_Entity::factory('User_Setting');

				$oUser_Setting->module_id = $moduleId;
				$oUser_Setting->type = $type;
				$oUser_Setting->entity_id = $entity_id;
				$oUser_Setting->position_x = intval(Core_Array::getGet('position_x'));
				$oUser_Setting->position_y = intval(Core_Array::getGet('position_y'));
				$oUser_Setting->width = intval(Core_Array::getGet('width'));
				$oUser_Setting->height = intval(Core_Array::getGet('height'));
				$oUser_Setting->active = intval(Core_Array::getGet('active', 1));

				$oUser->add($oUser_Setting);
			}

			// Shortcuts
			$aSh = Core_Array::getGet('sh');
			if ($aSh)
			{
				$type = 99;
				foreach ($aSh as $position => $moduleId)
				{
					$oUser_Setting = $oUser->User_Settings->getByModuleIdAndTypeAndEntityId($moduleId, 99, 0);

					is_null($oUser_Setting) && $oUser_Setting = Core_Entity::factory('User_Setting');

					$oUser_Setting->module_id = $moduleId;
					$oUser_Setting->type = $type;
					$oUser_Setting->position_x = Core_Array::getGet('blockId');
					$oUser_Setting->position_y = $position;

					$oUser->add($oUser_Setting);
				}
			}

			$oAdmin_Answer = Core_Skin::instance()->answer();
			$oAdmin_Answer
				->ajax($bAjax)
				->execute();
			exit();
		}

		// Widget ajax loading
		if (!is_null(Core_Array::getGet('ajaxWidgetLoad')))
		{
			ob_start();
			if (!is_null(Core_Array::getGet('moduleId')))
			{
				$moduleId = intval(Core_Array::getGet('moduleId'));
				$type = intval(Core_Array::getGet('type', 0));

				$oUser_Setting = $oUser->User_Settings->getByModuleIdAndTypeAndEntityId($moduleId, $type, 0);
				!is_null($oUser_Setting) && $oUser_Setting->active(1)->save();

				$modulePath = $moduleId == 0
					? 'core'
					: Core_Entity::factory('Module', $moduleId)->path;

				$sSkinModuleName = $this->getSkinModuleName($modulePath);

				if (class_exists($sSkinModuleName))
				{
					$Core_Module = new $sSkinModuleName();
					$Core_Module->adminPage($type, $bAjax && is_null(Core_Array::getGet('widgetAjax')));
				}
				else
				{
					throw new Core_Exception('SkinModuleName does not found.');
				}
			}
			else
			{
				throw new Core_Exception('moduleId does not exist.');
			}

			$oAdmin_Answer = Core_Skin::instance()->answer();
			$oAdmin_Answer
				->content(ob_get_clean())
				->ajax($bAjax)
				->execute();
			exit();
		}

		// Ajax note creating
		if (!is_null(Core_Array::getGet('ajaxCreateNote')))
		{
			$oUser_Note = Core_Entity::factory('User_Note')->save();

			$oUser_Note->User_Setting
				->type(98)
				->position_x(intval(Core_Array::getGet('position_x')))
				->position_y(intval(Core_Array::getGet('position_y')))
				->save();

			$oAdmin_Answer = Core_Skin::instance()->answer();
			$oAdmin_Answer
				->content($oUser_Note->id)
				->ajax($bAjax)
				->execute();
			exit();
		}

		// Ajax note changing
		if (!is_null(Core_Array::getGet('ajaxNote')))
		{
			$oUser_Note = Core_Entity::factory('User_Note')->find(intval(Core_Array::getGet('entity_id')));

			if (!is_null($oUser_Note->id) && $oUser_Note->user_id == $oUser->id)
			{
				switch (Core_Array::getGet('action'))
				{
					case 'delete':
						$oUser_Note->delete();
					break;
					case 'save':
						$oUser_Note->value(Core_Array::getPost('value', ''))->save();
					break;
				}
			}

			$oAdmin_Answer = Core_Skin::instance()->answer();
			$oAdmin_Answer
				->ajax($bAjax)
				->execute();
			exit();
		}

		$ajaxDesktopLoad = !is_null(Core_Array::getGet('ajaxDesktopLoad'));
		$ajaxDesktopLoad && ob_start();

		?><div id="desktop" class="cmVoice {cMenu: 'desktop_context'}">
			<div id="shortcuts">

			<?php
			// Список основных меню
			$aMainMenu = array();

			$iMenuCount = 0;
			$aModules = $this->_getAllowedModules();
			foreach ($aModules as $oModule)
			{
				$oCore_Module = Core_Module::factory($oModule->path);

				if ($oModule->active && $oCore_Module)
				{
					$aMenu = $oCore_Module->getMenu();

					if (is_array($aMenu))
					{
						foreach ($aMenu as $aTmpMenu)
						{
							if (isset($aTmpMenu['href']))
							{
								$oUser_Setting = $oUser->User_Settings->getByModuleIdAndTypeAndEntityId($oModule->id, 99, 0);

								$block = is_null($oUser_Setting) ? floor($iMenuCount % 3) : $oUser_Setting->position_x;

								$aMainMenu
									[$block]
									['sub'][] = array(
											'position' => is_null($oUser_Setting) ? 999 : $oUser_Setting->position_y,
											'block' => $block
										) + $aTmpMenu + array(
										'sorting' => 0,
										'moduleId' => $oModule->id,
										'image' => $oModule->path . '.png'
									);
								$iMenuCount++;
							}
						}
					}
				}
			}

			$iUlCount = 5;
			$aUl = array();
			for ($iUl = 0; $iUl < $iUlCount; $iUl++)
			{
				$aUl[] = Core::factory('Core_Html_Entity_Ul')
					->class('sortable')
					->id('sortable' . $iUl);
			}

			$sJs = '(function($){';
			foreach ($aMainMenu as $key => $aAdminMenu)
			{
				if (isset($aAdminMenu['sub']))
				{
					array_multisort($aAdminMenu['sub']);

					foreach ($aAdminMenu['sub'] as $aMenu)
					{
						$sId = 'sh_' . $aMenu['moduleId'];
						isset($aUl[$aMenu['block']]) && $aUl[$aMenu['block']]
							->add(
								Core::factory('Core_Html_Entity_Li')
									->id($sId)
									->add(
										Core::factory('Core_Html_Entity_Div')
											->class('shortcut')
											->add(
												Core::factory('Core_Html_Entity_Img')
													->src($this->getImageHref() . 'module/' . (
														empty($aMenu['image'])
															? 'default.png'
															: $aMenu['image'])
														)
											)
									)->add(
										Core::factory('Core_Html_Entity_Div')
											->class('shortcutLabel')
											//->value($aMenu['name'])
											->add(
												Core::factory('Core_Html_Entity_A')
												->href($aMenu['href'])
												->value($aMenu['name'])
											)
									)
							);

						$sJs .= "$('#{$sId}').linkShortcut({path: '" . htmlspecialchars($aMenu['href']) . "', Minimize: true, Closable: true});" . PHP_EOL;
					}
				}
			}

			foreach ($aUl as $oCore_Html_Entity_Ul)
			{
				$oCore_Html_Entity_Ul->execute();
			}

			$sJs .= '})(jQuery);';
			Core::factory('Core_Html_Entity_Script')
				->value($sJs)
				->execute();

			?></div><?php

			// Core
			$sSkinModuleName = $this->getSkinModuleName('core');
			if (class_exists($sSkinModuleName))
			{
				$Core_Module = new $sSkinModuleName();
				$aTypes = $Core_Module->getAdminPages();

				foreach ($aTypes as $type => $title)
				{
					$oUser_Setting = $oUser->User_Settings->getByModuleIdAndTypeAndEntityId(0, $type, 0);

					if (is_null($oUser_Setting) || $oUser_Setting->active)
					{
						$Core_Module->adminPage($type);
					}
				}
			}

			// Other modules
			$oSite = Core_Entity::factory('Site', CURRENT_SITE);
			foreach ($aModules as $oModule)
			{
				$sSkinModuleName = $this->getSkinModuleName($oModule->path);

				$Core_Module = class_exists($sSkinModuleName)
					? new $sSkinModuleName()
					: $oModule->Core_Module;

				if ($oModule->active
					&& !is_null($Core_Module)
					&& method_exists($Core_Module, 'adminPage')
					&& $oUser->checkModuleAccess(array($oModule->path), $oSite))
				{
					// 77 - widget settings
					$oUser_Setting = $oUser->User_Settings->getByModuleIdAndTypeAndEntityId($oModule->id, 77, 0);

					(is_null($oUser_Setting) || $oUser_Setting->active) && $Core_Module->adminPage();
				}
			}

			// Notes
			$aUser_Notes = $oUser->User_Notes->findAll();
			foreach ($aUser_Notes as $oUser_Note)
			{
				Core::factory('Core_Html_Entity_Div')
					->class('note')
					->id("note{$oUser_Note->id}")
					->value($oUser_Note->value)
					->style("left: {$oUser_Note->User_Setting->position_x}px; top: {$oUser_Note->User_Setting->position_y}px")
					->execute();
			}
			?>

			<!-- Контекстное меню рабочего стола -->
			<div id="desktop_context" class="mbmenu">
				<a class="{action: '$(\'<div>\').appendTo(\'body\').createNote({isNew: true})', img: '<?php echo $this->getImageHref()?>note_add.png'}"><?php echo Core::_('Core.addNote')?></a>
				<a class="{menu: 'submenuWidgets', img: '<?php echo $this->getImageHref()?>module/module.png'}"><?php echo Core::_('Core.widgets')?></a>
				<a class="{menu: 'submenuSites', img: '<?php echo $this->getImageHref()?>module/site.png'}"><?php echo Core::_('Site.model_name')?></a>
			</div>

			<div id="submenuWidgets" class="mbmenu">
				<a class="{action: '$.widgetLoad({ path: \'/admin/index.php?ajaxWidgetLoad&widgetAjax&moduleId=0&type=1\' })', img: '<?php echo $this->getImageHref()?>module/eventlog.png'}"><?php echo Core::_('Admin.index_systems_events')?></a>
				<a class="{action: '$.widgetLoad({ path: \'/admin/index.php?ajaxWidgetLoad&widgetAjax&moduleId=0&type=2\' })', img: '<?php echo $this->getImageHref()?>module/info.png'}"><?php echo Core::_('Admin.index_systems_characteristics')?></a>
				<?php

				// Other modules
				$oCurrentSite = Core_Entity::factory('Site', CURRENT_SITE);
				foreach ($aModules as $oModule)
				{
					$sSkinModuleName = $this->getSkinModuleName($oModule->path);

					$Core_Module = class_exists($sSkinModuleName)
						? new $sSkinModuleName()
						: $oModule->Core_Module;

					if ($oModule->active
						&& !is_null($Core_Module)
						&& method_exists($Core_Module, 'adminPage')
						&& $oUser->checkModuleAccess(array($oModule->path), $oCurrentSite))
					{
						$aAdminPages = $Core_Module->getAdminPages();

						foreach ($aAdminPages as $type => $aAdminPage)
						{
							$aAdminPage += array('title' => '');
							$oUser_Setting = $oUser->User_Settings->getByModuleIdAndTypeAndEntityId($oModule->id, $type, 0);

							Core::factory('Core_Html_Entity_A')
								->class("{action: '$.widgetLoad({ path: \'/admin/index.php?ajaxWidgetLoad&widgetAjax&moduleId={$oModule->id}&type={$type}\' })', img: '{$this->getImageHref()}module/{$oModule->path}.png'}")
								->value($aAdminPage['title'])
								->execute();
						}
					}
				}
				?>
			</div>

			<div id="submenuSites" class="mbmenu">
				<?php
				$aSites = $oUser->getSites();

				$aOptions = array();
				foreach ($aSites as $oSite)
				{
					//$aOptions[$oSite->id] = Core_Str::cut($oSite->name, 35);
					Core::factory('Core_Html_Entity_A')
						->class("{action: '$.reloadDesktop({$oSite->id})', img: '" . (
							$oSite->id == CURRENT_SITE ? ' /modules/skin/default/images/check.png' : ''
							) . "'}")
						->value(Core_Str::cut($oSite->name, 35))
						->execute();
				}
				?>
			</div>

			<!-- Note context menu -->
			<div id="note_conext" class="mbmenu">
				<!-- <a rel="text">Заметка</a>
				<a rel="separator"> </a> -->
				<a class="{action: '$($.mbMenu.lastContextMenuEl).destroyNote()', img: '<?php echo $this->getImageHref()?>note_delete.png'}"><?php echo Core::_('Core.deleteNote')?></a>
				<a class="{action: '$(\'<div>\').appendTo(\'body\').createNote({isNew: true})', img: '<?php echo $this->getImageHref()?>note_add.png'}"><?php echo Core::_('Core.addNote')?></a>
			</div>

		</div>

		<script>
		$(function() {
			$("#sortable0,#sortable1,#sortable2,#sortable3,#sortable4").sortable({
				cursor: 'move',
				distance: 5,
				helper : 'clone',
				//connectWith: '#shortcuts>.sortable',
				start: function(event, ui) {
					ui.item.toggleClass('drag');
				},
				stop: function(event, ui) {
					ui.item.toggleClass('drag');
				},
				update: function(event, ui) {
					var object = $(this), reg = /sortable(\d+)/, arr = reg.exec(object.prop('id'));
					$.ajax({
						url: '/admin/index.php?' + 'userSettings&blockId=' + arr[1] + '&' + object.sortable('serialize'),
						type: 'get',
						dataType: 'json',
						success: function(){}
					});
				}
			})
			.sortable('option', 'connectWith', '#shortcuts>.sortable')
			.disableSelection();

			$("#desktop div.note").createNote();

			$(window).bind('beforeunload', function(){return ''});

			// Доступные текущие позиции курсора
			$('#desktop').mousedown(function(e){
				window.mouseXPos = e.pageX;
				window.mouseYPos = e.pageY;
			});

			// Создаем контекстное меню
			$.createContextualMenu();
			$('#selectSite span').text('<?php echo Core_Str::escapeJavascriptVariable(
				Core_Str::cut($oCurrentSite->name, 25)
			)?>');

			<?php
			$oAlias = $oCurrentSite->getCurrentAlias();
			if (!is_null($oAlias))
			{
				?>$('#viewSite a').show().prop('href', 'http://<?php echo Core_Str::escapeJavascriptVariable($oAlias->name)?>');<?php
			}
			else
			{
				?>$('#viewSite a').hide();<?php
			}
			?>
		});
		</script>

		<?php
		if ($ajaxDesktopLoad)
		{
			$oAdmin_Answer = Core_Skin::instance()->answer();
			$oAdmin_Answer
				->content(ob_get_clean())
				->ajax($bAjax)
				->execute();
			exit();
		}
		return $this;
	}

	/**
	 * Show footer
	 */
	public function footer()
	{
		if ($this->_mode != 'blank')
		{
		?><div id="footer">
			<div id="copyright">&copy; 2005–2019 ООО «Хостмэйк»</div>
			<div id="links">
				<p><?php echo Core::_('Admin.website')?> <a href="http://www.hostcms.ru" target="_blank">www.hostcms.ru</a></p>
				<p><?php echo Core::_('Admin.support_email')?> <a href="mailto:support@hostcms.ru">support@hostcms.ru</a></p>
			</div>
		</div>
		<?php
		}
		?>
	</body>
	</html><?php
	}

	/**
	 * Get message.
	 *
	 * <code>
	 * echo Core_Message::get(Core::_('constant.name'));
	 * echo Core_Message::get(Core::_('constant.message', 'value1', 'value2'));
	 * </code>
	 * @param $message Message text
	 * @param $type Message type
	 * @see Core_Message::show()
	 * @return string
	 */
	public function getMessage($message, $type = 'message')
	{
		$return = '<div id="' . $type . '">' . $message . '</div>';
		return $return;
	}
}