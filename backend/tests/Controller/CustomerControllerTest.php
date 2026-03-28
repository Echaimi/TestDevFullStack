<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CustomerControllerTest extends WebTestCase
{
    public function testListCustomersReturnsJsonAfterImport(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $command = $application->find('ugo:orders:import');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        self::assertSame(0, $commandTester->getStatusCode());

        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', '/customers');

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($data);
        self::assertCount(3, $data);
        self::assertSame('Dupont', $data[0]['lastName']);
        self::assertSame('mme', $data[0]['title']);
    }
}
