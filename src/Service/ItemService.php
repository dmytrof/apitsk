<?php

namespace App\Service;

use Doctrine\Persistence\ObjectRepository;
use App\Entity\{Item, User};
use App\Exception\ItemException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class ItemService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var DataEncryption
     */
    private $dataEncryption;

    /**
     * ItemService constructor.
     * @param EntityManagerInterface $entityManager
     * @param DataEncryption $dataEncryption
     */
    public function __construct(EntityManagerInterface $entityManager, DataEncryption $dataEncryption)
    {
        $this->entityManager = $entityManager;
        $this->dataEncryption = $dataEncryption;
    }

    /**
     * Returns item repository
     * @return ObjectRepository
     */
    public function getItemRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(Item::class);
    }

    /**
     * Returns list of items
     * @param User $user
     * @return array
     */
    public function list(User $user): array
    {
        return $this->getItemRepository()->findBy(['user' => $user]);
    }

    /**
     * Creates item
     * @param User $user
     * @param string $data
     * @return Item
     */
    public function create(User $user, string $data): Item
    {
        $item = new Item();
        $item->setUser($user);
        $item->setData($data);

        $this->entityManager->persist($item);
        $this->encryptItemData($item);
        $this->entityManager->flush();

        return $item;
    }

    /**
     * Updates item
     * @param User $user
     * @param int $id
     * @param string|null $data
     * @return Item
     * @throws ItemException
     */
    public function update(User $user, int $id, ?string $data): Item
    {
        /** @var Item $item */
        $item = $this->getItemRepository()->find($id);
        if ($item === null) {
            throw new ItemException('Invalid item id');
        }

        if ($item->getUser() !== $user) {
            throw new ItemException('Invalid item id');
        }

        $item->setData($data);

        $this->encryptItemData($item);
        $this->entityManager->flush();

        return $item;
    }

    /**
     * Deletes item
     * @param User $user
     * @param int $id
     * @return Item
     * @throws ItemException
     */
    public function delete(User $user, int $id): Item
    {
        $item = $this->getItemRepository()->find($id);

        if ($item === null) {
            throw new ItemException('Invalid item id');
        }

        if ($item->getUser() !== $user) {
            throw new ItemException('Invalid item id');
        }

        $this->entityManager->remove($item);
        $this->entityManager->flush();

        return $item;
    }

    /**
     * Encrypts Item data
     * @param Item $item
     * @return $this
     */
    private function encryptItemData(Item $item): self
    {
        $encryptedData = $this->dataEncryption->encryptData($item);
        $item->setData($encryptedData);

        return $this;
    }
} 