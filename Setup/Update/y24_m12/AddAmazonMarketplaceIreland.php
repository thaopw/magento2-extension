<?php

declare(strict_types=1);

namespace Ess\M2ePro\Setup\Update\y24_m12;

use Ess\M2ePro\Helper\Module\Database\Tables;
use Ess\M2ePro\Model\ResourceModel\Amazon\Marketplace as AmazonMarketplaceResource;
use Ess\M2ePro\Model\ResourceModel\Marketplace as MarketplaceResource;

class AddAmazonMarketplaceIreland extends \Ess\M2ePro\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->createMarketplace();
        $this->createAmazonMarketplace();
    }

    private function createMarketplace(): void
    {
        $marketplaceTableName = $this->getFullTableName(Tables::TABLE_MARKETPLACE);

        $marketplace = $this->installer
            ->getConnection()->select()
            ->from($marketplaceTableName)
            ->where(
                MarketplaceResource::COLUMN_ID . ' = ?',
                \Ess\M2ePro\Helper\Component\Amazon::MARKETPLACE_IE
            )
            ->query()
            ->fetch();

        if ($marketplace !== false) {
            return;
        }

        $this->installer->getConnection()->insert(
            $marketplaceTableName,
            [
                MarketplaceResource::COLUMN_ID => \Ess\M2ePro\Helper\Component\Amazon::MARKETPLACE_IE,
                MarketplaceResource::COLUMN_NATIVE_ID => \Ess\M2ePro\Helper\Component\Amazon::NATIVE_ID_MARKETPLACE_IE,
                MarketplaceResource::COLUMN_TITLE => 'Ireland',
                MarketplaceResource::COLUMN_CODE => 'IE',
                MarketplaceResource::COLUMN_URL => 'amazon.ie',
                MarketplaceResource::COLUMN_STATUS => 0,
                MarketplaceResource::COLUMN_SORDER => 24,
                MarketplaceResource::COLUMN_GROUP_TITLE => 'Europe',
                MarketplaceResource::COLUMN_COMPONENT_MODE => 'amazon',
                'update_date' => '2024-12-20 00:00:00',
                'create_date' => '2023-12-20 00:00:00',
            ]
        );
    }

    private function createAmazonMarketplace(): void
    {
        $amazonMarketplaceTableName = $this->getFullTableName(Tables::TABLE_AMAZON_MARKETPLACE);

        $marketplace = $this->installer
            ->getConnection()
            ->select()
            ->from($amazonMarketplaceTableName)
            ->where(
                AmazonMarketplaceResource::COLUMN_MARKETPLACE_ID . ' = ?',
                \Ess\M2ePro\Helper\Component\Amazon::MARKETPLACE_IE
            )
            ->query()
            ->fetch();

        if ($marketplace !== false) {
            return;
        }

        $bind = [
            AmazonMarketplaceResource::COLUMN_MARKETPLACE_ID => \Ess\M2ePro\Helper\Component\Amazon::MARKETPLACE_IE,
            AmazonMarketplaceResource::COLUMN_DEFAULT_CURRENCY => 'EUR',
            AmazonMarketplaceResource::COLUMN_IS_MERCHANT_FULFILLMENT_AVAILABLE => 1,
            AmazonMarketplaceResource::COLUMN_IS_BUSINESS_AVAILABLE => 1,
            AmazonMarketplaceResource::COLUMN_IS_VAT_CALCULATION_SERVICE_AVAILABLE => 1,
            AmazonMarketplaceResource::COLUMN_IS_PRODUCT_TAX_CODE_POLICY_AVAILABLE => 0,
        ];

        if (
            $this->getTableModifier(Tables::TABLE_AMAZON_MARKETPLACE)
                 ->isColumnExists('is_new_asin_available')
        ) {
            // May be missing after migration from m1
            $bind['is_new_asin_available'] = 1;
        }

        $this->installer->getConnection()->insert(
            $amazonMarketplaceTableName,
            $bind
        );
    }
}
