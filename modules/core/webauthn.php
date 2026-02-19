<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * HostCMS Web_Authn authorization
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Webauthn extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'rpName',
		'rpId',
		'rpOrigin',
		'allowLocalhost',
		'allowedAndroidKeyHashes'
	);

	/**
	 * Hash
	 * @var mixed
	 */
	protected $_rpIdHash = NULL;

	/**
	 * Signature count
	 * @var mixed
	 */
	protected $_signatureCounter = NULL;

	/**
	 * Formats
	 * @var array
	 */
	protected $_formats = array('none', 'packed', 'fido-u2f', 'android-key', 'android-safetynet', 'tpm', 'apple');

	/**
	 * Supported algorithms
	 */
	static protected $ALG_EDDSA = -8;
	static protected $ALG_ES256 = -7;
	static protected $ALG_RS256 = -257;
	static protected $ALG_PS256 = -37;

	/**
	 * Errors
	 * @var mixed
	 */
	protected $_error = NULL;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->rpName = 'HostCMS WebAuthn';
		$this->rpId = Core_Array::get($_SERVER, 'HTTP_HOST', 'localhost');
		$this->rpOrigin = ($this->rpId === 'localhost' ? 'http' : 'https') . '://' . $this->rpId;
		$this->allowLocalhost = TRUE;
		$this->allowedAndroidKeyHashes = [];

		$this->_rpIdHash = hash('sha256', $this->rpId, TRUE);

		Core_Bytebuffer::$useBase64UrlEncoding = FALSE;
	}

	/**
	 * Get error
	 * @return mixed
	 */
	public function getError()
	{
		return $this->_error;
	}

	/**
	 * Generates a new challange
	 * @param int $length
	 * @return Core_Bytebuffer
     */
	protected function _createChallenge($length = 32)
	{
		return Core_Bytebuffer::randomBuffer($length);
	}

	/**
	 * Check if algorithm is supported
	 * @param int $alg
	 * @return bool
	 */
	protected function _isAlgorithmSupported(int $alg)
	{
		switch ($alg)
		{
			case self::$ALG_EDDSA:
				return function_exists('sodium_crypto_sign_verify_detached')
					|| in_array('ed25519', openssl_get_curve_names(), TRUE);

			case self::$ALG_ES256:
				return in_array('prime256v1', openssl_get_curve_names(), TRUE);

			case self::$ALG_RS256:
			case self::$ALG_PS256:
				return in_array('sha256', openssl_get_md_methods(), TRUE);

			default:
				return FALSE;
		}
	}

	/**
	 * Get supported algorithms
	 * @return array
	 */
	protected function _getSupportedAlgorithms()
	{
		$algorithms = [];

		if ($this->_isAlgorithmSupported(self::$ALG_EDDSA))
		{
			$algorithms[] = self::$ALG_EDDSA;
		}

		if ($this->_isAlgorithmSupported(self::$ALG_ES256))
		{
			$algorithms[] = self::$ALG_ES256;
		}

		if ($this->_isAlgorithmSupported(self::$ALG_RS256))
		{
			$algorithms[] = self::$ALG_RS256;
		}

		if ($this->_isAlgorithmSupported(self::$ALG_PS256))
		{
			$algorithms[] = self::$ALG_PS256;
		}

		return $algorithms;
	}

	/**
	 * Generates the object for a key registration
	 *
	 * @param string $userId
	 * @param string $userName
	 * @param string $userDisplayName
	 * @param int $timeout
	 * @param bool|string $requireResidentKey 'required', if the key should be stored by the authentication device. Valid values: TRUE = required, FALSE = preferred, string 'required' 'preferred' 'discouraged'
	 * @param bool|string $requireUserVerification indicates that you require user verification and will fail the operation if the response does not have the UV flag set. Valid values: TRUE = required, FALSE = preferred, string 'required' 'preferred' 'discouraged'
	 * @param bool|null $crossPlatformAttachment TRUE for cross-platform devices (eg. fido usb), FALSE for platform devices (eg. windows hello, android safetynet), NULL for both
	 * @param array $excludeCredentialIds a array of ids, which are already registered, to prevent re-registration
	 * @param string $attestation
	 * @return stdClass
	 * @throws Exception
	 */
	public function getCreateArgs(
		$userId,
		$userName,
		$userDisplayName,
		$timeout = 20,
		$requireResidentKey = FALSE,
		$requireUserVerification = FALSE,
		$crossPlatformAttachment = NULL,
		$excludeCredentialIds = [],
		$attestation = 'none'
	)
	{
		$args = new stdClass();
		$args->publicKey = new stdClass();

		// relying party
		$args->publicKey->rp = new stdClass();
		$args->publicKey->rp->name = $this->rpName;
		$args->publicKey->rp->id = $this->rpId;

		$args->publicKey->authenticatorSelection = new stdClass();
		$args->publicKey->authenticatorSelection->userVerification = 'preferred';

		// validate User Verification Requirement
		if (is_bool($requireUserVerification))
		{
			$args->publicKey->authenticatorSelection->userVerification = $requireUserVerification ? 'required' : 'preferred';
		}
		elseif (is_string($requireUserVerification) && in_array(strtolower($requireUserVerification), ['required', 'preferred', 'discouraged'], TRUE))
		{
			$args->publicKey->authenticatorSelection->userVerification = strtolower($requireUserVerification);
		}

		// validate Resident Key Requirement
		if (is_bool($requireResidentKey) && $requireResidentKey)
		{
			$args->publicKey->authenticatorSelection->requireResidentKey = TRUE;
			$args->publicKey->authenticatorSelection->residentKey = 'required';
		}
		elseif (is_string($requireResidentKey) && in_array(strtolower($requireResidentKey), ['required', 'preferred', 'discouraged'], TRUE))
		{
			$requireResidentKey = strtolower($requireResidentKey);
			$args->publicKey->authenticatorSelection->residentKey = $requireResidentKey;
			$args->publicKey->authenticatorSelection->requireResidentKey = $requireResidentKey === 'required';
		}

		// Filter authenticators by attachment modality
		if (is_bool($crossPlatformAttachment))
		{
			$args->publicKey->authenticatorSelection->authenticatorAttachment = $crossPlatformAttachment ? 'cross-platform' : 'platform';
		}

		// user
		$args->publicKey->user = new stdClass();
		$args->publicKey->user->id = $userId instanceof Core_Bytebuffer ? $userId : new Core_Bytebuffer($userId);
		$args->publicKey->user->name = $userName;
		$args->publicKey->user->displayName = $userDisplayName;

		// supported algorithms
		$args->publicKey->pubKeyCredParams = [];

		foreach ($this->_getSupportedAlgorithms() as $alg)
		{
			$param = new stdClass();
			$param->type = 'public-key';
			$param->alg = $alg;
			$args->publicKey->pubKeyCredParams[] = $param;
		}

		$args->publicKey->attestation = in_array($attestation, ['none', 'indirect', 'direct', 'enterprise'], TRUE)
			? $attestation
			: 'none';

		$args->publicKey->extensions = new stdClass();
		$args->publicKey->extensions->exts = TRUE;
		$args->publicKey->timeout = $timeout * 1000; // microseconds
		$args->publicKey->challenge = $this->_createChallenge(); // binary

		// Prevent re-registration by specifying existing credentials
		$args->publicKey->excludeCredentials = [];

		foreach ($excludeCredentialIds as $id)
		{
			$credential = new stdClass();
			$credential->id = $id instanceof Core_Bytebuffer ? $id : new Core_Bytebuffer($id);
			$credential->type = 'public-key';
			$credential->transports = ['usb', 'nfc', 'ble', 'hybrid', 'internal'];
			$args->publicKey->excludeCredentials[] = $credential;
		}

		return $args;
	}

	/**
	 * Process a create request and returns data to save for future logins
	 * @param string $clientDataJSON binary from browser
	 * @param string $attestationObject binary from browser
	 * @param string|Core_Bytebuffer $challenge binary used challange
	 * @param string $expectedUserId
	 * @param bool $requireUserVerification TRUE, if the device must verify user (e.g. by biometric data or pin)
	 * @param bool $requireUserPresent FALSE, if the device must NOT check user presence (e.g. by pressing a button)
	 * @return stdClass|FALSE
	 */
	public function processCreate(
		$clientDataJSON,
		$attestationObject,
		$challenge,
		$expectedUserId,
		$requireUserVerification = FALSE,
		$requireUserPresent = TRUE
	)
	{
		$this->_error = NULL;

		$clientDataHash = hash('sha256', $clientDataJSON, TRUE);
		$clientData = json_decode($clientDataJSON);
		$challenge = $challenge instanceof Core_Bytebuffer ? $challenge : new Core_Bytebuffer($challenge);

		// security: https://www.w3.org/TR/webauthn/#registering-a-new-credential

		// 1. Validate client data
		if (!is_object($clientData))
		{
			$this->_error = 'Invalid client data';
			return FALSE;
		}

		// 2. Verify type
		if (!property_exists($clientData, 'type') || $clientData->type !== 'webauthn.create')
		{
			$this->_error = 'Invalid type, expected webauthn.create';
			return FALSE;
		}

		// 3. Verify challenge
		if (!property_exists($clientData, 'challenge')
			|| Core_Bytebuffer::fromBase64Url($clientData->challenge)->getBinaryString() !== $challenge->getBinaryString()
		)
		{
			$this->_error = 'Invalid challenge';
			return FALSE;
		}

		// 4. Verify origin
		if (!property_exists($clientData, 'origin') || !$this->_checkOrigin($clientData->origin))
		{
			$this->_error = 'Invalid origin';
			return FALSE;
		}

		// 5. Verify token binding if present
		if (property_exists($clientData, 'tokenBinding'))
		{
			if (!property_exists($clientData->tokenBinding, 'status')
				||  !in_array($clientData->tokenBinding->status, ['present', 'supported'], TRUE)
			)
			{
				$this->_error = 'Invalid token binding status';
				return FALSE;
			}
		}

		// 6. Decode attestation object
		$enc = Core_Cbor::decode($attestationObject);
		if (!is_array($enc) || !array_key_exists('fmt', $enc) || !is_string($enc['fmt']))
		{
			$this->_error = 'Invalid attestation format';
			return FALSE;
		}
		if (!array_key_exists('attStmt', $enc) || !is_array($enc['attStmt']))
		{
			$this->_error = 'Invalid attestation statement';
			return FALSE;
		}
		if (!array_key_exists('authData', $enc) || !is_object($enc['authData']) || !($enc['authData'] instanceof Core_Bytebuffer))
		{
			$this->_error = 'Invalid authData';
			return FALSE;
		}

		// 7. Parse authenticator data
		$_authenticatorData = new Core_Webauthn_Authenticator_Data($enc['authData']->getBinaryString());
		$_attestationFormatName = $enc['fmt'];

		// 8. Check format support
		if (!in_array($_attestationFormatName, $this->_formats, TRUE))
		{
			$this->_error = 'Unsupported attestation format: ' . $_attestationFormatName;
			return FALSE;
		}

		// 9. Load attestation format handler
		$attestationClass = 'Core_Webauthn_Format_' . str_replace(' ', '', ucwords(str_replace('-', ' ', $_attestationFormatName)));
		if (!class_exists($attestationClass))
		{
			$this->_error = 'Attestation format handler not found: ' . $_attestationFormatName;
			return FALSE;
		}

		try
		{
			$_attestationFormat = new $attestationClass($enc, $_authenticatorData);
		}
		catch (Exception $e)
		{
			$this->_error = 'Failed to load attestation handler: ' . $e->getMessage();
			return FALSE;
		}

		// 10. Verify RP ID hash
		if ($this->_rpIdHash !== $_authenticatorData->getRpIdHash())
		{
			$this->_error = 'Invalid RP ID hash';
			return FALSE;
		}

		// 11. Verify user presence
		$userPresent = $_authenticatorData->getUserPresent();
		if ($requireUserPresent && !$userPresent)
		{
			$this->_error = 'User not present during authentication';
			return FALSE;
		}

		// 12. Verify user verification if required
		$userVerified = $_authenticatorData->getUserVerified();
		if ($requireUserVerification && !$userVerified)
		{
			$this->_error = 'User not verified during authentication';
			return FALSE;
		}

		// 13. Verify attestation statement
		if (!$_attestationFormat->validateAttestation($clientDataHash))
		{
			$this->_error = 'Invalid attestation signature';
			return FALSE;
		}

		// 14. Verify credential ID is not empty
		$credentialId = $_authenticatorData->getCredentialId();
		if (empty($credentialId))
		{
			$this->_error = 'Empty credential ID';
			return FALSE;
		}

		// 15. Verify public key
		$publicKeyPem = $_authenticatorData->getPublicKeyPem();
		if (empty($publicKeyPem))
		{
			$this->_error = 'Invalid public key';
			return FALSE;
		}

		// 16. Verify algorithm is supported
		$coseKey = $_authenticatorData->getCoseKey();
		if (!$coseKey || !isset($coseKey[3]))
		{
			$this->_error = 'No COSE key in authenticator data';
			return FALSE;
		}

		$algorithm = $_authenticatorData->getAlgorithm();
		if (!$algorithm || !$this->_isAlgorithmSupported($algorithm))
		{
			$this->_error = 'Unsupported algorithm: ' . Core_Array::get($coseKey, 3, 'unknown');
			return FALSE;
		}

		$signCount = $_authenticatorData->getSignCount();
		if ($signCount > 0)
		{
			$this->_signatureCounter = $signCount;
		}

		$pem = $_attestationFormat->getCertificatePem();

		// 17. Verify certificate if present
		// Вынесено в обработчики Core_Webauthn_Format_*
		/*if ($pem)
		{
			$certInfo = openssl_x509_parse($pem, TRUE);
			if ($certInfo === FALSE)
			{
				$this->_error = 'Invalid certificate';
				return FALSE;
			}

			// Check certificate validity period
			$now = time();
			if ($certInfo['validTo_time_t'] < $now || $certInfo['validFrom_time_t'] > $now)
			{
				$this->_error = 'Certificate is expired or not yet valid';
				return FALSE;
			}
		}*/

		// Prepare data to store
		$data = new stdClass();
		$data->rpId = $this->rpId;
		$data->userId = $expectedUserId;
		$data->attestationFormat = $_attestationFormatName;
		$data->credentialId = $_authenticatorData->getCredentialId();
		$data->credentialPublicKey = $publicKeyPem;
		$data->credentialAlgorithm = $algorithm;
		$data->credentialKeyType = $_authenticatorData->getKeyType();
		$data->credentialCurve = $_authenticatorData->getCurve();
		$data->certificateChain = $_attestationFormat->getCertificateChain();
		$data->certificate = $pem;
		$data->certificateIssuer = $pem ? $this->getCertificateIssuer($pem) : NULL;
		$data->certificateSubject = $pem ? $this->getCertificateSubject($pem) : NULL;
		$data->signatureCounter = $this->_signatureCounter;
		$data->AAGUID = $_authenticatorData->getAAGUID();
		$data->rootValid = NULL;
		$data->userPresent = $userPresent;
		$data->userVerified = $userVerified;
		$data->isBackupEligible = $_authenticatorData->getIsBackupEligible();
		$data->isBackedUp = $_authenticatorData->getIsBackup();
		$data->createdAt = time();

		return $data;
	}

    /**
     * Generates the object for key validation
     * Provide this data to navigator.credentials.get
     * @param array $credentialIds binary
     * @param int $timeout timeout in seconds
     * @param bool $allowInternal allow client device-specific transport. These authenticators are not removable from the client device.
     * @param bool|string $requireUserVerification indicates that you require user verification and will fail the operation if the response does not have the UV flag set. Valid values: TRUE = required, FALSE = preferred, string 'required' 'preferred' 'discouraged'
     * @return stdClass
     */
	public function getGetArgs($credentialIds = array(), $timeout = 20, $allowInternal = TRUE, $requireUserVerification = FALSE)
	{
		// validate User Verification Requirement
		if (is_bool($requireUserVerification))
		{
			$requireUserVerification = $requireUserVerification ? 'required' : 'preferred';
		}
		elseif (is_string($requireUserVerification) && in_array(strtolower($requireUserVerification), ['required', 'preferred', 'discouraged'], TRUE))
		{
			$requireUserVerification = strtolower($requireUserVerification);
		}
		else
		{
			$requireUserVerification = 'preferred';
		}

		$args = new stdClass();
		$args->publicKey = new stdClass();
		$args->publicKey->timeout = $timeout * 1000; // microseconds
		$args->publicKey->challenge = $this->_createChallenge(); // binary
		$args->publicKey->userVerification = $requireUserVerification;
		$args->publicKey->rpId = $this->rpId;

		if (is_array($credentialIds) && count($credentialIds) > 0)
		{
			$args->publicKey->allowCredentials = [];

			foreach ($credentialIds as $id)
			{
				$tmp = new stdClass();
				$tmp->id = $id instanceof Core_Bytebuffer ? $id : new Core_Bytebuffer($id);
				$tmp->transports = [];

				if ($allowInternal)
				{
					$tmp->transports[] = 'internal';
				}

				$tmp->type = 'public-key';
				$args->publicKey->allowCredentials[] = $tmp;
			}
		}

		return $args;
	}

	/**
	 * Process a get request
	 * @param string $clientDataJSON binary from browser
	 * @param string $authenticatorData binary from browser
	 * @param string $signature binary from browser
	 * @param string $credentialId
	 * @param array $allowedCredentials
	 * @param string|null $expectedUserHandle
	 * @param string $credentialPublicKey string PEM-formated public key from used credentialId
	 * @param string|Core_Bytebuffer $challenge  binary from used challange
	 * @param int $prevSignatureCnt signature count value of the last login
	 * @param bool $requireUserVerification TRUE, if the device must verify user (e.g. by biometric data or pin)
	 * @param bool $requireUserPresent TRUE, if the device must check user presence (e.g. by pressing a button)
	 * @return array|false [success, newSignatureCounter]
	 */
	public function processGet(
		$clientDataJSON,
		$authenticatorData,
		$signature,
		$credentialId,
		$allowedCredentials,
		$expectedUserHandle,
		$credentialPublicKey,
		$challenge,
		$prevSignatureCnt = NULL,
		$requireUserVerification = FALSE,
		$requireUserPresent = TRUE
	)
	{
		$this->_error = NULL;

		$authenticatorObj = new Core_Webauthn_Authenticator_Data($authenticatorData);
		$clientDataHash = hash('sha256', $clientDataJSON, TRUE);
		$clientData = json_decode($clientDataJSON);
		$challenge = $challenge instanceof Core_Bytebuffer ? $challenge : new Core_Bytebuffer($challenge);

		// 1. Verify credential ID is allowed
		$credentialIdBinary = $credentialId instanceof Core_Bytebuffer
			? $credentialId->getBinaryString()
			: $credentialId;

		$credentialAllowed = FALSE;
		foreach ($allowedCredentials as $allowedId)
		{
			$allowedIdBinary = $allowedId instanceof Core_Bytebuffer
				? $allowedId->getBinaryString()
				: $allowedId;

			if (hash_equals($allowedIdBinary, $credentialIdBinary))
			{
				$credentialAllowed = TRUE;
				// Не прерываем для защиты от атак по времени
				//break;
			}
		}

		if (!$credentialAllowed)
		{
			$this->_error = 'Credential ID not allowed';
			return FALSE;
		}

		// https://www.w3.org/TR/webauthn/#verifying-assertion
		// 1. If the allowCredentials option was given when this authentication ceremony was initiated,
		//    verify that credential.id identifies one of the public key credentials that were listed in allowCredentials.
		//    -> TO BE VERIFIED BY IMPLEMENTATION
		// 2. If credential.response.userHandle is present, verify that the user identified
		//    by this value is the owner of the public key credential identified by credential.id.
		//    -> TO BE VERIFIED BY IMPLEMENTATION
		// 3. Using credential’s id attribute (or the corresponding rawId, if base64url encoding is
		//    inappropriate for your use case), look up the corresponding credential public key.
		//    -> TO BE LOOKED UP BY IMPLEMENTATION

		// 2. Validate client data
		if (!is_object($clientData))
		{
			$this->_error = 'Invalid client data';
			return FALSE;
		}

		// 3. Verify type
		if (!property_exists($clientData, 'type') || $clientData->type !== 'webauthn.get')
		{
			$this->_error = 'Invalid type, expected webauthn.get';
			return FALSE;
		}

		// 4. Verify challenge
		if (!property_exists($clientData, 'challenge')
			|| Core_Bytebuffer::fromBase64Url($clientData->challenge)->getBinaryString() !== $challenge->getBinaryString()
		)
		{
			$this->_error = 'Invalid challenge';
			return FALSE;
		}

		// 5. Verify origin
		if (!property_exists($clientData, 'origin') || !$this->_checkOrigin($clientData->origin))
		{
			$this->_error = 'Invalid origin';
			return FALSE;
		}

		// 6. Verify token binding if present
		if (property_exists($clientData, 'tokenBinding'))
		{
			if (!property_exists($clientData->tokenBinding, 'status')
				|| !in_array($clientData->tokenBinding->status, ['present', 'supported'], TRUE)
			)
			{
				$this->_error = 'Invalid token binding status';
				return FALSE;
			}
		}

		// 7. Verify RP ID hash
		if ($authenticatorObj->getRpIdHash() !== $this->_rpIdHash)
		{
			$this->_error = 'Invalid RP ID hash';
			return FALSE;
		}

		// 8. Verify user presence
		if ($requireUserPresent && !$authenticatorObj->getUserPresent())
		{
			$this->_error = 'User not present during authentication';
			return FALSE;
		}

		// 9. Verify user verification if required
		if ($requireUserVerification && !$authenticatorObj->getUserVerified())
		{
			$this->_error = 'User not verified during authentication';
			return FALSE;
		}

		// 10. Verify user handle if present
		if (!is_null($expectedUserHandle))
		{
			$userHandle = $authenticatorObj->getUserHandle();
			if (!is_null($userHandle))
			{
				$userHandleString = $userHandle instanceof Core_Bytebuffer
					? $userHandle->getBinaryString()
					: $userHandle;

				if (!hash_equals($expectedUserHandle, $userHandleString))
				{
					$this->_error = 'Invalid user handle';
					return FALSE;
				}
			}
		}

		// 11. Verify signature
		$dataToVerify = $authenticatorData . $clientDataHash;

		if (!$this->_verifySignature($dataToVerify, $signature, $credentialPublicKey)) {
			$this->_error = 'Invalid signature';
			return FALSE;
		}

		// 12. Verify signature counter
		$signatureCounter = $authenticatorObj->getSignCount();
		if (!is_null($prevSignatureCnt))
		{
			if ($signatureCounter !== 0 || $prevSignatureCnt !== 0)
			{
				if ($prevSignatureCnt >= $signatureCounter)
				{
					$this->_error = 'Signature counter not valid - possible cloned authenticator';
					return FALSE;
				}
			}
		}

		$this->_signatureCounter = $signatureCounter;

		return [
			'success' => TRUE,
			'newSignatureCounter' => $signatureCounter,
			'userPresent' => $authenticatorObj->getUserPresent(),
			'userVerified' => $authenticatorObj->getUserVerified(),
			'isBackupEligible' => $authenticatorObj->getIsBackupEligible(),
			'isBackedUp' => $authenticatorObj->getIsBackup()
		];
	}

	/**
	 * Checks if the origin matchs the RP ID
	 * @param string $origin
	 * @return boolean
	 */
	protected function _checkOrigin($origin)
	{
		if (strpos($origin, 'android:apk-key-hash:') === 0)
		{
			return $this->_checkAndroidKeyHashes($origin);
		}

		// https://www.w3.org/TR/webauthn/#rp-id

		// The origin's scheme must be https
		if ($this->rpId !== 'localhost' && parse_url($origin, PHP_URL_SCHEME) !== 'https')
		{
			return FALSE;
		}

		// extract host from origin
		$host = parse_url($origin, PHP_URL_HOST);
		$host = trim($host, '.');

		// The RP ID must be equal to the origin's effective domain, or a registrable
		// domain suffix of the origin's effective domain.
		return preg_match('/' . preg_quote($this->rpId) . '$/i', $host) === 1;
	}

	/**
	 * Checks if the origin value contains a known android key hash
	 * @param string $origin
	 * @return boolean
	 */
	protected function _checkAndroidKeyHashes($origin)
	{
		$parts = explode('android:apk-key-hash:', $origin);
		if (count($parts) !== 2)
		{
			return FALSE;
		}

		return in_array($parts[1], $this->allowedAndroidKeyHashes, TRUE);
	}

	/**
	 * Check if the signature is valid.
	 * @param string $dataToVerify
	 * @param string $signature
	 * @param string $credentialPublicKey PEM format
	 * @return bool
	 */
	protected function _verifySignature($dataToVerify, $signature, $credentialPublicKey)
	{
		// Use Sodium to verify EdDSA 25519 as its not yet supported by openssl
		if (function_exists('sodium_crypto_sign_verify_detached') && !in_array('ed25519', openssl_get_curve_names(), TRUE))
		{
			$pkParts = [];
			if (preg_match('/BEGIN PUBLIC KEY\-+(?:\s|\n|\r)+([^\-]+)(?:\s|\n|\r)*\-+END PUBLIC KEY/i', $credentialPublicKey, $pkParts))
			{
				$rawPk = base64_decode($pkParts[1]);

				// 30        = der sequence
				// 2a        = length 42 byte
				// 30        = der sequence
				// 05        = lenght 5 byte
				// 06        = der OID
				// 03        = OID length 3 byte
				// 2b 65 70  = OID 1.3.101.112 curveEd25519 (EdDSA 25519 signature algorithm)
				// 03        = der bit string
				// 21        = length 33 byte
				// 00        = NULL padding
				// [...]     = 32 byte x-curve
				$okpPrefix = "\x30\x2a\x30\x05\x06\x03\x2b\x65\x70\x03\x21\x00";

				if ($rawPk && strlen($rawPk) === 44 && substr($rawPk,0, strlen($okpPrefix)) === $okpPrefix)
				{
					$publicKeyXCurve = substr($rawPk, strlen($okpPrefix));

					return sodium_crypto_sign_verify_detached($signature, $dataToVerify, $publicKeyXCurve);
				}
			}
		}

		// verify with openSSL
		$publicKey = openssl_pkey_get_public($credentialPublicKey);
		if ($publicKey === FALSE)
		{
			$this->_error = 'public key invalid';
			return FALSE;
		}

		return openssl_verify($dataToVerify, $signature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
	}

	/**
	 * Return the certificate issuer as string
	 * @return string
	 */
	public function getCertificateIssuer($pem)
	{
		$issuer = '';

		if ($pem)
		{
			$certInfo = openssl_x509_parse($pem, TRUE);

			if (is_array($certInfo) && array_key_exists('issuer', $certInfo) && is_array($certInfo['issuer']))
			{
				$cn = isset($certInfo['issuer']['CN']) ? $certInfo['issuer']['CN'] : '';
				$o = isset($certInfo['issuer']['O']) ? $certInfo['issuer']['O'] : '';
				$ou = isset($certInfo['issuer']['OU']) ? $certInfo['issuer']['OU'] : '';

				if ($cn)
				{
					$issuer .= $cn;
				}

				if ($issuer && ($o || $ou))
				{
					$issuer .= ' (' . trim($o . ' ' . $ou) . ')';
				}
				else
				{
					$issuer .= trim($o . ' ' . $ou);
				}
			}
		}

		return $issuer;
	}

	/**
	 * Return the certificate subject as string
	 * @return string
	 */
	public function getCertificateSubject($pem)
	{
		$subject = '';

		if ($pem)
		{
			$certInfo = openssl_x509_parse($pem, TRUE);

			if (is_array($certInfo) && array_key_exists('subject', $certInfo) && is_array($certInfo['subject']))
			{

				$cn = isset($certInfo['subject']['CN']) ? $certInfo['subject']['CN'] : '';
				$o = isset($certInfo['subject']['O']) ? $certInfo['subject']['O'] : '';
				$ou = isset($certInfo['subject']['OU']) ? $certInfo['subject']['OU'] : '';

				if ($cn)
				{
					$subject .= $cn;
				}

				if ($subject && ($o || $ou))
				{
					$subject .= ' (' . trim($o . ' ' . $ou) . ')';
				}
				else
				{
					$subject .= trim($o . ' ' . $ou);
				}
			}
		}

		return $subject;
	}
}