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
class Skin_Bootstrap extends Core_Skin
{
	/**
	 * Name of the skin
	 * @var string
	 */
	protected $_skinName = 'bootstrap';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$lng = $this->getLng();

		$this
			->addJs('/modules/skin/' . $this->_skinName . '/js/skins.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/jquery-2.0.3.min.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/bootstrap.min.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/bootstrap-hostcms.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/main.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/datetime/moment.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/datetime/bootstrap-datetimepicker.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/datetime/' . $lng . '.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/jquery.slimscroll.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/toastr/toastr.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/bootbox/bootbox.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/jquery.form.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/codemirror/lib/codemirror.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/codemirror/mode/css/css.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/codemirror/mode/htmlmixed/htmlmixed.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/codemirror/mode/javascript/javascript.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/codemirror/mode/clike/clike.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/codemirror/mode/php/php.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/codemirror/mode/xml/xml.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/codemirror/mode/smarty/smarty.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/codemirror/addon/selection/active-line.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/codemirror/addon/search/search.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/codemirror/addon/search/searchcursor.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/codemirror/addon/dialog/dialog.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/star-rating.min.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/typeahead-bs2.min.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/ui/jquery-ui.min.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/select2/select2.min.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/select2/i18n/' . $lng . '.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/dropzone/dropzone.min.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/colorpicker/jquery.minicolors.min.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/nouislider/nouislider.min.js')
			->addJs('/modules/skin/' . $this->_skinName . '/js/wickedpicker/wickedpicker.min.js')
			//->addJs('/modules/skin/' . $this->_skinName . '/js/jRange/jquery.range-min.js')
			;

		$this
			->addCss('/modules/skin/' . $this->_skinName . '/css/bootstrap.min.css')
			->addCss('/modules/skin/' . $this->_skinName . '/css/font-awesome.min.css')
			->addCss('/modules/skin/' . $this->_skinName . '/fonts/open-sans/open-sans.css')
			->addCss('/modules/skin/' . $this->_skinName . '/css/hostcms.min.css')
			->addCss('/modules/skin/' . $this->_skinName . '/css/animate.min.css')
			->addCss('/modules/skin/' . $this->_skinName . '/css/dataTables.bootstrap.css')
			->addCss('/modules/skin/' . $this->_skinName . '/css/bootstrap-datetimepicker.css')
			->addCss('/modules/skin/' . $this->_skinName . '/js/codemirror/lib/codemirror.css')
			->addCss('/modules/skin/' . $this->_skinName . '/js/codemirror/addon/dialog/dialog.css')
			->addCss('/modules/skin/' . $this->_skinName . '/css/star-rating.min.css')
			->addCss('/modules/skin/' . $this->_skinName . '/css/bootstrap-hostcms.css')
			->addCss('/modules/skin/' . $this->_skinName . '/js/dropzone/dropzone.css')
			->addCss('/modules/skin/' . $this->_skinName . '/js/nouislider/nouislider.min.css')
			->addCss('/modules/skin/' . $this->_skinName . '/css/wickedpicker.min.css')
			//->addCss('/modules/skin/' . $this->_skinName . '/js/jRange/jquery.range.css')
			;
	}

	/**
	 * Show HTML head
	 */
	public function showHead()
	{
		$timestamp = $this->_getTimestamp();

		$lng = $this->getLng();

		foreach ($this->_css as $sPath)
		{
			?><link type="text/css" href="<?php echo $sPath . '?' . $timestamp?>" rel="stylesheet" /><?php
			echo PHP_EOL;
		}?>

		<?php
		$this->addJs('/modules/skin/' . $this->_skinName . "/js/lng/{$lng}/{$lng}.js");
		foreach ($this->_js as $sPath)
		{
			Core::factory('Core_Html_Entity_Script')
				->src($sPath . '?' . $timestamp)
				->execute();
		}
		/*<!-- Fonts -->
		<link href="//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,300,400,600,700&subset=latin,cyrillic" rel="stylesheet" type="text/css">*/
		?>
		<script>
		<?php
		$bLogged = Core_Auth::logged();
		if ($bLogged)
		{
			?>var HostCMSFileManager = new HostCMSFileManager();

			<?php if (!defined('CONFIRM_CLOSE_BROWSER') || CONFIRM_CLOSE_BROWSER)
			{
				?>$(window).on('beforeunload', function () {return ' ';});<?php
			}
		}
		?>
		</script>

		<script src="/admin/wysiwyg/jquery.tinymce.min.js?<?php echo $timestamp?>"></script>
		<?php
		if ($this->_mode != 'install')
		{
			$wallpaperId = isset($_COOKIE['wallpaper-id'])
				? intval($_COOKIE['wallpaper-id'])
				: NULL;

			if ($bLogged)
			{
				$oUser = Core_Entity::factory('User')->getCurrent();
				$oModule = Core_Entity::factory('Module')->getByPath('user');
				$type = 95;
				$oUser_Settings = $oUser->User_Settings;
				$oUser_Settings->queryBuilder()
					->where('user_settings.module_id', '=', $oModule->id)
					->where('user_settings.type', '=', $type)
					->where('user_settings.active', '=', 1)
					->limit(1);

				$aUser_Settings = $oUser_Settings->findAll();

				isset($aUser_Settings[0])
					&& $wallpaperId = $aUser_Settings[0]->entity_id;
			}
			elseif (is_null($wallpaperId))
			{
				$oUser_Wallpapers = Core_Entity::factory('User_Wallpaper');
				$oUser_Wallpapers->queryBuilder()
					->clearOrderBy()
					->orderBy('RAND()')
					->limit(1);

				$aUser_Wallpapers = $oUser_Wallpapers->findAll();
				isset($aUser_Wallpapers[0])
					&& $wallpaperId = $aUser_Wallpapers[0]->id;
			}

			$sWallpaperPath = $wallpaperId
				? '/upload/user/wallpaper/' . htmlspecialchars(Core_Entity::factory('User_Wallpaper', $wallpaperId)->image_large)
				: '/modules/skin/bootstrap/img/bg.jpg';

			echo PHP_EOL;
			?><style type="text/css">body.hostcms-bootstrap1:before { background-image: url("<?php echo $sWallpaperPath?>"); }</style><?php
		}

		return $this;
	}

	protected function _navBar()
	{
		$oUser = Core_Entity::factory('User')->getCurrent();

		?><!-- Navbar -->
		<div class="navbar">
			<div class="navbar-inner">
				<div class="navbar-container">
					<!-- Navbar Barnd -->
					<div class="navbar-header pull-left">
						<a href="/admin/" <?php echo isset($_SESSION['valid_user'])
							? 'onclick="' . "$.adminLoad({path: '/admin/index.php'}); return false" . '"'
							: ''?> class="navbar-brand"><?php
							$sLogoTitle = Core_Auth::logged() ? ' v. ' . htmlspecialchars(CURRENT_VERSION) : '';
							?><img src="/modules/skin/bootstrap/img/logo-white.png" alt="(^) HostCMS" title="HostCMS <?php echo $sLogoTitle?>" /></a>
					</div>
					<!-- /Navbar Barnd -->
					<!-- Sidebar Collapse -->
					<div class="sidebar-collapse" id="sidebar-collapse">
						<i class="collapse-icon fa fa-bars"></i>
					</div>
					<!-- /Sidebar Collapse -->
					<!-- Account Area and Settings -->
					<div class="navbar-header pull-right">
					<?php
					if (Core_Auth::logged())
					{
						?><div class="navbar-account">
							<ul class="account-area">
								<li id="bookmarks">
									<a href="#" title="<?php echo Core::_('Admin.bookmarks')?>" data-toggle="dropdown" class="dropdown-toggle">
										<i class="icon fa fa-star-o"></i>
										<span class="badge hidden"></span>
									</a>
									<div id="bookmarksListBox" class="pull-left dropdown-menu dropdown-arrow dropdown-bookmark dropdown-notifications">
										<div class="scroll-bookmarks">
											<ul>
												<li id="bookmark-0">
													<a href="#">
														<div class="clearfix">
															<div class="notification-icon">
																<i class="fa fa-info bg-themeprimary white"></i>
															</div>
															<div class="notification-body">
																<span class="title margin-top-5"><?php echo Core::_('User_Bookmark.no_bookmarks')?></span>
															</div>
														</div>
													</a>
												</li>
											</ul>
										</div>
									</div>

									<?php
									$oModule = Core_Entity::factory('Module')->getByPath('user');
									if ($oModule)
									{
									?>
									<!--/Bookmark Dropdown-->
									<script>
									$(function (){
										$.bookmarksPrepare();

										$('.navbar-account #bookmarksListBox').data({
											moduleId: <?php echo $oModule->id?>
										});

										$.refreshBookmarksList();
									});
									</script>
									<?php
									}
									?>
								</li>
								<li>
									<a id="sound-switch" title="<?php echo Core::_('Admin.sound')?>" href="#">
										<i class="icon fa fa-<?php echo $oUser->sound ? 'volume-up' : 'volume-off'?>"></i>
									</a>

									<?php
									$oModule = Core_Entity::factory('Module')->getByPath('user');
									?>
									<script>
										$(function(){
											$("#sound-switch")
												.data('soundEnabled', <?php echo $oUser->sound ? 'true' : 'false'?>)
												.on('click', {path: '/admin/index.php?ajaxWidgetLoad&moduleId=<?php echo $oModule->id?>&type=84'}, $.soundSwitch );
										});
									</script>
								</li>
								<li id="notifications-clock">
									<a href="#" title="<?php echo Core::_('Admin.events')?>" data-toggle="dropdown" class="task-area dropdown-toggle">
										<div class="clock">
											<ul>
												<li id="hours"> </li><li id="point">:</li><li id="min"> </li>
											</ul>
										</div>
									</a>
									<script>
									$(function(){
										$.refreshClock();
									});
									</script>
									<?php
									if (Core::moduleIsActive('event'))
									{
										?><div id="notificationsClockListBox" class="pull-right dropdown-menu dropdown-arrow dropdown-notifications">
											<div class="scroll-notifications-clock">
												<ul>
												<?php
													$aEvents = $oUser->Events->getToday(FALSE);
													if (count($aEvents))
													{
														foreach ($aEvents as $oEvent)
														{
															?><li id="event-<?php echo $oEvent->id?>">
																<a href="/admin/event/index.php?hostcms[action]=edit&hostcms[operation]=&hostcms[current]=1&hostcms[checked][0][<?php echo $oEvent->id?>]=1" onclick="$(this).parents('li.open').click(); $.adminLoad({path: '/admin/event/index.php?hostcms[action]=edit&hostcms[operation]=&hostcms[current]=1&hostcms[checked][0][<?php echo $oEvent->id?>]=1'}); return false">
																	<div class="clearfix notification-clock">
																		<div class="notification-icon">
																			<i class="<?php echo htmlspecialchars($oEvent->Event_Type->icon)?> white" style="background-color: <?php echo htmlspecialchars($oEvent->Event_Type->color)?>"></i>
																		</div>
																		<div class="notification-body">
																			<span class="title"><?php echo htmlspecialchars($oEvent->name)?></span>
																			<span class="description"><i class="fa fa-clock-o"></i> <?php echo Event_Controller::getDateTime($oEvent->start)?> — <span class="notification-time"><?php echo Event_Controller::getDateTime($oEvent->finish)?></span></span>
																		</div>
																	</div>
																</a>
															</li><?php
														}
													}
													else
													{
														?><li id="event-0">
															<a href="#">
																<div class="clearfix">
																	<div class="notification-icon">
																		<i class="fa fa-info bg-themeprimary white"></i>
																	</div>
																	<div class="notification-body">
																		<span class="title margin-top-5"><?php echo Core::_('Notification.no_notifications')?></span>
																	</div>
																</div>
															</a>
														</li><?php
													}
													?>
													<li class="all-tasks">
														<a href="/admin/event/index.php" onclick="$(this).parents('li.open').click(); $.adminLoad({path: '/admin/event/index.php'}); return false"> <?php echo Core::_('Event.all-tasks')?></a>
													</li>
												</ul>
											</div>
										</div>
										<?php
										$oModule = Core_Entity::factory('Module')->getByPath('event');
										if ($oModule)
										{
											?><script>
												$(function(){
													$.eventsPrepare();

													var notificationsClockListBox = document.getElementById('notificationsClockListBox');
													notificationsClockListBox.onclick = function(event){
														event.stopPropagation ? event.stopPropagation() : (event.cancelBubble=true);
													};

													$('.navbar-account #notificationsClockListBox').data({
														'currentUserId': <?php echo $oUser->id?>,
														'moduleId': <?php echo $oModule->id?>
													});
												});
												</script><?php
										}
									}
									?>
								</li>
								<?php
								if (Core::moduleIsActive('notification'))
								{
								?>
								<li id="notifications">
									<a href="#" title="<?php echo Core::_('Admin.notifications')?>" data-toggle="dropdown" class="dropdown-toggle">
										<i class="icon fa fa-bell"></i>
										<span class="badge hidden"></span>
									</a>
									<div id="notificationsListBox" class="pull-right dropdown-menu dropdown-arrow dropdown-notifications">
										<div class="scroll-notifications">
										<ul>
											<li id="notification-0">
												<a href="#">
													<div class="clearfix">
														<div class="notification-icon">
															<i class="fa fa-info bg-themeprimary white"></i>
														</div>
														<div class="notification-body">
															<span class="title margin-top-5"><?php echo Core::_('Notification.no_notifications')?></span>
														</div>
													</div>
												</a>
											</li>
										</ul>
										</div>
										<div class="footer padding-10">
											<span class="input-icon">
												<input type="text" class="form-control input-xs" id="notification-search" />
												<i class="glyphicon glyphicon-search"></i>
												<i class="glyphicon glyphicon-remove palegreen" title="<?php echo Core::_('Notification.search_clear_button_tittle');?>" style="cursor:pointer; position: absolute; left: 93%;bottom: 0;line-height: 24px;font-size: 10px;width: 24px;padding-top: 0px;"></i>
											</span>
											<span class="pull-right darkorange" style="display: block; margin-right: 5px; cursor:pointer;"><i class="fa fa-trash-o" title="<?php echo Core::_('Notification.notifications_trash_title'); ?>"></i></span>
										</div>
									</div>

									<?php
									$oModule = Core_Entity::factory('Module')->getByPath('notification');
									if ($oModule)
									{
									?>
									<!--/Notification Dropdown-->
									<script>
									$(function (){
										$.notificationsPrepare();
										$('.navbar-account #notificationsListBox').data({
											// Идентификатор последнего загруженного уведомления
											'lastNotificationId': 0, //$('.navbar-account #notificationsListBox .scroll-notifications > ul li:first').attr('id'),
											'currentUserId': <?php echo $oUser->id?>,
											'moduleId': <?php echo $oModule->id?>
										});

										$.refreshNotificationsList();
									});
									</script>
									<?php
									}
									?>
								</li>
								<?php
								}
								?>
								<li>
								<?php
								$oCurrentSite = Core_Entity::factory('Site', CURRENT_SITE);
								$oAlias = $oCurrentSite->getCurrentAlias();

								if (!is_null($oAlias))
								{
									?><a title="<?php echo Core::_('Admin.viewSite')?>" target="_blank" href="//<?php echo htmlspecialchars($oAlias->name)?>">
										<i class="icon fa fa-desktop"></i>
									</a><?php
								}
								?>
								</li>
								<li>
									<span class="account-area-site-name hidden-xs hidden-sm hidden-md">
										<?php echo htmlspecialchars($oCurrentSite->name)?>
									</span>

									<a class="dropdown-toggle" id="sitesListIcon" data-toggle="dropdown" title="<?php echo Core::_('Admin.selectSite')?>" href="#">
										<i class="icon fa fa-globe"></i>
										<span class="badge"></span>
									</a>
									<!--Tasks Dropdown-->
									<div id="sitesListBox" class="pull-right dropdown-menu dropdown-arrow dropdown-notifications"></div>

									<script>
										var sitesListBox = document.getElementById('sitesListBox');
										sitesListBox.onclick = function(event){
											event.stopPropagation ? event.stopPropagation() : (event.cancelBubble=true);
										};

										$(document).ready(function() {
											$.loadSiteList();
										});
									</script>
									<!--/Tasks Dropdown-->
								</li>
								<li>
									<a class="dropdown-toggle" data-toggle="dropdown" title="<?php echo Core::_('Admin.backend-language')?>" href="#">
										<i class="icon fa fa-flag"></i>
									</a>

									<div id="languagesListBox" class=" pull-right dropdown-menu dropdown-arrow dropdown-notifications">
										<div class="scroll-languages">
											<ul>
											<?php
											$aAdmin_Languages = Core_Entity::factory('Admin_Language')
												->getAllByActive(1);

											foreach ($aAdmin_Languages as $oAdmin_Language)
											{
												?><li>
												<a <?php echo Core_Array::getSession('current_lng') != $oAdmin_Language->shortname ? 'href="/admin/index.php?lng_value=' . htmlspecialchars($oAdmin_Language->shortname) . '"' : ''?> onmousedown="$(window).off('beforeunload')">

													<div class="clearfix">
														<div class="notification-icon">
															<img src="<?php echo '/modules/skin/bootstrap/img/flags/' . htmlspecialchars($oAdmin_Language->shortname) . '.png'?>" class="message-avatar" alt="<?php echo htmlspecialchars($oAdmin_Language->name)?>" />
														</div>
														<div class="notification-body">
															<?php echo htmlspecialchars($oAdmin_Language->name)?>
														</div>
														<div class="notification-extra">
															<?php
															if (Core_Array::getSession('current_lng') == $oAdmin_Language->shortname)
															{
																?><i class="fa fa-check-circle-o pull-right green"></i><?php
															}
															?>
														</div>
													</div>
												</a></li>
											<?php
											}
											?>
											</ul>
										</div>
									</div>
									<script>
										var languagesListBox = document.getElementById('languagesListBox');
										languagesListBox.onclick = function(event){
											event.stopPropagation ? event.stopPropagation() : (event.cancelBubble=true);
										};
										$('.scroll-languages').slimscroll({
											// height: '215px',
											height: 'auto',
											color: 'rgba(0, 0, 0, 0.3)',
											size: '5px'
										});
									</script>
								</li>
								<?php
								$oModule = Core_Entity::factory('Module')->getByPath('user');
								if (Core::$mainConfig['chat'])
								{
								?><li>
									<a id="chat-link" title="<?php echo Core::_('Admin.chat')?>" href="#">
										<i class="icon glyphicon glyphicon-comment"></i>
										<span class="badge hidden"></span>
									</a>
									<div id="chatbar" class="page-chatbar">
										<div class="chatbar-contacts">
											<ul class="contacts-list">
												<!-- Типовые -->
												<li class="contact hidden">
													<div class="contact-avatar">
														<img />
													</div>
													<div class="contact-info">
														<div class="contact-name"><span class="badge">0</span></div>
														<div class="contact-status">
															<div data-user-id="" class="online"></div>
															<div class="status"></div>
														</div>
														<div class="last-chat-time"></div>
													</div>
												</li>
											</ul>
										</div>
										<div class="chatbar-messages" style="display: none;">
											<div class="messages-contact">
												<div class="contact-avatar">
													<img />
												</div>
												<div class="contact-info">
													<div class="contact-name"></div>
													<div class="contact-status">
														<div data-user-id="" class="online"></div>
														<div class="status"></div>
													</div>
													<div class="last-chat-time"></div>
													<div class="back">
														<i class="fa fa-arrow-circle-left"></i>
													</div>
												</div>
											</div>
											<div id="messages-none" class="hidden margin-left-10 margin-top-10"><?php echo Core::_('User.chat_messages_none')?></div>
											<ul class="messages-list" data-module-id="<?php echo $oModule->id ?>">
												<li class="message hidden">
													<div class="message-info">
														<div class="bullet"></div>
														<div class="contact-name"></div>
														<div class="message-time"></div>
													</div>
													<div class="message-body"></div>
												</li>
											</ul>
											<form method="post">
												<div class="send-message">
													<span class="input-icon icon-right">
														<textarea rows="4" class="form-control" placeholder="<?php echo Core::_('User.chat_message')?>"></textarea>
														<i class="fa fa-comment-o themeprimary"></i>
													</span>
												</div>
											</form>
											<div id="new_messages" class="hidden margin-top-10 text-align-center"><?php echo Core::_('User.chat_count_new_message')?> <span class="count_new_messages"></span><i class="fa fa-caret-down margin-left-5"></i></div>
											<i class="fa fa-spinner fa-pulse fa-3x chatbar-message-spinner hidden"></i>
										</div>
									</div>
									<script>
										$(function(){
											// Chat
											$('.page-container').append($('#chatbar'));
											$("#chat-link, div.back").on('click', {path: '/admin/index.php?ajaxWidgetLoad&moduleId=<?php echo $oModule->id?>&type=77', context: $('#chatbar .contacts-list') }, $.chatGetUsersList );

											$('.contacts-list')
												.on('click', 'li.contact', $.chatClearMessagesList)
												.on('click', 'li.contact', { path: '/admin/index.php?ajaxWidgetLoad&moduleId=<?php echo $oModule->id?>&type=78' }, $.chatGetUserMessages);

											$('.send-message textarea').on('keyup', { path: '/admin/index.php?ajaxWidgetLoad&moduleId=<?php echo $oModule->id?>&type=79' }, $.chatSendMessage);

											$.refreshChat({path: '/admin/index.php?ajaxWidgetLoad&moduleId=<?php echo $oModule->id?>&type=80'});
										});
									</script>
								</li>
								<?php
								}
								$oUserName = ($oUser->name != '' || $oUser->surname != '') ? ($oUser->name . ' ' . $oUser->surname) : '';
								?>
								<li id="user-info-dropdown">
									<a class="login-area dropdown-toggle" data-toggle="dropdown">
										<div class="avatar avatar-user" title="<?php echo Core::_('Admin.profile')?>">
											<img src="<?php echo $oUser->getAvatar()?>">
										</div>
										<section class="hidden-xs">
											<h2>
												<span class="profile">
													<span>
														<?php echo htmlspecialchars($oUserName != '' ? $oUserName : $oUser->login)?>
													</span>
												</span>
											</h2>
										</section>
									</a>
									<!--Login Area Dropdown-->
									<ul class="pull-right dropdown-menu dropdown-arrow dropdown-login-area">
										<?php
										if (strlen($oUserName))
										{
											?><li class="username">
												<a><?php echo htmlspecialchars($oUserName)?></a>
											</li><?php
										}

										$currentDay = Core_Date::timestamp2sqldate(time());

										$duration = $oUser->getWorkdayDuration($currentDay);

										$workdayStatus = $oUser->getStatusWorkday($currentDay);

										$aWorkdayStatuses = array('ready', 'denied', 'working', 'break', 'completed', 'expired');

										$statusClassName = Core_Array::get($aWorkdayStatuses, $workdayStatus, 0);
										?>
										<li class="workday">
											<div id="workdayControl" class="<?php echo $statusClassName?> pull-left">
												<span class="user-workday-start palegreen"><i class="fa fa-play"></i><span><?php echo Core::_('User_Workday.start_day')?></span></span>
												<span class="user-workday-pause warning"><i class="fa fa-pause"></i></span>
												<span class="user-workday-continue workday-green"><i class="fa fa-eject fa-rotate-90"></i><span><?php echo Core::_('User_Workday.continue_day')?></span></span>
												<span class="user-workday-stop darkorange" data-confirm="<?php echo Core::_('User_Workday.stop_day_confirm')?>"><i class="fa fa-stop"></i><span><?php echo Core::_('User_Workday.stop_day')?></span><?php
												if ($workdayStatus == 1)
												{
													$oLastUserWorkday = $oUser->User_Workdays->getLast(FALSE);

													if (!is_null($oLastUserWorkday) && $oLastUserWorkday->date != date('Y-m-d'))
													{
														?><span class="user-workday-last-date"> <?php echo date('d.m', Core_Date::sql2timestamp($oLastUserWorkday->date))?></span><?php
													}
												}
												?></span>
												<span class="user-workday-end-text gray"><?php echo Core::_('User_Workday.stop_day_message')?></span>
												<span class="user-workday-stop-another-time" data-title="<?php echo Core::_('User_Workday.another_time_modal_title')?>"><?php echo Core::_('User_Workday.another_time')?></span>
											</div>
											<div class="workdayTimer pull-right">
												<i class="fa fa fa-clock-o"></i><span class="workday-timer"><?php echo $duration?></span>
											</div>
										</li>
										<!--Avatar Area-->
										<li>
											<div class="avatar-area">
												<img src="<?php echo $oUser->getAvatar()?>" class="avatar avatar-user">
											</div>
										</li>
										<li class="email">
											<a>
												<i class="fa fa-<?php echo $oUser->superuser ? 'graduation-cap' : 'user'?>"></i> <?php echo htmlspecialchars($oUser->login)?>
											</a>
										</li>
										<!--Theme Selector Area-->
										<li class="theme-area">
											<ul id="skin-changer" class="wallpaper-picker">
												<?php
												$aUser_Wallpapers = Core_Entity::factory('User_Wallpaper')->findAll(FALSE);
												foreach ($aUser_Wallpapers as $oUser_Wallpaper)
												{
												?>
												<li>
													<span class="colorpick-btn">
														<img onclick="$.changeWallpaper(this)" data-id="<?php echo $oUser_Wallpaper->id?>" data-original-path="<?php echo htmlspecialchars($oUser_Wallpaper->getLargeImageFileHref())?>" src="<?php echo htmlspecialchars($oUser_Wallpaper->getSmallImageFileHref())?>" />
													</span>
												</li>
												<?php
												}
												?>
											</ul>
										</li>
										<li class="dropdown-footer">
											<a href="/admin/logout.php" onmousedown="$(window).off('beforeunload')"><?php echo Core::_('Admin.exit')?></a>
										</li>
									</ul>

									<script>
									$(function(){
										$('li.workday #workdayControl').data('status', <?php echo $workdayStatus?>);

										$('#user-info-dropdown .dropdown-menu li:not(.workday)').on({
											'click': function(e){
												e.stopPropagation();
											}
										});

										$.blinkColon(<?php echo $workdayStatus?>);
									});
									</script>
									<!--/Login Area Dropdown-->
								</li>
								<!-- /Account Area -->
								<!--Note: notice that setting div must start right after account area list.
								no space must be between these elements-->
								<!-- Settings -->
							</ul><div class="setting">
								<a id="btn-setting" title="<?php echo Core::_('Admin.settings')?>" href="#">
									<i class="icon glyphicon glyphicon-cog"></i>
								</a>
							</div><div class="setting-container">
								<label>
									<span class="text"><?php echo Core::_('Admin.fixed')?></span>
								</label>
								<label>
									<input type="checkbox" id="checkbox_fixednavbar">
									<span class="text"><?php echo Core::_('Admin.fixed-navbar')?></span>
								</label>
								<label>
									<input type="checkbox" id="checkbox_fixedsidebar">
									<span class="text"><?php echo Core::_('Admin.fixed-sideBar')?></span>
								</label>
								<label>
									<input type="checkbox" id="checkbox_fixedbreadcrumbs">
									<span class="text"><?php echo Core::_('Admin.fixed-breadcrumbs')?></span>
								</label>
								<label>
									<input type="checkbox" id="checkbox_fixedheader">
									<span class="text"><?php echo Core::_('Admin.fixed-header')?></span>
								</label>
							</div>
							<!-- Settings -->
						</div>
					<?php
					}
					?></div>
					<!-- /Account Area and Settings -->
				</div>
			</div>
		</div>
		<!-- /Navbar -->
		<?php
	}

	protected function _pageSidebar()
	{
		?><!-- Page Sidebar -->
		<div class="page-sidebar" id="sidebar">
			<!-- Page Sidebar Header-->
			<div class="sidebar-header-wrapper">
				<input type="text" class="searchinput" />
				<i class="searchicon fa fa-search"></i>

				<!-- Search Reports, Charts, Emails or Notifications -->
				<!-- <div class="searchhelper"></div>-->
			</div>
			<?php if (Core::moduleIsActive('search'))
			{
				$oSearchModule = Core_Entity::factory('Module')->getByPath('search');
				?>
				<script>
				$(function(){
					// Search
					$('[class = searchinput]').autocomplete({
						appendTo: '.sidebar-header-wrapper',
						source: function(request, response) {

							$('.sidebar-header-wrapper i.searchicon')
								.removeClass('fa-search')
								.addClass('fa-spinner')
								.addClass('fa-spin');

							$.ajax({
							  url: '/admin/index.php?ajaxWidgetLoad&moduleId=<?php echo $oSearchModule->id?>&type=1&autocomplete=1',
							  dataType: 'json',
							  data: {
								queryString: request.term
							  },
							  success: function( data ) {
								$('.sidebar-header-wrapper i.searchicon')
									.removeClass('fa-spinner')
									.removeClass('fa-spin')
									.addClass('fa-search');

								response( data );
							  }
							});
						},
						minLength: 1,
						create: function() {
							$(this).data('ui-autocomplete')._renderItem = function( ul, item ) {
								return $('<li></li>')
									.data('item.autocomplete', item)
									.append($('<i>').addClass(item.icon))
									.append(
										$('<a>')
											.attr('href', item.href)
											.attr('onclick', item.onclick)
											.text(item.label)
									)
									.appendTo(ul.addClass('searchhelper'));
							}

							$(this).prev('.ui-helper-hidden-accessible').remove();
						},
						select: function( event, ui ) {
							var myClick = new Function(ui.item.onclick);
							myClick();
						},
						open: function() {
							$(this).removeClass('ui-corner-all').addClass('ui-corner-top');
						},
						close: function() {
							$(this).removeClass('ui-corner-top').addClass('ui-corner-all');
						}
					});
				});
			</script>
			<?php
			}
			?>
			<!-- /Page Sidebar Header -->
			<!-- Sidebar Menu -->
			<ul class="nav sidebar-menu">
				<?php
				$this->navSidebarMenu();
				?>
			</ul>
		</div>
		<!-- /Page Sidebar -->
		<?php
	}

	public function navSidebarMenu()
	{
		?><li id="menu-dashboard">
			<a href="/admin/index.php" onclick="$.adminLoad({path: '/admin/index.php'}); return false">
				<i class="menu-icon glyphicon glyphicon-home"></i>
				<span class="menu-text"><?php echo Core::_('Admin.home')?></span>
			</a>
		</li>
		<?php
		// Список основных меню скина
		$this->_config = Core_Config::instance()->get('skin_bootstrap_config');

		$aModules = $this->_getAllowedModules();

		$aModuleList = $aCore_Module = array();
		foreach ($aModules as $key => $oModule)
		{
			$aModuleList[$oModule->path] = $oModule;
			// До onLoadSkinConfig, чтобы отработать навешенные в конструкторе Skin_Module_... хуки
			$aCore_Module[$oModule->path] = $this->getSkinModule($oModule->path);
			// Не для каждого модуля определен Skin_ класс
			is_null($aCore_Module[$oModule->path]) && $aCore_Module[$oModule->path] = Core_Module::factory($oModule->path);
		}
		unset($aModules);

		Core_Event::notify(get_class($this) . '.onLoadSkinConfig', $this);

		if (isset($this->_config['adminMenu']))
		{
			foreach ($this->_config['adminMenu'] as $key => $aAdminMenu)
			{
				$aAdminMenu += array('ico' => 'fa-file-o',
					'modules' => array()
				);

				$subItems = array();
				foreach ($aAdminMenu['modules'] as $sModulePath)
				{
					if (isset($aModuleList[$sModulePath]))
					{
						$subItems[] = $aModuleList[$sModulePath];
						unset($aModuleList[$sModulePath]);
					}
				}

				if (count($subItems))
				{
					?><li>
						<a class="menu-dropdown">
							<i class="menu-icon <?php echo $aAdminMenu['ico']?>"></i>
							<span class="menu-text"> <?php echo nl2br(htmlspecialchars(
								Core_Array::get($aAdminMenu, 'caption', Core::_("Skin_Bootstrap.admin_menu_{$key}"))
							))?> </span>
							<i class="menu-expand"></i>
						</a>

						<ul class="submenu">
						<?php
						foreach ($subItems as $oModule)
						{
							//$oCore_Module = Core_Module::factory($oModule->path);
							$oCore_Module = Core_Array::get($aCore_Module, $oModule->path);

							if ($oCore_Module)
							{
								$aMenu = $oCore_Module->getMenu();

								if (is_array($aMenu))
								{
									foreach ($aMenu as $aTmpMenu)
									{
										$aTmpMenu += array(
											'sorting' => 0,
											'block' => 0,
											'ico' => 'fa-file-o'
										);

										$bSubmenu = isset($aTmpMenu['submenu']) && count($aTmpMenu['submenu']);
										?><li id="menu-<?php echo $oCore_Module->getModuleName()?>">
											<a <?php if ($bSubmenu) { echo 'class="menu-dropdown" '; }?>href="<?php echo htmlspecialchars($aTmpMenu['href'])?>" onclick="<?php echo htmlspecialchars($aTmpMenu['onclick'])?>">
												<i class="menu-icon <?php echo htmlspecialchars($aTmpMenu['ico'])?>"></i>
												<span class="menu-text"><?php echo htmlspecialchars($aTmpMenu['name'])?></span>

												<?php if ($bSubmenu) {?>
													<i class="menu-expand"></i>
												<?php } ?>
											</a>
											<?php if ($bSubmenu)
											{
												?><ul class="submenu"><?php

												foreach ($aTmpMenu['submenu'] as $aSubmenu)
												{
													$aSubmenu += array(
														'name' => NULL,
														'href' => NULL,
														'onclick' => NULL,
														'ico' => 'fa-file-o'
													);
													?><li id="menu-<?php echo $oCore_Module->getModuleName()?>">
														<a href="<?php echo htmlspecialchars($aSubmenu['href'])?>" onclick="<?php echo htmlspecialchars($aSubmenu['onclick'])?>">
															<i class="menu-icon <?php echo htmlspecialchars($aSubmenu['ico'])?>"></i>
															<span class="menu-text"><?php echo htmlspecialchars($aSubmenu['name'])?></span>
														</a>
													</li><?php
												}
												?></ul><?php
											}
											?>
										</li>

										<?php
									}
								}
							}
						}
						?></ul>
					</li><?php
				}
			}

			// Невошедшие в другие группы
			foreach ($aModuleList as $oModule)
			{
				//$oCore_Module = Core_Module::factory($oModule->path);
				$oCore_Module = Core_Array::get($aCore_Module, $oModule->path);

				if ($oCore_Module)
				{
					$aMenu = $oCore_Module->getMenu();

					if (is_array($aMenu))
					{
						foreach ($aMenu as $aTmpMenu)
						{
							if (isset($aTmpMenu['name']))
							{
								$aTmpMenu += array(
									'name' => NULL,
									'href' => NULL,
									'onclick' => NULL,
									'ico' => 'fa-file-o'
								);
								?><li>
									<a href="<?php echo htmlspecialchars($aTmpMenu['href'])?>" onclick="<?php echo htmlspecialchars($aTmpMenu['onclick'])?>" class="menu-icon">
										<i class="menu-icon fa <?php echo $aTmpMenu['ico']?>"></i>
										<span class="menu-text"><?php echo $aTmpMenu['name']?></span>
									</a>
								</li>
								<?php
							}
						}
					}
				}
			}
		}

	}

	public function loadingContainer()
	{
		?><!-- Loading Container -->
		<div class="loading-container">
			<div class="loader"></div>
		</div>
		<!-- /Loading Container --><?php

		return $this;
	}

	/**
	 * Show header
	 */
	public function header()
	{
		//$timestamp = $this->_getTimestamp();

		?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8" />
<title><?php echo $this->_title?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="referrer" content="no-referrer" />
<link rel="apple-touch-icon" href="/modules/skin/bootstrap/ico/icon-iphone-retina.png" />
<link rel="shortcut icon" type="image/x-icon" href="/modules/skin/bootstrap/ico/favicon.ico" />
<link rel="icon" type="image/png" href="/modules/skin/bootstrap/ico/favicon.png" />
<?php $this->showHead()?>
</head>
<body class="body-<?php echo htmlspecialchars($this->_mode)?> hostcms-bootstrap1">
		<?php
		if ($this->_mode != 'install')
		{
			if (Core_Auth::logged())
			{
				$this->loadingContainer();

				if (!in_array($this->_mode, array('blank', 'authorization')))
				{
					$this->_navBar();

					echo PHP_EOL;
					?><!-- Main Container -->
					<div class="main-container container-fluid">
						<!-- Page Container -->
						<div class="page-container">

							<?php $this->_pageSidebar()?>

							<!-- Page Content -->
							<div class="page-content">
					<?php
				}
			}
		}
		// Install mode
		else
		{
			$this->loadingContainer();
		}

		return $this;
	}

	/**
	 * Show authorization form
	 */
	public function authorization()
	{
		$this->_mode = 'authorization';

		?><div class="login-container animated fadeInDown">
		<div class="loginbox-largelogo">
			<img src="/modules/skin/bootstrap/img/large-logo.png">
		</div>

		<?php
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
		?>

		<div class="loginbox">
			<form class="form-horizontal" action="/admin/index.php" method="post">
				<div class="loginbox-textbox">
					<span class="input-icon">
						<input type="text" name="login" class="form-control" placeholder="<?php echo Core::_('Admin.authorization_form_login')?>" />
						<i class="fa fa-user"></i>
					</span>
				</div>
				<div class="loginbox-textbox">
					<span class="input-icon">
						<input type="password" name="password" class="form-control" placeholder="<?php echo Core::_('Admin.authorization_form_password')?>" />
						<i class="fa fa-lock"></i>
					</span>
				</div>
				<div class="loginbox-forgot">
					<label>
						<input type="checkbox" checked="checked" name="ip" /><span class="text"><?php echo Core::_('Admin.authorization_form_ip')?></span>
					</label>
				</div>
				<div class="loginbox-submit">
					<input type="submit" name="submit" class="btn btn-danger btn-block" value="<?php echo Core::_('Admin.authorization_form_button')?>">
				</div>
			</form>
		</div>
		</div>

		<div class="widget hostcms-notice transparent">
		<div class="widget-body">
			<?php echo Core::_('Admin.authorization_notice')?>
		</div>
		<div class="widget-body">
			<?php echo Core::_('Admin.authorization_notice2')?>
		</div>
		</div>

		<script>
			$("#authorization input[name='login']").focus();

			// Check Google chrome data saver mode
			if ('connection' in navigator)
			{
				if (navigator.connection.saveData)
				{
					$("input[name='ip']").prop('checked', false);
				}
			}
		</script>
		<?php
		}

		/**
		 * Show footer
		 */
		public function footer()
		{
			if ($this->_mode != 'install')
			{
				if (Core_Auth::logged())
				{
					if ($this->_mode != 'blank')
					{
						?>		</div>
								<!-- /Page Content -->
							</div>
							<!-- /Page Container -->
							<!-- Main Container -->
						</div><?php
					}
				}
				else
				{
				?><footer>
<div class="container">
	<div class="row">
		<div class="col-xs-12">
			<p class="copy pull-left copyright">Copyright © 2005–2019 <?php echo Core::_('Admin.company')?></p>
			<p class="copy text-right contacts">
				<?php echo Core::_('Admin.website')?> <a href="http://<?php echo Core::_('Admin.company-website')?>" target="_blank"><?php echo Core::_('Admin.company-website')?></a>
				<br/>
				<?php echo Core::_('Admin.support_email')?> <a href="mailto:<?php echo Core::_('Admin.company-support')?>"><?php echo Core::_('Admin.company-support')?></a>
			</p>
		</div>
	</div>
</div>
</footer><?php
				}
			}
			?><script src="/modules/skin/bootstrap/js/hostcms.js"></script>
</body>
</html><?php
	}

	/**
	 * Show back-end index page
	 * @return self
	 */
	public function index()
	{
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

				if ($moduleId == 0 || Core_Entity::factory('Module', $moduleId)->active)
				{
					$modulePath = $moduleId == 0
						? 'core'
						: Core_Entity::factory('Module', $moduleId)->path;

					Core_Session::close();

					$Core_Module = $this->getSkinModule($modulePath);

					if (!is_null($Core_Module))
					{
						$Core_Module->adminPage($type, $bAjax && is_null(Core_Array::getGet('widgetAjax')));
					}
					else
					{
						throw new Core_Exception('SkinModuleName does not found.');
					}
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
						$oUser_Note->markDeleted();
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

		$aModules = $this->_getAllowedModules();

		$oAdmin_View = Admin_View::create();
		$oAdmin_View->showFormBreadcrumbs();
		?>

		<div class="page-header position-relative">
			<div class="header-title">
				<h1><?php echo Core::_('Admin.dashboard')?></h1>
			</div>
			<!--Header Buttons-->
			<div class="header-buttons">
				<a href="#" class="sidebar-toggler">
					<i class="fa fa-arrows-h"></i>
				</a>
				<a href="" id="refresh-toggler" class="refresh">
					<i class="glyphicon glyphicon-refresh"></i>
				</a>
				<a href="#" id="fullscreen-toggler" class="fullscreen">
					<i class="glyphicon glyphicon-fullscreen"></i>
				</a>
			</div>
			<!--Header Buttons End-->
		</div>
		<div class="page-body">

			<div class="row">
				<?php
				// Core
				$Core_Module = $this->getSkinModule('core');

				if (!is_null($Core_Module))
				{
					if (method_exists($Core_Module, 'widget'))
					{
						$Core_Module->widget();
					}
				}

				// Other modules
				$oSite = Core_Entity::factory('Site', CURRENT_SITE);
				foreach ($aModules as $oModule)
				{
					$Core_Module = $this->getSkinModule($oModule->path);

					is_null($Core_Module) && $Core_Module = $oModule->Core_Module;

					if ($oModule->active
						&& !is_null($Core_Module)
						&& method_exists($Core_Module, 'widget')
						&& $oUser->checkModuleAccess(array($oModule->path), $oSite))
					{
						// 78 - informer(widget) settings
						$oUser_Setting = $oUser->User_Settings->getByModuleIdAndTypeAndEntityId($oModule->id, 78, 0);

						//$iStartTime = Core::getmicrotime();

						(is_null($oUser_Setting) || $oUser_Setting->active)
							&& $Core_Module->widget();

						//echo '<!-- Debug time "', $oModule->path, '": ', sprintf('%.3f', Core::getmicrotime() - $iStartTime), ' -->';
					}
				}
				?>
			</div>

			<div class="row">
				<?php
				// Other modules
				$oSite = Core_Entity::factory('Site', CURRENT_SITE);
				foreach ($aModules as $oModule)
				{
					$Core_Module = $this->getSkinModule($oModule->path);

					is_null($Core_Module) && $Core_Module = $oModule->Core_Module;

					if ($oModule->active
						&& !is_null($Core_Module)
						&& method_exists($Core_Module, 'adminPage')
						&& $oUser->checkModuleAccess(array($oModule->path), $oSite))
					{
						// 77 - widget settings
						//$oUser_Setting = $oUser->User_Settings->getByModuleIdAndTypeAndEntityId($oModule->id, 77, 0);

						// Временно отключена проверка
						//if (is_null($oUser_Setting) || $oUser_Setting->active)
						if (TRUE)
						{
							$aTypes = $Core_Module->getAdminPages();
							foreach ($aTypes as $type => $title)
							{
								//$iStartTime = Core::getmicrotime();

								$Core_Module->adminPage($type);

								//echo '<!-- Debug time "', $oModule->path, '": ', sprintf('%.3f', Core::getmicrotime() - $iStartTime), ' -->';
							}
						}
					}
				}

				// Core
				$Core_Module = $this->getSkinModule('core');

				if (!is_null($Core_Module))
				{
					//$Core_Module = new $sSkinModuleName();
					$aTypes = $Core_Module->getAdminPages();

					foreach ($aTypes as $type => $title)
					{
						//$oUser_Setting = $oUser->User_Settings->getByModuleIdAndTypeAndEntityId(0, $type, 0);

						// Временно отключена проверка
						//if (is_null($oUser_Setting) || $oUser_Setting->active)
						if (TRUE)
						{
							$Core_Module->adminPage($type);
						}
					}
				}
				?>
			</div>
		</div><?php

		return $this;
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
		switch ($type)
		{
			case 'error':
				$class = 'alert alert-danger fade in';
			break;
			default:
				$class = 'alert alert-success fade in';
		}
		$return = '<div class="' . $class . '">
		<button type="button" class="close" data-dismiss="alert">&times;</button>' . $message . '</div>';
		return $return;
	}

	/**
	 * Change language
	 */
	public function changeLanguage()
	{
		?><form name="authorization" action="./index.php" method="post">
			<div class="row">
			<?php
			$aInstallConfig = Core_Config::instance()->get('install_config');
			$aLng = Core_Array::get($aInstallConfig, 'lng', array());

			Admin_Form_Entity::factory('Select')
				->name('lng_value')
				->caption(Core::_('Install.changeLanguage'))
				->options($aLng)
				->value(isset($_SESSION['LNG_INSTALL']) ? $_SESSION['LNG_INSTALL'] : DEFAULT_LNG)
				->divAttr(array('class' => 'form-group col-xs-12 col-md-6'))
				->execute();
			?>
			</div>

			<div class="row">
				<div class="form-group col-xs-12 text-align-right">
					 <button name="step_0" type="submit" class="btn btn-info">
						<?php echo Core::_('Install.next')?> <i class="fa fa-arrow-right"></i>
					</button>
				</div>
			</div>
		</form>
		<?php
	}
}