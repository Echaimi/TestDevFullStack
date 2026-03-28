<?php

namespace App\Command;

use App\Application\Import\CsvOrdersImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Path;

#[AsCommand(
    name: 'ugo:orders:import',
    description: 'Import customers and purchases from CSV files into the database',
)]
class UgoOrdersImportCommand extends Command
{
    public function __construct(
        private readonly CsvOrdersImporter $csvOrdersImporter,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dataDir = Path::join($this->projectDir, 'data');
        $customersPath = Path::join($dataDir, 'customers.csv');
        $purchasesPath = Path::join($dataDir, 'purchases.csv');

        if (!is_readable($customersPath)) {
            $io->error(sprintf('Missing or unreadable file: %s', $customersPath));

            return Command::FAILURE;
        }
        if (!is_readable($purchasesPath)) {
            $io->error(sprintf('Missing or unreadable file: %s', $purchasesPath));

            return Command::FAILURE;
        }

        try {
            $count = $this->csvOrdersImporter->import(
                $customersPath,
                $purchasesPath,
                static fn (string $message) => $io->warning($message),
            );
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success(sprintf('Import finished (%d orders).', $count));

        return Command::SUCCESS;
    }
}
