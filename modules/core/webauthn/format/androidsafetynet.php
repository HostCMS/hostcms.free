<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * WebAuthn Android SafetyNet Attestation Format
 *
 * Format: https://www.w3.org/TR/webauthn-2/#sctn-android-safetynet-attestation
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Webauthn_Format_AndroidSafetyNet extends Core_Webauthn_Format
{
	/**
	 * JWT payload
	 * @var object|null
	 */
	protected $_jwtPayload = NULL;

	/**
	 * JWT header
	 * @var object|null
	 */
	protected $_jwtHeader = NULL;

	/**
	 * JWT signature
	 * @var string|null
	 */
	protected $_jwtSignature = NULL;

	/**
	 * JWT data
	 * @var string|null
	 */
	protected $_jwtData = NULL;

	/**
	 * Initialize from JWT
	 */
	protected function _initJwt()
	{
		if (!isset($this->_attestationObject['attStmt']['response']))
		{
			throw new Core_Exception('Android SafetyNet: response not set');
		}

		$response = $this->_attestationObject['attStmt']['response'];
		$response = $response instanceof Core_Bytebuffer
			? $response->getBinaryString()
			: $response;

		$jwtParts = explode('.', $response);
		if (count($jwtParts) !== 3)
		{
			throw new Core_Exception('Android SafetyNet: invalid JWT format');
		}

		list($headerB64, $payloadB64, $signatureB64) = $jwtParts;

		$this->_jwtHeader = json_decode(Core_Bytebuffer::fromBase64Url($headerB64)->getBinaryString());
		$this->_jwtPayload = json_decode(Core_Bytebuffer::fromBase64Url($payloadB64)->getBinaryString());
		$this->_jwtSignature = Core_Bytebuffer::fromBase64Url($signatureB64)->getBinaryString();

		$this->_jwtData = $headerB64 . '.' . $payloadB64;

		if (!is_object($this->_jwtHeader) || !is_object($this->_jwtPayload))
		{
			throw new Core_Exception('Android SafetyNet: invalid JWT payload');
		}

		// Initialize x5c chain from JWT header
		if (property_exists($this->_jwtHeader, 'x5c') && is_array($this->_jwtHeader->x5c))
		{
			$this->_x5c_chain = array();
			foreach ($this->_jwtHeader->x5c as $x5c) {
				$this->_x5c_chain[] = Core_Bytebuffer::fromBase64Url($x5c)->getBinaryString();
			}
			$this->_createX5cChainFile();
		}
	}

	/**
	 * Get certificate PEM
	 * @return string|null
	 */
	protected function _doGetCertificatePem()
	{
		if ($this->_jwtHeader === NULL)
		{
			$this->_initJwt();
		}

		$x5c = $this->_getX5cCertificate(0);
		return $x5c ? $this->_createCertificatePem($x5c) : NULL;
	}

	/**
	 * Get public key for verification
	 * @return false|OpenSSLAsymmetricKey
     */
	protected function _doGetPublicKey()
	{
		if ($this->_jwtHeader === NULL)
		{
			$this->_initJwt();
		}

		$x5c = $this->_getX5cCertificate(0);
		if (!$x5c)
		{
			throw new Core_Exception('Android SafetyNet: x5c not set in header');
		}

		$pem = $this->_createCertificatePem($x5c);
		return openssl_pkey_get_public($pem);
	}

	/**
	 * Validate attestation signature
	 * @param string $clientDataHash
	 * @return bool
	 */
	public function validateAttestation($clientDataHash)
	{
		if ($this->_jwtPayload === NULL)
		{
			$this->_initJwt();
		}
		
		$pem = $this->getCertificatePem();
		if (!$this->_verifyCertificatePurpose($pem, 'serverAuth'))
		{
			throw new Core_Exception('Android SafetyNet: invalid certificate purpose');
		}
		
		if (!$this->_verifyCertificateValidity($pem))
		{
			throw new Core_Exception('Android SafetyNet: certificate expired or not yet valid');
		}

		// Verify nonce
		if (!property_exists($this->_jwtPayload, 'nonce'))
		{
			throw new Core_Exception('Android SafetyNet: nonce not set');
		}

		$authData = $this->_getAuthenticatorDataBinary();
		$expectedNonce = base64_encode(hash('sha256', $authData . $clientDataHash, TRUE));

		if ($this->_jwtPayload->nonce !== $expectedNonce)
		{
			throw new Core_Exception('Android SafetyNet: invalid nonce');
		}

		// Verify timestamp
		if (!property_exists($this->_jwtPayload, 'timestampMs'))
		{
			throw new Core_Exception('Android SafetyNet: timestamp not set');
		}

		$timestamp = $this->_jwtPayload->timestampMs / 1000;
		$now = time();
		$maxAge = 15;

		if ($timestamp < $now - $maxAge || $timestamp > $now + $maxAge)
		{
			throw new Core_Exception('Android SafetyNet: timestamp out of range');
		}

		// Verify ctsProfileMatch
		if (!property_exists($this->_jwtPayload, 'ctsProfileMatch'))
		{
			throw new Core_Exception('Android SafetyNet: ctsProfileMatch not set');
		}

		if ($this->_jwtPayload->ctsProfileMatch !== TRUE)
		{
			throw new Core_Exception('Android SafetyNet: device not CTS certified');
		}

		// Verify JWT signature
		$publicKey = $this->_getPublicKey();
		return openssl_verify($this->_jwtData, $this->_jwtSignature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
	}

	/**
	 * Validates the certificate against root certificates
	 * @param array $rootCas
	 * @return boolean
	 */
	public function validateRootCertificate($rootCas)
	{
		// Check for Google Trust Services Root
		$googleRootFound = FALSE;
		foreach ($rootCas as $rootCa)
		{
			if (strpos($rootCa, 'Google Trust Services') !== FALSE)
			{
				$googleRootFound = TRUE;
				break;
			}
		}

		if (!$googleRootFound)
		{
			return FALSE;
		}

		return $this->_validateCertificateWithRootCas($rootCas);
	}
}