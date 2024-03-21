<?php

namespace UnitM\Solute\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use UnitM\Solute\Core\Module;
use UnitM\Solute\Service\ModuleSettingsInterface;

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

    /** @var ModuleSettingServiceInterface */
    private ModuleSettingServiceInterface $moduleSettingService;

    /**
     * @param ModuleSettingServiceInterface $moduleSettingService
     */
    public function __construct(
        ModuleSettingServiceInterface $moduleSettingService
    ) {
        $this->moduleSettingService = $moduleSettingService;
    }

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
        return $this->getIntegerSettingValue(self::UMSOLUTE_COUNT_ARTICLE_LIST);
    }

    /**
     * @return int
     */
    public function getApiRequestLimit(): int
    {
        return $this->getIntegerSettingValue(self::UMSOLUTE_API_REQUEST_LIMIT);
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
        return match ($system) {
            self::UMSOULTE_STAGEING => $this->getStringSettingValue(self::UMSOLUTE_STAGE_API_BEARERTOKEN),
            self::UMSOLUTE_PRODUCTION => $this->getStringSettingValue(self::UMSOLUTE_PRODUCTION_API_BEARERTOKEN),
            default => ''
        };
    }

    /**
     * @param string $system
     * @return string
     */
    public function getApiEndpoint(string $system): string
    {
        return match ($system) {
            self::UMSOULTE_STAGEING => $this->getStringSettingValue(self::UMSOLUTE_STAGE_API_ENDPOINT),
            self::UMSOLUTE_PRODUCTION => $this->getStringSettingValue(self::UMSOLUTE_PRODUCTION_API_ENDPOINT),
            default => ''
        };
    }

    /**
     * @param string $system
     * @return string
     */
    public function getApiShopId(string $system): string
    {
        return match ($system) {
            self::UMSOULTE_STAGEING => $this->getStringSettingValue(self::UMSOLUTE_STAGE_API_SHOP_ID),
            self::UMSOLUTE_PRODUCTION => $this->getStringSettingValue(self::UMSOLUTE_PRODUCTION_API_SHOP_ID),
            default => ''
        };
    }

    /**
     * @param string $system
     * @return string
     */
    public function getApiFeedId(string $system): string
    {
        return match ($system) {
            self::UMSOULTE_STAGEING => $this->getStringSettingValue(self::UMSOLUTE_STAGE_API_FEED_ID),
            self::UMSOLUTE_PRODUCTION => $this->getStringSettingValue(self::UMSOLUTE_PRODUCTION_API_FEED_ID),
            default => ''
        };
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
        return $this->moduleSettingService->getString(
            $key,
            Module::MODULE_ID
        )->trim()->toString();
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function getBoolSettingValue(string $key): bool
    {
        return $this->moduleSettingService->getBoolean(
            $key,
            Module::MODULE_ID
        );
    }

    /**
     * @param string $key
     * @return int
     */
    protected function getIntegerSettingValue(string $key): int
    {
        return $this->moduleSettingService->getInteger(
            $key,
            Module::MODULE_ID
        );
    }
}
