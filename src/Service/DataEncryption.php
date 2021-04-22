<?php

namespace App\Service;

use App\Entity\Item;
use App\Exception\{DataDecryptionException, DataEncryptionException};

class DataEncryption
{
    private const CIPHER = 'AES-128-CBC';

    /**
     * @var int
     */
    private $ivLength;

    /**
     * Returns item key
     * @param Item $item
     * @return string
     */
    protected function getItemKey(Item $item): string
    {
        return sha1($item->getUser()->getId());
    }

    /**
     * Returns cipher
     * @return string
     */
    protected function getCipher(): string
    {
        return self::CIPHER;
    }

    /**
     * Returns iv length
     * @return int
     */
    protected function getIvLength(): int
    {
        if (null === $this->ivLength) {
            $this->ivLength = openssl_cipher_iv_length($this->getCipher());
        }

        return $this->ivLength;
    }

    /**
     * Encrypts item's data
     * @param Item $item
     * @return string
     */
    public function encryptData(Item $item): string
    {
        $key = $this->getItemKey($item);
        $iv = openssl_random_pseudo_bytes($this->getIvLength());
        $encryptedRawData = openssl_encrypt($item->getData(), $this->getCipher(), $key, OPENSSL_RAW_DATA, $iv);
        if (false === $encryptedRawData) {
            throw new DataEncryptionException('Unable to encrypt data');
        }
        $hmac = hash_hmac('sha256', $encryptedRawData, $key, true);
        $encryptedString = base64_encode($iv . $hmac . $encryptedRawData);

        return $encryptedString;
    }

    /**
     * Decrypts item's data
     * @param Item $item
     * @return string
     */
    public function decryptData(Item $item): string
    {
        $key = $this->getItemKey($item);
        $decodedEncryptedRaw = base64_decode($item->getData());
        $iv = substr($decodedEncryptedRaw, 0, $this->getIvLength());
        $hmac = substr($decodedEncryptedRaw, $this->getIvLength(), $sha2Length = 32);
        $encryptedRawData = substr($decodedEncryptedRaw, $this->getIvLength() + $sha2Length);
        if (strlen($iv) !== $this->getIvLength() || strlen($hmac) !== $sha2Length) {
            throw new DataDecryptionException('Unable to decrypt data');
        }
        $decodedData = openssl_decrypt($encryptedRawData, $this->getCipher(), $key, OPENSSL_RAW_DATA, $iv);

        if (false === $decodedData) {
            throw new DataDecryptionException('Unable to decrypt data');
        }
        $calcHmac = hash_hmac('sha256', $encryptedRawData, $key, true);
        if (!hash_equals($hmac, $calcHmac)) {
            throw new DataDecryptionException('Keyed hashes are not equals');
        }

        return $decodedData;
    }
}