<?php

namespace App\Tests\Unit;

use App\Exception\DataDecryptionException;
use App\Service\ItemTransformer;
use App\Entity\{Item, User};
use App\Service\DataEncryption;
use PHPUnit\Framework\TestCase;

class ItemTransformerTest extends TestCase
{
    /**
     * @var DataEncryption
     */
    private $itemTransformer;

    public function setUp(): void
    {
        $this->itemTransformer = new ItemTransformer(new DataEncryption());
    }

    public function testTransformWithValidData(): void
    {
        $id = 123;
        $data = 'Some text';
        $createdAt = new \DateTime('2021-01-01');
        $updatedAt = new \DateTime();

        /** @var User */
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(rand(1, 99999));

        $item = $this->createPartialMock(Item::class, ['getId']);
        $item->method('getId')->willReturn($id);
        $item
            ->setUser($user)
            ->setData($data)
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($updatedAt)
        ;

        $dataEncryption = new DataEncryption();
        $encryptedData = $dataEncryption->encryptData($item);
        $item->setData($encryptedData);

        $transformedData = $this->itemTransformer->transformToArray($item);
        $this->assertEquals([
            'id' => $id,
            'data' => $data,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ], $transformedData);
    }

    public function testTransformWithInvalidData(): void
    {
        $id = 123;
        $data = 'Some text';
        $createdAt = new \DateTime('2021-01-01');
        $updatedAt = new \DateTime();

        /** @var User */
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(rand(1, 99999));

        $item = $this->createPartialMock(Item::class, ['getId']);
        $item->method('getId')->willReturn($id);
        $item
            ->setUser($user)
            ->setData($data)
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($updatedAt)
        ;

        $transformedData = $this->itemTransformer->transformToArray($item);
        $this->assertEquals([
            'id' => $id,
            'data' => null,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ], $transformedData);
    }
}
