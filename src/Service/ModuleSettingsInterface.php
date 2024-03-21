<?php

namespace UnitM\Solute\Service;

interface ModuleSettingsInterface
{
    public function getDebugMode(): bool;
    public function getCountEventEntries(): int;
    public function getCountArticleList(): int;
    public function getApiRequestLimit(): int;
    public function getApiEndpointSelection(): string;
    public function getApiBaerertoken(string $system): string;
    public function getApiEndpoint(string $system): string;
    public function getApiShopId(string $system): string;
    public function getApiFeedId(string $system): string;
    public function getTrackingLandingEndpoint(): string;
    public function getTrackingConversionEndpoint(): string;
}
