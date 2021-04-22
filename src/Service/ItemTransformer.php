<?php

namespace App\Service;

use App\Entity\Item;
use App\Exception\DataDecryptionException;

class ItemTransformer
{
    /**
     * @var DataEncryption
     */
    private $dataEncryption;

    /**
     * ItemTransformer constructor.
     * @param DataEncryption $dataEncryption
     */
    public function __construct(DataEncryption $dataEncryption)
    {
        $this->dataEncryption = $dataEncryption;
    }

    /**
     * Transforms item to array
     * @param Item $item
     * @return array
     */
    public function transformToArray(Item $item): array
    {
        try {
            $data = $this->dataEncryption->decryptData($item);
        } catch (DataDecryptionException $e) {
            $data = null;
        }
        return [
            'id' => $item->getId(),
            'data' => $data,
            'created_at' => $item->getCreatedAt(),
            'updated_at' => $item->getUpdatedAt(),
        ];
    }
}