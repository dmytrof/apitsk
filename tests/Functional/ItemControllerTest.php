<?php

namespace App\Tests;

use App\Repository\{ItemRepository, UserRepository};
use App\Service\DataEncryption;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ItemControllerTest extends WebTestCase
{
    public function testCreate()
    {
        $client = static::createClient();

        $userRepository = static::$container->get(UserRepository::class);
        $itemRepository = static::$container->get(ItemRepository::class);
        $dataEncryption = static::$container->get(DataEncryption::class);

        $user = $userRepository->findOneByUsername('john');

        $client->loginUser($user);
        
        $data = 'very secure new item data on '.date('Y-m-d H:i:s');

        $newItemData = ['data' => $data];

        $client->request('POST', '/item', $newItemData);
        $client->request('GET', '/item');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString($data, $client->getResponse()->getContent());

        $item = $itemRepository->findByUser($user, ['id' => 'DESC'], 1)[0];
        $this->assertEquals($data, $dataEncryption->decryptData($item));
    }

    public function testUpdate()
    {
        $client = static::createClient();

        $userRepository = static::$container->get(UserRepository::class);
        $itemRepository = static::$container->get(ItemRepository::class);
        $dataEncryption = static::$container->get(DataEncryption::class);

        $user = $userRepository->findOneByUsername('john');

        $client->loginUser($user);

        $item = $itemRepository->findByUser($user, ['id' => 'DESC'], 1)[0];
        $id = $item->getId();

        $data = 'Very secure updated item data on '.date('Y-m-d H:i:s');

        $updateData = ['id' => $id, 'data' => $data];

        $client->request('PUT', '/item', $updateData);
        $client->request('GET', '/item');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString($data, $client->getResponse()->getContent());

        $item = $itemRepository->find($id);
        $this->assertEquals($data, $dataEncryption->decryptData($item));
    }

    public function testDelete()
    {
        $client = static::createClient();

        $userRepository = static::$container->get(UserRepository::class);
        $itemRepository = static::$container->get(ItemRepository::class);

        $user = $userRepository->findOneByUsername('john');

        $client->loginUser($user);

        $item = $itemRepository->findByUser($user, ['id' => 'DESC'], 1)[0];
        $id = $item->getId();

        $client->request('DELETE', '/item/'.$id);
        $client->request('GET', '/item');

        $this->assertResponseIsSuccessful();
        $this->assertStringNotContainsString('"id":'.$id.',', $client->getResponse()->getContent());

        $item = $itemRepository->find($id);
        $this->assertNull($item);
    }
}
