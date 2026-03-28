<?php

namespace App\Dto\Api;

use App\Entity\Customer;
use App\Entity\Order;

/**
 * Contrat JSON pour une ligne de GET /customers/{id}/orders.
 */
final readonly class OrderListItem
{
    public function __construct(
        public string $lastName,
        public string $purchaseIdentifier,
        public string $productId,
        public int $quantity,
        public float $price,
        public string $currency,
        public string $date,
    ) {
    }

    public static function fromOrder(Order $order, Customer $customer): self
    {
        $d = $order->getDate();
        if (null === $d) {
            throw new \LogicException('Order date must be set.');
        }

        return new self(
            lastName: $customer->getLastName(),
            purchaseIdentifier: $order->getPurchaseIdentifier(),
            productId: $order->getProductId(),
            quantity: $order->getQuantity(),
            price: $order->getPrice(),
            currency: $order->getCurrency(),
            date: $d->format('Y-m-d'),
        );
    }
}
