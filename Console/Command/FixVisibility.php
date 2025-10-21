<?php
namespace Merlin\ProductVisibilityFix\Console\Command;


use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Merlin\ProductVisibilityFix\Model\Service\FixVisibility as FixVisibilityService;


class FixVisibility extends Command
{
const INPUT_KEY_BATCH = 'batch-size';
const INPUT_KEY_DRY = 'dry-run';
const INPUT_KEY_STORE = 'store-id';


private FixVisibilityService $service;


public function __construct(FixVisibilityService $service)
{
parent::__construct();
$this->service = $service;
}


protected function configure()
{
$this->setName('merlin:product:fix-visibility')
->setDescription('Set enabled products from Catalog-only to Catalog, Search and reindex search indexer')
->addOption(self::INPUT_KEY_BATCH, null, InputOption::VALUE_REQUIRED, 'Batch size for processing', 500)
->addOption(self::INPUT_KEY_DRY, null, InputOption::VALUE_NONE, 'Dry-run (no writes, no reindex)')
->addOption(self::INPUT_KEY_STORE, null, InputOption::VALUE_REQUIRED, 'Optional store ID to use for status scope filter', null);
}


protected function execute(InputInterface $input, OutputInterface $output)
{
$batchSize = (int)$input->getOption(self::INPUT_KEY_BATCH);
$dryRun = (bool)$input->getOption(self::INPUT_KEY_DRY);
$storeId = $input->getOption(self::INPUT_KEY_STORE);
$storeId = $storeId !== null ? (int)$storeId : null;


$output->writeln('<info>Starting visibility fix...</info>');
$output->writeln(sprintf('Batch size: %d | Dry-run: %s%s', $batchSize, $dryRun ? 'yes' : 'no', $storeId !== null ? ' | Store ID: ' . $storeId : ''));


try {
$result = $this->service->execute($batchSize, $dryRun, $storeId);
$output->writeln(sprintf('<info>Examined: %d | Updated: %d</info>', $result['examined'], $result['updated']));
if (!$dryRun) {
$output->writeln('<info>Reindexed: catalogsearch_fulltext</info>');
}
} catch (\Throwable $e) {
$output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
return Cli::RETURN_FAILURE;
}


return Cli::RETURN_SUCCESS;
}
}
