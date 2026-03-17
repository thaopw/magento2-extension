<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Template\Shipping;

class Builder extends \Ess\M2ePro\Model\ActiveRecord\AbstractBuilder
{
    /** @var \Ess\M2ePro\Model\Amazon\Template\Shipping */
    protected $model;

    private \Ess\M2ePro\Helper\Component\Amazon $amazonHelper;
    private Repository $templateShippingRepository;

    public function __construct(
        Repository $templateShippingRepository,
        \Ess\M2ePro\Helper\Component\Amazon $amazonHelper,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        array $data = []
    ) {
        parent::__construct($helperFactory, $modelFactory, $data);
        $this->amazonHelper = $amazonHelper;
        $this->templateShippingRepository = $templateShippingRepository;
    }

    public function getDefaultData(): array
    {
        return [
            'title' => '',
            'account_id' => '',
            'marketplace_id' => '',
            'mode' => \Ess\M2ePro\Model\Amazon\Template\Shipping::MODE_AMAZON_TEMPLATE,
            'template_id' => '',
            'custom_attribute' => '',
        ];
    }

    /**
     * @param \Ess\M2ePro\Model\Amazon\Template\Shipping $model
     * @param array $rawData
     *
     * @return \Ess\M2ePro\Model\Amazon\Template\Shipping
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function build($model, array $rawData)
    {
        if (empty($rawData)) {
            return $model;
        }

        $this->model = $model;
        $this->rawData = $rawData;

        $preparedData = $this->prepareData();

        if ($this->model->isObjectNew()) {
            $this->create($preparedData);
        } else {
            $this->update($preparedData);
        }

        return $this->model;
    }

    /**
     * @return array
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    protected function prepareData(): array
    {
        $data = [];

        $keys = array_keys($this->getDefaultData());

        foreach ($keys as $key) {
            if (isset($this->rawData[$key])) {
                $data[$key] = $this->rawData[$key];
            }
        }

        if (isset($data['account_id'])) {
            $data['account_id'] = (int)$data['account_id'];
            $data['marketplace_id'] = $this->amazonHelper->getAccountMarketplace($data['account_id']);
        }

        if (isset($data['mode'])) {
            $data['mode'] = (int)$data['mode'];
            if ($data['mode'] === \Ess\M2ePro\Model\Amazon\Template\Shipping::MODE_AMAZON_TEMPLATE) {
                $data['custom_attribute'] = '';
            }

            if ($data['mode'] === \Ess\M2ePro\Model\Amazon\Template\Shipping::MODE_MAGENTO_ATTRIBUTE) {
                $data['template_id'] = '';
            }
        }

        return $data;
    }

    private function create(array $data): void
    {
        $this->model->create(
            (string)$data['title'],
            (int)$data['account_id'],
            (int)$data['marketplace_id'],
            (int)$data['mode'],
            (string)$data['template_id'],
            (string)$data['custom_attribute'],
        );
        $this->templateShippingRepository->create($this->model);
    }

    private function update(array $data)
    {
        $this->model->update(
            (string)$data['title'],
            (int)$data['mode'],
            (string)$data['template_id'],
            (string)$data['custom_attribute']
        );

        $this->templateShippingRepository->update($this->model);
    }
}
