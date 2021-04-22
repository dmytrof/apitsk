<?php

namespace App\Tests\Unit;

use App\Exception\DataDecryptionException;
use App\Entity\{Item, User};
use App\Service\DataEncryption;
use PHPUnit\Framework\TestCase;

class DataEncryptionTest extends TestCase
{
    /**
     * @var DataEncryption
     */
    private $dataEncryption;

    public function setUp(): void
    {
        $this->dataEncryption = new DataEncryption();
    }

    /**
     * @dataProvider dataProviderToTestEncryptDecrypt
     */
    public function testEncryptDecrypt($data): void
    {
        /** @var User */
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(rand(1, 99999));

        $item = new Item();
        $item
            ->setUser($user)
            ->setData($data)
        ;

        $encryptedData = $this->dataEncryption->encryptData($item);
        $item->setData($encryptedData);

        $decryptedData = $this->dataEncryption->decryptData($item);

        $this->assertEquals($data, $decryptedData);
    }

    /**
     * @return array[]
     */
    public function dataProviderToTestEncryptDecrypt(): array
    {
        return [
            [
                'Some text',
            ],
            [
                '5.75',
            ],
            [
                str_repeat('Very long text', 999),
            ]
        ];
    }

    public function testDecryptException(): void
    {
        /** @var User */
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(rand(1,99999));

        $item = new Item();
        $item
            ->setUser($user)
            ->setData('some text')
        ;

        $encryptedData = $this->dataEncryption->encryptData($item);
        $item->setData(substr($encryptedData, 1));

        $this->expectException(DataDecryptionException::class);
        $this->dataEncryption->decryptData($item);
    }
}
