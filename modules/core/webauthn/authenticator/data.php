<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core_Webauthn_Authenticator_Data
 *
 * Modified version of https://github.com/lbuchs/WebAuthn/blob/master/src/Attestation/AuthenticatorData.php
 * Copyright © 2024 Lukas Buchs - MIT licensed
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Webauthn_Authenticator_Data
{
	/**
	 * Binary data
	 * @var mixed
	 */
	protected $_binary = NULL;

	/**
	 * Hash
	 * @var mixed
	 */
	protected $_rpIdHash = NULL;

	/**
	 * Flags
	 * @var mixed
	 */
	protected $_flags = NULL;

	/**
	 * Sign count
	 * @var mixed
	 */
	protected $_signCount = 0;

	/**
	 * AAGUID
	 * @var Core_Bytebuffer|null
	 */
	protected $_AAGUID = NULL;

	/**
	 * Credential ID length
	 * @var int
	 */
	protected $_credentialIdLength = 0;

	/**
	 * Credential ID
	 * @var Core_Bytebuffer|null
	 */
	protected $_credentialId = NULL;

	/**
	 * Credential public key (COSE)
	 * @var array|null
	 */
	protected $_credentialPublicKey = NULL;

	/**
	 * Credential public key PEM
	 * @var string|null
	 */
	protected $_credentialPublicKeyPem = NULL;

	/**
	 * Extensions
	 * @var array
	 */
	protected $_extensions = array();

	/**
	 * Constructor
	 * @param string $binary
	 */
	public function __construct($binary)
	{
		$this->_binary = $binary instanceof Core_Bytebuffer
			? $binary->getBinaryString()
			: $binary;

		$this->_parse();
	}

	/**
	 * Parse authenticator data
	 * @throws Core_Exception
	 */
	protected function _parse()
	{
		$offset = 0;
		$binary = $this->_binary;

		// 1. RP ID Hash (32 bytes)
		$this->_rpIdHash = substr($binary, $offset, 32);
		$offset += 32;

		// 2. Flags (1 byte)
		$this->_flags = substr($binary, $offset, 1);
		$offset += 1;

		// 3. Sign Count (4 bytes)
		$this->_signCount = unpack('N', substr($binary, $offset, 4))[1];
		$offset += 4;

		// 4. Check if attested credential data is present
		if ($this->hasAttestedCredentialData())
		{
			// AAGUID (16 bytes)
			$aaguid = substr($binary, $offset, 16);
			$this->_AAGUID = new Core_Bytebuffer($aaguid);
			$offset += 16;

			// Credential ID Length (2 bytes)
			$credentialIdLength = unpack('n', substr($binary, $offset, 2))[1];

			// Максимальный разумный размер
			if ($credentialIdLength > 1024)
			{
				throw new Core_Exception('Credential ID too large');
			}

			$this->_credentialIdLength = $credentialIdLength;
			$offset += 2;

			// Credential ID
			$this->_credentialId = substr($binary, $offset, $credentialIdLength);
			$offset += $credentialIdLength;

			// Credential Public Key (COSE)
			$cbor = new Core_Cbor();
			$this->_credentialPublicKey = $cbor->decode(substr($binary, $offset));

			// Convert COSE to PEM
			$this->_credentialPublicKeyPem = $this->_coseToPem($this->_credentialPublicKey);
		}

		// 5. Check if extensions are present
		if ($this->hasExtensions()) {
			$cbor = new Core_Cbor();
			$this->_extensions = $cbor->decode(substr($binary, $offset));
		}
	}

	/**
	 * Returns the authenticatorData as binary
	 * @return string
	 */
	public function getBinary()
	{
		return $this->_binary;
	}

	/**
	 * Get RP ID hash
	 * @return string
	 */
	public function getRpIdHash()
	{
		return $this->_rpIdHash;
	}

	/**
	 * Get flags byte
	 * @return string
	 */
	public function getFlags()
	{
		return $this->_flags;
	}

	/**
	 * Get flags as integer
	 * @return int
	 */
	public function getFlagsInt()
	{
		return ord($this->_flags);
	}

	/**
	 * Check if User Present bit is set
	 * @return bool
	 */
	public function getUserPresent()
	{
		return (ord($this->_flags) & 1) === 1;
	}

	/**
	 * Check if User Verified bit is set
	 * @return bool
	 */
	public function getUserVerified()
	{
		return (ord($this->_flags) & 4) === 4;
	}

	/**
	 * Check if attested credential data is present
	 * @return bool
	 */
	public function hasAttestedCredentialData()
	{
		return (ord($this->_flags) & 64) === 64;
	}

	/**
	 * Check if extensions are present
	 * @return bool
	 */
	public function hasExtensions()
	{
		return (ord($this->_flags) & 128) === 128;
	}

	/**
	 * Get sign count
	 * @return int
	 */
	public function getSignCount()
	{
		return $this->_signCount;
	}

	/**
	 * Get AAGUID
	 * @return Core_Bytebuffer|null
	 */
	public function getAAGUID()
	{
		return $this->_AAGUID;
	}

	/**
	 * Get credential ID
	 * @return Core_Bytebuffer|null
	 */
	public function getCredentialId()
	{
		return $this->_credentialId;
	}

	/**
	 * Get credential public key (COSE)
	 * @return array|null
	 */
	public function getCoseKey()
	{
		return $this->_credentialPublicKey;
	}

	/**
	 * Get credential public key in PEM format
	 * @return string|null
	 */
	public function getPublicKeyPem()
	{
		return $this->_credentialPublicKeyPem;
	}

	/**
	 * Get extensions
	 * @return array
	 */
	public function getExtensions()
	{
		return $this->_extensions;
	}

	/**
	 * Check if backup eligible bit is set
	 * @return bool
	 */
	public function getIsBackupEligible()
	{
		return (ord($this->_flags) & 8) === 8;
	}

	/**
	 * Check if backed up bit is set
	 * @return bool
	 */
	public function getIsBackup()
	{
		return (ord($this->_flags) & 16) === 16;
	}

	/**
	 * Get user handle from extensions
	 * @return Core_Bytebuffer|null
	 */
	public function getUserHandle()
	{
		return isset($this->_extensions['userHandle'])
			? new Core_Bytebuffer($this->_extensions['userHandle'])
			: NULL;
	}

	/**
	 * Convert COSE key to PEM format
	 * @param array $coseKey
	 * @return string|null
	 * @throws Core_Exception
	 */
	protected function _coseToPem($coseKey)
	{
		if (!is_array($coseKey))
		{
			return NULL;
		}

		// COSE key structure:
		// 1: kty, 2: kid, 3: alg, -1: crv, -2: x, -3: y, -4: d (private), -5: n, -6: e

		$kty = isset($coseKey[1]) ? $coseKey[1] : NULL;
		//$alg = isset($coseKey[3]) ? $coseKey[3] : NULL;

		if (!$kty)
		{
			throw new Core_Exception('COSE key: missing kty');
		}

		switch ($kty)
		{
			case 2: // EC2 key
				return $this->_ec2ToPem($coseKey);

			case 3: // RSA key
				return $this->_rsaToPem($coseKey);

			case 1: // Octet key (OKP) - Ed25519
				return $this->_okpToPem($coseKey);

			default:
				throw new Core_Exception('COSE key: unsupported kty: ' . $kty);
		}
	}

	/**
	 * Convert EC2 COSE key to PEM
	 * @param array $coseKey
	 * @return string
	 * @throws Core_Exception
	 */
	protected function _ec2ToPem($coseKey)
	{
		$crv = isset($coseKey[-1]) ? $coseKey[-1] : NULL;
		$x = isset($coseKey[-2]) ? $coseKey[-2] : NULL;
		$y = isset($coseKey[-3]) ? $coseKey[-3] : NULL;

		if (!$crv || !$x || !$y) {
			throw new Core_Exception('EC2 key: missing required fields');
		}

		// Convert to binary
		$x = $x instanceof Core_Bytebuffer ? $x->getBinaryString() : $x;
		$y = $y instanceof Core_Bytebuffer ? $y->getBinaryString() : $y;

		// Determine curve
		switch ($crv) {
			case 1: // P-256
			case 2: // P-384
			case 3: // P-521
				return $this->_buildEcPem($x, $y, $crv);

			default:
				throw new Core_Exception('EC2 key: unsupported curve: ' . $crv);
		}
	}

	/**
	 * Build EC public key PEM
	 * @param string $x
	 * @param string $y
	 * @param int $crv
	 * @return string
	 */
	protected function _buildEcPem($x, $y, $crv)
	{
		// Uncompressed format: 0x04 + x + y
		$uncompressed = "\x04" . $x . $y;

		// Build DER sequence
		$der = "\x30\x59\x30\x13\x06\x07\x2a\x86\x48\xce\x3d\x02\x01\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07\x03\x42\x00" . $uncompressed;

		return "-----BEGIN PUBLIC KEY-----\n" .
			   chunk_split(base64_encode($der), 64, "\n") .
			   "-----END PUBLIC KEY-----\n";
	}

	/**
	 * Convert RSA COSE key to PEM
	 * @param array $coseKey
	 * @return string
	 * @throws Core_Exception
	 */
	protected function _rsaToPem($coseKey)
	{
		$n = isset($coseKey[-1]) ? $coseKey[-1] : NULL; // modulus
		$e = isset($coseKey[-2]) ? $coseKey[-2] : NULL; // exponent

		if (!$n || !$e)
		{
			throw new Core_Exception('RSA key: missing modulus or exponent');
		}

		$n = $n instanceof Core_Bytebuffer ? $n->getBinaryString() : $n;
		$e = $e instanceof Core_Bytebuffer ? $e->getBinaryString() : $e;

		// Проверяем что экспонента - 65537 (единственная поддерживаемая)
		if ($e !== "\x01\x00\x01" && $e !== "\x00\x01\x00\x01")
		{
			throw new Core_Exception('RSA key: only exponent 65537 is supported');
		}
		
		// Удаляем лишние ведущие нули из модуля
		$n = ltrim($n, "\x00");
		
		// Добавляем ведущий ноль если старший бит = 1
		if (ord($n[0]) & 0x80)
		{
			$n = "\x00" . $n;
		}
		
		// Проверяем что модуль правильной длины для RSA 2048+
		if (strlen($n) < 256 || strlen($n) > 512)
		{
			throw new Core_Exception('RSA key: invalid modulus length');
		}

		 // DER структура для RSA с e=65537
		$der = "\x30\x82\x01\x22" .          // SEQUENCE length 290
			   "\x30\x0d" .                 // SEQUENCE length 13
			   "\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01" . // OID rsaEncryption
			   "\x05\x00" .                 // NULL
			   "\x03\x82\x01\x0f\x00" .     // BIT STRING length 271, 0 unused bits
			   "\x30\x82\x01\x0a" .         // SEQUENCE length 266
			   "\x02\x82\x01\x01\x00" . $n . // INTEGER modulus
			   "\x02\x03\x01\x00\x01";      // INTEGER exponent 65537

		return "-----BEGIN PUBLIC KEY-----\n" .
		   chunk_split(base64_encode($der), 64, "\n") .
		   "-----END PUBLIC KEY-----\n";
	}

	/**
	 * Convert OKP (Ed25519) COSE key to PEM
	 * @param array $coseKey
	 * @return string
	 * @throws Core_Exception
	 */
	protected function _okpToPem($coseKey)
	{
		$crv = isset($coseKey[-1]) ? $coseKey[-1] : NULL;
		$x = isset($coseKey[-2]) ? $coseKey[-2] : NULL;

		if (!$crv || !$x) {
			throw new Core_Exception('OKP key: missing required fields');
		}

		if ($crv !== 6) { // Ed25519
			throw new Core_Exception('OKP key: unsupported curve: ' . $crv);
		}

		$x = $x instanceof Core_Bytebuffer ? $x->getBinaryString() : $x;

		// Build DER sequence for Ed25519 public key
		$der = "\x30\x2a\x30\x05\x06\x03\x2b\x65\x70\x03\x21\x00" . $x;

		return "-----BEGIN PUBLIC KEY-----\n" .
			   chunk_split(base64_encode($der), 64, "\n") .
			   "-----END PUBLIC KEY-----\n";
	}

	/**
	 * Get algorithm from COSE key
	 * @return int|null
	 */
	public function getAlgorithm()
	{
		return isset($this->_credentialPublicKey[3]) ? $this->_credentialPublicKey[3] : NULL;
	}

	/**
	 * Get key type from COSE key
	 * @return int|null
	 */
	public function getKeyType()
	{
		return isset($this->_credentialPublicKey[1]) ? $this->_credentialPublicKey[1] : NULL;
	}

	/**
	 * Get curve from COSE key
	 * @return int|null
	 */
	public function getCurve()
	{
		return isset($this->_credentialPublicKey[-1]) ? $this->_credentialPublicKey[-1] : NULL;
	}

	/**
	 * Check if credential is discoverable (resident key)
	 * @return bool
	 */
	public function isDiscoverableCredential()
	{
		return $this->_credentialId !== NULL && $this->_AAGUID !== NULL;
	}

	/**
	 * Get diagnostic representation
	 * @return array
	 */
	public function debug()
	{
		return array(
			'rpIdHash' => bin2hex($this->_rpIdHash),
			'flags' => bin2hex($this->_flags),
			'flags_bits' => sprintf('%08b', ord($this->_flags)),
			'userPresent' => $this->getUserPresent(),
			'userVerified' => $this->getUserVerified(),
			'hasAttestedCredentialData' => $this->hasAttestedCredentialData(),
			'hasExtensions' => $this->hasExtensions(),
			'isBackupEligible' => $this->getIsBackupEligible(),
			'isBackedUp' => $this->getIsBackup(),
			'signCount' => $this->_signCount,
			'AAGUID' => $this->_AAGUID ? bin2hex($this->_AAGUID->getBinaryString()) : NULL,
			'credentialIdLength' => $this->_credentialIdLength,
			'credentialId' => $this->_credentialId ? bin2hex($this->_credentialId) : NULL,
			'algorithm' => $this->getAlgorithm(),
			'keyType' => $this->getKeyType(),
			'curve' => $this->getCurve(),
			'extensions' => $this->_extensions
		);
	}
}