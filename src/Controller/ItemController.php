<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ItemException;
use App\Service\{ItemService, ItemTransformer};
use Symfony\Component\HttpFoundation\{Request, JsonResponse, Response};
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ItemController extends AbstractController
{
    /**
     * @Route("/item", name="item_list", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function list(ItemService $itemService, ItemTransformer $itemTransformer): JsonResponse
    {
        $allItems = [];
        foreach ($itemService->list($this->getUser()) as $item) {
            array_push($allItems, $itemTransformer->transformToArray($item));
        }

        return $this->json($allItems);
    }

    /**
     * @Route("/item", name="item_create", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function create(Request $request, ItemService $itemService): JsonResponse
    {
        $data = $request->get('data');

        if (empty($data)) {
            return $this->json(['error' => 'No data parameter']);
        }

        $item = $itemService->create($this->getUser(), $data);

        return $this->json([
            'id' => $item->getId(),
        ]);
    }

    /**
     * @Route("/item", name="item_update", methods={"PUT"})
     * @IsGranted("ROLE_USER")
     */
    public function update(Request $request, ItemService $itemService): JsonResponse
    {
        $id = $request->request->getInt('id');
        $data = $request->request->get('data');

        if (empty($id)) {
            return $this->json(['error' => 'No data parameter'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $itemService->update($this->getUser(), $id, $data);
            return $this->json([]);
        } catch (ItemException|\Exception $e) {
            return $this->json(
                ['error' => $e->getMessage()],
                $e instanceof HttpExceptionInterface ? $e->getStatusCode() : Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * @Route("/item/{id}", name="items_delete", methods={"DELETE"})
     * @IsGranted("ROLE_USER")
     */
    public function delete(int $id, ItemService $itemService)
    {
        if (empty($id)) {
            return $this->json(['error' => 'No data parameter'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $itemService->delete($this->getUser(), $id);
            return $this->json([]);
        } catch (ItemException|\Exception $e) {
            return $this->json(
                ['error' => $e->getMessage()],
                $e instanceof HttpExceptionInterface ? $e->getStatusCode() : Response::HTTP_BAD_REQUEST
            );
        }
    }
}