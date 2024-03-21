<?php

namespace UnitM\Solute\Core;

interface SoluteConfig
{
    public const UM_MODULE_ID                   = 'oxid_solute';
    public const UM_USERAGENT                   = 'Billiger.de-Plugin';

    // solute tables
    public const UM_TABLE_ATTRIBUTE_GROUPS      = 'um_solute_attribute_groups';
    public const UM_TABLE_ATTRIBUTE_MAPPING     = 'um_solute_attribute_mapping';
    public const UM_TABLE_ATTRIBUTE_SCHEMA      = 'um_solute_attribute_schema';
    public const UM_TABLE_FIELD_SELECTION       = 'um_solute_field_selection';
    public const UM_TABLE_LOG                   = 'um_solute_log';
    public const UM_TABLE_HASH                  = 'um_solute_hash';

    // oxid tables
    public const OX_TABLE_ATTRIBUTE             = 'oxattribute';
    public const OX_TABLE_ARTICLE_EXTEND        = 'oxartextends';
    public const OX_TABLE_ARTICLE               = 'oxarticles';
    public const OX_TABLE_CATEGORY              = 'oxcategories';
    public const OX_TABLE_MANUFACTURER          = 'oxmanufacturers';
    public const OX_TABLE_OBJECT2ATTRIBUTE      = 'oxobject2attribute';
    public const OX_TABLE_OBJECT2CATEGORY       = 'oxobject2category';
    public const OX_TABLE_ORDER                 = 'oxorder';
    public const OX_TABLE_SHOPS                 = 'oxshops';

    // columns
    public const UM_COL_ATTRIBUTE_ID            = 'UM_ATTRIBUTE_ID';
    public const UM_COL_ATTRIBUTE_GROUP_ID      = 'UM_ATTRIBUTE_GROUP_ID';
    public const UM_COL_DATA_RESSOURCE          = 'UM_DATA_RESSOURCE';
    public const UM_COL_DATA_RESSOURCE_ID       = 'UM_DATA_RESSOURCE_ID';
    public const UM_COL_DESCRIPTION             = 'UM_DESCRIPTION';
    public const UM_COL_FIELD_TITLE             = 'UM_FIELD_TITLE';
    public const UM_COL_MANUAL_VALUE            = 'UM_MANUAL_VALUE';
    public const UM_COL_OBJECT_ID               = 'UM_OBJECT_ID';
    public const UM_COL_OID                     = 'UM_SOLUTE_OID';
    public const UM_COL_PRIMARY_NAME            = 'UM_NAME_PRIMARY';
    public const UM_COL_REQUIRED                = 'UM_REQUIRED';
    public const UM_COL_SECONDARY_NAME          = 'UM_NAME_ALTERNATIVE';
    public const UM_COL_SHOP_ID                 = 'UM_SHOP_ID';
    public const UM_COL_LOG                     = 'UM_LOG';
    public const UM_COL_FEED_HASH               = 'UM_FEED_HASH';
    public const UM_COL_SORT                    = 'UM_SORT';
    public const UM_COL_THIRD_NAME              = 'UM_NAME_THIRD';
    public const UM_COL_TITLE                   = 'UM_TITLE';
    public const UM_COL_VALIDATOR               = 'UM_VALIDATOR';
    public const UM_COL_VALID_VALUES            = 'UM_VALID_VALUES';
    public const UM_COL_VISIBILITY              = 'UM_SOLUTE_VISIBILITY';

    // oxid columns
    public const OX_COL_ACTIVE                  = 'OXACTIVE';
    public const OX_COL_ARTICLENUMBER           = 'OXARTNUM';
    public const OX_COL_ATTRIBUTE_ID            = 'OXATTRID';
    public const OX_COL_CATEGORY_ID             = 'OXCATNID';
    public const OX_COL_DISPLAY_IN_BASKET       = 'OXDISPLAYINBASKET';
    public const OX_COL_EAN                     = 'OXEAN';
    public const OX_COL_HEIGHT                  = 'OXHEIGHT';
    public const OX_COL_HIDDEN                  = 'OXHIDDEN';
    public const OX_COL_ID                      = 'OXID';
    public const OX_COL_LENGTH                  = 'OXLENGTH';
    public const OX_COL_LONGEDESCRIPTION        = 'OXLONGDESC';
    public const OX_COL_MANUFACTURER_ID         = 'OXMANUFACTURERID';
    public const OX_COL_MPN                     = 'OXMPN';
    public const OX_COL_OBJECT_ID               = 'OXOBJECTID';
    public const OX_COL_PICTURE_1               = 'OXPIC1';
    public const OX_COL_PICTURE_2               = 'OXPIC2';
    public const OX_COL_PICTURE_3               = 'OXPIC3';
    public const OX_COL_PICTURE_4               = 'OXPIC4';
    public const OX_COL_PARENTID                = 'OXPARENTID';
    public const OX_COL_PRICE                   = 'OXPRICE';
    public const OX_COL_PRICE_A                 = 'OXPRICEA';
    public const OX_COL_PRICE_B                 = 'OXPRICEB';
    public const OX_COL_PRICE_C                 = 'OXPRICEC';
    public const OX_COL_PRICE_RETAIL            = 'OXTPRICE';
    public const OX_COL_SHOPID                  = 'OXSHOPID';
    public const OX_COL_SHORTDESCRIPTION        = 'OXSHORTDESC';
    public const OX_COL_STOCK                   = 'OXSTOCK';
    public const OX_COL_TIMESTAMP               = 'OXTIMESTAMP';
    public const OX_COL_TITLE                   = 'OXTITLE';
    public const OX_COL_VALUE                   = 'OXVALUE';
    public const OX_COL_WEIGHT                  = 'OXWEIGHT';
    public const OX_COL_WIDTH                   = 'OXWIDTH';

    // types
    public const UM_TYPE_BOOL                   = 'BOOLEAN';
    public const UM_TYPE_DATE                   = 'DATE';
    public const UM_TYPE_FLOAT                  = 'FLOAT';
    public const UM_TYPE_JSON                   = 'JSON';
    public const UM_TYPE_INT                    = 'INT';
    public const UM_TYPE_VARCHAR                = 'VARCHAR';
    public const UM_TYPE_VARCHAR_NOHTML         = 'VARCHAR_NOHTML';

    // ids
    public const UM_ID_ATTRIBUTE_GROUP          = 'attribite_group';
    public const UM_ID_ATTRIBUTE_ID             = 'attribite_id';
    public const UM_ID_DATA_RESSOURCE           = 'data';
    public const UM_ID_DATA_RESSOURCE_ID        = 'data_ressource_id';
    public const UM_ID_DESCRIPTION              = 'description';
    public const UM_ID_FIX_VALUE                = 'fix_values';
    public const UM_ID_GROUP_NAME               = 'group_name';
    public const UM_ID_PRIMARY_NAME             = 'primary_name';
    public const UM_ID_REQUIRED                 = 'required';
    public const UM_ID_SECONDARY_NAME           = 'secondary_name';
    public const UM_ID_THIRD_NAME               = 'third_name';
    public const UM_ID_TITLE                    = 'title';
    public const UM_ID_VALIDATOR                = 'validator';
    public const UM_ID_VALUE_MAX                = 'value_max';
    public const UM_ID_VALUE_MIN                = 'value_min';
    public const UM_ID_VALUE_STEP               = 'value_step';

    // validator ids
    public const UM_ID_ALTERNATIVE_TITLE        = 'alternative_title';
    public const UM_ID_MAX_LENGTH               = 'max-length';
    public const UM_ID_REGEX                    = 'regex';
    public const UM_ID_SCHEMA                   = 'schema';
    public const UM_ID_TYPE                     = 'type';

    // field selection data ressource types
    public const UM_DR_TYPE_ATTRIBUTE           = 'attribute';
    public const UM_DR_TYPE_FIELD               = 'field';
    public const UM_DR_TYPE_GENERATEDFIELD      = 'generatedfield';
    public const UM_DR_TYPE_RELATIONFIELD       = 'relationfield';

    // field selection data ressource values
    public const UM_DR_CONVERT                  = 'convert';
    public const UM_DR_GENERATED                = 'generated';
    public const UM_DR_LABEL                    = 'label';
    public const UM_DR_PRIMARY_FIELD            = 'primaryfield';
    public const UM_DR_PRIMARY_ID               = 'primaryid';
    public const UM_DR_PRIMARY_TABLE            = 'primarytable';
    public const UM_DR_RELATION_FIELD           = 'relationfield';
    public const UM_DR_RELATION_ID              = 'relationid';
    public const UM_DR_RELATION_TABLE           = 'relationtable';

    // data converter
    public const UM_CONV_MYSQL2ISO8601          = 'mysql2iso8601';
    public const UM_CONV_ERASE_SID              = 'eraseSid';
    public const UM_CONV_ADD_WEIGHT_UNIT        = 'addWeightUnit';

    // data generator
    public const UM_GEN_AVAILABILITY            = 'availability';
    public const UM_GEN_BREADCRUMB              = 'breadcrumb';
    public const UM_GEN_DELIVERY_COSTS          = 'deliveryCosts';
    public const UM_GEN_DELIVERY_COSTS_AT       = 'deliveryCostsAT';
    public const UM_GEN_DELIVERY_TIME           = 'deliveryTime';
    public const UM_GEN_IMAGE_URL               = 'image_url';
    public const UM_GEN_PRODUCT_URL             = 'product_url';


    // attribute groups (sorted by appearance)
    public const UM_ATTR_GROUP_COMMON           = 'Allgemein';
    public const UM_ATTR_GROUP_PRICE            = 'Preise';
    public const UM_ATTR_GROUP_PERIOD_PRICE     = 'Laufzeitabhängige Kosten';
    public const UM_ATTR_GROUP_DELIVERY         = 'Verfügbarkeit und Lieferzeit';
    public const UM_ATTR_GROUP_CATEGORY         = 'Kategorie';
    public const UM_ATTR_GROUP_VOUCHER          = 'Promotionen und Gutscheine';
    public const UM_ATTR_GROUP_ENERGY           = 'Energieeffizienz (EU-Label)';
    public const UM_ATTR_GROUP_DESCRIPTION      = 'Detaillierte Artikelbeschreibung';
    public const UM_ATTR_GROUP_MESSUREMENT      = 'Abmessungen';
    public const UM_ATTR_GROUP_MEDICAL          = 'Relevante Attribute für Apotheken';
    public const UM_ATTR_GROUP_WHEELS           = 'Relevante Attribute für Reifen (EU-Label)';
    public const UM_ATTR_GROUP_CAR              = 'Relevante Attribute für Autoteile';
    public const UM_ATTR_GROUP_EYELENS          = 'Relevante Attribute für Kontaktlinsen';

    // solute attributes
    public const UM_ATTR_MODIFIED_DATE          = 'modified_date';
    public const UM_ATTR_AID                    = 'aid';
    public const UM_ATTR_ASIN                   = 'asin';
    public const UM_ATTR_GTIN                   = 'GTIN';
    public const UM_ATTR_MPN                    = 'mpn';
    public const UM_ATTR_NAME                   = 'name';
    public const UM_ATTR_BRAND                  = 'brand';
    public const UM_ATTR_DESCRIPTION            = 'desc';
    public const UM_ATTR_LINK                   = 'link';
    public const UM_ATTR_TARGET_URL             = 'target_url';
    public const UM_ATTR_IMAGES                 = 'images';
    public const UM_ATTR_PRICE                  = 'price';
    public const UM_ATTR_OLD_PRICE              = 'old_price';
    public const UM_ATTR_VOUCHER_PRICE          = 'voucher_price';
    public const UM_ATTR_SALE_PRICE             = 'sale_price';
    public const UM_ATTR_SALE_PRICE_EFFECTIVE   = 'sale_price_effective_date';
    public const UM_ATTR_SALE_PRICE_PUBLISHING  = 'sale_price_publishing_date';
    public const UM_ATTR_PPU                    = 'ppu';
    public const UM_ATTR_PRICING_MEASURE        = 'unit_pricing_measure';
    public const UM_ATTR_PRICING_BASE_MEASURE   = 'unit_pricing_base_measure';
    public const UM_ATTR_SUBSCRIPTION_PERIOD    = 'subscription_cost_period';
    public const UM_ATTR_SUBSCRIPTION_LENGTH    = 'subscription_cost_period_length';
    public const UM_ATTR_SUBSCRIPTION_VALUE     = 'subscription_cost_amount_value';
    public const UM_ATTR_INSTALLMENT            = 'installment';
    public const UM_ATTR_DLV_COST               = 'dlv_cost';
    public const UM_ATTR_DLV_COST_AT            = 'dlv_cost_AT';
    public const UM_ATTR_DLV_TIME               = 'dlv_time';
    public const UM_ATTR_AVAILABILITY           = 'availability';
    public const UM_ATTR_AVAILABILITY_DATE      = 'availability_date';
    public const UM_ATTR_STOCK_QUANTITY         = 'stock_quantity';
    public const UM_ATTR_SHOP_CAT               = 'shop_cat';
    public const UM_ATTR_GOOGLE_PRODUCT_CAT     = 'google_product_category';
    public const UM_ATTR_GOOGLE_PRODUCT_CAT_ID  = 'google_product_category_ID';
    public const UM_ATTR_PROMO_TEXT             = 'promo_text';
    public const UM_ATTR_VOUCHER_TEXT           = 'voucher_text';
    public const UM_ATTR_SPECIAL                = 'special';
    public const UM_ATTR_ENERGY_CLASS           = 'energy_efficiency_class';
    public const UM_ATTR_ENERGY_CLASS_MIN       = 'min_energy_efficiency_class';
    public const UM_ATTR_ENERGY_CLASS_MAX       = 'max_energy_efficiency_class';
    public const UM_ATTR_ENERGY_CLASS_ILLUMIN   = 'energy_efficiency_class_illuminant';
    public const UM_ATTR_CONDITION              = 'condition';
    public const UM_ATTR_ITEM_GROUP_ID          = 'item_group_id';
    public const UM_ATTR_COMPATIBLE_PRODUCT     = 'compatible_products';
    public const UM_ATTR_QUANTITY_NUMBER        = 'quantity_number';
    public const UM_ATTR_IS_BUNDLE              = 'is_bundle';
    public const UM_ATTR_SIZE                   = 'size';
    public const UM_ATTR_SIZE_SYSTEM            = 'size_system';
    public const UM_ATTR_COLOR                  = 'color';
    public const UM_ATTR_GENDER                 = 'gender';
    public const UM_ATTR_MATERIAL               = 'material';
    public const UM_ATTR_PATTERN                = 'pattern';
    public const UM_ATTR_AGE_RATING            = 'age_rating';
    public const UM_ATTR_AGE_GROUP              = 'age_group';
    public const UM_ATTR_ADULT                  = 'adult';
    public const UM_ATTR_PLATFORM               = 'platform';
    public const UM_ATTR_PRODUCT_TYPE           = 'product_type';
    public const UM_ATTR_STYLE                  = 'style';
    public const UM_ATTR_PROPERTIES             = 'properties';
    public const UM_ATTR_FUNCTIONS              = 'functions';
    public const UM_ATTR_EQUIPMENT              = 'equipment';
    public const UM_ATTR_HEIGHT                 = 'height';
    public const UM_ATTR_WIDTH                  = 'width';
    public const UM_ATTR_LENGTH                 = 'length';
    public const UM_ATTR_DEPTH                  = 'depth';
    public const UM_ATTR_PZN                    = 'PZN';
    public const UM_ATTR_WET_GRIP               = 'wet_grip';
    public const UM_ATTR_FUEL_EFFICIENCY        = 'fuel_efficiency';
    public const UM_ATTR_ROLLING_NOISE          = 'external_rolling_noise';
    public const UM_ATTR_ROLLING_NOISE_CLASS    = 'rolling_noise_class';
    public const UM_ATTR_HSN_TSN                = 'hsn_tsn';
    public const UM_ATTR_SPH                    = 'sph_pwr';
    public const UM_ATTR_DIA                    = 'dia';
    public const UM_ATTR_BC                     = 'bc';
    public const UM_ATTR_CYL                    = 'cyl';
    public const UM_ATTR_AXIS                   = 'axis';
    public const UM_ATTR_ADD                    = 'add';
    public const UM_ATTR_WEIGHT                 = 'weight';

    // valid oxid delivery time units
    public const UM_DELIVERY_TIME_UNIT_DAY      = 'DAY';
    public const UM_DELIVERY_TIME_UNIT_WEEK     = 'WEEK';
    public const UM_DELIVERY_TIME_UNIT_MONTH    = 'MONTH';

    // titles
    public const UM_TITLE_MODIFIED_DATE         = 'Datum der letzten Änderung';
    public const UM_TITLE_AID                   = 'Artikelnummer';
    public const UM_TITLE_ASIN                  = 'Amazon Artikelnummer';
    public const UM_TITLE_GTIN                  = 'EAN (GTIN)';
    public const UM_TITLE_MPN                   = 'Hersteller-Artikelnummer';
    public const UM_TITLE_NAME                  = 'Artikelbezeichnung';
    public const UM_TITLE_BRAND                 = 'Markenname';
    public const UM_TITLE_DESCRIPTION           = 'Artikelbeschreibung';
    public const UM_TITLE_LINK                  = 'Produktlink';
    public const UM_TITLE_IMAGES                = 'Produktbilder (Links)';
    public const UM_TITLE_PRICE                 = 'Preis';
    public const UM_TITLE_OLD_PRICE             = 'Streichpreis';
    public const UM_TITLE_VOUCHER_PRICE         = 'Voucher Preis';
    public const UM_TITLE_SALE_PRICE            = 'Sonderangebotspreis';
    public const UM_TITLE_SALE_PRICE_EFFECTIVE  = 'Sonderangebotszeitraum';
    public const UM_TITLE_SALE_PRICE_PUBLISHING = 'Sonderangebotsveröffentlichungdatum';
    public const UM_TITLE_PPU                   = 'Preis je Mengeneinheit';
    public const UM_TITLE_PRICING_MEASURE       = 'Maß für den Grundpreis';
    public const UM_TITLE_PRICING_BASE_MEASURE  = 'Maß für Grundpreis bezogen auf eine Normgröße';
    public const UM_TITLE_SUBSCRIPTION_PERIOD   = 'Abrechnugsperiode';
    public const UM_TITLE_SUBSCRIPTION_LENGTH   = 'Abolaufzeit';
    public const UM_TITLE_SUBSCRIPTION_VALUE    = 'Monatlicher Betrag';
    public const UM_TITLE_INSTALLMENT           = 'monatliche Rate und Laufzeit';
    public const UM_TITLE_DLV_COST              = 'Versandkosten in das Zielland';
    public const UM_TITLE_DLV_COST_AT           = 'Versandkosten nach Österreich';
    public const UM_TITLE_DLV_TIME              = 'Lieferzeit';
    public const UM_TITLE_AVAILABILITY          = 'Verfügbarkeit';
    public const UM_TITLE_AVAILABILITY_DATE     = 'Datum, ab dem ein vorbestellter Artikel lieferbar ist';
    public const UM_TITLE_STOCK_QUANTITY        = 'Lagerbestand';
    public const UM_TITLE_SHOP_CAT              = 'Kategoriepfad im Shop';
    public const UM_TITLE_GOOGLE_PRODUCT_CAT    = 'Produktkategorie bei Google';
    public const UM_TITLE_GOOGLE_PRODUCT_CAT_ID = 'ID der Produktkategorie bei Google';
    public const UM_TITLE_PROMO_TEXT            = 'Werbetext';
    public const UM_TITLE_VOUCHER_TEXT          = 'Gutscheintext';
    public const UM_TITLE_SPECIAL               = 'Artikelkennzeichnung für fokussierte Bewerbung';
    public const UM_TITLE_ENERGY_CLASS          = 'Energieeffizienzklasse';
    public const UM_TITLE_ENERGY_CLASS_MIN      = 'Schlechteste Energieeffizienzklasse der Produktklasse';
    public const UM_TITLE_ENERGY_CLASS_MAX      = 'Beste Energieeffizienzklasse der Produktklasse';
    public const UM_TITLE_ENERGY_CLASS_ILLUMIN  = 'Energieeffizienzklasse des Leuchtmittels';
    public const UM_TITLE_CONDITION             = 'Zustand';
    public const UM_TITLE_ITEM_GROUP_ID         = 'Gruppen ID für Varianten';
    public const UM_TITLE_COMPATIBLE_PRODUCT    = 'Kompatible Artikel';
    public const UM_TITLE_QUANTITY_NUMBER       = 'Anzahl der identischen Artikel in einem Pack';
    public const UM_TITLE_IS_BUNDLE             = 'Artikel gehört zu einem Set';
    public const UM_TITLE_SIZE                  = 'Größe';
    public const UM_TITLE_SIZE_SYSTEM           = 'Größensystem';
    public const UM_TITLE_COLOR                 = 'Farbe';
    public const UM_TITLE_GENDER                = 'Geschlecht, für das der Artikel bestimmt ist';
    public const UM_TITLE_MATERIAL              = 'Material';
    public const UM_TITLE_PATTERN               = 'Muster / Design';
    public const UM_TITLE_AGE_RATING            = 'Altersfreigabe';
    public const UM_TITLE_AGE_GROUP             = 'Demographische Zielgruppe';
    public const UM_TITLE_ADULT                 = 'Nur für Erwachsene';
    public const UM_TITLE_PLATFORM              = 'Plattform';
    public const UM_TITLE_PRODUCT_TYPE          = 'Produkttyp';
    public const UM_TITLE_STYLE                 = 'Style';
    public const UM_TITLE_PROPERTIES            = 'Eigenschaften';
    public const UM_TITLE_FUNCTIONS             = 'Funktionen';
    public const UM_TITLE_EQUIPMENT             = 'Ausstattung';
    public const UM_TITLE_HEIGHT                = 'Höhe';
    public const UM_TITLE_WIDTH                 = 'Breite';
    public const UM_TITLE_LENGTH                = 'Länge';
    public const UM_TITLE_DEPTH                 = 'Tiefe';
    public const UM_TITLE_PZN                   = 'PZN';
    public const UM_TITLE_WET_GRIP              = 'Nasshaftung';
    public const UM_TITLE_FUEL_EFFICIENCY       = 'Kraftstoffeffizienz';
    public const UM_TITLE_ROLLING_NOISE         = 'Externes Rollgeräusch';
    public const UM_TITLE_ROLLING_NOISE_CLASS   = 'Rollgeräuschklasse';
    public const UM_TITLE_HSN_TSN               = 'HSN / TSN';
    public const UM_TITLE_SPH                   = 'Dioptrin/Sphäre';
    public const UM_TITLE_DIA                   = 'Durchmesser';
    public const UM_TITLE_BC                    = 'Basiskurve / Radius';
    public const UM_TITLE_CYL                   = 'Zylinder';
    public const UM_TITLE_AXIS                  = 'Achse';
    public const UM_TITLE_ADD                   = 'Nahzusatz';
    public const UM_TITLE_WEIGHT                = 'Artikelgewicht';

    // regex schemas
    public const UM_REGEX_URL                   = 'https:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)';

    // log level
    public const UM_LOG_INFO                    = 0;
    public const UM_LOG_ERROR                   = 1;

    // article log events
    public const UM_LOG_SHOP_VALIDATION         = 10;
    public const UM_LOG_API_CHECK               = 20;
    public const UM_LOG_API_COMMIT              = 21;
    public const UM_LOG_API_DELETE              = 22;
    public const UM_LOG_API_NOT_SEND            = 23;

    public const UM_LOG_ID_EVENT                = 'event';
    public const UM_LOG_ID_MESSAGE              = 'message';
    public const UM_LOG_ID_STATE                = 'state';

    // array keys in ajax context
    public const UM_AJAX_ARTILCEID              = 'articleId';
    public const UM_AJAX_CATEGORYID             = 'categoryId';
    public const UM_AJAX_FEED_HASH              = 'feedHash';

    // argv
    public const UM_ARGV_SHOPID                 = 'shopId';
    public const UM_ARGV_LANGUAGEID             = 'languageId';

    // others
    public const UM_UNIT_KG                     = 'kg';

    public const SOLUTE_CLICK_ID                = 'soluteclid';
    public const SOLUTE_TIMESTAMP               = 'solutetimestamp';
    public const SOLUTE_URL                     = 'soluteurl';
    public const SOLUTE_SESSION_TIMEOUT         = 60 * 60 * 24 * 30; // 30 days in seconds
    public const SOLUTE_LINK_DIVIDER            = ';';
    public const UM_TRANSLATION_NUMERUS_SINGULAR = 'SINGULAR';
    public const UM_TRANSLATION_NUMERUS_PLURAL  = 'PLURAL';
}