<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * WebAuthn Android Key Attestation Format
 *
 * Format: https://www.w3.org/TR/webauthn-2/#sctn-android-key-attestation
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Webauthn_Format_AndroidKey extends Core_Webauthn_Format
{
	/**
	 * Get certificate PEM
	 * @return string|null
	 */
	protected function _doGetCertificatePem()
	{
		$x5c = $this->_getX5cCertificate(0);
		return $x5c ? $this->_createCertificatePem($x5c) : NULL;
	}

	/**
	 * Get public key for verification
	 * @return false|OpenSSLAsymmetricKey
     */
	protected function _doGetPublicKey()
	{
		$x5c = $this->_getX5cCertificate(0);
		if (!$x5c)
		{
			throw new Core_Exception('Android Key: x5c not set');
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
		// Verify certificate extensions first
		$this->_verifyCertificateExtensions();

		$pem = $this->getCertificatePem();
		if (!$this->_verifyCertificatePurpose($pem, 'serverAuth'))
		{
			throw new Core_Exception('Android Key: invalid certificate purpose');
		}
		
		if (!$this->_verifyCertificateValidity($pem))
		{
			throw new Core_Exception('Android Key: certificate expired or not yet valid');
		}

		$authData = $this->_getAuthenticatorDataBinary();
		$dataToVerify = $authData . $clientDataHash;

		return $this->_verifySignature($dataToVerify);
	}

	/**
	 * Verify Android Key certificate extensions
	 * @throws Core_Exception
	 */
	protected function _verifyCertificateExtensions()
	{
		$x5c = $this->_getX5cCertificate(0);
		if (!$x5c)
		{
			throw new Core_Exception('Android Key: no certificate available');
		}

		$pem = $this->_createCertificatePem($x5c);
		$certInfo = openssl_x509_parse($pem, FALSE);

		if (!is_array($certInfo)) {
			throw new Core_Exception('Android Key: failed to parse certificate');
		}

		// Check for Android Key attestation extension
		if (!isset($certInfo['extensions']['1.3.6.1.4.1.11129.2.1.17'])) {
			throw new Core_Exception('Android Key: missing attestation extension');
		}
	}

	/**
	 * Validates the certificate against root certificates
	 * @param array $rootCas
	 * @return boolean
	 */
	public function validateRootCertificate($rootCas)
	{
		// Check for Google Hardware Attestation Root
		$googleRootFound = FALSE;
		foreach ($rootCas as $rootCa)
		{
			if (strpos($rootCa, 'Google Hardware Attestation Root') !== FALSE)
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