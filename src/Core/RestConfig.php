<?php

namespace UnitM\Solute\Core;

interface RestConfig
{
    public const DOC_ADDITIONAL_IMAGES      = 'additionalImageLinks';
    public const DOC_ADULT                  = 'adult';
    public const DOC_BRAND                  = 'brand';
    public const DOC_COLOR                  = 'color';
    public const DOC_CONTENT_LANGUAGE       = 'contentLanguage';
    public const DOC_COUNTRY                = 'country';
    public const DOC_DESCRIPTION            = 'description';
    public const DOC_ENERGY_CLASS           = 'energyEfficiencyClass';
    public const DOC_ENERGY_CLASS_MIN       = 'minEnergyEfficiencyClass';
    public const DOC_ENERGY_CLASS_MAX       = 'maxEnergyEfficiencyClass';
    public const DOC_IMAGELINK              = 'imageLink';
    public const DOC_LINK                   = 'link';
    public const DOC_NAME                   = 'name';
    public const DOC_MAX_HANDLING_TIME      = 'maxHandlingTime';
    public const DOC_MAX_TRANSIT_TIME       = 'maxTransitionTime';
    public const DOC_MIN_HANDLING_TIME      = 'minHandlingTime';
    public const DOC_MIN_TRANSIT_TIME       = 'minTransitTime';

    public const DOC_TARGET_COUNTRY         = 'targetCountry';
    public const DOC_TITLE                  = 'title';

    public const DOC_GOOGLE_CATEGORY_ID     = 'googleProductCategory';
    public const DOC_GTIN                   = 'gtin';
    public const DOC_MPN                    = 'mpn';
    public const DOC_PRICE                  = 'price';
    public const DOC_SALE_PRICE             = 'salePrice';
    public const DOC_SALE_EFFECTIVE_DATE    = 'salePriceEffectiveDate';
    public const DOC_UNIT_PRICE             = 'unitPricingMeasure';
    public const DOC_UNIT_BASE_PRICE        = 'unitPricingBaseMeasure';
    public const DOC_SHIPPING               = 'shipping';
    public const DOC_ITEMGROUPID            = 'itemGroupId';
    public const DOC_MATERIAL               = 'material';
    public const DOC_PATTERN                = 'pattern';
    public const DOC_SIZES                  = 'sizes';
    public const DOC_HEIGHT                 = 'productHeight';
    public const DOC_LENGTH                 = 'productLength';
    public const DOC_WIDTH                  = 'productWidth';
    public const DOC_WEIGHT                 = 'productWeight';
    public const DOC_CUSTOM_ATTRIBUTES      = 'customAttributes';
    public const DOC_INSTALLMENT            = 'installment';
    public const DOC_MONTHS                 = 'months';
    public const DOC_AMOUNT                 = 'amount';
    public const DOC_MULTIPACK              = 'multipack';
    public const DOC_IS_BUNDLE              = 'isBundle';
    public const DOC_PRODUCT_TYPE           = 'productTypes';
    public const DOC_AGE_GROUP              = 'ageGroup';
    public const DOC_AVAILABILITY           = 'availability';
    public const DOC_CONDITION              = 'condition';
    public const DOC_GENDER                 = 'gender';
    public const DOC_SIZE_SYSTEM            = 'sizeSystem';

    public const DOC_SUBSCRIPTIONCOST       = 'subscriptionCost';
    public const DOC_SUBSCRIPTION_PERIOD    = 'period';
    public const DOC_SUBSCRIPTION_PERIOD_LENGTH = 'periodLength';
    public const DOC_SUBSCRIPTION_AMOUNT        = 'amount';

    public const DOC_VALUE                  = 'value';
    public const DOC_CURRENCY               = 'currency';
    public const DOC_UNIT                   = 'unit';

    public const SERVER_STATE_422           = 422;
    public const SERVER_STATE_403           = 403;
    public const SERVER_STATE_404           = 404;
}
