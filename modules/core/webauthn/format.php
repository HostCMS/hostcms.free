<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Abstract Web_Authns format
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
abstract class Core_Webauthn_Format
{
	/**
	 * Attestation Object
	 * @var array
	 */
	protected $_attestationObject = NULL;

	/**
	 * Authenticator Data
	 * @var Core_Webauthn_Authenticator_Data
	 */
	protected $_authenticatorData = NULL;

	/**
	 * x5c chain
	 * @var array
	 */
	protected $_x5c_chain = array();

	/**
	 * x5c tempFile
	 * @var string|null
	 */
	protected $_x5c_tempFile = NULL;

	/**
	 * Cached certificate PEM
	 * @var string|null
	 */
	protected $_certificatePem = NULL;

	/**
	 * Cached public key resource
	 * @var resource|null
	 */
	protected $_publicKey = NULL;

	/**
	 * Constructor
	 * @param array $attestationObject
	 * @param Core_Webauthn_Authenticator_Data $authenticatorData
	 */
	public function __construct($attestationObject, Core_Webauthn_Authenticator_Data $authenticatorData)
	{
		$this->_attestationObject = $attestationObject;
		$this->_authenticatorData = $authenticatorData;

		// Инициализируем x5c chain если есть
		$this->_initX5cChain();
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		$this->_cleanupTempFiles();
	}

	/**
	 * Initialize x5c chain from attestation object
	 */
	protected function _initX5cChain()
	{
		if (isset($this->_attestationObject['attStmt']['x5c'])
			&& is_array($this->_attestationObject['attStmt']['x5c'])
			&& count($this->_attestationObject['attStmt']['x5c']) > 0
		)
		{
			$this->_x5c_chain = $this->_attestationObject['attStmt']['x5c'];
			$this->_createX5cChainFile();
		}
	}

	/**
	 * Clean up temporary files
	 */
	protected function _cleanupTempFiles()
	{
		if ($this->_x5c_tempFile && Core_File::isFile($this->_x5c_tempFile))
		{
			Core_File::delete($this->_x5c_tempFile);
		}
	}

	/**
	 * Get certificate from x5c chain
	 * @param int $index
	 * @return string|null
	 */
	protected function _getX5cCertificate($index = 0)
	{
		if (empty($this->_x5c_chain) || !isset($this->_x5c_chain[$index]))
		{
			return NULL;
		}

		$x5c = $this->_x5c_chain[$index];
		return $x5c instanceof Core_Bytebuffer
			? $x5c->getBinaryString()
			: $x5c;
	}

	/**
	 * Returns the certificate chain in PEM format
	 * @return string|NULL
	 */
	public function getCertificateChain()
	{
		if ($this->_x5c_tempFile && Core_File::isFile($this->_x5c_tempFile))
		{
			return Core_File::read($this->_x5c_tempFile);
		}
		return NULL;
	}

	/**
	 * Get certificate in PEM format (with caching)
	 * @return string|null
	 */
	public function getCertificatePem()
	{
		if (is_null($this->_certificatePem))
		{
			$this->_certificatePem = $this->_doGetCertificatePem();
		}
		return $this->_certificatePem;
	}

	/**
	 * Actual implementation of getting certificate PEM
	 * @return string|null
	 */
	abstract protected function _doGetCertificatePem();

	/**
	 * Get public key for verification (with caching)
	 * @return resource|false|null
	 */
	protected function _getPublicKey()
	{
		if (is_null($this->_publicKey))
		{
			$this->_publicKey = $this->_doGetPublicKey();
		}
		return $this->_publicKey;
	}

	/**
	 * Actual implementation of getting public key
	 * @return resource|false|null
	 */
	abstract protected function _doGetPublicKey();

	/**
	 * Checks validity of the signature
	 * @param string $clientDataHash
	 * @return bool
	 */
	abstract public function validateAttestation($clientDataHash);

	/**
	 * Validates the certificate against root certificates
	 * @param array $rootCas
	 * @return boolean
	 */
	abstract public function validateRootCertificate($rootCas);

	/**
	 * Create a PEM encoded certificate with X.509 binary data
	 * @param string $der
	 * @return string
	 */
	protected function _createCertificatePem($der)
	{
		return "-----BEGIN CERTIFICATE-----\n" .
			chunk_split(base64_encode($der), 64, "\n") .
			"-----END CERTIFICATE-----\n";
	}

	/**
	 * Creates a PEM encoded chain file
	 * @return string|NULL
	 */
	protected function _createX5cChainFile()
	{
		$content = '';

		foreach ($this->_x5c_chain as $x5c)
		{
			$der = $x5c instanceof Core_Bytebuffer ? $x5c->getBinaryString() : $x5c;
			$pem = $this->_createCertificatePem($der);

			if (!$this->_isSelfSignedCertificate($pem))
			{
				$content .= "\n" . $pem . "\n";
			}
		}

		if ($content)
		{
			$this->_x5c_tempFile = tempnam(CMS_FOLDER . TMP_DIR, 'x5c_');
			if (Core_File::write($this->_x5c_tempFile, $content))
			{
				return $this->_x5c_tempFile;
			}
		}

		return NULL;
	}

	/**
	 * Check if certificate is self-signed
	 * @param string $pem
	 * @return bool
	 */
	protected function _isSelfSignedCertificate($pem)
	{
		$certInfo = openssl_x509_parse($pem, TRUE);

		if (!is_array($certInfo) || !is_array($certInfo['issuer']) || !is_array($certInfo['subject']))
		{
			return FALSE;
		}
		
		// Сравнение issuer и subject
		$issuerCn = isset($certInfo['issuer']['CN']) ? $certInfo['issuer']['CN'] : '';
		$subjectCn = isset($certInfo['subject']['CN']) ? $certInfo['subject']['CN'] : '';
		
		if ($issuerCn && $subjectCn && $issuerCn === $subjectCn)
		{
			return TRUE;
		}

		$subjectKeyId = isset($certInfo['extensions']['subjectKeyIdentifier']) ? $certInfo['extensions']['subjectKeyIdentifier'] : NULL;
		$authorityKeyId = isset($certInfo['extensions']['authorityKeyIdentifier']) ? $certInfo['extensions']['authorityKeyIdentifier'] : NULL;

		// Clean key identifiers
		$subjectKeyId = $this->_cleanKeyIdentifier($subjectKeyId);
		$authorityKeyId = $this->_cleanKeyIdentifier($authorityKeyId);

		// Self-signed if no authority key or matches subject key
		return ($subjectKeyId && !$authorityKeyId) || ($authorityKeyId && $authorityKeyId === $subjectKeyId);
	}

	/**
	 * Clean key identifier from prefix
	 * @param string|null $keyId
	 * @return string|null
	 */
	protected function _cleanKeyIdentifier($keyId)
	{
		if (is_null($keyId))
		{
			return NULL;
		}

		if (strpos($keyId, 'keyid:') === 0)
		{
			return substr($keyId, 6);
		}

		return $keyId;
	}

	/**
	 * Get authenticator data binary
	 * @return string
	 */
	protected function _getAuthenticatorDataBinary()
	{
		$authData = $this->_authenticatorData->getBinary();
		return $authData instanceof Core_Bytebuffer
			? $authData->getBinaryString()
			: $authData;
	}

	/**
	 * Get signature from attestation statement
	 * @return string
	 */
	protected function _getSignature()
	{
		if (!isset($this->_attestationObject['attStmt']['sig']))
		{
			throw new Core_Exception(get_class($this) . ': sig not set');
		}

		$signature = $this->_attestationObject['attStmt']['sig'];
		return $signature instanceof Core_Bytebuffer
			? $signature->getBinaryString()
			: $signature;
	}

	/**
	 * Get algorithm from attestation statement
	 * @return stdClass
	 */
	protected function _getAlgorithm()
	{
		if (!isset($this->_attestationObject['attStmt']['alg']))
		{
			throw new Core_Exception(get_class($this) . ': alg not set');
		}

		$coseAlgorithm = $this->_getCoseAlgorithm($this->_attestationObject['attStmt']['alg']);
		if (is_null($coseAlgorithm))
		{
			throw new Core_Exception(get_class($this) . ': unknown algorithm: ' . $this->_attestationObject['attStmt']['alg']);
		}

		return $coseAlgorithm;
	}

	/**
	 * Verify signature with public key
	 * @param string $data
	 * @param string $signature
	 * @param resource $publicKey
	 * @param int $algorithm
	 * @return bool
	 */
	protected function _verifySignatureWithKey($data, $signature, $publicKey, $algorithm)
	{
		return openssl_verify($data, $signature, $publicKey, $algorithm) === 1;
	}

	/**
	 * Verify signature using current public key
	 * @param string $dataToVerify
	 * @return bool
	 */
	protected function _verifySignature($dataToVerify)
	{
		$signature = $this->_getSignature();
		$algorithm = $this->_getAlgorithm();
		$publicKey = $this->_getPublicKey();

		if (!$publicKey)
		{
			return FALSE;
		}

		return $this->_verifySignatureWithKey($dataToVerify, $signature, $publicKey, $algorithm->openssl);
	}

	/**
	 * Validate certificate against root CAs using openssl
	 * @param array $rootCas
	 * @return bool
	 */
	protected function _validateCertificateWithRootCas($rootCas)
	{
		if (empty($this->_x5c_chain) || !$this->_x5c_tempFile)
		{
			return FALSE;
		}

		$certificatePem = $this->getCertificatePem();
		if (!$certificatePem)
		{
			return FALSE;
		}

		foreach ($rootCas as $rootCa)
		{
			if (@openssl_x509_checkpurpose($certificatePem, X509_PURPOSE_SSL_CLIENT, [$rootCa]))
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Verify certificate has correct extendedKeyUsage for WebAuthn
	 * @param string $pem Certificate in PEM format
	 * @param string $purpose
	 * @return bool
	 */
	protected function _verifyCertificatePurpose($pem, $purpose = 'clientAuth')
	{
		if (empty($pem))
		{
			return FALSE;
		}

		$certInfo = openssl_x509_parse($pem, TRUE);
		if (!is_array($certInfo))
		{
			return FALSE;
		}

		// Если нет расширения extendedKeyUsage, проверяем по ключевым用途ам
		if (!isset($certInfo['extensions']['extendedKeyUsage'])) {
			// Для FIDO U2F и некоторых старых ключей может не быть этого расширения
			// Проверяем по subject и issuer
			return $this->_isLegacyFidoCertificate($certInfo);
		}

		$extKeyUsage = $certInfo['extensions']['extendedKeyUsage'];

		// Допустимые OID для WebAuthn/FIDO
		$allowedPurposes = [
			'clientAuth',                    // id-kp-clientAuth
			'1.3.6.1.5.5.7.3.2',           // id-kp-clientAuth (OID)
			'2.5.29.37.0',                 // anyExtendedKeyUsage
			'1.3.6.1.4.1.45724.2.1.1',     // FIDO U2F
		];

		// Для Android SafetyNet и Android Key
		if (strpos(get_class($this), 'Android') !== FALSE && $purpose === 'serverAuth')
		{
			$allowedPurposes[] = 'serverAuth';
			$allowedPurposes[] = '1.3.6.1.5.5.7.3.1'; // id-kp-serverAuth
		}

		// Для Apple
		if (strpos(get_class($this), 'Apple') !== FALSE && $purpose === 'appleWebAuthn')
		{
			$allowedPurposes[] = 'appleWebAuthn';
		}

		foreach ($allowedPurposes as $allowed)
		{
			if (strpos($extKeyUsage, $allowed) !== FALSE)
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Check if legacy FIDO U2F certificate
	 * @param array $certInfo
	 * @return bool
	 */
	protected function _isLegacyFidoCertificate($certInfo)
	{
		// Проверяем по issuer для старых FIDO ключей
		if (isset($certInfo['issuer']['O']))
		{
			$issuerOrg = $certInfo['issuer']['O'];
			$fidoIssuers = ['Yubico', 'Feitian', 'Google', 'NXP', 'Infineon'];
			foreach ($fidoIssuers as $issuer)
			{
				if (strpos($issuerOrg, $issuer) !== FALSE)
				{
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	/**
	 * Verify certificate validity period
	 * @param string $pem
	 * @return bool
	 */
	protected function _verifyCertificateValidity($pem)
	{
		if (empty($pem))
			{
			return FALSE;
		}

		$certInfo = openssl_x509_parse($pem, FALSE);
		if (!is_array($certInfo))
		{
			return FALSE;
		}

		$now = time();
		return ($certInfo['validTo_time_t'] > $now && $certInfo['validFrom_time_t'] < $now);
	}

	/**
	 * Returns the name and openssl key for provided cose number.
	 * @param int $coseNumber
	 * @return stdClass|NULL
	 */
	protected function _getCoseAlgorithm($coseNumber)
	{
		static $coseAlgorithms = NULL;

		if (is_null($coseAlgorithms))
		{
			$coseAlgorithms = array(
				array('hash' => 'SHA1',   'openssl' => OPENSSL_ALGO_SHA1,   'cose' => array(-65535)),
				array('hash' => 'SHA256', 'openssl' => OPENSSL_ALGO_SHA256, 'cose' => array(-257, -37, -7, 5)),
				array('hash' => 'SHA384', 'openssl' => OPENSSL_ALGO_SHA384, 'cose' => array(-258, -38, -35, 6)),
				array('hash' => 'SHA512', 'openssl' => OPENSSL_ALGO_SHA512, 'cose' => array(-259, -39, -36, 7))
			);
		}

		foreach ($coseAlgorithms as $coseAlgorithm)
		{
			if (in_array($coseNumber, $coseAlgorithm['cose'], TRUE))
			{
				$return = new stdClass();
				$return->hash = $coseAlgorithm['hash'];
				$return->openssl = $coseAlgorithm['openssl'];
				return $return;
			}
		}

		return NULL;
	}

	/**
	 * Get credential public key from authenticator data
	 * @return false|OpenSSLAsymmetricKey|null
     */
	protected function _getCredentialPublicKey()
	{
		$publicKeyPem = $this->_authenticatorData->getPublicKeyPem();
		return $publicKeyPem ? openssl_pkey_get_public($publicKeyPem) : NULL;
	}
}