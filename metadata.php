<?php

use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\ViewConfig;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\AttributeList;

$sMetadataVersion = '2.1';

$aModule = [
    'id'          => 'oxid_solute',
    'title'       => [
        'de' => 'billiger.de Conversion Tracking & Feed Plugin',
        'en' => 'billiger.de Conversion Tracking & Feed Plugin'
        ],
    'description' => [
        'de' => '',
        'en' => '',
        ],
    'thumbnail'   => 'admin/billiger_Logo_schwarz.jpg',
    'version'     => '1.0.0',
    'author'      => 'Unit M GmbH',
    'email'       => 'info@unit-m.de',
    'url'         => 'https://www.unit-m.de/',
    'controllers' => [
        'ValidatorController'       => UnitM\Solute\Controller\Admin\ValidatorController::class,
        'ShopMappingController'     => UnitM\Solute\Controller\Admin\ShopMappingController::class,
        'ArticleMappingController'  => UnitM\Solute\Controller\Admin\ArticleMappingController::class,
        'CategoryMappingController' => UnitM\Solute\Controller\Admin\CategoryMappingController::class,
        'FieldDefinitionController' => UnitM\Solute\Controller\Admin\FieldDefinitionController::class,

        // Ajax-Controller
        'SoluteAjaxArticleList'         => UnitM\Solute\Controller\Ajax\SoluteAjaxArticleList::class,
        'SoluteAjaxArticleMapping'      => UnitM\Solute\Controller\Ajax\SoluteAjaxArticleMapping::class,
        'SoluteAjaxCategoryMapping'     => UnitM\Solute\Controller\Ajax\SoluteAjaxCategoryMapping::class,
        'SoluteAjaxCheckArticleOnApi'   => UnitM\Solute\Controller\Ajax\SoluteAjaxCheckArticleOnApi::class,
        'SoluteAjaxDeleteArticleOnApi'  => UnitM\Solute\Controller\Ajax\SoluteAjaxDeleteArticleOnApi::class,
        'SoluteAjaxDeleteDefinition'    => UnitM\Solute\Controller\Ajax\SoluteAjaxDeleteDefinition::class,
        'SoluteAjaxEditDefinition'      => UnitM\Solute\Controller\Ajax\SoluteAjaxEditDefinition::class,
        'SoluteAjaxSaveDefinition'      => UnitM\Solute\Controller\Ajax\SoluteAjaxSaveDefinition::class,
        'SoluteAjaxSaveMapping'         => UnitM\Solute\Controller\Ajax\SoluteAjaxSaveMapping::class,
        'SoluteAjaxSendToApi'           => UnitM\Solute\Controller\Ajax\SoluteAjaxSendToApi::class,
        'SoluteAjaxShopMapping'         => UnitM\Solute\Controller\Ajax\SoluteAjaxShopMapping::class,
        'SoluteAjaxTableFieldList'      => UnitM\Solute\Controller\Ajax\SoluteAjaxTableFieldList::class,
        'SoluteAjaxValidateArticle'     => UnitM\Solute\Controller\Ajax\SoluteAjaxValidateArticle::class,
    ],
    'extend'      => [
        Config::class        => UnitM\Solute\Core\Config::class,
        ViewConfig::class    => UnitM\Solute\Core\ViewConfig::class,
        Order::class         => UnitM\Solute\Model\Order::class,
        AttributeList::class => UnitM\Solute\Model\AttributeList::class,
    ],
    'events'      => [
        'onActivate'   => '\UnitM\Solute\Core\Events::onActivate',
        'onDeactivate' => '\UnitM\Solute\Core\Events::onDeactivate',
    ],
    'settings' => [
        [
            'group' => 'umsoluteCommonSettings',
            'name'  => 'umsoluteIsDebugMode',
            'type'  => 'bool',
            'value' => 'true'
        ],
        [
            'group' => 'umsoluteCommonSettings',
            'name'  => 'umsoluteCountEventEntries',
            'type'  => 'num',
            'value' => 8
        ],
        [
            'group' => 'umsoluteCommonSettings',
            'name'  => 'umsoluteCountArticleList',
            'type'  => 'num',
            'value' => 20
        ],
        [
            'group' => 'umsoluteCommonSettings',
            'name'  => 'umsoluteApiReqestLimit',
            'type'  => 'num',
            'value' => 500
        ],
        [
            'group' => 'umsoluteCommonSettings',
            'name'  => 'umsoluteRestapiEndpointSelection',
            'type'  => 'select',
            'value' => 'Staging',
            'constraints' => 'Local|Staging|Production'
        ],
        [
            'group' => 'umsoluteRestapiSettingsStaging',
            'name'  => 'umsoluteRestapiStagingShopId',
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => 'umsoluteRestapiSettingsStaging',
            'name'  => 'umsoluteRestapiStagingFeedId',
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => 'umsoluteRestapiSettingsStaging',
            'name'  => 'umsoluteRestapiStagingBearerToken',
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => 'umsoluteRestapiSettingsStaging',
            'name'  => 'umsoluteRestapiStagingEndpoint',
            'type'  => 'str',
            'value' => 'https://ingestapi-staging.solutenetwork.com/ingest/v1/documents/',
        ],
        [
            'group' => 'umsoluteRestapiSettingsProduction',
            'name'  => 'umsoluteRestapiProductionShopId',
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => 'umsoluteRestapiSettingsProduction',
            'name'  => 'umsoluteRestapiProductionFeedId',
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => 'umsoluteRestapiSettingsProduction',
            'name'  => 'umsoluteRestapiProductionBearerToken',
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => 'umsoluteRestapiSettingsProduction',
            'name'  => 'umsoluteRestapiProductionEndpoint',
            'type'  => 'str',
            'value' => 'https://api.solutenetwork.com/ingest/v1/documents/',
        ],
        [
            'group' => 'umsoluteTrackingSettings',
            'name'  => 'umsoluteApiTrackingLandingEndpoint',
            'type'  => 'str',
            'value' => 'https://cmodul.solutenetwork.com/landing'
        ],
        [
            'group' => 'umsoluteTrackingSettings',
            'name'  => 'umsoluteApiTrackingConversionEndpoint',
            'type'  => 'str',
            'value' => 'https://cmodul.solutenetwork.com/conversion'
        ],
    ],
];
