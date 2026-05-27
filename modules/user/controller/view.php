<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * View profile controller
 *
 * Контроллер просмотра профиля.
 *
 * @package HostCMS
 * @subpackage User
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
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
	 * @return string
	 */
	protected function _showEditForm()
	{
		ob_start();
		?>
		<div class="row">
			<div class="col-xs-12">
				<div class="hc-profile-card">

					<aside class="hc-profile-sidebar">
						<div class="hc-profile-avatar">
							<img src="<?php echo htmlspecialchars((string) $this->_object->getAvatar())?>" alt="">
						</div>

						<h1 class="hc-profile-name">
							<?php echo htmlspecialchars((string) $this->_object->getFullName())?>
							<?php if ($this->_object->superuser): ?>
								<i class="fa-solid fa-crown hc-profile-superuser-star" title="Superuser"></i>
							<?php endif; ?>
						</h1>

						<div class="hc-profile-status">
							<?php echo nl2br(htmlspecialchars((string) $this->_object->description))?>
						</div>

						<div class="hc-profile-meta hc-meta-row">
							<?php if (strlen((string) $this->_object->address)): ?>
								<div class="hc-profile-meta-item" style="flex-direction: column; align-items: center; text-align: center; gap: 4px;">
									<span class="hc-profile-meta-label"><i class="fa-solid fa-location-dot"></i></span>
									<span class="hc-profile-meta-value" style="text-align: center;"><?php echo htmlspecialchars($this->_object->address)?></span>
								</div>
							<?php endif; ?>

							<div class="d-flex w-100" style="gap: 20px;">
								<div class="hc-profile-meta-item">
									<span class="hc-profile-meta-label"><?php echo Core::_("User.view_sex")?></span>
									<span class="hc-profile-meta-value margin-left-10"><?php echo $this->_object->getSex()?></span>
								</div>

								<?php if (!is_null($this->_object->birthday) && $this->_object->birthday != '0000-00-00'): ?>
									<div class="hc-profile-meta-item">
										<span class="hc-profile-meta-label"><?php echo Core::_("User.view_age")?></span>
										<span class="hc-profile-meta-value margin-left-10"><?php echo htmlspecialchars((string) $this->_object->getAge())?></span>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</aside>

					<main class="hc-profile-content">
						<?php
						$aCompanies = Core_Entity::factory('Company')->findAll(FALSE);

						$bHasCareer = FALSE;

						ob_start();
						foreach ($aCompanies as $oCompany)
						{
							$aCompany_Department_Post_Users = $this->_object->Company_Department_Post_Users->getAllByCompany_id($oCompany->id);

							if (count($aCompany_Department_Post_Users))
							{
								$bHasCareer = true;
								?>
								<div class="hc-profile-career-block">
									<h3 class="hc-profile-company-name"><?php echo htmlspecialchars((string) $oCompany->name)?></h3>
									<ul class="hc-profile-role-list">
										<?php foreach ($aCompany_Department_Post_Users as $oCompany_Department_Post_User): ?>
											<li class="hc-profile-role-item">
												<span class="hc-profile-role-dept" title="<?php echo htmlspecialchars((string) $oCompany_Department_Post_User->Company_Department->name)?>"><?php echo htmlspecialchars((string) $oCompany_Department_Post_User->Company_Department->name)?></span>
												<span class="hc-profile-role-pos" title="<?php echo htmlspecialchars((string) $oCompany_Department_Post_User->Company_Post->name)?>"><?php echo htmlspecialchars((string) $oCompany_Department_Post_User->Company_Post->name)?></span>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
								<?php
							}
						}
						$sCareerContent = ob_get_clean();

						if ($bHasCareer): ?>
							<section class="hc-profile-section">
								<?php echo $sCareerContent; ?>
							</section>
						<?php endif; ?>

						<section class="hc-profile-section">
							<div class="hc-profile-contacts-grid">
								<?php
								// Телефоны
								$aDirectory_Phones = $this->_object->Directory_Phones->findAll();
								foreach ($aDirectory_Phones as $oDirectory_Phone)
								{
									$oDirectory_Phone_Type = Core_Entity::factory('Directory_Phone_Type')->find($oDirectory_Phone->directory_phone_type_id);
									$sPhoneType = !is_null($oDirectory_Phone_Type->id) ? htmlspecialchars($oDirectory_Phone_Type->name) : Core::_("User.view_phones");
									?>
									<div class="hc-profile-contact-card hc-profile-highlight-green">
										<div class="hc-profile-contact-icon"><i class="fa-solid fa-phone"></i></div>
										<div class="hc-profile-contact-info">
											<div class="hc-profile-contact-type"><?php echo $sPhoneType?></div>
											<div class="hc-profile-contact-value"><?php echo htmlspecialchars((string) $oDirectory_Phone->value)?></div>
										</div>
									</div>
									<?php
								}

								// Электронные адреса
								$aDirectory_Emails = $this->_object->Directory_Emails->findAll();
								foreach ($aDirectory_Emails as $oDirectory_Email)
								{
									$oDirectory_Email_Type = Core_Entity::factory('Directory_Email_Type')->find($oDirectory_Email->directory_email_type_id);
									$sEmailType = !is_null($oDirectory_Email_Type->id) ? htmlspecialchars($oDirectory_Email_Type->name) : Core::_("User.view_emails");
									?>
									<div class="hc-profile-contact-card hc-profile-highlight-yellow">
										<div class="hc-profile-contact-icon"><i class="fa-regular fa-envelope"></i></div>
										<div class="hc-profile-contact-info">
											<div class="hc-profile-contact-type"><?php echo $sEmailType?></div>
											<div class="hc-profile-contact-value"><a href="mailto:<?php echo htmlspecialchars((string) $oDirectory_Email->value)?>"><?php echo htmlspecialchars((string) $oDirectory_Email->value)?></a></div>
										</div>
									</div>
									<?php
								}

								// Социальные сети
								$aDirectory_Socials = $this->_object->Directory_Socials->findAll();
								foreach ($aDirectory_Socials as $oDirectory_Social)
								{
									$oDirectory_Social_Type = Core_Entity::factory('Directory_Social_Type')->find($oDirectory_Social->directory_social_type_id);
									$sSocialType = !is_null($oDirectory_Social_Type->id) ? htmlspecialchars($oDirectory_Social_Type->name) : Core::_("User.view_socials");
									$sIconClass = (!is_null($oDirectory_Social_Type->id) && strlen($oDirectory_Social_Type->ico)) ? htmlspecialchars($oDirectory_Social_Type->ico) : 'fa-solid fa-share-nodes';
									?>
									<div class="hc-profile-contact-card hc-profile-highlight-blue">
										<div class="hc-profile-contact-icon"><i class="<?php echo $sIconClass; ?>"></i></div>
										<div class="hc-profile-contact-info">
											<div class="hc-profile-contact-type"><?php echo $sSocialType?></div>
											<div class="hc-profile-contact-value"><a href="<?php echo htmlspecialchars((string) $oDirectory_Social->value)?>" target="_blank"><?php echo htmlspecialchars((string) $oDirectory_Social->value)?></a></div>
										</div>
									</div>
									<?php
								}

								// Мессенджеры
								$aDirectory_Messengers = $this->_object->Directory_Messengers->findAll();
								foreach ($aDirectory_Messengers as $oDirectory_Messenger)
								{
									$oDirectory_Messenger_Type = Core_Entity::factory('Directory_Messenger_Type')->find($oDirectory_Messenger->directory_messenger_type_id);
									$sMessengerType = !is_null($oDirectory_Messenger_Type->id) ? htmlspecialchars($oDirectory_Messenger_Type->name) : Core::_("User.view_messengers");
									$sIconClass = (!is_null($oDirectory_Messenger_Type->id) && strlen($oDirectory_Messenger_Type->ico)) ? htmlspecialchars($oDirectory_Messenger_Type->ico) : 'fa-solid fa-comment-dots';
									$sLink = !is_null($oDirectory_Messenger_Type->id) ? sprintf($oDirectory_Messenger_Type->link, $oDirectory_Messenger->value) : $oDirectory_Messenger->value;
									?>
									<div class="hc-profile-contact-card hc-profile-highlight-purple">
										<div class="hc-profile-contact-icon"><i class="<?php echo $sIconClass; ?>"></i></div>
										<div class="hc-profile-contact-info">
											<div class="hc-profile-contact-type"><?php echo $sMessengerType?></div>
											<div class="hc-profile-contact-value"><a href="<?php echo htmlspecialchars((string) $sLink)?>" target="_blank"><?php echo htmlspecialchars((string) $oDirectory_Messenger->value)?></a></div>
										</div>
									</div>
									<?php
								}

								// Сайты
								$aDirectory_Websites = $this->_object->Directory_Websites->findAll();
								foreach ($aDirectory_Websites as $oDirectory_Website)
								{
									?>
									<div class="hc-profile-contact-card hc-profile-highlight-pink">
										<div class="hc-profile-contact-icon"><i class="fa-solid fa-globe"></i></div>
										<div class="hc-profile-contact-info">
											<div class="hc-profile-contact-type"><?php echo Core::_("User.view_websites")?></div>
											<div class="hc-profile-contact-value"><a href="<?php echo htmlspecialchars((string) $oDirectory_Website->value)?>" target="_blank"><?php echo htmlspecialchars((string) $oDirectory_Website->value)?></a></div>
										</div>
									</div>
									<?php
								}
								?>
							</div>
						</section>

					</main>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}