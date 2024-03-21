<?php

namespace UnitM\Solute\Model;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Category;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use UnitM\Solute\Core\SoluteConfig;
use Exception;

class mapData implements SoluteConfig
{
    /**
     * @var int
     */
    private int $shopId = 1;

    /**
     * @var int
     */
    private int $languageId;

    /**
     * @var string
     */
    private string $articleId = '';

    /**
     * @var string
     */
    private string $categoryId;

    /**
     * @var array
     */
    private array $attributeSchema;

    /**
     * @var DatabaseInterface
     */
    private DatabaseInterface $database;

    /**
     * @var TableViewNameGenerator
     */
    private TableViewNameGenerator $tableViewNameGenerator;

    /**
     * @var array|true[]
     */
    private array $validDeliveryTimeUnits = [
        SoluteConfig::UM_DELIVERY_TIME_UNIT_DAY     => true,
        SoluteConfig::UM_DELIVERY_TIME_UNIT_WEEK    => true,
        SoluteConfig::UM_DELIVERY_TIME_UNIT_MONTH   => true,
    ];

    /**
     * @param int $shopId
     * @param int $languageId
     * @param string $articleId
     * @param string $categoryId
     * @param array $attributeSchema
     * @throws DatabaseConnectionException
     */
    public function __construct(
        int $shopId,
        int $languageId,
        string $articleId,
        string $categoryId,
        array $attributeSchema
    ) {
        $this->shopId = $shopId;
        $this->languageId = $languageId;
        $this->articleId = $articleId;
        $this->categoryId = $categoryId;
        $this->attributeSchema = $attributeSchema;
        $this->tableViewNameGenerator = oxNew(TableViewNameGenerator::class);
        $this->database = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
    }

    /**
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function getData(): array
    {
        $mappingList = $this->getMappingList($this->shopId, $this->articleId, $this->categoryId);
        $mappingPlan = $this->getMappingPlan($this->attributeSchema, $mappingList, $this->articleId, $this->categoryId);
        $articleData = $this->getArticleData($this->articleId);
        $attributeList = $this->getArticleAttributes($this->articleId);
        if (!empty($articleData[SoluteConfig::OX_COL_PARENTID])) {
            $articleData = $this->mergeParentArticleValues($articleData);
            $attributeList = $this->mergeParentAttributeValues(
                $this->articleId,
                $articleData[SoluteConfig::OX_COL_PARENTID],
                $attributeList
            );
        }

        return $this->collectValues($mappingPlan, $articleData, $attributeList);
    }

    /**
     * @param array $attributeSchema
     * @param array $mappingList
     * @param string $articleId
     * @param string $categoryId
     * @return array
     */
    private function getMappingPlan(
        array $attributeSchema,
        array $mappingList,
        string $articleId,
        string $categoryId
    ): array
    {
        $mappingPlan = [];

        foreach ($attributeSchema as $attribute) {
            $mapping = $mappingList[$attribute[SoluteConfig::OX_COL_ID]];

            if (array_key_exists($articleId, $mapping)) {
                $objectId = $articleId;
            } elseif (array_key_exists($categoryId, $mapping)) {
                $objectId = $categoryId;
            } else {
                $objectId = '';
            }

            $mappingPlan[$attribute[SoluteConfig::UM_COL_PRIMARY_NAME]] = $mapping[$objectId];
        }

        return $mappingPlan;
    }

    /**
     * @param int $shopId
     * @param string $articleId
     * @param string $categoryId
     * @return array
     * @throws DatabaseErrorException
     */
    private function getMappingList(int $shopId, string $articleId, string $categoryId): array
    {
        $fieldSelectionList = $this->getFieldSelectionList($shopId);
        $tableNameAttributeMapping = $this->tableViewNameGenerator->getViewName(SoluteConfig::UM_TABLE_ATTRIBUTE_MAPPING);

        $select = "
            SELECT
                `am`.`" . SoluteConfig::UM_COL_ATTRIBUTE_ID . "`,
                `am`.`" . SoluteConfig::UM_COL_OBJECT_ID . "`,
                `am`.`" . SoluteConfig::UM_COL_MANUAL_VALUE . "`,
                `am`.`" . SoluteConfig::UM_COL_DATA_RESSOURCE_ID . "`
            FROM
                `" . $tableNameAttributeMapping . "` AS `am`
            WHERE
                    `am`.`" . SoluteConfig::UM_COL_SHOP_ID . "` = '" . $shopId . "'
                AND (
                        `am`.`" . SoluteConfig::UM_COL_OBJECT_ID . "` = '" . $articleId . "' 
                    OR `am`.`" . SoluteConfig::UM_COL_OBJECT_ID . "` = '" . $categoryId . "'
                    OR `am`.`" . SoluteConfig::UM_COL_OBJECT_ID . "` = ''
                    );
            ";

        $result = $this->database->getAll($select);

        $list = [];
        foreach ($result as $item) {
            if (!empty($item[SoluteConfig::UM_COL_MANUAL_VALUE])) {
                $list[$item[SoluteConfig::UM_COL_ATTRIBUTE_ID]][$item[SoluteConfig::UM_COL_OBJECT_ID]]
                    = $item[SoluteConfig::UM_COL_MANUAL_VALUE];
            } else {
                $list[$item[SoluteConfig::UM_COL_ATTRIBUTE_ID]][$item[SoluteConfig::UM_COL_OBJECT_ID]]
                    = $fieldSelectionList[$item[SoluteConfig::UM_COL_DATA_RESSOURCE_ID]];
            }
        }

        return $list;
    }

    /**
     * @param int $shopId
     * @return array
     * @throws DatabaseErrorException
     */
    private function getFieldSelectionList(int $shopId): array
    {
        $tableNameFieldSelection = $this->tableViewNameGenerator->getViewName(SoluteConfig::UM_TABLE_FIELD_SELECTION);

        $select = "
            SELECT 
                * 
            FROM 
                `" . $tableNameFieldSelection . "` 
            WHERE 
                `" . SoluteConfig::UM_COL_SHOP_ID . "` = '" . $shopId . "';
                ";
        $result = $this->database->getAll($select);
        $list = [];
        foreach ($result as $row) {
            $list[$row['OXID']] = $row['UM_DATA_RESSOURCE'];
        }

        return $list;
    }

    /**
     * @param string $articleId
     * @return array
     * @throws DatabaseConnectionException
     */
    private function getArticleData(string $articleId): array
    {
        $tableNameOxarticle = $this->tableViewNameGenerator->getViewName(SoluteConfig::OX_TABLE_ARTICLE);

        $select = "
            SELECT 
                * 
            FROM 
                `" . $tableNameOxarticle . "` 
            WHERE 
                `" . SoluteConfig::OX_COL_ID . "` = '" . $articleId . "';";
        $article = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getRow($select);

        return $article;
    }

    /**
     * @param string $articleId
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function getArticleAttributes(string $articleId): array
    {
        $tableNameObjectToAttribute = $this->tableViewNameGenerator->getViewName(SoluteConfig::OX_TABLE_OBJECT2ATTRIBUTE);
        $tableNameOxattribute = $this->tableViewNameGenerator->getViewName(SoluteConfig::OX_TABLE_ATTRIBUTE);

        $select = "
            SELECT 
                `o2a`.`" . SoluteConfig::OX_COL_VALUE . "`,
                `a`.`" . SoluteConfig::OX_COL_TITLE . "` 
            FROM 
                `" . $tableNameObjectToAttribute . "` AS `o2a`,
                `" . $tableNameOxattribute . "` AS `a`
            WHERE 
                    `o2a`.`" . SoluteConfig::OX_COL_OBJECT_ID . "` = '" . $articleId . "'
                AND `a`.`" . SoluteConfig::OX_COL_ID . "` = `o2a`.`" . SoluteConfig::OX_COL_ATTRIBUTE_ID . "`
            ;";

        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($select);

        $list = [];
        foreach ($result as $item) {
            $list[$item[SoluteConfig::OX_COL_TITLE]] = $item[SoluteConfig::OX_COL_VALUE];
        }

        return $list;
    }

    /**
     * @param array $mappingPlan
     * @param array $articleData
     * @param array $attributeList
     * @return array
     * @throws DatabaseConnectionException
     * @throws Exception
     */
    private function collectValues(array $mappingPlan, array $articleData, array $attributeList): array
    {
        $data = [];
        $article = oxNew(Article::class);
        $article->load($articleData[SoluteConfig::OX_COL_ID]);

        foreach ($mappingPlan as $attributeId => $mapping) {
            $fieldDefinition = json_decode($mapping, true);
            if ($fieldDefinition === null && !empty($mapping)) {
                $fieldDefinition = $mapping;
            }

            // manual value
            if (!is_array($fieldDefinition) && !empty($fieldDefinition)) {
                $data[$attributeId] = $fieldDefinition;
                continue;
            }

            // mapping data - attribute
            if (
                    array_key_exists(SoluteConfig::UM_DR_TYPE_ATTRIBUTE, $fieldDefinition)
                &&  array_key_exists($fieldDefinition[SoluteConfig::UM_DR_TYPE_ATTRIBUTE][SoluteConfig::UM_DR_LABEL], $attributeList)
            ) {
                $data[$attributeId]
                    = $attributeList[$fieldDefinition[SoluteConfig::UM_DR_TYPE_ATTRIBUTE][SoluteConfig::UM_DR_LABEL]];
                $data[$attributeId] = $this->convert(
                    $data[$attributeId],
                    $fieldDefinition[SoluteConfig::UM_DR_TYPE_ATTRIBUTE][SoluteConfig::UM_DR_CONVERT]
                );
                continue;
            }

            // mapping data - field oxarticles
            if (
                    array_key_exists(SoluteConfig::UM_DR_TYPE_FIELD, $fieldDefinition)
                &&  $fieldDefinition[SoluteConfig::UM_DR_TYPE_FIELD][SoluteConfig::UM_DR_PRIMARY_TABLE] === SoluteConfig::OX_TABLE_ARTICLE
                &&  array_key_exists($fieldDefinition[SoluteConfig::UM_DR_TYPE_FIELD][SoluteConfig::UM_DR_PRIMARY_FIELD], $articleData)
                &&  !empty($articleData[$fieldDefinition[SoluteConfig::UM_DR_TYPE_FIELD][SoluteConfig::UM_DR_PRIMARY_FIELD]])
            ) {
                $data[$attributeId]
                    = $articleData[$fieldDefinition[SoluteConfig::UM_DR_TYPE_FIELD][SoluteConfig::UM_DR_PRIMARY_FIELD]];
                $data[$attributeId] = $this->convert(
                    $data[$attributeId],
                    $fieldDefinition[SoluteConfig::UM_DR_TYPE_FIELD][SoluteConfig::UM_DR_CONVERT]
                );
                continue;
            }

            // mapping data - relationfield
            if (array_key_exists(SoluteConfig::UM_DR_TYPE_RELATIONFIELD, $fieldDefinition)) {
                $fieldValue = $this->getRelationFieldValue(
                    $fieldDefinition[SoluteConfig::UM_DR_TYPE_RELATIONFIELD],
                    $articleData[SoluteConfig::OX_COL_ID]
                );
                if (empty($fieldValue) && !empty($articleData[SoluteConfig::OX_COL_PARENTID])) {
                    $fieldValue = $this->getRelationFieldValue(
                        $fieldDefinition[SoluteConfig::UM_DR_TYPE_RELATIONFIELD],
                        $articleData[SoluteConfig::OX_COL_PARENTID]
                    );
                }

                if (!empty($fieldValue)) {
                    $data[$attributeId] = $fieldValue;
                    $data[$attributeId] = $this->convert(
                        $data[$attributeId],
                        $fieldDefinition[SoluteConfig::UM_DR_TYPE_RELATIONFIELD][SoluteConfig::UM_DR_CONVERT]
                    );
                }
                continue;
            }

            // mapping data - field on other than oxarticles
            if (
                array_key_exists(SoluteConfig::UM_DR_TYPE_FIELD, $fieldDefinition)
            ) {
                $fieldValue = $this->getFieldValue($fieldDefinition, $articleData[SoluteConfig::OX_COL_ID]);
                if (empty($fieldValue) && !empty($articleData[SoluteConfig::OX_COL_PARENTID])) {
                    $fieldValue = $this->getFieldValue($fieldDefinition, $articleData[SoluteConfig::OX_COL_PARENTID]);
                }

                if (!empty($fieldValue)) {
                    $data[$attributeId] = $fieldValue;
                    $data[$attributeId] = $this->convert(
                        $data[$attributeId],
                        $fieldDefinition[SoluteConfig::UM_DR_TYPE_FIELD][SoluteConfig::UM_DR_CONVERT]
                    );
                }
                continue;
            }

            if (array_key_exists(SoluteConfig::UM_DR_TYPE_GENERATEDFIELD, $fieldDefinition)) {
                $type = $fieldDefinition[SoluteConfig::UM_DR_TYPE_GENERATEDFIELD][SoluteConfig::UM_DR_GENERATED];
                switch ($type) {
                    case SoluteConfig::UM_GEN_PRODUCT_URL:
                        $value = $this->getProductUrl($article);
                        break;

                    case SoluteConfig::UM_GEN_IMAGE_URL:
                        $value = $this->getImageUrls($article);
                        break;

                    case SoluteConfig::UM_GEN_AVAILABILITY:
                        // ToDo in further version

                    case SoluteConfig::UM_GEN_BREADCRUMB:
                        $value = $this->getBreadcrumb($article);
                        break;

                    case SoluteConfig::UM_GEN_DELIVERY_TIME:
                        $value = $this->getDeliveryTime($article);
                        break;

                    default:
                        $value = '';
                }

                if (!empty($value)) {
                    $data[$attributeId] = $value;
                    $data[$attributeId] = $this->convert(
                        $data[$attributeId],
                        $fieldDefinition[SoluteConfig::UM_DR_TYPE_GENERATEDFIELD][SoluteConfig::UM_DR_CONVERT]
                    );
                }
            }
        }

        return $data;
    }

    /**
     * @param string $valueRaw
     * @param string $converter
     * @return string
     * @throws Exception
     */
    private function convert(string $valueRaw, string $converter): string
    {
        if (empty($converter)) {
            return $valueRaw;
        }

        $dataConverter = new DataConverter();
        return $dataConverter->convert($converter, $valueRaw);
    }

    /**
     * @param array $relation
     * @param string $primaryId
     * @return string
     * @throws DatabaseConnectionException
     */
    private function getRelationFieldValue(array $relation, string $primaryId): string
    {
        if (
                !array_key_exists(SoluteConfig::UM_DR_PRIMARY_TABLE, $relation)
            ||  !array_key_exists(SoluteConfig::UM_DR_RELATION_TABLE, $relation)
            ||  !array_key_exists(SoluteConfig::UM_DR_RELATION_FIELD, $relation)
            ||  !array_key_exists(SoluteConfig::UM_DR_RELATION_ID, $relation)
            ||  !array_key_exists(SoluteConfig::UM_DR_PRIMARY_FIELD, $relation)
            ||  empty($relation[SoluteConfig::UM_DR_PRIMARY_TABLE])
            ||  empty($relation[SoluteConfig::UM_DR_RELATION_TABLE])
            ||  empty($relation[SoluteConfig::UM_DR_RELATION_FIELD])
            ||  empty($relation[SoluteConfig::UM_DR_RELATION_ID])
            ||  empty($relation[SoluteConfig::UM_DR_PRIMARY_FIELD])
            ||  empty($primaryId)
        ) {
            $message = 'No field found in relation array' . json_encode($relation) . ' on primary id ' . $primaryId;
            Logger::addLog($message, SoluteConfig::UM_LOG_ERROR);
            return '';
        }

        $tableNamePrimaryTable = $this->tableViewNameGenerator
            ->getViewName($relation[SoluteConfig::UM_DR_PRIMARY_TABLE]);
        $tableNameRelationTable = $this->tableViewNameGenerator
            ->getViewName($relation[SoluteConfig::UM_DR_RELATION_TABLE]);

        $select = "
            SELECT 
                `rt`.`" . $relation[SoluteConfig::UM_DR_RELATION_FIELD] . "`
            FROM
                `" . $tableNamePrimaryTable . "` AS `pt`,
                `" . $tableNameRelationTable . "` AS `rt`
            WHERE
                    `pt`.`" . SoluteConfig::OX_COL_ID . "` = '" . $primaryId . "'
                AND `rt`.`" . $relation[SoluteConfig::UM_DR_RELATION_ID] . "` = `pt`.`" . $relation[SoluteConfig::UM_DR_PRIMARY_FIELD] . "`
        ";
        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getOne($select);
        return (string) $result;
    }

    /**
     * @param array $field
     * @param string $articleId
     * @return string
     * @throws DatabaseConnectionException
     */
    private function getFieldValue(array $field, string $articleId): string
    {
        if (
                !array_key_exists(SoluteConfig::UM_DR_PRIMARY_TABLE, $field)
            ||  !array_key_exists(SoluteConfig::UM_DR_PRIMARY_FIELD, $field)
            ||  !array_key_exists(SoluteConfig::UM_DR_PRIMARY_ID, $field)
            ||  empty($field[SoluteConfig::UM_DR_PRIMARY_TABLE])
            ||  empty($field[SoluteConfig::UM_DR_PRIMARY_FIELD])
            ||  empty($field[SoluteConfig::UM_DR_PRIMARY_ID])
            ||  empty($articleId)
        ) {
            $message = 'No valid field found on given array ' . json_encode($field)
                . '  or article id is empty (articleId = ' . $articleId . ') .';
            Logger::addLog($message, SoluteConfig::UM_LOG_ERROR);
            return '';
        }

        $tableNamePrimaryTable = $this->tableViewNameGenerator->getViewName($field[SoluteConfig::UM_DR_PRIMARY_TABLE]);
        $select = "
            SELECT
                `" . $field[SoluteConfig::UM_DR_PRIMARY_FIELD] . "`
            FROM
                `" . $tableNamePrimaryTable . "`
            WHERE
                `" . $field[SoluteConfig::UM_DR_PRIMARY_ID] . "` = '" . $articleId . "'
        ";
        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getOne($select);

        return (string) $result;
    }

    /**
     * @param Article $article
     * @return string
     */
    private function getProductUrl(Article $article): string
    {
        return $article->getBaseSeoLink($this->languageId, true);
    }

    /**
     * @param Article $article
     * @return string
     */
    private function getImageUrls(Article $article): string
    {
        $imageList = '';
        for ($imageNumber = 1; $imageNumber <= 12; $imageNumber++) {
            $field = 'oxarticles__oxpic' . $imageNumber;
            $imageUrl = $article->{$field}->value;
            if (empty($imageUrl)) {
                continue;
            }

            if (!empty($imageList)) {
                $imageList .= SoluteConfig::SOLUTE_LINK_DIVIDER;
            }

            $imageList .= Registry::getConfig()->getShopUrl() . 'out/pictures/master/product/' . $imageNumber
                . "/" . $imageUrl;
        }

        return $imageList;
    }

    /**
     * @param Article $article
     * @return string
     */
    private function getBreadcrumb(Article $article): string
    {
        if (!empty($article->getParentId())) {
            $articleId = $article->getParentId();
        } else {
            $articleId = $article->getId();
        }

        $select = "
            SELECT 
                `" . SoluteConfig::OX_COL_CATEGORY_ID . "` 
            FROM 
                `" . SoluteConfig::OX_TABLE_OBJECT2CATEGORY . "` 
            WHERE 
                `" . SoluteConfig::OX_COL_OBJECT_ID . "` = '" . $articleId . "' 
            ORDER BY `" . SoluteConfig::OX_COL_TIMESTAMP . "` ASC;
            ";
        $categoryId = $this->database->getOne($select);
        $category = oxNew(Category::class);
        $category->load($categoryId);

        $pathList[] = $category->getTitle();

        $parentCategory = $category->getParentCategory();
        while ($parentCategory !== null) {
            $pathList[] = $parentCategory->getTitle();
            $parentCategory = $parentCategory->getParentCategory();
        }

        $pathListReverse = array_reverse($pathList);
        $breadcrumb = '';
        foreach ($pathListReverse as $item) {
            if (!empty($breadcrumb)) {
                $breadcrumb .= '>';
            }
            $breadcrumb .= $item;
        }

        return $breadcrumb;
    }

    /**
     * @param Article $article
     * @return string
     */
    private function getDeliveryTime(Article $article): string
    {
        $minDeliveryTime = (int) $article->oxarticles__oxmindeltime->value;
        $maxDeliveryTime = (int) $article->oxarticles__oxmaxdeltime->value;
        $deliveryTimeUnit = (string) $article->oxarticles__oxdeltimeunit->value;

        if (empty($deliveryTimeUnit) || !array_key_exists($deliveryTimeUnit, $this->validDeliveryTimeUnits)) {
            $message = 'No valid delivery time unit given in field oxarticles__oxdeltimeunit for article '
                . $article->oxarticles__oxartnum->value . ' Given value: ' . $deliveryTimeUnit
                . '. Allowed values: DAY, WEEK or MONTH.';
            Logger::addLog($message, SoluteConfig::UM_LOG_ERROR);
            return '';
        }

        if ($minDeliveryTime === 0 && $maxDeliveryTime === 0) {
            $message = 'No values given for min and max delivery time for article '
                . $article->oxarticles__oxartnum->value;
            Logger::addLog($message, SoluteConfig::UM_LOG_ERROR);
            return '';
        }

        if ($maxDeliveryTime !== 0 && $minDeliveryTime > $maxDeliveryTime) {
            $message = 'Max delivery time ist set with ' . $maxDeliveryTime . ' and min delivery time with '
                . $minDeliveryTime . ' is greater than max delivery time for article '
                . $article->oxarticles__oxartnum->value;
            Logger::addLog($message, SoluteConfig::UM_LOG_ERROR);
            return '';
        }

        if (empty($minDeliveryTime) || empty($maxDeliveryTime)) {
            $deliveryTimeValue = $minDeliveryTime + $maxDeliveryTime;
            $unit = $this->getDeliveryUnitTranslation($deliveryTimeUnit, $deliveryTimeValue);

            return $deliveryTimeValue . ' ' . $unit;
        }

        if ($minDeliveryTime === $maxDeliveryTime) {
            $unit = $this->getDeliveryUnitTranslation($deliveryTimeUnit, $maxDeliveryTime);

            return $maxDeliveryTime . ' ' . $unit;
        }

        $unit = $this->getDeliveryUnitTranslation($deliveryTimeUnit, $maxDeliveryTime);

        return $minDeliveryTime . '-' . $maxDeliveryTime . ' ' . $unit;
    }

    /**
     * @param string $unit
     * @param int $amount
     * @return string
     */
    private function getDeliveryUnitTranslation(string $unit, int $amount): string
    {
        if ($amount === 0 || empty($unit)) {
            return '';
        }

        if ($amount === 1) {
            $numerus = SoluteConfig::UM_TRANSLATION_NUMERUS_SINGULAR;
        } else {
            $numerus = SoluteConfig::UM_TRANSLATION_NUMERUS_PLURAL;
        }

        $translationKey = 'UMSOLUTE_' . $unit . '_' . $numerus;

        return (string) Registry::getLang()->translateString($translationKey);
    }

    /**
     * @param array $article
     * @return array
     * @throws DatabaseConnectionException
     */
    private function mergeParentArticleValues(array $article): array
    {
        $parentId = $article[SoluteConfig::OX_COL_PARENTID];
        $parent = $this->getArticleData($parentId);
        foreach ($article as $col => $value) {
            if (empty($value) && !empty($parent[$col])) {
                $article[$col] = $parent[$col];
            }
        }

        return $article;
    }

    /**
     * @param string $articleId
     * @param string $parentId
     * @param array $attributeList
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function mergeParentAttributeValues(string $articleId, string $parentId, array $attributeList): array
    {
        if (empty($attributeList) || empty($articleId) || empty($parentId)) {
            return $attributeList;
        }

        $parentAttributeList = $this->getArticleAttributes($parentId);
        foreach ($parentAttributeList as $col => $value) {
            if (
                !empty($value) &&
                (   !array_key_exists($col, $attributeList) ||
                    empty($attributeList[$col])
                )
            ) {
                $attributeList[$col] = $value;
            }
        }

        return $attributeList;
    }

    /**
     * @return string
     */
    public function getArticleId(): string
    {
        return $this->articleId;
    }

    /**
     * @return int
     */
    public function getShopId(): int
    {
        return $this->shopId;
    }
}
