<?php
/**
 * Back end
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
require_once ('../bootstrap.php');

Core_Auth::systemInit();
Core_Auth::setCurrentSite();

if (Core::moduleIsActive('ipaddress'))
{
	// Check IP addresses
	$ip = Core::getClientIp();

	$aIp = array($ip);
	$HTTP_X_FORWARDED_FOR = Core_Array::get($_SERVER, 'HTTP_X_FORWARDED_FOR');
	if (!is_null($HTTP_X_FORWARDED_FOR) && $ip != $HTTP_X_FORWARDED_FOR)
	{
		$aIp[] = $HTTP_X_FORWARDED_FOR;
	}

	$oIpaddress_Controller = new Ipaddress_Controller();

	$bBlocked = $oIpaddress_Controller->isBackendBlocked($aIp);

	if ($bBlocked)
	{
		$oCore_Response = new Core_Response();

		$oCore_Response
			->status(403)
			->header('Pragma', 'no-cache')
			->header('Cache-Control', 'private, no-cache')
			->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
			->header('X-Powered-By', 'HostCMS')
			->body('HostCMS: Error 403. Access Forbidden!')
			->sendHeaders()
			->showBody();
		exit();
	}
}

Core_Session::start();

if (!is_null(Core_Array::getGet('webauthnLoadList')))
{
	try
	{
		$oCore_Webauthn = new Core_Webauthn();

		// Передаем пустой массив ключей ($ids), чтобы устройство показало пользователю список сохраненных аккаунтов
		$args = $oCore_Webauthn->getGetArgs(array(), 60*4, true, 'discouraged');

		if ($args)
		{
			$challengeBinary = $args->publicKey->challenge->getBinaryString();

			if (!isset($_SESSION['webauthn_challenges']) || !is_array($_SESSION['webauthn_challenges']))
			{
				$_SESSION['webauthn_challenges'] = array();
			}

			$now = time();

			// Очищаем протухшие challenge (старше 5 минут)
			foreach ($_SESSION['webauthn_challenges'] as $key => $expires)
			{
				if ($expires < $now)
				{
					unset($_SESSION['webauthn_challenges'][$key]);
				}
			}

			// Ограничиваем размер массива, чтобы сессия не раздувалась (максимум 10 вкладок/попыток)
			if (count($_SESSION['webauthn_challenges']) > 10)
			{
				array_shift($_SESSION['webauthn_challenges']); // Удаляем самый старый
			}

			// Добавляем новый challenge со сроком жизни 5 минут (300 секунд)
			$_SESSION['webauthn_challenges'][$challengeBinary] = $now + 300;
		}
	}
	catch (Exception $e)
	{
		$args = array('error' => $e->getMessage());
	}

	Core::showJson($args);
}

if (!is_null(Core_Array::getGet('webauthnCheck')))
{
	$aReturn = array('success' => FALSE);

	try
	{
		$post = trim(file_get_contents('php://input'));
		if ($post)
		{
			$post = json_decode($post, NULL, 512, defined('JSON_THROW_ON_ERROR') ? JSON_THROW_ON_ERROR : 0);

			if (is_object($post))
			{
				$clientDataJSON = !empty($post->clientDataJSON) ? base64_decode($post->clientDataJSON) : NULL;

				// 1. Извлекаем challenge из ответа клиента
				$clientData = $clientDataJSON ? json_decode($clientDataJSON) : NULL;
				$challenge = '';

				if (is_object($clientData) && property_exists($clientData, 'challenge'))
				{
					$challengeBinary = Core_Bytebuffer::fromBase64Url($clientData->challenge)->getBinaryString();

					// Проверяем, есть ли такой challenge в сессии и не истек ли его срок
					if (isset($_SESSION['webauthn_challenges'][$challengeBinary]) && $_SESSION['webauthn_challenges'][$challengeBinary] >= time())
					{
						$challenge = $challengeBinary;

						// Сразу удаляем использованный challenge (защита от Replay-атак)
						unset($_SESSION['webauthn_challenges'][$challengeBinary]);
					}
				}

				if (empty($challenge))
				{
					throw new Core_Exception('Session challenge expired, missing or invalid');
				}

				$authenticatorData = !empty($post->authenticatorData) ? base64_decode($post->authenticatorData) : NULL;
				$signature = !empty($post->signature) ? base64_decode($post->signature) : NULL;
				$credentialId = !empty($post->id) ? $post->id : NULL;

				if (!is_null($credentialId))
				{
					// Ищем ключ в БД по credentialId
					$oUser_Webauthn = Core_Entity::factory('User_Webauthn');
					$oUser_Webauthn->queryBuilder()
						->where('credential_id', '=', $credentialId)
						->limit(1);

					$aUser_Webauthns = $oUser_Webauthn->findAll(FALSE);

					if (isset($aUser_Webauthns[0]) && $aUser_Webauthns[0]->User->id)
					{
						$oFoundKey = $aUser_Webauthns[0];
						$oUser = $oFoundKey->User;

						$credentialPublicKey = $oFoundKey->credential_public_key;
						$allowedCredentials = array($oFoundKey->credential_id);

						// Формируем expectedUserHandle так же, как при регистрации
						$expectedUserHandle = hex2bin(sha1($oUser->guid));

						// Получаем предыдущий счетчик подписей
						$prevSignatureCnt = isset($oFoundKey->signature_counter) ? (int)$oFoundKey->signature_counter : 0;

						$oCore_Webauthn = new Core_Webauthn();
						$processGetResult = $oCore_Webauthn->processGet(
							$clientDataJSON,
							$authenticatorData,
							$signature,
							$credentialId,
							$allowedCredentials,
							$expectedUserHandle,
							$credentialPublicKey,
							$challenge,
							$prevSignatureCnt,
							FALSE
						);

						if (is_array($processGetResult) && $processGetResult['success'])
						{
							$aReturn['success'] = TRUE;

							// Обновляем счетчик для защиты от клонирования ключа
							if (isset($processGetResult['newSignatureCounter'])) {
								$oFoundKey->signature_counter = $processGetResult['newSignatureCounter'];
								$oFoundKey->save();
							}

							Core_Auth::setCurrentUser($oUser, FALSE);
						}
						else
						{
							$aReturn['msg'] = Core_Message::get(Core::_('Admin.wrong_fast_login'), 'error');
						}
					}
					else
					{
						$aReturn['msg'] = Core_Message::get(Core::_('Admin.wrong_fast_login'), 'error');
					}
				}
				else
				{
					$aReturn['msg'] = Core_Message::get(Core::_('Admin.wrong_fast_login'), 'error');
				}
			}
			else
			{
				$aReturn['msg'] = 'webauthnCheck JSON error';
			}
		}
		else
		{
			$aReturn['msg'] = 'webauthnCheck data error';
		}
	}
	catch (Exception $e)
	{
		$aReturn['msg'] = 'webauthnCheck unknown error, check logs';
	}

	Core::showJson($aReturn);
}

if (Core_Auth::logged())
{
	$oCurrentUser = Core_Auth::getCurrentUser();

	if (!is_null(Core_Array::getGet('webauthnRegisterList')))
	{
		try
		{
			$userId = sha1($oCurrentUser->guid);
			$userName = $userDisplayName = $oCurrentUser->login;

			$excludeCredentialIds = array();

			$aUser_Webauthns = $oCurrentUser->User_Webauthns->findAll(FALSE);
			foreach ($aUser_Webauthns as $oUser_Webauthn)
			{
				$excludeCredentialIds[] = base64_decode($oUser_Webauthn->credential_id);
			}

			$oCore_Webauthn = new Core_Webauthn();

			$args = $oCore_Webauthn->getCreateArgs(
				hex2bin($userId),
				$userName,
				$userDisplayName,
				60*4,
				TRUE, // requireResidentKey = TRUE (заставляем ключ запомнить пользователя)
				'discouraged', // requireUserVerification
				FALSE, // crossPlatformAttachment
				$excludeCredentialIds
			);

			if ($args)
			{
				$challengeBinary = $args->publicKey->challenge->getBinaryString();

				if (!isset($_SESSION['webauthn_challenges']) || !is_array($_SESSION['webauthn_challenges']))
				{
					$_SESSION['webauthn_challenges'] = array();
				}

				$now = time();

				// Очищаем протухшие challenge (старше 5 минут)
				foreach ($_SESSION['webauthn_challenges'] as $key => $expires)
				{
					if ($expires < $now)
					{
						unset($_SESSION['webauthn_challenges'][$key]);
					}
				}

				// Ограничиваем размер массива, чтобы сессия не раздувалась (максимум 10 вкладок/попыток)
				if (count($_SESSION['webauthn_challenges']) > 10)
				{
					array_shift($_SESSION['webauthn_challenges']); // Удаляем самый старый
				}

				// Добавляем новый challenge со сроком жизни 5 минут (300 секунд)
				$_SESSION['webauthn_challenges'][$challengeBinary] = $now + 300;
			}
		}
		catch (Exception $e)
		{
			$args['msg'] = 'webauthnRegisterList error';
		}

		Core::showJson($args);
	}

	if (!is_null(Core_Array::getGet('webauthnRegister')))
	{
		$aReturn = array();

		try
		{
			$post = trim(file_get_contents('php://input'));

			if ($post)
			{
				$post = json_decode($post, NULL, 512, defined('JSON_THROW_ON_ERROR') ? JSON_THROW_ON_ERROR : 0);

				if (is_object($post))
				{
					$clientDataJSON = !empty($post->clientDataJSON) ? base64_decode($post->clientDataJSON) : NULL;
					$attestationObject = !empty($post->attestationObject) ? base64_decode($post->attestationObject) : NULL;

					$clientData = $clientDataJSON ? json_decode($clientDataJSON) : NULL;
					$challenge = NULL;

					if (is_object($clientData) && property_exists($clientData, 'challenge'))
					{
						$challengeBinary = Core_Bytebuffer::fromBase64Url($clientData->challenge)->getBinaryString();

						if (isset($_SESSION['webauthn_challenges'][$challengeBinary]) && $_SESSION['webauthn_challenges'][$challengeBinary] >= time())
						{
							$challenge = $challengeBinary;
							unset($_SESSION['webauthn_challenges'][$challengeBinary]);
						}
					}

					if (empty($challenge))
					{
						throw new Core_Exception('Session challenge expired, missing or invalid for registration');
					}

					$aReturn['success'] = FALSE;

					$oCore_Webauthn = new Core_Webauthn();

					$data = $oCore_Webauthn->processCreate($clientDataJSON, $attestationObject, $challenge, $oCurrentUser->id, TRUE, TRUE);

					if ($data instanceof stdClass)
					{
						$oUser_WebAuthn = Core_Entity::factory('User_Webauthn');
						$oUser_WebAuthn->user_id = $oCurrentUser->id;
						$oUser_WebAuthn->credential_id = base64_encode($data->credentialId);
						$oUser_WebAuthn->credential_public_key = $data->credentialPublicKey;
						$oUser_WebAuthn->save();

						Core_Cookie::set('_h_login', $oCurrentUser->login, array('expires' => time() + 15552000, 'path' => '/')); // half year

						$aReturn['success'] = TRUE;
					}
				}
				else
				{
					$aReturn['msg'] = 'webauthnRegister JSON error';
				}
			}
			else
			{
				$aReturn['msg'] = 'webauthnRegister data error';
			}
		}
		catch (Exception $e)
		{
			$aReturn['msg'] = 'webauthnRegister error';
		}

		Core::showJson($aReturn);
	}
}

ob_start();

if (!is_null(Core_Array::getGet('skinName')))
{
	$skinName = Core_Array::getGet('skinName');

	$aConfig = Core_Config::instance()->get('skin_config');
	if (isset($aConfig[$skinName]))
	{
		Core::$mainConfig['skin'] = $_SESSION['skin'] = $skinName;
	}
	else
	{
		throw new Core_Exception('Skin does not allow.');
	}
}
elseif (isset($_SESSION['skin']))
{
	Core::$mainConfig['skin'] = $_SESSION['skin'];
}

$oAdmin_Answer = Core_Skin::instance()->answer();

if (!is_null(Core_Array::getPost('submit')) && !Core_Auth::logged())
{
	$bDeviceTracking = isset($_POST['ip']);

	$_COOKIE['hostcms_device_tracking'] = $bDeviceTracking ? 'on' : 'off';
	Core_Cookie::set('hostcms_device_tracking', $_COOKIE['hostcms_device_tracking'], array('expires' => time() + 31536000, 'path' => '/'));

	try {
		/*$aConfig = Core_Session::getConfig();
		Core_Cookie::set(session_name(), '', array(
			'expires' => 0,
			'path' => '/',
			'domain' => Core_Session::correctDomain(Core_Session::getDomain()),
			'secure' => $aConfig['secure'],
			'httponly' => $aConfig['httponly'])
		);*/

		if (!Core_Security::checkCsrf(Core_Array::getPost('secret_csrf', '', 'str'), Core::$mainConfig['csrf_lifetime']))
		{
			switch (Core_Security::getCsrfError())
			{
				case 'wrong-length':
					throw new Core_Exception(Core::_('Core.csrf_wrong_token'), array(), 0, FALSE);
				break;
				case 'wrong-token':
				default:
					throw new Core_Exception(Core::_('Core.csrf_wrong_token'), array(), 0, FALSE);
				break;
				case 'timeout':
					throw new Core_Exception(Core::_('Core.csrf_token_timeout'), array(), 0, FALSE);
				break;
			}
		}

		$login = Core_Array::getPost('login', '', 'str');
		$password = Core_Array::getPost('password', '', 'str');

		$authResult = Core_Auth::login($login, $password, $bDeviceTracking);

		$authResult
			&& Core_Cookie::set('_h_login', $login, array('expires' => time() + 15552000, 'path' => '/')); // half year

		Core_Auth::setCurrentSite();
	}
	catch (Exception $e)
	{
		$oAdmin_Answer->message(
			Core_Message::get($e->getMessage(), 'error')
		);
	}
}

if (!Core_Auth::logged())
{
	$title = Core::_('Admin.authorization_title');

	if (isset($authResult))
	{
		if ($authResult == FALSE)
		{
			Core_Log::instance()->clear()
				->status(Core_Log::$ERROR)
				//->notify(FALSE)
				->write(Core::_('Core.error_log_authorization_error', $login, FALSE));

			$oAdmin_Answer->message(
				Core_Message::get(Core::_('Admin.authorization_error_valid_user', $login, Core::getClientIp()), 'error')
			);
		}
		// если пользователю сейчас запрещен ввод пароля
		elseif (is_array($authResult) && $authResult['result'] == -1)
		{
			$error_admin_access = $authResult['value'];

			$oAdmin_Answer->message(
				Core_Message::get(Core::_('Admin.authorization_error_access_temporarily_unavailable', $error_admin_access), 'error')
			);
		}
	}

	Core_Skin::instance()->authorization();
}
else
{
	$title = Core::_('Admin.index_title', 'HostCMS', Core_Auth::logged() ? strip_tags(CURRENT_VERSION) : 7);
	Core::initConstants(Core_Entity::factory('Site', CURRENT_SITE));
	Core_Skin::instance()->index();
}

$oAdmin_Answer
	->ajax(Core_Array::getRequest('_', FALSE))
	->content(ob_get_clean())
	->title($title)
	->module('dashboard')
	->execute();