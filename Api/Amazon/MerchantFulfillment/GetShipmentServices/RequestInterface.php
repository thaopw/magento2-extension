<?php

declare(strict_types=1);

namespace Ess\M2ePro\Api\Amazon\MerchantFulfillment\GetShipmentServices;

interface RequestInterface
{
    /**
     * @return float
     */
    public function getPackageDimensionLength(): ?float;

    /**
     * @param float $value
     */
    public function setPackageDimensionLength(float $value): void;

    /**
     * @return float
     */
    public function getPackageDimensionWidth(): float;

    /**
     * @param float $value
     */
    public function setPackageDimensionWidth(float $value): void;

    /**
     * @return float
     */
    public function getPackageDimensionHeight(): ?float;

    /**
     * @param float $value
     */
    public function setPackageDimensionHeight(float $value): void;

    /**
     * @return string
     */
    public function getPackageDimensionMeasure(): string;

    /**
     * @param string $value
     */
    public function setPackageDimensionMeasure(string $value): void;

    /**
     * @return float
     */
    public function getPackageWeight(): float;

    /**
     * @param float $value
     */
    public function setPackageWeight(float $value): void;

    /**
     * @return string
     */
    public function getPackageWeightMeasure(): string;

    /**
     * @param string $value
     */
    public function setPackageWeightMeasure(string $value): void;

    /**
     * @return string
     */
    public function getShipFromAddressName(): string;

    /**
     * @param string $value
     */
    public function setShipFromAddressName(string $value): void;

    /**
     * @return string
     */
    public function getShipFromAddressEmail(): string;

    /**
     * @param string $value
     */
    public function setShipFromAddressEmail(string $value): void;

    /**
     * @return string
     */
    public function getShipFromAddressPhone(): string;

    /**
     * @param string $value
     */
    public function setShipFromAddressPhone(string $value): void;

    /**
     * @return string
     */
    public function getShipFromAddressCountry(): string;

    /**
     * @param string $value
     */
    public function setShipFromAddressCountry(string $value): void;

    /**
     * @return string|null
     */
    public function getShipFromAddressRegionState(): ?string;

    /**
     * @param string|null $value
     */
    public function setShipFromAddressRegionState(?string $value): void;

    /**
     * @return string
     */
    public function getShipFromAddressCity(): string;

    /**
     * @param string $value
     */
    public function setShipFromAddressCity(string $value): void;

    /**
     * @return string
     */
    public function getShipFromAddressAddressLine1(): string;

    /**
     * @param string $value
     */
    public function setShipFromAddressAddressLine1(string $value): void;

    /**
     * @return string|null
     */
    public function getShipFromAddressAddressLine2(): ?string;

    /**
     * @param string|null $value
     */
    public function setShipFromAddressAddressLine2(?string $value): void;

    /**
     * @return string
     */
    public function getShipFromAddressPostalCode(): string;

    /**
     * @param string $value
     */
    public function setShipFromAddressPostalCode(string $value): void;

    /**
     * @return bool
     */
    public function getCarrierWillPickup(): bool;

    /**
     * @param bool $value
     */
    public function setCarrierWillPickup(bool $value): void;

    /**
     * @return int
     */
    public function getDeliveryConfirmationLevel(): int;

    /**
     * @param int $value
     */
    public function setDeliveryConfirmationLevel(int $value): void;

    /**
     * @return string
     */
    public function getArriveByDate(): string;

    /**
     * @param string $date
     */
    public function setArriveByDate(string $date): void;
}
