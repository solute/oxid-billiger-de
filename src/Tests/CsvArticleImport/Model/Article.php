<?php

namespace UnitM\Solute\Tests\CsvArticleImport\Model;

use OxidEsales\Eshop\Application\Model\Article as OxidArticle;
use OxidEsales\Eshop\Application\Model\Manufacturer;
use OxidEsales\Eshop\Core\Field;
use UnitM\Solute\Core\SoluteConfig;
use UnitM\Solute\Tests\CsvArticleImport\Model\CsvConverter;
use Exception;

class Article implements SoluteConfig
{
    /**
     * @var string
     */
    private string $artnum = '';

    /** data template
     *
     * @var array|array[]
     */
    private array $fieldList = [
        SoluteConfig::OX_TABLE_ARTICLE => [
            SoluteConfig::OX_COL_ARTICLENUMBER => '',
            SoluteConfig::OX_COL_EAN => '',
            SoluteConfig::OX_COL_MPN => '',
            SoluteConfig::OX_COL_TITLE => '',
            SoluteConfig::OX_COL_PRICE => 0,
            SoluteConfig::OX_COL_PRICE_RETAIL => 0,
            SoluteConfig::OX_COL_PICTURE_1 => '',
            SoluteConfig::OX_COL_PICTURE_2 => '',
            SoluteConfig::OX_COL_PICTURE_3 => '',
            SoluteConfig::OX_COL_PICTURE_4 => '',
            SoluteConfig::OX_COL_WEIGHT => 0,
            SoluteConfig::OX_COL_STOCK => 0,
            SoluteConfig::OX_COL_LENGTH => 0,
            SoluteConfig::OX_COL_WIDTH => 0,
            SoluteConfig::OX_COL_HEIGHT  => 0
            ],
        SoluteConfig::OX_TABLE_MANUFACTURER => [
            SoluteConfig::OX_COL_TITLE  => ''
        ],
        SoluteConfig::OX_TABLE_ARTICLE_EXTEND => [
            SoluteConfig::OX_COL_LONGEDESCRIPTION  => ''
        ]
    ];

    /**
     * @var array|array[]
     */
    private array $fieldValues;

    /**
     * @var CsvConverter
     */
    private CsvConverter $csvConverter;

    /**
     * @param CsvConverter $csvConverter
     */
    public function __construct(
        CsvConverter $csvConverter
    )
    {
        $this->csvConverter = $csvConverter;
        $this->fieldValues = $this->fieldList;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function importData(): string
    {
        foreach ($this->fieldList as $table => $fieldList) {
            foreach ($fieldList as $field => $defaultValue) {
                $this->setValue(
                    $table,
                    $field,
                    $this->csvConverter->getArticleValue($table, $field)
                );
            }
        }

        return $this->save();
    }

    /**
     * @param string $table
     * @param string $field
     * @param string $value
     * @return void
     */
    private function setValue(string $table, string $field, string $value): void
    {
        if (empty($table) || empty($field) || empty($value)) {
            return;
        }

        if (array_key_exists($table, $this->fieldValues) && array_key_exists($field, $this->fieldValues[$table])) {
            $this->fieldValues[$table][$field] = $value;
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    private function save(): string
    {
        $article = oxNew(OxidArticle::class);

        $this->artnum = $this->fieldValues[SoluteConfig::OX_TABLE_ARTICLE][SoluteConfig::OX_COL_ARTICLENUMBER];
        $ean = $this->fieldValues[SoluteConfig::OX_TABLE_ARTICLE][SoluteConfig::OX_COL_EAN];
        $title = $this->fieldValues[SoluteConfig::OX_TABLE_ARTICLE][SoluteConfig::OX_COL_TITLE];
        $shopId = $this->csvConverter->getShopId();

        $oxid = md5($this->artnum . $ean . $title . $shopId);

        if (!$article->load($oxid)) {
            $article->setId($oxid);
        }

        $article->oxarticles__oxshopid  = new Field($shopId, Field::T_RAW);
        $article->oxarticles__oxactive  = new Field(1, Field::T_RAW);
        $article->oxarticles__oxhidden  = new Field(0, Field::T_RAW);

        $article->oxarticles__oxartnum  = new Field($this->artnum, Field::T_RAW);
        $article->oxarticles__oxean     = new Field($ean, Field::T_RAW);
        $article->oxarticles__oxmpn     = new Field($this->fieldValues[SoluteConfig::OX_TABLE_ARTICLE][SoluteConfig::OX_COL_MPN], Field::T_RAW);
        $article->oxarticles__oxtitle   = new Field($title, Field::T_RAW);
        $article->oxarticles__oxprice   = new Field((float) $this->fieldValues[SoluteConfig::OX_TABLE_ARTICLE][SoluteConfig::OX_COL_PRICE], Field::T_RAW);
        $article->oxarticles__oxtprice  = new Field((float) $this->fieldValues[SoluteConfig::OX_TABLE_ARTICLE][SoluteConfig::OX_COL_PRICE_RETAIL], Field::T_RAW);
        $article->oxarticles__oxpic1    = new Field($this->fieldValues[SoluteConfig::OX_TABLE_ARTICLE][SoluteConfig::OX_COL_PICTURE_1], Field::T_RAW);
        $article->oxarticles__oxpic2    = new Field($this->fieldValues[SoluteConfig::OX_TABLE_ARTICLE][SoluteConfig::OX_COL_PICTURE_1], Field::T_RAW);
        $article->oxarticles__oxpic3    = new Field($this->fieldValues[SoluteConfig::OX_TABLE_ARTICLE][SoluteConfig::OX_COL_PICTURE_3], Field::T_RAW);
        $article->oxarticles__oxpic4    = new Field($this->fieldValues[SoluteConfig::OX_TABLE_ARTICLE][SoluteConfig::OX_COL_PICTURE_4], Field::T_RAW);
        $article->oxarticles__oxweight  = new Field((float) $this->fieldValues[SoluteConfig::OX_TABLE_ARTICLE][SoluteConfig::OX_COL_WEIGHT], Field::T_RAW);
        $article->oxarticles__oxstock   = new Field((float) $this->fieldValues[SoluteConfig::OX_TABLE_ARTICLE][SoluteConfig::OX_COL_STOCK], Field::T_RAW);
        $article->oxarticles__oxlength  = new Field((float) $this->fieldValues[SoluteConfig::OX_TABLE_ARTICLE][SoluteConfig::OX_COL_LENGTH], Field::T_RAW);
        $article->oxarticles__oxwidth   = new Field((float) $this->fieldValues[SoluteConfig::OX_TABLE_ARTICLE][SoluteConfig::OX_COL_WIDTH], Field::T_RAW);
        $article->oxarticles__oxheight  = new Field((float) $this->fieldValues[SoluteConfig::OX_TABLE_ARTICLE][SoluteConfig::OX_COL_HEIGHT], Field::T_RAW);
        $article->setArticleLongDesc($this->fieldValues[SoluteConfig::OX_TABLE_ARTICLE_EXTEND][SoluteConfig::OX_COL_LONGEDESCRIPTION]);

        $manufacturerId = $this->saveManufacturer($shopId);
        $article->oxarticles__oxmanufacturerid = new Field($manufacturerId, Field::T_RAW);

        if ($article->save()) {
            return $oxid;
        }

        return '';
    }

    /**
     * @param int $shopId
     * @return string
     * @throws Exception
     */
    private function saveManufacturer(int $shopId): string
    {
        $manufacturerTitle = $this->fieldValues[SoluteConfig::OX_TABLE_MANUFACTURER][SoluteConfig::OX_COL_TITLE];
        if (empty($manufacturerTitle)) {
            return '';
        }

        $oxid = md5($manufacturerTitle . $shopId);
        $manufacturer = oxNew(Manufacturer::class);
        if ($manufacturer->load($oxid)) {
            return $oxid;
        }

        $manufacturer->setId($oxid);
        $manufacturer->oxmanufacturers__oxtitle = new Field($manufacturerTitle, Field::T_RAW);
        $manufacturer->oxmanufacturers__oxshopid = new Field($shopId, Field::T_RAW);

        if ($manufacturer->save()) {
            return $oxid;
        }

        return '';
    }

    /**
     * @return string
     */
    public function getArticleNumber(): string
    {
        return $this->artnum;
    }
}
