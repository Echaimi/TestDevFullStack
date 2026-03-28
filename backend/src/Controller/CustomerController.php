<?php

namespace App\Controller;

use App\Dto\Api\CustomerListItem;
use App\Repository\CustomerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CustomerController extends AbstractController
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
        private readonly SerializerInterface $serializer,
    ) {
    }

    #[Route('/customers', name: 'app_customers_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $customers = $this->customerRepository->findBy([], ['id' => 'ASC']);
        $items = array_map(CustomerListItem::fromEntity(...), $customers);

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($items, 'json'),
        );
    }
}
