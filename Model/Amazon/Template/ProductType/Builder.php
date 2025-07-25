<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Template\ProductType;

class Builder extends \Ess\M2ePro\Model\ActiveRecord\AbstractBuilder
{
    /** @var array */
    private $specifics = [];
    /** @var array */
    private $otherImagesSpecifics;

    private \Ess\M2ePro\Model\Amazon\Dictionary\ProductType\Repository $dictionaryProductTypeRepository;

    public function __construct(
        \Ess\M2ePro\Model\Amazon\Dictionary\ProductType\Repository $dictionaryProductTypeRepository,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        array $data = []
    ) {
        parent::__construct($helperFactory, $modelFactory, $data);
        $this->otherImagesSpecifics = \Ess\M2ePro\Helper\Component\Amazon\ProductType::getOtherImagesSpecifics();
        $this->dictionaryProductTypeRepository = $dictionaryProductTypeRepository;
    }

    protected function prepareData()
    {
        if ($this->model->getId()) {
            $data = $this->model->getData();
        } else {
            $data = $this->getDefaultData();

            $temp = [];
            $keys = ['marketplace_id', 'nick'];
            foreach ($keys as $key) {
                if (empty($this->rawData['general'][$key])) {
                    throw new \Ess\M2ePro\Model\Exception(
                        "Missing required field for Product Type: $key"
                    );
                }

                $temp[$key] = $this->rawData['general'][$key];
            }

            $dictionary = $this->dictionaryProductTypeRepository->findByMarketplaceAndNick(
                (int)$temp['marketplace_id'],
                (string)$temp['nick']
            );
            if ($dictionary === null) {
                throw new \Ess\M2ePro\Model\Exception(
                    "Product Type data not found for provided marketplace_id and product type nick"
                );
            }

            $data['dictionary_product_type_id'] = $dictionary->getId();
        }

        if (isset($this->rawData['general']['product_type_title'])) {
            $data['title'] = $this->rawData['general']['product_type_title'];
        }

        if (!empty($this->rawData['field_data']) && is_array($this->rawData['field_data'])) {
            $this->specifics = [];
            $this->collectSpecifics($this->rawData['field_data']);

            $data['settings'] = \Ess\M2ePro\Helper\Json::encode($this->specifics);
        }

        $data['view_mode'] = $this->rawData['general']['view_mode'];

        return $data;
    }

    /**
     * @param array $data
     * @param array $path
     *
     * @return void
     * @throws \Ess\M2ePro\Model\Exception
     */
    private function collectSpecifics(array $data, array $path = []): void
    {
        $pathString = implode("/", $path);
        foreach ($data as $key => $value) {
            if (isset($value['mode']) && is_string($value['mode'])) {
                if (!isset($this->specifics[$pathString])) {
                    $this->specifics[$pathString] = [];
                }

                if ($fieldData = $this->collectFieldData($value, $path)) {
                    $this->specifics[$pathString][] = $fieldData;
                }
            } else {
                $currentPath = $path;
                $currentPath[] = $key;
                $this->collectSpecifics($value, $currentPath);
            }
        }

        if (empty($this->specifics[$pathString])) {
            unset($this->specifics[$pathString]);
        }
    }

    /**
     * @throws \Ess\M2ePro\Model\Exception
     */
    private function collectFieldData(array $field, array $path): array
    {
        if (empty($field['mode'])) {
            return [];
        }

        switch ((int)$field['mode']) {
            case \Ess\M2ePro\Model\Amazon\Template\ProductType::FIELD_CUSTOM_VALUE:
                if (empty($field['value'])) {
                    return [];
                }

                if (!empty($field['format']) && $field['format'] === 'date-time') {
                    $timestamp = \Ess\M2ePro\Helper\Date::createDateInCurrentZone($field['value'])->getTimestamp();
                    $datetime = \Ess\M2ePro\Helper\Date::createCurrentGmt();
                    $datetime->setTimestamp($timestamp);

                    $field['value'] = $datetime->format('Y-m-d H:i:s');
                }

                return [
                    'mode' => (int)$field['mode'],
                    'value' => $field['value'],
                ];
            case \Ess\M2ePro\Model\Amazon\Template\ProductType::FIELD_CUSTOM_ATTRIBUTE:
                if (empty($field['attribute_code'])) {
                    return [];
                }

                $key = implode('/', $path);
                if (in_array($key, $this->otherImagesSpecifics)) {
                    if (is_numeric($field['attribute_code'])) {
                        return [
                            'mode' => (int)$field['mode'],
                            'attribute_code' => 'media_gallery',
                            'images_limit' => (int)$field['attribute_code'],
                        ];
                    }
                }

                return [
                    'mode' => (int)$field['mode'],
                    'attribute_code' => $field['attribute_code'],
                ];
            default:
                throw new \Ess\M2ePro\Model\Exception('Incorrect mode for Product Type specifics.');
        }
    }

    /**
     * @return array
     */
    public function getDefaultData(): array
    {
        return [
            'id' => '',
            'title' => '',
            'view_mode' => \Ess\M2ePro\Model\Amazon\Template\ProductType::VIEW_MODE_ALL_ATTRIBUTES,
            'dictionary_product_type_id' => '',
            'settings' => '[]',
        ];
    }
}
