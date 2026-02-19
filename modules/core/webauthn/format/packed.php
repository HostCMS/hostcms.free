<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * WebAuthn Packed Attestation Format
 * Format: https://www.w3.org/TR/webauthn-2/#sctn-packed-attestation
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Webauthn_Format_Packed extends Core_Webauthn_Format
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
	 * @return resource|false|null
	 */
	protected function _doGetPublicKey()
	{
		// Case 1: x5c certificate chain present
		$x5c = $this->_getX5cCertificate(0);
		if ($x5c)
		{
			$pem = $this->_createCertificatePem($x5c);
			return openssl_pkey_get_public($pem);
		}

		// Case 2: Self attestation - ECDAA not supported
		if (isset($this->_attestationObject['attStmt']['ecdaaKeyId']))
		{
			throw new Core_Exception('Packed attestation: ECDAA not supported');
		}

		// Case 3: Self attestation - use credential public key
		return $this->_getCredentialPublicKey();
	}

	/**
	 * Validate attestation signature
	 * @param string $clientDataHash
	 * @return bool
	 */
	public function validateAttestation($clientDataHash)
	{
		$x5c = $this->_getX5cCertificate(0);
		if ($x5c)
		{
			$pem = $this->getCertificatePem();
			if (!$this->_verifyCertificatePurpose($pem, 'clientAuth'))
			{
				throw new Core_Exception('Packed: invalid certificate purpose');
			}
			
			if (!$this->_verifyCertificateValidity($pem))
			{
				throw new Core_Exception('Packed: certificate expired or not yet valid');
			}
		}

		$authData = $this->_getAuthenticatorDataBinary();
		$dataToVerify = $authData . $clientDataHash;

		return $this->_verifySignature($dataToVerify);
	}

	/**
	 * Validates the certificate against root certificates
	 * @param array $rootCas
	 * @return boolean
	 */
	public function validateRootCertificate($rootCas)
	{
		return $this->_validateCertificateWithRootCas($rootCas);
	}
}