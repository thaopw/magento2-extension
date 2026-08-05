<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Api\MerchantFulfillment\CreateShipmentOffer;

use Ess\M2ePro\Api\Amazon\MerchantFulfillment\CreateShipmentOffer\ResponseInterface;

class Response implements ResponseInterface
{
    private array $rawData;

    public function __construct(array $rawData)
    {
        $this->rawData = $rawData;
    }

    public function getShipmentId(): string
    {
        return (string)$this->rawData['shipment_id'];
    }

    public function getStatus(): string
    {
        return (string)$this->rawData['status'];
    }

    public function getTrackingId(): string
    {
        return (string)$this->rawData['tracking_id'];
    }

    public function getLabelFileType(): string
    {
        return (string)$this->rawData['label']['file']['type'];
    }

    public function getLabelFileContents(): string
    {
        return (string)$this->rawData['label']['file']['contents'];
    }

    public function getShippingServiceCarrierName(): string
    {
        return (string)$this->rawData['shipping_service']['carrier_name'];
    }

    public function getShippingServiceName(): string
    {
        return (string)$this->rawData['shipping_service']['name'];
    }

    public function getShippingServiceRateCurrencyCode(): string
    {
        return (string)$this->rawData['shipping_service']['rate']['currency_code'];
    }

    public function getShippingServiceRateAmount(): float
    {
        return (float)$this->rawData['shipping_service']['rate']['amount'];
    }

    public function getShippingServiceDateShip(): string
    {
        return (string)$this->rawData['shipping_service']['date']['ship'];
    }

    public function getShippingServiceDateEstimatedDeliveryLatest(): string
    {
        return (string)$this->rawData['shipping_service']['date']['estimated_delivery']['latest'];
    }

    public function getInsuranceCurrencyCode(): string
    {
        return (string)$this->rawData['insurance']['currency_code'];
    }

    public function getInsuranceAmount(): float
    {
        return (float)$this->rawData['insurance']['amount'];
    }

    public function getDateCreated(): string
    {
        return (string)$this->rawData['date']['created'];
    }

    public function getPackagePredefinedDimensions(): string
    {
        return (string)$this->rawData['package']['predefined_dimensions'];
    }

    public function getPackageDimensionLength(): float
    {
        return (float)$this->rawData['package']['dimensions']['length'];
    }

    public function getPackageDimensionWidth(): float
    {
        return (float)$this->rawData['package']['dimensions']['width'];
    }

    public function getPackageDimensionHeight(): float
    {
        return (float)$this->rawData['package']['dimensions']['height'];
    }

    public function getPackageDimensionUnitOfMeasure(): string
    {
        return (string)$this->rawData['package']['dimensions']['unit_of_measure'];
    }

    public function getPackageWeightValue(): float
    {
        return (float)$this->rawData['package']['weight']['value'];
    }

    public function getPackageWeightUnitOfMeasure(): string
    {
        return (string)$this->rawData['package']['weight']['unit_of_measure'];
    }

    public function getAddressToName(): string
    {
        return (string)$this->rawData['address']['to']['info']['name'];
    }

    public function getAddressToCountry(): string
    {
        return (string)$this->rawData['address']['to']['physical']['country'];
    }

    public function getAddressToState(): string
    {
        return (string)$this->rawData['address']['to']['physical']['state'];
    }

    public function getAddressToRegion(): string
    {
        return (string)$this->rawData['address']['to']['physical']['region'];
    }

    public function getAddressToCity(): string
    {
        return (string)$this->rawData['address']['to']['physical']['city'];
    }

    public function getAddressToAddress1(): string
    {
        return (string)$this->rawData['address']['to']['physical']['address_1'];
    }

    public function getAddressToAddress2(): string
    {
        return (string)$this->rawData['address']['to']['physical']['address_2'];
    }

    public function getAddressToPostalCode(): string
    {
        return (string)$this->rawData['address']['to']['physical']['postal_code'];
    }

    public function getAddressFromName(): string
    {
        return (string)$this->rawData['address']['from']['info']['name'];
    }

    public function getAddressFromEmail(): string
    {
        return (string)$this->rawData['address']['from']['info']['email'];
    }

    public function getAddressFromPhone(): string
    {
        return (string)$this->rawData['address']['from']['info']['phone'];
    }

    public function getAddressFromCountry(): string
    {
        return (string)$this->rawData['address']['from']['physical']['country'];
    }

    public function getAddressFromState(): string
    {
        return (string)$this->rawData['address']['from']['physical']['state'];
    }

    public function getAddressFromRegion(): string
    {
        return (string)$this->rawData['address']['from']['physical']['region'];
    }

    public function getAddressFromCity(): string
    {
        return (string)$this->rawData['address']['from']['physical']['city'];
    }

    public function getAddressFromAddress1(): string
    {
        return (string)$this->rawData['address']['from']['physical']['address_1'];
    }

    public function getAddressFromAddress2(): string
    {
        return (string)$this->rawData['address']['from']['physical']['address_2'];
    }

    public function getAddressFromPostalCode(): string
    {
        return (string)$this->rawData['address']['from']['physical']['postal_code'];
    }
}
