<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * WebAuthn FIDO U2F Attestation Format
 * Format: https://www.w3.org/TR/webauthn-2/#sctn-fido-u2f-attestation
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Webauthn_Format_FidoU2f extends Core_Webauthn_Format
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
			throw new Core_Exception('FIDO U2F: x5c not set');
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
		$pem = $this->getCertificatePem();
		if (!$this->_verifyCertificatePurpose($pem, 'clientAuth'))
		{
			// Для FIDO U2F может быть legacy сертификат
			if (!$this->_isLegacyFidoCertificate(openssl_x509_parse($pem, TRUE)))
			{
				throw new Core_Exception('FIDO U2F: invalid certificate purpose');
			}
		}
		
		if (!$this->_verifyCertificateValidity($pem)) {
			throw new Core_Exception('FIDO U2F: certificate expired or not yet valid');
		}
	
		// Get credential public key components
		$coseKey = $this->_authenticatorData->getCoseKey();
		if (!isset($coseKey[-2]) || !isset($coseKey[-3]))
		{
			throw new Core_Exception('FIDO U2F: invalid COSE key');
		}
		
		$x = $coseKey[-2] instanceof Core_Bytebuffer ? $coseKey[-2]->getBinaryString() : $coseKey[-2];
		$y = $coseKey[-3] instanceof Core_Bytebuffer ? $coseKey[-3]->getBinaryString() : $coseKey[-3];
		
		// Build U2F public key: 0x04 + x + y
		$u2fPublicKey = "\x04" . $x . $y;
		
		// Get RP ID hash
		$rpIdHash = $this->_authenticatorData->getRpIdHash();
		$rpIdHash = $rpIdHash instanceof Core_Bytebuffer ? $rpIdHash->getBinaryString() : $rpIdHash;
		
		// Get credential ID
		$credentialId = $this->_authenticatorData->getCredentialId();
		$credentialId = $credentialId instanceof Core_Bytebuffer ? $credentialId->getBinaryString() : $credentialId;
		
		// Build verification data
		$dataToVerify = "\x00" . $rpIdHash . $clientDataHash . $credentialId . $u2fPublicKey;
		
		// Verify signature with certificate public key
		$signature = $this->_getSignature();
		$publicKey = $this->_getPublicKey();
		
		return openssl_verify($dataToVerify, $signature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
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