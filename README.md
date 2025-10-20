#V1.0.0
This extension will loop through enabled products with the visibility of Catalog only and change them to Catalog/Search, this can be done from the command line or added to cron for automation.

#Install


bin/magento module:enable Merlin_ProductVisibilityFix

bin/magento setup:upgrade

#Dry-Run Check First


# 2) (Optional) Check what would change

bin/magento merlin:product:fix-visibility --dry-run --batch-size=1000

#Run

# 3) Apply changes + reindex search

bin/magento merlin:product:fix-visibility --batch-size=1000

#Restrict Store View
# Optional: restrict status check to a store view
bin/magento merlin:product:fix-visibility --store-id=1
