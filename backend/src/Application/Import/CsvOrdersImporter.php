<?php

namespace App\Application\Import;

use App\Entity\Customer;
use App\Entity\Order;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Cas d’usage métier : import CSV → persistance Doctrine, avec validation explicite.
 */
final class CsvOrdersImporter
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CustomerRepository $customerRepository,
        private readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * @param callable(string): void $onWarning message utilisateur (CLI ou logs)
     *
     * @return int nombre de commandes importées
     */
    public function import(string $customersPath, string $purchasesPath, callable $onWarning): int
    {
        $this->entityManager->createQueryBuilder()
            ->delete(Order::class, 'o')
            ->getQuery()
            ->execute();
        $this->entityManager->flush();

        $customersByNumber = $this->importCustomers($customersPath, $onWarning);
        $this->entityManager->flush();

        return $this->importPurchases($purchasesPath, $customersByNumber, $onWarning);
    }

    /**
     * @param callable(string): void $onWarning
     *
     * @return array<string, Customer>
     */
    private function importCustomers(string $path, callable $onWarning): array
    {
        $handle = fopen($path, 'r');
        if (false === $handle) {
            throw new \RuntimeException(sprintf('Could not open customers CSV: %s', $path));
        }

        $customersByNumber = [];
        $header = fgetcsv($handle);
        $line = 1;

        try {
            while (($row = fgetcsv($handle)) !== false) {
                ++$line;
                if ($this->rowIsEmpty($row)) {
                    continue;
                }
                $data = $this->combineRow($header, $row);
                $number = $data['customer_number'] ?? '';
                if ('' === $number) {
                    continue;
                }

                $customer = $this->customerRepository->findOneByCustomerNumber($number);
                $isNew = null === $customer;
                if ($isNew) {
                    $customer = (new Customer())->setCustomerNumber($number);
                }

                $customer
                    ->setTitle($this->mapTitle($data['title'] ?? ''))
                    ->setLastName($data['last_name'] ?? '')
                    ->setFirstName($data['first_name'] ?? '')
                    ->setPostalCode($data['postal_code'] ?? '')
                    ->setCity($data['city'] ?? '')
                    ->setEmail($data['email'] ?? '');

                if ($isNew) {
                    $this->entityManager->persist($customer);
                }

                $violations = $this->validator->validate($customer);
                if ($violations->count() > 0) {
                    $onWarning(sprintf('Ligne %d (client %s) ignorée : %s', $line, $number, $violations));
                    if ($isNew) {
                        $this->entityManager->detach($customer);
                    } else {
                        $this->entityManager->refresh($customer);
                    }
                    continue;
                }

                $customersByNumber[$number] = $customer;
            }
        } finally {
            fclose($handle);
        }

        return $customersByNumber;
    }

    /**
     * @param array<string, Customer> $customersByNumber
     * @param callable(string): void  $onWarning
     */
    private function importPurchases(string $path, array $customersByNumber, callable $onWarning): int
    {
        $handle = fopen($path, 'r');
        if (false === $handle) {
            throw new \RuntimeException(sprintf('Could not open purchases CSV: %s', $path));
        }

        $header = fgetcsv($handle);
        $importedOrders = 0;
        $line = 1;

        try {
            while (($row = fgetcsv($handle)) !== false) {
                ++$line;
                if ($this->rowIsEmpty($row)) {
                    continue;
                }
                $data = $this->combineRow($header, $row);
                $number = $data['customer_number'] ?? '';
                $customer = $customersByNumber[$number] ?? $this->customerRepository->findOneByCustomerNumber($number);
                if (null === $customer) {
                    $onWarning(sprintf('Ligne %d : client inconnu (%s), commande ignorée.', $line, $number));
                    continue;
                }

                $dateRaw = $data['date'] ?? '';
                $date = \DateTimeImmutable::createFromFormat('Y-m-d', $dateRaw)
                    ?: \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $dateRaw)
                    ?: \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateRaw);
                if (false === $date) {
                    $onWarning(sprintf('Ligne %d : date invalide (%s).', $line, $dateRaw));
                    continue;
                }

                $order = (new Order())
                    ->setPurchaseIdentifier($data['purchase_identifier'] ?? '')
                    ->setProductId($data['product_id'] ?? '')
                    ->setQuantity((int) ($data['quantity'] ?? 0))
                    ->setPrice((float) ($data['price'] ?? 0))
                    ->setCurrency($data['currency'] ?? '')
                    ->setDate($date)
                    ->setCustomer($customer);

                $violations = $this->validator->validate($order);
                if ($violations->count() > 0) {
                    $onWarning(sprintf('Ligne %d (commande) ignorée : %s', $line, $violations));
                    continue;
                }

                $this->entityManager->persist($order);
                ++$importedOrders;
            }
        } finally {
            fclose($handle);
        }

        $this->entityManager->flush();

        return $importedOrders;
    }

    /** @param list<string|null>|false $row */
    private function rowIsEmpty(array|false $row): bool
    {
        if (false === $row) {
            return true;
        }
        $trimmed = array_filter(array_map(static fn (?string $v) => trim((string) $v), $row));

        return [] === $trimmed;
    }

    /**
     * @param list<string|null>|null $header
     * @param list<string|null>      $row
     *
     * @return array<string, string>
     */
    private function combineRow(?array $header, array $row): array
    {
        if (null === $header) {
            return [];
        }
        $out = [];
        foreach ($header as $i => $key) {
            $k = strtolower(trim((string) $key));
            if ('' === $k) {
                continue;
            }
            $out[$k] = trim((string) ($row[$i] ?? ''));
        }

        return $out;
    }

    private function mapTitle(string $raw): string
    {
        $v = trim($raw);

        return match ($v) {
            '1' => 'mme',
            '2' => 'm',
            default => $v,
        };
    }
}
