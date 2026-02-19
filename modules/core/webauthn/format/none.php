<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * WebAuthn None Attestation Format
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Webauthn_Format_None extends Core_Webauthn_Format
{
    /**
     * Get certificate PEM
     * @return string|null
     */
    protected function _doGetCertificatePem()
    {
        return NULL;
    }

    /**
     * Get public key for verification
     * @return resource|false|null
     */
    protected function _doGetPublicKey()
    {
        return NULL;
    }

    /**
     * Validate attestation signature
     * @param string $clientDataHash
     * @return bool
     */
    public function validateAttestation($clientDataHash)
    {
	    // Убеждаемся, что для формата 'none' оператор аттестации пуст
		if (!empty($this->_attestationObject['attStmt']))
		{
			return FALSE;
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
        return FALSE;
    }
}