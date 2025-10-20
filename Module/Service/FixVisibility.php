<?php
namespace Merlin\ProductVisibilityFix\Model\Service;

use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Action as ProductAction;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\IndexerRegistry;

class FixVisibility
{
    private ProductCollectionFactory $collectionFactory;
    private ProductAction $productAction;
    private IndexerRegistry $indexerRegistry;
    private State $appState;

    public function __construct(
        ProductCollectionFactory $collectionFactory,
        ProductAction $productAction,
        IndexerRegistry $indexerRegistry,
        State $appState
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->productAction = $productAction;
        $this->indexerRegistry = $indexerRegistry;
        $this->appState = $appState;
    }

    /**
     * Loop through enabled products visible in catalog only and update to catalog/search
     *
     * @param int $batchSize
     * @param bool $dryRun
     * @param int|null $storeId
     * @return array
     * @throws LocalizedException
     */
    public function execute(int $batchSize = 500, bool $dryRun = false, ?int $storeId = null): array
    {
        // Ensure a valid area code for indexer/logging contexts in CLI
        try {
            $this->appState->getAreaCode();
        } catch (\Exception $e) {
            $this->appState->setAreaCode('adminhtml');
        }

        $examined = 0;
        $updated = 0;
        $page = 1;

        do {
            $collection = $this->collectionFactory->create();
            if ($storeId !== null) {
                $collection->setStoreId($storeId);
            }

            $collection
                ->addAttributeToSelect('visibility')
                ->addAttributeToFilter('status', Status::STATUS_ENABLED)
                ->addAttributeToFilter('visibility', ProductVisibility::VISIBILITY_IN_CATALOG)
                ->setPageSize($batchSize)
                ->setCurPage($page);

            $ids = $collection->getAllIds();
            $count = count($ids);
            $examined += $count;

            if ($count > 0 && !$dryRun) {
                // Visibility is global; pass storeId 0
                $this->productAction->updateAttributes(
                    $ids,
                    ['visibility' => ProductVisibility::VISIBILITY_BOTH],
                    0
                );
                $updated += $count;
            }

            $page++;
        } while ($count === $batchSize);

        if (!$dryRun) {
            // Reindex catalog search
            $indexer = $this->indexerRegistry->get('catalogsearch_fulltext');
            $indexer->invalidate();
            $indexer->reindexAll();
        }

        return ['examined' => $examined, 'updated' => $updated];
    }
}
