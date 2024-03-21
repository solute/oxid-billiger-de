<?php

namespace UnitM\Solute\Tests\CsvArticleImport\Model;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Field;
use UnitM\Solute\Core\SoluteConfig;
use UnitM\Solute\Model\Logger;
use UnitM\Solute\Tests\CsvArticleImport\Model\CsvConverter;
use \Exception;

class Category
{
    /**
     * @var string
     */
    private string $articleId;

    /**
     * @var \UnitM\Solute\Tests\CsvArticleImport\Model\CsvConverter
     */
    private CsvConverter $csvConverter;

    /**
     * @param string $articleId
     * @param \UnitM\Solute\Tests\CsvArticleImport\Model\CsvConverter $csvConverter
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
        $targetCategoryId = $this->setCategoryLevel();
        if (!empty($targetCategoryId)) {
            $this->saveRelation($targetCategoryId);
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function setCategoryLevel(): string
    {
        $rootCategoryTitle = $this->csvConverter->getRootCategory();
        if (empty($rootCategoryTitle)) {
            $message = 'No root category given. End of script.';
            Logger::addLog($message, SoluteConfig::UM_LOG_ERROR);
            die;
        }
        $rootCategoryId = $this->saveCategory($rootCategoryTitle, '', $rootCategoryTitle);

        $categoryLevel1Title = $this->csvConverter->getCategoryLevel1();
        if (empty($categoryLevel1Title)) {
            $message = 'No category given. End of script.';
            Logger::addLog($message, SoluteConfig::UM_LOG_ERROR);
            die;
        }
        $categoryLevel1Id = $this->saveCategory($categoryLevel1Title, $rootCategoryId, $rootCategoryTitle);
        $targetCategoryId = $categoryLevel1Id;

        $categoryLevel2Title = $this->csvConverter->getCategoryLevel2();
        if (empty($categoryLevel2Title)) {
            return $targetCategoryId;
        }
        $categoryLevel2Id = $this->saveCategory($categoryLevel2Title, $categoryLevel1Id, $rootCategoryTitle);
        $targetCategoryId = $categoryLevel2Id;

        $categoryLevel3Title = $this->csvConverter->getCategoryLevel3();
        if (empty($categoryLevel3Title)) {
            return $targetCategoryId;
        }

        $categoryLevel3Id = $this->saveCategory($categoryLevel3Title, $categoryLevel2Id, $rootCategoryTitle);

        return $categoryLevel3Id;
    }

    /**
     * @param string $categoryTitle
     * @param string $parentCategoryId
     * @param string $rootCategoryTitle
     * @return string
     * @throws Exception
     */
    private function saveCategory(string $categoryTitle, string $parentCategoryId, string $rootCategoryTitle): string
    {
        if (empty($categoryTitle) || empty($rootCategoryTitle)) {
            return '';
        }

        $category = oxNew(\OxidEsales\Eshop\Application\Model\Category::class);
        $shopId = $this->csvConverter->getShopId();

        $categoryId = md5($categoryTitle . $shopId);
        $rootCategoryId = md5($rootCategoryTitle . $shopId);

        if (!$category->load($categoryId)) {
            $category->setId($categoryId);
            $category->oxcategories__oxtitle = new Field($categoryTitle, Field::T_RAW);

            if (empty ($parentCategoryId)) {
                $category->oxcategories__oxparentid = new Field('oxrootid', Field::T_RAW);
            } else {
                $category->oxcategories__oxparentid = new Field($parentCategoryId, Field::T_RAW);
            }

            $category->oxcategories__oxrootid = new Field($rootCategoryId, Field::T_RAW);
            $category->oxcategories__oxactive = new Field(1, Field::T_RAW);
            $category->oxcategories__oxhidden = new Field(0, Field::T_RAW);
            $category->oxcategories__oxshopid = new Field($shopId, Field::T_RAW);

            $category->save();
        }

        return $categoryId;
    }

    /**
     * @param string $categoryId
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function saveRelation(string $categoryId): void
    {
        if (empty($this->articleId) || empty($categoryId)) {
            return;
        }

        $oxid = md5($categoryId . $this->articleId);
        $sql = "
            REPLACE INTO `" . SoluteConfig::OX_TABLE_OBJECT2CATEGORY . "` 
                (`" . SoluteConfig::OX_COL_ID . "`, `" . SoluteConfig::OX_COL_OBJECT_ID . "`, `"
            . SoluteConfig::OX_COL_CATEGORY_ID . "`) 
            VALUES 
                ('" . $oxid . "', '" . $this->articleId . "', '" . $categoryId . "');";
        DatabaseProvider::getDb()->execute($sql);
    }
}
