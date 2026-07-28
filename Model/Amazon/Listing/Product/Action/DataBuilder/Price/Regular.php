<?php

namespace Ess\M2ePro\Model\Amazon\Listing\Product\Action\DataBuilder\Price;

class Regular extends \Ess\M2ePro\Model\Amazon\Listing\Product\Action\DataBuilder\AbstractModel
{
    public function getBuilderData()
    {
        $data = [];
        $amazonListingProduct = $this->getAmazonListingProduct();
        if (!isset($this->cachedData['regular_price'])) {
            $this->cachedData['regular_price'] = $amazonListingProduct->getRegularPrice();
        }

        if (!isset($this->cachedData['regular_map_price'])) {
            $this->cachedData['regular_map_price'] = $amazonListingProduct->getRegularMapPrice();
        }

        if (!isset($this->cachedData['regular_sale_price_info'])) {
            $salePriceInfo = $this->retireRegularSalePriceInfo($amazonListingProduct);
            $this->cachedData['regular_sale_price_info'] = $salePriceInfo;
        }

        $data['price'] = $this->cachedData['regular_price'];

        if ((float)$this->cachedData['regular_map_price'] <= 0) {
            $data[\Ess\M2ePro\Model\Amazon\Listing\Product\Action\RequestData::MAP_PRICE] = 0;
        } else {
            $data[
                \Ess\M2ePro\Model\Amazon\Listing\Product\Action\RequestData::MAP_PRICE
            ] = $this->cachedData['regular_map_price'];
        }

        if ($this->cachedData['regular_sale_price_info'] === false) {
            $data['sale_price'] = 0;
        } else {
            $data['sale_price'] = $this->cachedData['regular_sale_price_info']['price'];
            $data['sale_price_start_date'] = $this->cachedData['regular_sale_price_info']['start_date'];
            $data['sale_price_end_date'] = $this->cachedData['regular_sale_price_info']['end_date'];
        }

        if (
            !$amazonListingProduct->isGeneralIdOwner()
            && !$amazonListingProduct->getVariationManager()
                                     ->isVariationParent()
        ) {
            $listPrice = $amazonListingProduct->getRegularListPrice();
            if ($listPrice > 0) {
                $data['list_price'] = $listPrice;
            }
        }

        return $data;
    }

    /**
     * @return array|false
     * @throws \Ess\M2ePro\Model\Exception
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    private function retireRegularSalePriceInfo(\Ess\M2ePro\Model\Amazon\Listing\Product $amazonListingProduct)
    {
        $salePriceInfo = $amazonListingProduct->getRegularSalePriceInfo();
        if ($salePriceInfo !== false) {
            return $salePriceInfo;
        }

        $onlineSalePrice = $amazonListingProduct->getOnlineRegularSalePrice();
        if (empty($onlineSalePrice)) {
            return false;
        }

        return [
            'price' => $onlineSalePrice,
            'start_date' => \Ess\M2ePro\Helper\Date::createCurrentGmt()->modify('-7 days')->format('Y-m-d H:i:s'),
            'end_date' => \Ess\M2ePro\Helper\Date::createCurrentGmt()->modify('-6 days')->format('Y-m-d H:i:s'),
        ];
    }
}
