<?php

declare(strict_types=1);

namespace Ess\M2ePro\Api\Amazon\MerchantFulfillment\CreateShipmentOffer;

interface ResponseInterface
{
    /**
     * @return string
     */
    public function getShipmentId(): string;

    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @return string
     */
    public function getTrackingId(): string;

    /**
     * @return string
     */
    public function getLabelFileType(): string;

    /**
     * @return string
     */
    public function getLabelFileContents(): string;

    /**
     * @return string
     */
    public function getShippingServiceCarrierName(): string;

    /**
     * @return string
     */
    public function getShippingServiceName(): string;

    /**
     * @return string
     */
    public function getShippingServiceRateCurrencyCode(): string;

    /**
     * @return float
     */
    public function getShippingServiceRateAmount(): float;

    /**
     * @return string
     */
    public function getShippingServiceDateShip(): string;

    /**
     * @return string
     */
    public function getShippingServiceDateEstimatedDeliveryLatest(): string;

    /**
     * @return string
     */
    public function getInsuranceCurrencyCode(): string;

    /**
     * @return float
     */
    public function getInsuranceAmount(): float;

    /**
     * @return string
     */
    public function getDateCreated(): string;

    /**
     * @return string
     */
    public function getPackagePredefinedDimensions(): string;

    /**
     * @return float
     */
    public function getPackageDimensionLength(): float;

    /**
     * @return float
     */
    public function getPackageDimensionWidth(): float;

    /**
     * @return float
     */
    public function getPackageDimensionHeight(): float;

    /**
     * @return string
     */
    public function getPackageDimensionUnitOfMeasure(): string;

    /**
     * @return float
     */
    public function getPackageWeightValue(): float;

    /**
     * @return string
     */
    public function getPackageWeightUnitOfMeasure(): string;

    /**
     * @return string
     */
    public function getAddressToName(): string;

    /**
     * @return string
     */
    public function getAddressToCountry(): string;

    /**
     * @return string|null
     */
    public function getAddressToState(): string;

    /**
     * @return string
     */
    public function getAddressToRegion(): string;

    /**
     * @return string
     */
    public function getAddressToCity(): string;

    /**
     * @return string
     */
    public function getAddressToAddress1(): string;

    /**
     * @return string
     */
    public function getAddressToAddress2(): string;

    /**
     * @return string
     */
    public function getAddressToPostalCode(): string;

    /**
     * @return string
     */
    public function getAddressFromName(): string;

    /**
     * @return string
     */
    public function getAddressFromEmail(): string;

    /**
     * @return string
     */
    public function getAddressFromPhone(): string;

    /**
     * @return string
     */
    public function getAddressFromCountry(): string;

    /**
     * @return string
     */
    public function getAddressFromState(): string;

    /**
     * @return string
     */
    public function getAddressFromRegion(): string;

    /**
     * @return string
     */
    public function getAddressFromCity(): string;

    /**
     * @return string
     */
    public function getAddressFromAddress1(): string;

    /**
     * @return string
     */
    public function getAddressFromAddress2(): string;

    /**
     * @return string
     */
    public function getAddressFromPostalCode(): string;
}
