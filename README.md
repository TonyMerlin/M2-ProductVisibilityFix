#V1.0.0
This extension will loop through enabled products with the visibility of Catalog only and change them to Catalog/Search, this can be done from the command line or added to cron for automation.


# 1) Install
bin/magento module:enable Merlin_ProductVisibilityFix

bin/magento setup:upgrade



# 2) (Optional) Dry-Run Check First

bin/magento merlin:product:fix-visibility --dry-run --batch-size=1000



# 3) Apply changes + reindex search

bin/magento merlin:product:fix-visibility --batch-size=1000


# 4) Restrict Store View: restrict status check to a store view

bin/magento merlin:product:fix-visibility --store-id=1
