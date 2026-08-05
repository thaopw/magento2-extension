<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Api\MerchantFulfillment\CreateShipmentOffer;

class Request implements \Ess\M2ePro\Api\Amazon\MerchantFulfillment\CreateShipmentOffer\RequestInterface
{
    private float $packageDimensionLength;
    private float $packageDimensionWidth;
    private float $packageDimensionHeight;
    private string $packageDimensionMeasure;
    private float $packageWeight;
    private string $packageWeightMeasure;
    private string $shipFromAddressName;
    private string $shipFromAddressEmail;
    private string $shipFromAddressPhone;
    private string $shipFromAddressCountry;
    private ?string $shipFromAddressRegionState = null;
    private string $shipFromAddressCity;
    private string $shipFromAddressAddressLine1;
    private ?string $shipFromAddressAddressLine2 = null;
    private string $shipFromAddressPostalCode;
    private bool $shipCarrierWillPickup;
    private int $deliveryConfirmationLevel;
    private string $arriveByDate;
    private string $shippingServiceId;

    public function getPackageDimensionLength(): float
    {
        return $this->packageDimensionLength;
    }

    public function setPackageDimensionLength(float $value): void
    {
        $this->packageDimensionLength = $value;
    }

    public function getPackageDimensionWidth(): float
    {
        return $this->packageDimensionWidth;
    }

    public function setPackageDimensionWidth(float $value): void
    {
        $this->packageDimensionWidth = $value;
    }

    public function getPackageDimensionHeight(): ?float
    {
        return $this->packageDimensionHeight;
    }

    public function setPackageDimensionHeight(float $value): void
    {
        $this->packageDimensionHeight = $value;
    }

    public function getPackageDimensionMeasure(): string
    {
        return $this->packageDimensionMeasure;
    }

    public function setPackageDimensionMeasure(string $value): void
    {
        $this->packageDimensionMeasure = $value;
    }

    public function getPackageWeight(): float
    {
        return $this->packageWeight;
    }

    public function setPackageWeight(float $value): void
    {
        $this->packageWeight = $value;
    }

    public function getPackageWeightMeasure(): string
    {
        return $this->packageWeightMeasure;
    }

    public function setPackageWeightMeasure(string $value): void
    {
        $this->packageWeightMeasure = $value;
    }

    public function getShipFromAddressName(): string
    {
        return $this->shipFromAddressName;
    }

    public function setShipFromAddressName(string $value): void
    {
        $this->shipFromAddressName = $value;
    }

    public function getShipFromAddressEmail(): string
    {
        return $this->shipFromAddressEmail;
    }

    public function setShipFromAddressEmail(string $value): void
    {
        $this->shipFromAddressEmail = $value;
    }

    public function getShipFromAddressPhone(): string
    {
        return $this->shipFromAddressPhone;
    }

    public function setShipFromAddressPhone(string $value): void
    {
        $this->shipFromAddressPhone = $value;
    }

    public function getShipFromAddressCountry(): string
    {
        return $this->shipFromAddressCountry;
    }

    public function setShipFromAddressCountry(string $value): void
    {
        $this->shipFromAddressCountry = $value;
    }

    public function getShipFromAddressRegionState(): ?string
    {
        return $this->shipFromAddressRegionState;
    }

    public function setShipFromAddressRegionState(?string $value): void
    {
        $this->shipFromAddressRegionState = $value;
    }

    public function getShipFromAddressCity(): string
    {
        return $this->shipFromAddressCity;
    }

    public function setShipFromAddressCity(string $value): void
    {
        $this->shipFromAddressCity = $value;
    }

    public function getShipFromAddressAddressLine1(): string
    {
        return $this->shipFromAddressAddressLine1;
    }

    public function setShipFromAddressAddressLine1(string $value): void
    {
        $this->shipFromAddressAddressLine1 = $value;
    }

    public function getShipFromAddressAddressLine2(): ?string
    {
        return $this->shipFromAddressAddressLine2;
    }

    public function setShipFromAddressAddressLine2(?string $value): void
    {
        $this->shipFromAddressAddressLine2 = $value;
    }

    public function getShipFromAddressPostalCode(): string
    {
        return $this->shipFromAddressPostalCode;
    }

    public function setShipFromAddressPostalCode(string $value): void
    {
        $this->shipFromAddressPostalCode = $value;
    }

    public function getCarrierWillPickup(): bool
    {
        return $this->shipCarrierWillPickup;
    }

    public function setCarrierWillPickup(bool $value): void
    {
        $this->shipCarrierWillPickup = $value;
    }

    public function getDeliveryConfirmationLevel(): int
    {
        return $this->deliveryConfirmationLevel;
    }

    public function setDeliveryConfirmationLevel(int $value): void
    {
        $this->deliveryConfirmationLevel = $value;
    }

    public function getArriveByDate(): string
    {
        return $this->arriveByDate;
    }

    public function setArriveByDate(string $date): void
    {
        $this->arriveByDate = $date;
    }

    public function getShippingServiceId(): string
    {
        return $this->shippingServiceId;
    }

    public function setShippingServiceId(string $value): void
    {
        $this->shippingServiceId = $value;
    }
}
