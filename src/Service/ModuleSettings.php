<?php

namespace UnitM\Solute\Service;

use OxidEsales\Eshop\Core\Registry;

final class ModuleSettings implements ModuleSettingsInterface
{
    private const UMSOLUTE_LOCAL = 'Local';
    private const UMSOULTE_STAGEING = 'Staging';
    private const UMSOLUTE_PRODUCTION = 'Production';

    public const UMSOULTE_IS_DEBUG_MODE = 'umsoluteIsDebugMode';
    public const UMSOLUTE_COUNT_EVENT_ENTRIES = 'umsoluteCountEventEntries';
    public const UMSOLUTE_COUNT_ARTICLE_LIST = 'umsoluteCountArticleList';
    public const UMSOLUTE_API_REQUEST_LIMIT = 'umsoluteApiReqestLimit';
    public const UMSOLUTE_API_ENDPOINT_SELECTION = 'umsoluteRestapiEndpointSelection';
    public const UMSOLUTE_STAGE_API_BEARERTOKEN = 'umsoluteRestapiStagingBearerToken';
    public const UMSOLUTE_STAGE_API_ENDPOINT = 'umsoluteRestapiStagingEndpoint';
    public const UMSOLUTE_STAGE_API_SHOP_ID = 'umsoluteRestapiStagingShopId';
    public const UMSOLUTE_STAGE_API_FEED_ID = 'umsoluteRestapiStagingFeedId';
    public const UMSOLUTE_PRODUCTION_API_BEARERTOKEN = 'umsoluteRestapiProductionBearerToken';
    public const UMSOLUTE_PRODUCTION_API_ENDPOINT = 'umsoluteRestapiProductionEndpoint';
    public const UMSOLUTE_PRODUCTION_API_SHOP_ID = 'umsoluteRestapiProductionShopId';
    public const UMSOLUTE_PRODUCTION_API_FEED_ID = 'umsoluteRestapiProductionFeedId';
    public const UMSOLUTE_TRACKING_LANDING_ENDPOINT = 'umsoluteApiTrackingLandingEndpoint';
    public const UMSOLUTE_TRACKING_CONVERSION_ENDPOINT = 'umsoluteApiTrackingConversionEndpoint';

    /**
     * @return bool
     */
    public function getDebugMode(): bool
    {
        return $this->getBoolSettingValue(self::UMSOULTE_IS_DEBUG_MODE);
    }

    /**
     * @return int
     */
    public function getCountEventEntries(): int
    {
        return $this->getIntegerSettingValue(self::UMSOLUTE_COUNT_EVENT_ENTRIES);
    }

    /**
     * @return int
     */
    public function getCountArticleList(): int
    {
        $count = $this->getIntegerSettingValue(self::UMSOLUTE_COUNT_ARTICLE_LIST);
        if ($count === 0) {
            return 20;
        }
        return $count;
    }

    /**
     * @return int
     */
    public function getApiRequestLimit(): int
    {
        $limit = $this->getIntegerSettingValue(self::UMSOLUTE_API_REQUEST_LIMIT);
        if ($limit === 0) {
            return 500;
        }
        return $limit;
    }

    /**
     * @return string
     */
    public function getApiEndpointSelection(): string
    {
        return $this->getStringSettingValue(self::UMSOLUTE_API_ENDPOINT_SELECTION);
    }

    /**
     * @param string $system
     * @return string
     */
    public function getApiBaerertoken(string $system): string
    {
        switch ($system) {
            case self::UMSOULTE_STAGEING:
                return $this->getStringSettingValue(self::UMSOLUTE_STAGE_API_BEARERTOKEN);
            case self::UMSOLUTE_PRODUCTION:
                return $this->getStringSettingValue(self::UMSOLUTE_PRODUCTION_API_BEARERTOKEN);
            default:
                return '';
        }
    }

    /**
     * @param string $system
     * @return string
     */
    public function getApiEndpoint(string $system): string
    {
        switch ($system) {
            case self::UMSOULTE_STAGEING:
                return $this->getStringSettingValue(self::UMSOLUTE_STAGE_API_ENDPOINT);
            case self::UMSOLUTE_PRODUCTION:
                return $this->getStringSettingValue(self::UMSOLUTE_PRODUCTION_API_ENDPOINT);
            default:
                return '';
        }
    }

    /**
     * @param string $system
     * @return int
     */
    public function getApiShopId(string $system): int
    {
        switch ($system) {
            case self::UMSOULTE_STAGEING:
                return $this->getIntegerSettingValue(self::UMSOLUTE_STAGE_API_SHOP_ID);
            case self::UMSOLUTE_PRODUCTION:
                return $this->getIntegerSettingValue(self::UMSOLUTE_PRODUCTION_API_SHOP_ID);
            default:
                return 0;
        }
    }

    /**
     * @param string $system
     * @return int
     */
    public function getApiFeedId(string $system): int
    {
        switch ($system) {
            case self::UMSOULTE_STAGEING:
                return $this->getIntegerSettingValue(self::UMSOLUTE_STAGE_API_FEED_ID);
            case self::UMSOLUTE_PRODUCTION:
                return $this->getIntegerSettingValue(self::UMSOLUTE_PRODUCTION_API_FEED_ID);
            default:
                return 0;
        }
    }

    /**
     * @return string
     */
    public function getTrackingLandingEndpoint(): string
    {
        return $this->getStringSettingValue(self::UMSOLUTE_TRACKING_LANDING_ENDPOINT);
    }

    /**
     * @return string
     */
    public function getTrackingConversionEndpoint(): string
    {
        return $this->getStringSettingValue(self::UMSOLUTE_TRACKING_CONVERSION_ENDPOINT);
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getStringSettingValue(string $key): string
    {
        return (string) Registry::getConfig()->getConfigParam($key) ?: '';
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function getBoolSettingValue(string $key): bool
    {
        return (bool) Registry::getConfig()->getConfigParam($key) ?: true;
    }

    /**
     * @param string $key
     * @return int
     */
    protected function getIntegerSettingValue(string $key): int
    {
        return (int) Registry::getConfig()->getConfigParam($key) ?: 0;
    }
}
