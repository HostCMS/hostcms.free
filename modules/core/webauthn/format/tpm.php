<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * WebAuthn TPM Attestation Format
 * Format: https://www.w3.org/TR/webauthn-2/#sctn-tpm-attestation
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Webauthn_Format_Tpm extends Core_Webauthn_Format
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
		if ($x5c)
		{
			$pem = $this->_createCertificatePem($x5c);
			return openssl_pkey_get_public($pem);
		}

		if (isset($this->_attestationObject['attStmt']['ecdaaKeyId'])) {
			throw new Core_Exception('TPM: ECDAA not supported');
		}

		throw new Core_Exception('TPM: no public key available');
	}

	/**
	 * Validate attestation signature
	 * @param string $clientDataHash
	 * @return bool
	 */
	public function validateAttestation($clientDataHash)
	{
		// Check TPM version
		if (!isset($this->_attestationObject['attStmt']['ver']))
		{
			throw new Core_Exception('TPM: ver not set');
		}

		if ($this->_attestationObject['attStmt']['ver'] !== '2.0')
		{
			throw new Core_Exception('TPM: only version 2.0 supported');
		}

		// Get certInfo
		if (!isset($this->_attestationObject['attStmt']['certInfo']))
		{
			throw new Core_Exception('TPM: certInfo not set');
		}

		$certInfo = $this->_attestationObject['attStmt']['certInfo'];
		$certInfo = $certInfo instanceof Core_Bytebuffer ? $certInfo->getBinaryString() : $certInfo;

		// Verify certInfo magic number
		if (substr($certInfo, 0, 4) !== "\xff\x54\x43\x47")
		{
			throw new Core_Exception('TPM: invalid magic number');
		}

		// Verify certInfo type
		if (substr($certInfo, 4, 2) !== "\x01\x00")
		{
			throw new Core_Exception('TPM: invalid attestation type');
		}

		// Verify certInfo extraData
		$extraData = substr($certInfo, 6, 32);
		$authData = $this->_getAuthenticatorDataBinary();
		$expectedExtraData = hash('sha256', $authData . $clientDataHash, TRUE);

		if ($extraData !== $expectedExtraData)
		{
			throw new Core_Exception('TPM: invalid extraData');
		}

		// Verify certInfo signature
		if (!$this->_verifySignature($certInfo))
		{
			throw new Core_Exception('TPM: invalid certInfo signature');
		}

		// Verify algorithm matches
		$coseKey = $this->_authenticatorData->getCoseKey();
		if (!$coseKey || !isset($coseKey[3]))
		{
			throw new Core_Exception('TPM: no COSE key available');
		}

		if ($coseKey[3] !== $this->_attestationObject['attStmt']['alg'])
		{
			throw new Core_Exception('TPM: algorithm mismatch');
		}
		
		$x5c = $this->_getX5cCertificate(0);
		if ($x5c)
		{
			$pem = $this->getCertificatePem();
			if (!$this->_verifyCertificatePurpose($pem, 'clientAuth'))
			{
				throw new Core_Exception('TPM: invalid certificate purpose');
			}
			
			if (!$this->_verifyCertificateValidity($pem))
			{
				throw new Core_Exception('TPM: certificate expired or not yet valid');
			}
		}

		return TRUE;
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