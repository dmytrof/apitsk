<?php

namespace App\Service;

use App\Entity\{Item, User};
use App\Exception\ItemException;
use Doctrine\ORM\EntityManagerInterface;

class ItemService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * ItemService constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Creates item
     * @param User $user
     * @param string $data
     */
    public function create(User $user, string $data): void
    {
        $item = new Item();
        $item->setUser($user);
        $item->setData($data);

        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }

    /**
     * Updates item
     * @param int $id
     * @param string|null $data
     * @throws ItemException
     */
    public function update(int $id, ?string $data): void
    {
        $item = $this->entityManager->getRepository(Item::class)->find($id);
        if ($item === null) {
            throw new ItemException('Invalid item id');
        }

        $item->setData($data);
        $this->entityManager->flush();
    }
} 