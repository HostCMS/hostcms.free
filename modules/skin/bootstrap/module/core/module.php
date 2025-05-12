<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core. Backend's Index Pages and Widget.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Skin_Bootstrap_Module_Core_Module extends Core_Module
{
	/**
	 * Name of the skin
	 * @var string
	 */
	protected $_skinName = 'bootstrap';

	/**
	 * Name of the module
	 * @var string
	 */
	protected $_moduleName = 'core';

	/**
	 * Widget path
	 * @var string|NULL
	 */
	protected $_path = NULL;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_adminPages = array(
			1 => array('title' => Core::_('Admin.index_systems_events')),
			2 => array('title' => Core::_('Admin.index_systems_characteristics')),
			3 => array('title' => Core::_('Admin.notes'))
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
		$type = intval($type);

		$this->_path = Admin_Form_Controller::correctBackendPath("/{admin}/index.php?ajaxWidgetLoad&moduleId=0&type={$type}");

		switch ($type)
		{
			//Заметки
			case 1:
				$windowId = 'modalNotes';
			break;
			// Журнал событий
			case 2:
				$windowId = 'modalEvents';
			break;
			default:
				$windowId = 'modalCharacteristics';
			break;
		}

		switch ($type)
		{
			// Заметки
			case 1:
				if ($ajax)
				{
					$this->_notesContent();
				}
				else
				{
					?><div class="col-xs-12" id="notesAdminPage">
						<script>
						$.widgetLoad({ path: '<?php echo $this->_path?>', context: $('#notesAdminPage') });
						</script>
					</div><?php
				}
			break;
			// Журнал событий
			case 2:
				if ($ajax)
				{
					$this->_eventsContent();
				}
				else
				{
					?><div class="col-xs-12 col-sm-8" id="coreEventsAdminPage">
						<script>
						$.widgetLoad({ path: '<?php echo $this->_path?>', context: $('#coreEventsAdminPage') });
						</script>
					</div><?php
				}
			break;
			// Список сайтов
			case 10:
				$this->_siteContent();
			break;
			// Системные характеристики
			default:
				if ($ajax)
				{
					$this->_characteristicsContent();
				}
				else
				{
					?><div class="systems-characteristics col-xs-12 col-sm-4 col-md-4 col-lg-4" id="characteristicsAdminPage">
						<script>
						$.widgetLoad({ path: '<?php echo $this->_path?>', context: $('#characteristicsAdminPage') });
						</script>
					</div><?php
				}
			break;
		}

		return $this;
	}

	protected function _eventsContent()
	{
		$oCore_Log = Core_Log::instance();
		$file_name = $oCore_Log->getLogName(date('Y-m-d'));

		$oUser = Core_Auth::getCurrentUser();
		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$access = Core::moduleIsActive('eventlog')
			? $oUser->checkModuleAccess(array('eventlog'), $oSite)
			: FALSE;

		?><div class="widget">
			<div class="widget-header bordered-bottom bordered-themeprimary">
				<i class="widget-icon fa fa-tasks themeprimary"></i>
				<span class="widget-caption themeprimary"><?php echo Core::_('Admin.index_systems_events');?></span>
				<div class="widget-buttons">
					<a data-toggle="maximize">
						<i class="fa fa-expand gray"></i>
					</a>
					<a data-toggle="refresh" onclick="$(this).find('i').addClass('fa-spin'); $.widgetLoad({ path: '<?php echo $this->_path?>', context: $('#coreEventsAdminPage'), 'button': $(this).find('i') });">
						<i class="fa-solid fa-rotate gray"></i>
					</a>
				</div>
			</div>
			<div class="widget-body">
				<!--<div class="padd scroll-systemsevents">-->
				<div class="widget-main no-padding">
					<div class="tickets-container">
					<!--<ul class="eventsjournal timeline fadeInDown">-->
					<?php
					if (Core_File::isFile($file_name))
					{
						if ($fp = @fopen($file_name, 'r'))
						{
							?>
							<ul class="tickets-list">
							<?php
							$countEvents = 10;

							$aLines = array();
							$iSize = @filesize($file_name);
							$iSlice = 10240;

							$iSize > $iSlice && fseek($fp, $iSize - $iSlice);

							// [0]-дата/время, [1]-имя пользователя, [2]-события, [3]-статус события, [4]-сайт, [5]-страница
							while (!feof($fp))
							{
								$event = fgetcsv($fp, $iSlice, ",", "\"", "\\");
								if (empty($event[0]) || count($event) < 3)
								{
									continue;
								}
								$aLines[] = $event;
							}

							count($aLines) > $countEvents && $aLines = array_slice($aLines, -$countEvents);
							$aLines = array_reverse($aLines);

							foreach ($aLines as $aLine)
							{
								if (count($aLine) > 3)
								{
									switch (intval($aLine[3]))
									{
										case 1:
											$statusCharClassName = ' fa-check';
											$statusColorName = 'palegreen';
										break;
										case 2:
											$statusCharClassName = 'fa-exclamation';
											$statusColorName = 'yellow';
										break;
										case 3:
											$statusCharClassName = 'fa-exclamation';
											$statusColorName = 'orange';
										break;
										case 4:
											$statusCharClassName = 'fa-exclamation';
											$statusColorName = 'red';
										break;
										default:
											$statusCharClassName = 'fa-info';
											$statusColorName = 'darkgray';
									}
									?><li class="ticket-item">
										<div class="row">
											<div class="ticket-user col-xs-12 col-lg-7">
												<span class="user-name"><?php echo htmlspecialchars(Core_Str::cut(strip_tags($aLine[2]), 70))?></span>
											</div>
											<div class="ticket-time col-xs-6 col-lg-3">
												<div class="divider hidden-md hidden-sm hidden-xs"></div>
												<i class="fa fa-clock-o"></i>
												<span class="time"><?php echo htmlspecialchars(Core_Date::sql2datetime($aLine[0]));?></span>
											</div>
											<div class="ticket-type col-xs-6 col-lg-2">
												<span class="divider hidden-xs"></span>
												<i class="fa fa-user"></i>
												<span class="type user-login"><?php echo htmlspecialchars($aLine[1])?></span>
											</div>
											<div class="ticket-state bg-<?php echo $statusColorName?>">
												<i class="fa <?php echo $statusCharClassName?>"></i>
											</div>
										</div>
									</li>
								<?php
								}
							}
							unset($aLines);
							?>
							</ul>
							<?php
							if ($access)
							{
								$sEventlogHref = Admin_Form_Controller::correctBackendPath('/{admin}/eventlog/index.php');
								?>
								<br />
								<div class="footer">
									<a class="btn btn-info" href="<?php echo $sEventlogHref?>" onclick="$.adminLoad({path: '<?php echo $sEventlogHref?>'}); return false"><i class="fa fa-book"></i><?php echo Core::_('Admin.index_events_journal_link')?></a>
								</div>
								<?php
							}
						}
						else
						{
							Core_Message::show(Core::_('Admin.index_error_open_log') . $file_name, 'error');
						}
					}
					?>
					</div>
				</div>
			</div>
		</div>
		<?php
		return $this;
	}

	protected function _characteristicsContent()
	{
		$dbVersion = Core_DataBase::instance()->getVersion();
		$gdVersion = Core_Image::instance('gd')->getVersion();
		$pcreVersion = Core::getPcreVersion();
		$memoryLimit = ini_get('memory_limit')
			? ini_get('memory_limit')
			: 'undefined';

		$maxExecutionTime = intval(ini_get('max_execution_time'));
		?><div class="widget">
			<div class="widget-header bordered-bottom bordered-blue">
				<i class="widget-icon fa fa-gears blue"></i>
				<span class="widget-caption blue"><?php echo Core::_('Admin.index_systems_characteristics')?></span>
				<div class="widget-buttons">
					<a data-toggle="maximize">
						<i class="fa fa-expand gray"></i>
					</a>
					<a data-toggle="refresh" onclick="$(this).find('i').addClass('fa-spin'); $.widgetLoad({ path: '<?php echo $this->_path?>', context: $('#characteristicsAdminPage'), 'button': $(this).find('i') });">
						<i class="fa fa-refresh gray"></i>
					</a>
				</div>
			</div>
			<div class="widget-body">
				<div class="widget-main no-padding">
					<div class="tickets-container">
						<ul class="tickets-list">
							<li class="ticket-item">
								<div class="row">
									<div class="ticket-user">
										<span class="user-name"><?php echo Core::_('Admin.index_tech_date_hostcms')?></span>
										<span class="user-company"><?php echo Core::getVersion()?></span>
									</div>
									<div class="ticket-state bg-palegreen">
										<i class="fa fa-check"></i>
									</div>
								</div>
							</li>
							<li class="ticket-item">
								<div class="row">
									<div class="ticket-user">
										<span class="user-name"><?php echo Core::_('Admin.index_tech_date_editorial')?></span>
										<span class="user-company"><?php echo Core::_('Core.redaction' . Core_Array::get(Core::$config->get('core_hostcms'), 'integration', 0))?></span>
									</div>
									<div class="ticket-state bg-palegreen">
										<i class="fa fa-check"></i>
									</div>
								</div>
							</li>
							<li class="ticket-item">
								<div class="row">
									<div class="ticket-user">
										<span class="user-name"><?php echo Core::_('Admin.index_tech_date_php') ?></span>
										<span class="user-company"><?php echo phpversion() ?></span>
									</div>
									<?php
									if (version_compare(phpversion(), '5.4.0', '>='))
									{
										$divClass = ' bg-palegreen';
										$iClass = ' fa-check';
									}
									else
									{
										$divClass = ' bg-darkorange';
										$iClass = ' fa-times';
									}
									?>
									<div class="ticket-state<?php echo $divClass?>">
										<i class="fa<?php echo $iClass?>"></i>
									</div>
								</div>
							</li>
							<li class="ticket-item">
								<div class="row">
									<div class="ticket-user">
										<span class="user-name"><?php echo Core::_('Admin.index_tech_date_sql') ?></span>
										<span class="user-company"><?php echo $dbVersion ?></span>
									</div>
									<?php
									if (!is_null($dbVersion) && version_compare($dbVersion, '5.1.0', '>='))
									{
										$divClass = ' bg-palegreen';
										$iClass = ' fa-check';
									}
									else
									{
										$divClass = ' bg-darkorange';
										$iClass = ' fa-times';
									}
									?>
									<div class="ticket-state<?php echo $divClass?>">
										<i class="fa<?php echo $iClass?>"></i>
									</div>
								</div>
							</li>
							<li class="ticket-item">
								<div class="row">
									<div class="ticket-user">
										<span class="user-name"><?php echo Core::_('Admin.index_tech_date_gd') ?></span>
										<span class="user-company"><?php echo $gdVersion ?></span>
									</div>
									<?php
									if (!is_null($gdVersion) && version_compare($gdVersion, '2.0', '>='))
									{
										$divClass = ' bg-palegreen';
										$iClass = ' fa-check';
									}
									else
									{
										$divClass = ' bg-darkorange';
										$iClass = ' fa-times';
									}
									?>
									<div class="ticket-state<?php echo $divClass?>">
										<i class="fa<?php echo $iClass?>"></i>
									</div>
								</div>
							</li>
							<li class="ticket-item">
								<div class="row">
									<div class="ticket-user">
										<span class="user-name"><?php echo Core::_('Admin.index_tech_date_pcre') ?></span>
										<span class="user-company"><?php echo $pcreVersion ?></span>
									</div>

									<?php
									if (!is_null($pcreVersion) && version_compare($pcreVersion, '7.0', '>='))
									{
										$divClass = ' bg-palegreen';
										$iClass = ' fa-check';
									}
									else
									{
										$divClass = ' bg-darkorange';
										$iClass = ' fa-times';
									}
									?>
									<div class="ticket-state<?php echo $divClass?>">
										<i class="fa<?php echo $iClass?>"></i>
									</div>
								</div>
							</li>
							<li class="ticket-item">
								<div class="row">
									<div class="ticket-user">
										<span class="user-name"><?php echo Core::_('Admin.index_tech_date_max_time') ?></span>
										<span class="user-company"><?php echo $maxExecutionTime ?></span>
									</div>

									<?php
									if (!$maxExecutionTime || $maxExecutionTime >= 30)
									{
										$divClass = ' bg-palegreen';
										$iClass = ' fa-check';
									}
									else
									{
										$divClass = ' bg-darkorange';
										$iClass = ' fa-times';
									}
									?>
									<div class="ticket-state<?php echo $divClass?>">
										<i class="fa<?php echo $iClass?>"></i>
									</div>
								</div>
							</li>
							<li class="ticket-item">
								<div class="row">
									<div class="ticket-user">
										<span class="user-name"><?php echo Core::_('Admin.index_memory_limit') ?></span>
										<span class="user-company"><?php echo $memoryLimit?></span>
									</div>

									<?php
									if (Core_Str::convertSizeToBytes($memoryLimit) >= Core_Str::convertSizeToBytes('16M'))
									{
										$divClass = ' bg-palegreen';
										$iClass = ' fa-check';
									}
									else
									{
										$divClass = ' bg-darkorange';
										$iClass = ' fa-times';
									}
									?>
									<div class="ticket-state<?php echo $divClass?>">
										<i class="fa<?php echo $iClass?>"></i>
									</div>
								</div>
							</li>
							<li class="ticket-item">
								<div class="row">
									<div class="ticket-user">
										<span class="user-name"><?php echo Core::_('Admin.index_tech_date_mb')?></span>
										<span class="user-company"><?php echo function_exists('mb_internal_encoding') ? Core::_('Admin.index_on') : Core::_('Admin.index_off')?></span>
									</div>

									<?php
									if (function_exists('mb_internal_encoding'))
									{
										$divClass = ' bg-palegreen';
										$iClass = ' fa-check';
									}
									else
									{
										$divClass = ' bg-darkorange';
										$iClass = ' fa-times';
									}
									?>
									<div class="ticket-state<?php echo $divClass?>">
										<i class="fa<?php echo $iClass?>"></i>
									</div>
								</div>
							</li>
							<?php
							if (PHP_VERSION_ID < 80000)
							{
								$mb_overload = ini_get('mbstring.func_overload');
								if ($mb_overload)
								{
									?><li class="ticket-item">
										<div class="row">
											<div class="ticket-user">
												<span class="user-name"><?php echo Core::_('Admin.index_tech_date_mb_overload')?></span>
												<span class="user-company"><?php echo htmlspecialchars($mb_overload)?></span>
											</div>
											<div class="ticket-state bg-darkorange">
												<i class="fa fa-times"></i>
											</div>
										</div>
									</li><?php
								}
							}
							?>
							<li class="ticket-item">
								<div class="row">
									<div class="ticket-user">
										<span class="user-name"><?php echo Core::_('Admin.index_tech_date_json')?></span>
										<span class="user-company"><?php echo function_exists('json_encode') ? Core::_('Admin.index_on') : Core::_('Admin.index_off')?></span>
									</div>

									<?php
									if (function_exists('json_encode'))
									{
										$divClass = ' bg-palegreen';
										$iClass = ' fa-check';
									}
									else
									{
										$divClass = ' bg-darkorange';
										$iClass = ' fa-times';
									}
									?>
									<div class="ticket-state<?php echo $divClass?>">
										<i class="fa<?php echo $iClass?>"></i>
									</div>
								</div>
							</li>
							<li class="ticket-item">
								<div class="row">
									<div class="ticket-user">
										<span class="user-name"><?php echo Core::_('Admin.index_tech_date_simplexml')?></span>
										<span class="user-company"><?php echo function_exists('simplexml_load_string') ? Core::_('Admin.index_on') : Core::_('Admin.index_off')?></span>
									</div>

									<?php
									if (function_exists('simplexml_load_string'))
									{
										$divClass = ' bg-palegreen';
										$iClass = ' fa-check';
									}
									else
									{
										$divClass = ' bg-darkorange';
										$iClass = ' fa-times';
									}
									?>
									<div class="ticket-state<?php echo $divClass?>">
										<i class="fa<?php echo $iClass?>"></i>
									</div>
								</div>
							</li>
							<li class="ticket-item">
								<div class="row">
									<div class="ticket-user">
										<span class="user-name"><?php echo Core::_('Admin.index_tech_date_iconv') ?></span>
										<span class="user-company"><?php echo function_exists('iconv') ? Core::_('Admin.index_on') : Core::_('Admin.index_off')?></span>
									</div>

									<?php
									if (function_exists('iconv'))
									{
										$divClass = ' bg-palegreen';
										$iClass = ' fa-check';
									}
									else
									{
										$divClass = ' bg-darkorange';
										$iClass = ' fa-times';
									}
									?>
									<div class="ticket-state<?php echo $divClass?>">
										<i class="fa<?php echo $iClass?>"></i>
									</div>
								</div>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<?php
		return $this;
	}

	protected function _notesContent()
	{
		$oUser = Core_Auth::getCurrentUser();

		if (!is_null($oUser))
		{
			?><div id="overview" class="row">
				<div class="col-xs-12">
					<div class="widget">
						<div class="widget-header bordered-bottom bordered-darkorange">
							<i class="widget-icon fa fa-tasks darkorange"></i>
							<span class="widget-caption darkorange"><?php echo Core::_('Admin.notes')?></span>
							<?php
							if (!$oUser->read_only)
							{
								?><div class="widget-buttons">
								<a onclick="$.addNote()">
									<i class="fa fa-plus darkorange" title="<?php echo Core::_('Admin.add_note')?>"></i>
								</a>
								</div><?php
							}
							?>
						</div>
						<div class="widget-body">
							<div id="user-notes" class="row">
								<!-- Default note -->
								<div id="default-user-note" class="user-note col-xs-12 col-sm-6 col-md-4 col-lg-3">
									<div class="row">
										<div class="user-note-block">
											<div>
												<textarea<?php echo $oUser->read_only ? ' readonly="readonly"' : ''?>></textarea>
											</div>
											<div class="user-note-state bg-darkorange">
												<a data-id="0" onclick="res = confirm('<?php echo Core::_('Admin_form.msg_information_delete')?>'); if (res) { $.destroyNote($(this).parents('div.user-note')) } return false"><i class="fa fa-remove"></i></a>
											</div>
										</div>
									</div>
								</div>
								<script>
								<?php
								$aUser_Notes = $oUser->User_Notes->findAll(FALSE);
								foreach ($aUser_Notes as $oUser_Note)
								{
									?>
									$.createNote({
										'id': <?php echo $oUser_Note->id?>,
										'value': '<?php echo Core_Str::escapeJavascriptVariable($oUser_Note->value)?>'
									});
									<?php
								}
								?>
								</script>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php
		}

		return $this;
	}

	protected function _siteContent()
	{
		$oUser = Core_Auth::getCurrentUser();
		$aSites = $oUser->getSites();

		$aJson = array(
			'count' => count($aSites)
		);

		ob_start();
		?><div class="scroll-sites">
			<?php
			if (count($aSites) > 5)
			{
				?><div class="filter-sites">
					<input type="text" class="filter-sites-input" placeholder="<?php echo Core::_('Site.filter_placeholder')?>">
					<i class="fa-solid fa-magnifying-glass"></i>
				</div><?php
			}
			?>
			<ul>
			<?php
			$aSiteColors = array(
				'bg-themeprimary',
				'bg-darkorange',
				'bg-warning',
				'bg-success'
			);
			$iCountColor = 0;
			$sListSitesContent = '';

			foreach ($aSites as $oSite)
			{
				$oSite_Alias = $oSite->Site_Aliases->getByCurrent(1);

				$sSite = '<div class="notification-icon">
					<i class="fa ' . $aSiteColors[$iCountColor < 4 ? $iCountColor++ : $iCountColor = 0] . ' white hostcms-font"><b>' . $oSite->id . '</b></i>
				</div>
				<div class="notification-body">
					<span class="title">' . htmlspecialchars(Core_Str::cut($oSite->name, 35)) . '</span>
					<span class="description">' .
					 (!is_null($oSite_Alias)
						? htmlspecialchars($oSite_Alias->name)
						: 'undefined' ) . '
					</span>
				</div>';

				$classInactive = !$oSite->active
					? 'site-inactive'
					: '';

				if ($oSite->id != CURRENT_SITE)
				{
					$sListSitesContent .= '<li class="' . $classInactive . '">
						<a href="' . Admin_Form_Controller::correctBackendPath('/{admin}/index.php') . '?changeSiteId=' . $oSite->id . '" onmousedown="$(window).off(\'beforeunload\')">
							<div class="clearfix">' . $sSite . '</div>
						</a></li>';
				}
				else
				{
					$sListSitesContent = '<li class="' . $classInactive . '">
						<a>
							<div class="clearfix">
								' . $sSite . '
								<div class="notification-extra"><i class="fa fa-check-circle-o green"></i></div>
							</div>
						</a></li>' . $sListSitesContent;
				}
			}

			echo $sListSitesContent;
			?>
			</ul>
		</div>
		<script>
			$(function(){
				$('.filter-sites-input').on('input', function(){
					var txt = $(this).val();

					$('div.scroll-sites ul > *:not(:icontains("' + txt + '"))').hide();
					$('div.scroll-sites ul > *:icontains("'+ txt +'")').show();
				});
			});
		</script>

		<?php
		$aJson['content'] = ob_get_clean();

		Core::showJson($aJson);
	}

	public function widget()
	{
		if (
			!defined('HOSTCMS_USER_LOGIN') || !defined('HOSTCMS_CONTRACT_NUMBER') || !defined('HOSTCMS_PIN_CODE')
			||
			!strlen(HOSTCMS_USER_LOGIN) || !strlen(HOSTCMS_CONTRACT_NUMBER) || !strlen(HOSTCMS_PIN_CODE)
		)
		{
			$iAdmin_Form_Id = 42;
			$sAdminFormAction = '/{admin}/site/index.php';

			$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);
			$oAdmin_Form_Action = $oAdmin_Form
				->Admin_Form_Actions
				->getByName('accountInfo');

			if ($oAdmin_Form_Action)
			{
				$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
				$oAdmin_Form_Controller
					->path($sAdminFormAction)
					->window('accountInfo')
					->checked(array(0 => array(0)));

				$oMainTab = Admin_Form_Entity::factory('Tab')
					->caption('Main')
					->name('main');

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

				$oMainRow1->add(Admin_Form_Entity::factory('Input')
					->caption(Core::_("Site.accountinfo_login"))
					->divAttr(array('class'=>'form-group col-xs-12'))
					->name("HOSTCMS_USER_LOGIN")
					->value(defined('HOSTCMS_USER_LOGIN')
						? HOSTCMS_USER_LOGIN
						: ''
					)
				);

				$oMainRow2->add(Admin_Form_Entity::factory('Input')
					->caption(Core::_("Site.accountinfo_contract_number"))
					->divAttr(array('class'=>'form-group col-xs-12 col-sm-6'))
					->name("HOSTCMS_CONTRACT_NUMBER")
					->value(defined('HOSTCMS_CONTRACT_NUMBER')
						? HOSTCMS_CONTRACT_NUMBER
						: ''
					)
				)->add(Admin_Form_Entity::factory('Input')
					->caption(Core::_("Site.accountinfo_pin_code"))
					->divAttr(array('class'=>'form-group col-xs-12 col-sm-6'))
					->name("HOSTCMS_PIN_CODE")
					->value(defined('HOSTCMS_PIN_CODE')
						? HOSTCMS_PIN_CODE
						: ''
					)
					->placeholder('XXXX')
				);

				?><!-- Core License -->
				<div id="note-license" class="hidden">
					<div class="row">
						<div class="col-xs-12 margin-bottom-20">
							<strong><?php echo Core::_('Admin_Form.note')?>: </strong><?php echo Core::_('Admin_Form.note-license')?>
						</div>
						<div class="col-xs-12">
							<?php Admin_Form_Entity::factory('Form')
								->action($sAdminFormAction)
								->controller($oAdmin_Form_Controller)
								->add($oMainTab)
								->add(
									Admin_Form_Entity::factory('Buttons')
										->add(
											Admin_Form_Entity::factory('Button')
											->name('apply')
											->class('btn btn-palegreen')
											->type('submit')
											->value(Core::_('admin_form.apply'))
											->onclick(
												// '$(\'.modal-license\').hide(); '
												'bootbox.hideAll(); '
												. $oAdmin_Form_Controller->getAdminSendForm(array('action' => 'accountInfo', 'operation' => 'apply'))
											)
										)
								)
								->execute();
							?>
						</div>
					</div>
				</div>

				<script>
				var dialog = bootbox.dialog({
					message: $("#note-license").html(),
					title: '<?php echo Core::_('Site.menu2_sub_caption')?>',
					className: "modal-darkorange modal-license"
				});
				</script>
				<?php
			}
		}

		// Check password
		$oAdmin_User = Core_Entity::factory('User')->getByLogin('admin');
		if (!is_null($oAdmin_User)
			&& $oAdmin_User->password == Core_Hash::instance()->hash('admin'))
		{
			?><!-- Core Password -->
			<div class="col-xs-12">
				<div class="well bordered-left bordered-themesecondary">
					<i class="fa fa-star yellow margin-right-5"></i>
					<strong><?php echo Core::_('Admin_Form.note')?>: </strong><?php echo Core::_('Admin_Form.note-bad-password')?>
				</div>
			</div>
			<?php
		}
	}
}