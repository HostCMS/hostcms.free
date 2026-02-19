<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * WebAuthn Apple Attestation Format
 * Format: https://www.w3.org/TR/webauthn-2/#sctn-apple-anonymous-attestation
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Webauthn_Format_Apple extends Core_Webauthn_Format
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
			throw new Core_Exception('Apple: x5c not set');
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
		// Apple attestation always uses ES256
		if (!isset($this->_attestationObject['attStmt']['alg'])
			|| $this->_attestationObject['attStmt']['alg'] !== -7
		)
		{
			throw new Core_Exception('Apple: alg must be ES256 (-7)');
		}

		$pem = $this->getCertificatePem();
		if (!$this->_verifyCertificatePurpose($pem, 'appleWebAuthn'))
		{
			throw new Core_Exception('Apple: invalid certificate purpose');
		}
		
		if (!$this->_verifyCertificateValidity($pem))
		{
			throw new Core_Exception('Apple: certificate expired or not yet valid');
		}

		// Verify certificate nonce extension
		$this->_verifyNonceExtension($clientDataHash);

		$authData = $this->_getAuthenticatorDataBinary();
		$dataToVerify = $authData . $clientDataHash;

		return $this->_verifySignature($dataToVerify);
	}

	/**
	 * Verify Apple nonce extension
	 * @param string $clientDataHash
	 * @throws Core_Exception
	 */
	protected function _verifyNonceExtension($clientDataHash)
	{
		$x5c = $this->_getX5cCertificate(0);
		if (!$x5c)
		{
			throw new Core_Exception('Apple: no certificate available');
		}

		$pem = $this->_createCertificatePem($x5c);
		$certInfo = openssl_x509_parse($pem, FALSE);

		if (!is_array($certInfo))
		{
			throw new Core_Exception('Apple: failed to parse certificate');
		}

		// Check for Apple nonce extension
		if (!isset($certInfo['extensions']['1.2.840.113635.100.8.2']))
		{
			throw new Core_Exception('Apple: missing nonce extension');
		}

		$nonceExtension = $certInfo['extensions']['1.2.840.113635.100.8.2'];

		// Nonce should be SHA256 hash of authenticatorData + clientDataHash
		$authData = $this->_getAuthenticatorDataBinary();
		$expectedNonce = hash('sha256', $authData . $clientDataHash, TRUE);
		$expectedNonceHex = bin2hex($expectedNonce);

		if (strpos($nonceExtension, $expectedNonceHex) === FALSE)
		{
			$expectedNonceHexNoColons = str_replace(':', '', $expectedNonceHex);
			if (strpos(str_replace(':', '', $nonceExtension), $expectedNonceHexNoColons) === FALSE) {
				throw new Core_Exception('Apple: invalid nonce in certificate');
			}
		}
	}

	/**
	 * Validates the certificate against root certificates
	 * @param array $rootCas
	 * @return boolean
	 */
	public function validateRootCertificate($rootCas)
	{
		// Check for Apple WebAuthn Root CA
		$appleRootFound = FALSE;
		foreach ($rootCas as $rootCa)
		{
			if (strpos($rootCa, 'Apple WebAuthn Root CA') !== FALSE)
			{
				$appleRootFound = TRUE;
				break;
			}
		}

		if (!$appleRootFound)
		{
			return FALSE;
		}

		return $this->_validateCertificateWithRootCas($rootCas);
	}
}