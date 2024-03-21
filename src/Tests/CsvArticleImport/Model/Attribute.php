<?php

namespace UnitM\Solute\Tests\CsvArticleImport\Model;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Field;
use UnitM\Solute\Core\SoluteConfig;
use UnitM\Solute\Tests\CsvArticleImport\Model\CsvConverter;
use Exception;

class Attribute implements SoluteConfig
{
    /**
     * @var string
     */
    private string $articleId;

    /**
     * @var CsvConverter
     */
    private CsvConverter $csvConverter;

    /**
     * @var array|string[]
     */
    private array $fieldList = [
        SoluteConfig::UM_ATTR_ASIN                  => '',
        SoluteConfig::UM_ATTR_VOUCHER_PRICE         => '',
        SoluteConfig::UM_ATTR_SALE_PRICE            => '',
        SoluteConfig::UM_ATTR_SALE_PRICE_EFFECTIVE  => '',
        SoluteConfig::UM_ATTR_SALE_PRICE_PUBLISHING => '',
        SoluteConfig::UM_ATTR_PPU                   => '',
        SoluteConfig::UM_ATTR_PRICING_MEASURE       => '',
        SoluteConfig::UM_ATTR_PRICING_BASE_MEASURE  => '',
        SoluteConfig::UM_ATTR_SUBSCRIPTION_PERIOD   => '',
        SoluteConfig::UM_ATTR_SUBSCRIPTION_LENGTH   => '',
        SoluteConfig::UM_ATTR_SUBSCRIPTION_VALUE    => '',
        SoluteConfig::UM_ATTR_INSTALLMENT           => '',
        SoluteConfig::UM_ATTR_DLV_COST              => '',
        SoluteConfig::UM_ATTR_DLV_COST_AT           => '',
        SoluteConfig::UM_ATTR_DLV_TIME              => '',
        SoluteConfig::UM_ATTR_AVAILABILITY_DATE     => '',
        SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT    => '',
        SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID => '',
        SoluteConfig::UM_ATTR_PROMO_TEXT            => '',
        SoluteConfig::UM_ATTR_VOUCHER_TEXT          => '',
        SoluteConfig::UM_ATTR_SPECIAL               => '',
        SoluteConfig::UM_ATTR_ENERGY_CLASS          => '',
        SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN      => '',
        SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX      => '',
        SoluteConfig::UM_ATTR_ENERGY_CLASS_ILLUMIN  => '',
        SoluteConfig::UM_ATTR_CONDITION             => '',
        SoluteConfig::UM_ATTR_ITEM_GROUP_ID         => '',
        SoluteConfig::UM_ATTR_COMPATIBLE_PRODUCT    => '',
        SoluteConfig::UM_ATTR_QUANTITY_NUMBER       => '',
        SoluteConfig::UM_ATTR_IS_BUNDLE             => '',
        SoluteConfig::UM_ATTR_SIZE                  => '',
        SoluteConfig::UM_ATTR_SIZE_SYSTEM           => '',
        SoluteConfig::UM_ATTR_COLOR                 => '',
        SoluteConfig::UM_ATTR_GENDER                => '',
        SoluteConfig::UM_ATTR_MATERIAL              => '',
        SoluteConfig::UM_ATTR_PATTERN               => '',
        SoluteConfig::UM_ATTR_AGE_RATING            => '',
        SoluteConfig::UM_ATTR_AGE_GROUP             => '',
        SoluteConfig::UM_ATTR_ADULT                 => '',
        SoluteConfig::UM_ATTR_PLATFORM              => '',
        SoluteConfig::UM_ATTR_PRODUCT_TYPE          => '',
        SoluteConfig::UM_ATTR_STYLE                 => '',
        SoluteConfig::UM_ATTR_PROPERTIES            => '',
        SoluteConfig::UM_ATTR_FUNCTIONS             => '',
        SoluteConfig::UM_ATTR_EQUIPMENT             => '',
        SoluteConfig::UM_ATTR_DEPTH                 => '',
        SoluteConfig::UM_ATTR_PZN                   => '',
        SoluteConfig::UM_ATTR_WET_GRIP              => '',
        SoluteConfig::UM_ATTR_FUEL_EFFICIENCY       => '',
        SoluteConfig::UM_ATTR_ROLLING_NOISE         => '',
        SoluteConfig::UM_ATTR_ROLLING_NOISE_CLASS   => '',
        SoluteConfig::UM_ATTR_HSN_TSN               => '',
        SoluteConfig::UM_ATTR_SPH                   => '',
        SoluteConfig::UM_ATTR_DIA                   => '',
        SoluteConfig::UM_ATTR_BC                    => '',
        SoluteConfig::UM_ATTR_CYL                   => '',
        SoluteConfig::UM_ATTR_AXIS                  => '',
        SoluteConfig::UM_ATTR_ADD                   => '',
    ];

    /**
     * @param string $articleId
     * @param CsvConverter $csvConverter
     */
    public function __construct(
        string $articleId,
        CsvConverter $csvConverter
    ){
        $this->articleId = $articleId;
        $this->csvConverter = $csvConverter;
    }

    /**
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function importData(): void
    {
        $sqlCache = '';
        foreach ($this->fieldList as $attribute => $defaultValue) {
            $attributeTitle = 'SOLUTE_' . mb_strtoupper($attribute);
            $value = $this->csvConverter->getAttributeValue($attribute);
            $sqlCache .= $this->save($attributeTitle, $value);
        }
        DatabaseProvider::getDb()->execute($sqlCache);
    }

    /**
     * @param string $attributeTitle
     * @param string $value
     * @return string
     * @throws DatabaseConnectionException
     * @throws Exception
     */
    private function save(string $attributeTitle, string $value): string
    {
        if (empty($this->articleId) || empty($attributeTitle) || empty($value)) {
            return '';
        }
        $shopId = $this->csvConverter->getShopId();
        $attributeId = md5($attributeTitle . $shopId);

        $attribute = oxNew(\OxidEsales\Eshop\Application\Model\Attribute::class);
        if (!$attribute->load($attributeId)) {
            $attribute->setId($attributeId);
            $attribute->oxattribute__oxtitle = new Field($attributeTitle, Field::T_RAW);
            $attribute->oxattribute__oxshopid = new Field($shopId, Field::T_RAW);
            $attribute->oxattribute__um_solute_visibility = new Field(false, Field::T_RAW);

            $attribute->save();
        }

        $oxid = md5($attributeId . $this->articleId . $value);
        $dbo = DatabaseProvider::getDb();
        return "
            REPLACE INTO `" . SoluteConfig::OX_TABLE_OBJECT2ATTRIBUTE . "` 
                (`" . SoluteConfig::OX_COL_ID . "`, `" . SoluteConfig::OX_COL_OBJECT_ID . "`, `"
            . SoluteConfig::OX_COL_ATTRIBUTE_ID . "`, `" . SoluteConfig::OX_COL_VALUE . "`) 
            VALUES 
                ('" . $oxid . "', '" . $this->articleId . "', '" . $attributeId . "', " . $dbo->quote($value) . ");";
    }
}
