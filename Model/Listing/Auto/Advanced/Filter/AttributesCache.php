<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Listing\Auto\Advanced\Filter;

class AttributesCache
{
    private const CACHE_KEY = 'auto_advanced_filter_attributes';
    private const CACHE_LIFETIME = 15 * 60;

    private \Ess\M2ePro\Helper\Data\Cache\Permanent $cache;

    public function __construct(\Ess\M2ePro\Helper\Data\Cache\Permanent $cache)
    {
        $this->cache = $cache;
    }

    public function has(): bool
    {
        return $this->cache->getValue(self::CACHE_KEY) !== null;
    }

    public function set(array $attributes): void
    {
        $this->cache->setValue(self::CACHE_KEY, $attributes, [], self::CACHE_LIFETIME);
    }

    public function get(): array
    {
        return $this->cache->getValue(self::CACHE_KEY) ?? [];
    }

    public function reset(): void
    {
        $this->cache->removeValue(self::CACHE_KEY);
    }
}
