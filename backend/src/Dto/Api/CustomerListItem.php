<?php

namespace App\Dto\Api;

use App\Entity\Customer;

/**
 * Contrat JSON exposé par GET /customers (couche présentation, indépendante du modèle domaine).
 */
final readonly class CustomerListItem
{
    public function __construct(
        public int $id,
        public string $title,
        public string $lastName,
        public string $firstName,
        public string $postalCode,
        public string $city,
        public string $email,
    ) {
    }

    public static function fromEntity(Customer $customer): self
    {
        $id = $customer->getId();
        if (null === $id) {
            throw new \LogicException('Customer must be persisted before serialization.');
        }

        return new self(
            id: $id,
            title: $customer->getTitle(),
            lastName: $customer->getLastName(),
            firstName: $customer->getFirstName(),
            postalCode: $customer->getPostalCode(),
            city: $customer->getCity(),
            email: $customer->getEmail(),
        );
    }
}
