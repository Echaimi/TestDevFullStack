<?php

namespace App\Controller;

use App\Dto\Api\OrderListItem;
use App\Repository\CustomerRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class OrderController extends AbstractController
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
        private readonly OrderRepository $orderRepository,
        private readonly SerializerInterface $serializer,
    ) {
    }

    #[Route('/customers/{id}/orders', name: 'app_customer_orders', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function byCustomer(int $id): JsonResponse
    {
        $customer = $this->customerRepository->find($id);
        if (null === $customer) {
            return new JsonResponse(
                ['error' => 'Customer not found', 'detail' => sprintf('No customer with id %d.', $id)],
                Response::HTTP_NOT_FOUND,
            );
        }

        $orders = $this->orderRepository->findByCustomerOrdered($customer);
        $items = array_map(
            static fn ($order) => OrderListItem::fromOrder($order, $customer),
            $orders,
        );

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($items, 'json'),
        );
    }
}
