<?php

namespace App\Tests\Unit;

use App\Entity\{Item, User};
use App\Service\{DataEncryption, ItemService};
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\{TestCase, MockObject\MockObject};

class ItemServiceTest extends TestCase
{
    /**
     * @var EntityManagerInterface|MockObject
     */
    private $entityManager;

    /**
     * @var ItemService
     */
    private $itemService;

    /**
     * @var DataEncryption
     */
    private $dataEncryption;

    public function setUp(): void
    {
        /** @var EntityManagerInterface */
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->dataEncryption = new DataEncryption();

        $this->itemService = new ItemService($this->entityManager, $this->dataEncryption);
    }

    public function testCreate(): void
    {
        /** @var User */
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(rand(1, 99999));
        $data = 'secret data';

        $expectedObject = new Item();
        $expectedObject
            ->setUser($user)
            ->setData($data)
        ;

        $this->entityManager->expects($this->once())->method('persist')->with($expectedObject);

        $item = $this->itemService->create($user, $data);

        $this->assertEquals($data, $this->dataEncryption->decryptData($item));
    }

    public function testUpdate(): void
    {
        $itemId = 33;
        /** @var User */
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(rand(1, 99999));
        $data = 'new secret data';

        $expectedObject = new Item();
        $expectedObject
            ->setUser($user)
            ->setData('some encrypted value')
        ;

        $repo = $this->createMock(ObjectRepository::class);
        $repo->method('find')->with($itemId)->willReturn($expectedObject);
        $this->entityManager->method('getRepository')->with(Item::class)->willReturn($repo);

        $item = $this->itemService->update($itemId, $data);

        $this->assertEquals($data, $this->dataEncryption->decryptData($item));
    }
}
