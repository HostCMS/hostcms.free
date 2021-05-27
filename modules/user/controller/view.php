<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * View profile controller
 *
 * Контроллер просмотра профиля.
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Controller_View extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'title', // Form Title
		'skipColumns', // Array of skipped columns
	);

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation for action
	 * @return boolean
	 * @hostcms-event User_Controller_View.onBeforeExecute
	 * @hostcms-event User_Controller_View.onAfterExecute
	 */
	public function execute($operation = NULL)
	{
		Core_Event::notify('User_Controller_View.onBeforeExecute', $this, array($operation, $this->_Admin_Form_Controller));

		$eventResult = Core_Event::getLastReturn();

		if (!is_null($eventResult))
		{
			return $eventResult;
		}

		switch ($operation)
		{
			case 'modal':
				$this->addContent($this->_showEditForm());

				$return = TRUE;
			break;

			default:
			case NULL: // Показ формы

				ob_start();

				$content = $this->_showEditForm();

				$oAdmin_View = Admin_View::create();
				$oAdmin_View
					->children($this->_children)
					->pageTitle($this->title)
					->module($this->_Admin_Form_Controller->getModule())
					->content($content)
					->show();

				$this->addContent(ob_get_clean());

				$this->_Admin_Form_Controller
					->title($this->title)
					->pageTitle($this->title);

				$return = TRUE;
			break;
		}

		Core_Event::notify('User_Controller_View.onAfterExecute', $this, array($operation, $this->_Admin_Form_Controller));

		return $return;
	}

	/**
	 * Show edit form
	 * @return boolean
	 */
	protected function _showEditForm()
	{
		ob_start();
		?>
		<div class="row">
			<div class="col-md-12">
				<div class="profile-container">
					<div class="profile-header row">
						<div class="col-lg-2 col-md-4 col-sm-12 text-center">
							<img class="header-avatar" src="<?php echo $this->_object->getAvatar()?>" alt="">
						</div>
						<div class="col-lg-5 col-md-8 col-sm-12 profile-info">
							<div class="header-fullname"><?php echo htmlspecialchars($this->_object->getFullName())?></div>
							<div class="header-information"><?php echo htmlspecialchars($this->_object->description)?></div>
							<?php
							if (strlen($this->_object->address))
							{
							?>
								<div class="header-information"><i class="glyphicon glyphicon-map-marker margin-right-5 red"></i><?php echo htmlspecialchars($this->_object->address)?></div>
							<?php
							}
							?>
						</div>
						<div class="col-lg-5 col-md-12 col-sm-12 col-xs-12 profile-stats">
							<div class="row">
								<div class="col-xs-12 stats-col">
									<!-- <div class="stats-value pink">284</div>-->
									<!-- <div class="stats-title">FOLLOWING</div>-->

									<?php
									$aCompanies = Core_Entity::factory('Company')->findAll();

									foreach ($aCompanies as $oCompany)
									{
										$aCompany_Department_Post_Users = $this->_object->Company_Department_Post_Users->getAllByCompany_id($oCompany->id);

										foreach ($aCompany_Department_Post_Users as $oCompany_Department_Post_User)
										{
											if (count($aCompanies))
											{
											?>
												<div class="h5">
													<?php echo htmlspecialchars($oCompany->name)?>
												</div>
											<?php
											}
											?>
											<div class="semi-bold"><?php echo htmlspecialchars($oCompany_Department_Post_User->Company_Department->name)?></div>
											<div class="gray"><?php echo htmlspecialchars($oCompany_Department_Post_User->Company_Post->name)?></div>
											<?php
										}
									}
									?>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 inlinestats-col">
								<?php
								if ($this->_object->superuser)
								{
								?>
									<span class="fa fa-star gold"></span>
								<?php
								}
								?>
								</div>
								<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 inlinestats-col">
									<?php echo Core::_("User.view_sex")?> <strong><?php echo $this->_object->getSex()?></strong>
								</div>
								<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 inlinestats-col">
								<?php
								if (!is_null($this->_object->birthday) && $this->_object->birthday != '0000-00-00')
								{
								?>
									<?php echo Core::_("User.view_age")?> <strong><?php echo $this->_object->getAge()?></strong>
								<?php
								}
								?>
								</div>
							</div>
						</div>
					</div>
					<div class="profile-body">
						<div class="col-lg-12">
							<div class="tabbable">
								<div class="tab-content tabs-flat">
									<div id="overview" class="tab-pane active">
										<div class="row profile-overview">
											<div class="col-xs-12">
												<div class="row">
												<?php
												// Телефоны
												$aDirectory_Phones = $this->_object->Directory_Phones->findAll();

												if (count($aDirectory_Phones))
												{
												?>
													<div class="col-xs-12 col-md-6">
														<div class="profile-contacts no-padding-left no-padding-top no-padding-right">
															<div class="profile-badge orange">
																<i class="fa fa-phone orange"></i>
																<span><?php echo Core::_("User.view_phones")?></span>
															</div>
															<div class="contact-info">
															<?php
															foreach ($aDirectory_Phones as $oDirectory_Phone)
															{
																$oDirectory_Phone_Type = Core_Entity::factory('Directory_Phone_Type')->find($oDirectory_Phone->directory_phone_type_id);

																$sPhoneType = !is_null($oDirectory_Phone_Type->id)
																	? htmlspecialchars($oDirectory_Phone_Type->name) . ": "
																	: '<i class="fa fa-phone orange margin-right-10"></i>';
															?>
																<p><?php echo $sPhoneType?><span class="semi-bold"><?php echo htmlspecialchars($oDirectory_Phone->value)?></span></p>
															<?php
															}
															?>
															</div>
														</div>
													</div>
												<?php
												}

												// Электронные адреса
												$aDirectory_Emails = $this->_object->Directory_Emails->findAll();

												if (count($aDirectory_Emails))
												{
												?>
													<div class="col-xs-12 col-md-6">
														<div class="profile-contacts no-padding-left no-padding-top no-padding-right">
															<div class="profile-badge palegreen">
																<i class="fa fa-envelope-o palegreen"></i>
																<span><?php echo Core::_("User.view_emails")?></span>
															</div>
															<div class="contact-info">
															<?php
															foreach ($aDirectory_Emails as $oDirectory_Email)
															{
																$oDirectory_Email_Type = Core_Entity::factory('Directory_Email_Type')->find($oDirectory_Email->directory_email_type_id);

																$sEmailType = !is_null($oDirectory_Email_Type->id)
																	? htmlspecialchars($oDirectory_Email_Type->name) . ": "
																	: '<i class="fa fa-envelope-o palegreen margin-right-10"></i>';
															?>
																<p><?php echo $sEmailType?><a href="mailto:<?php echo htmlspecialchars($oDirectory_Email->value)?>"><span class="semi-bold"><?php echo htmlspecialchars($oDirectory_Email->value)?></span></a></p>
															<?php
															}
															?>
															</div>
														</div>
													</div>
												<?php
												}
												?>
												</div>
												<div class="row">
												<?php
												// Социальные сети
												$aDirectory_Socials = $this->_object->Directory_Socials->findAll();

												if (count($aDirectory_Socials))
												{
												?>
													<div class="col-xs-12 col-md-6">
														<div class="profile-contacts no-padding-left no-padding-top no-padding-right">
															<div class="profile-badge azure">
																<i class="fa fa-share-alt azure"></i>
																<span><?php echo Core::_("User.view_socials")?></span>
															</div>
															<div class="contact-info">
															<?php
															foreach ($aDirectory_Socials as $oDirectory_Social)
															{
																$oDirectory_Social_Type = Core_Entity::factory('Directory_Social_Type')->find($oDirectory_Social->directory_social_type_id);

																$sSocialType = !is_null($oDirectory_Social_Type->id) && strlen($oDirectory_Social_Type->ico)
																	? '<i class="' . htmlspecialchars($oDirectory_Social_Type->ico) . ' margin-right-10"></i>'
																	: '<i class="fa fa-envelope-o azure margin-right-10"></i>';
															?>
																<p><?php echo $sSocialType?><a href="<?php echo htmlspecialchars($oDirectory_Social->value)?>" target="_blank"><?php echo htmlspecialchars($oDirectory_Social->value)?></a></p>
															<?php
															}
															?>
															</div>
														</div>
													</div>
												<?php
												}

												// Мессенджеры
												$aDirectory_Messengers = $this->_object->Directory_Messengers->findAll();

												if (count($aDirectory_Messengers))
												{
												?>
													<div class="col-xs-12 col-md-6">
														<div class="profile-contacts no-padding-left no-padding-top no-padding-right">
															<div class="profile-badge yellow">
																<i class="fa fa-comments-o yellow"></i>
																<span><?php echo Core::_("User.view_messengers")?></span>
															</div>
															<div class="contact-info">
															<?php
															foreach ($aDirectory_Messengers as $oDirectory_Messenger)
															{
																$oDirectory_Messenger_Type = Core_Entity::factory('Directory_Messenger_Type')->find($oDirectory_Messenger->directory_messenger_type_id);

																$sMessengerType = !is_null($oDirectory_Messenger_Type->id) && strlen($oDirectory_Messenger_Type->ico)
																	? '<i class="' . htmlspecialchars($oDirectory_Messenger_Type->ico) . ' margin-right-10"></i>'
																	: '<i class="fa fa-comments-o yellow margin-right-10"></i>';
															?>
																<p><?php echo $sMessengerType?><a href="<?php echo sprintf($oDirectory_Messenger_Type->link, $oDirectory_Messenger->value)?>" target="_blank"><?php echo htmlspecialchars($oDirectory_Messenger->value)?></a></p>
															<?php
															}
															?>
															</div>
														</div>
													</div>
												<?php
												}
												?>
												</div>
												<div class="row">
												<?php
												// Сайты
												$aDirectory_Websites = $this->_object->Directory_Websites->findAll();

												if (count($aDirectory_Websites))
												{
												?>
													<div class="col-xs-12 col-md-6">
														<div class="profile-contacts no-padding-left no-padding-top no-padding-right">
															<div class="profile-badge magenta">
																<i class="fa fa-globe magenta"></i>
																<span><?php echo Core::_("User.view_websites")?></span>
															</div>
															<div class="contact-info">
															<?php
															foreach ($aDirectory_Websites as $oDirectory_Website)
															{
															?>
																<p><a href="<?php echo htmlspecialchars($oDirectory_Website->value)?>" target="_blank"><?php echo htmlspecialchars($oDirectory_Website->value)?></a></p>
															<?php
															}
															?>
															</div>
														</div>
													</div>
												<?php
												}
												?>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}
}