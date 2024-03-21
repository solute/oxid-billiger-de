<?php

namespace UnitM\Solute\Controller\Ajax;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use UnitM\Solute\Core\SoluteConfig;
use UnitM\Solute\Model\DataRessource;
use UnitM\Solute\Model\FieldDefinitionBase;

class SoluteAjaxEditDefinition extends FrontendController
{
    /**
     * @var string
     */
    private string $objectId = '';

    /**
     * @var array
     */
    private array $attributeLabelList = [];

    /**
     * @var array|string[]
     */
    private array $primaryTableList = [
        SoluteConfig::OX_TABLE_ARTICLE
    ];

    /**
     * @var array|string[]
     */
    private array $relationTableList = [
        SoluteConfig::OX_TABLE_ARTICLE_EXTEND,
        SoluteConfig::OX_TABLE_MANUFACTURER,
    ];

    /**
     * @var array
     */
    private array $converterList = [
        SoluteConfig::UM_CONV_MYSQL2ISO8601,
        SoluteConfig::UM_CONV_ERASE_SID,
        SoluteConfig::UM_CONV_ADD_WEIGHT_UNIT
    ];

    /**
     * @var array
     */
    private array $generatedFieldList = [
        SoluteConfig::UM_GEN_AVAILABILITY,
        SoluteConfig::UM_GEN_BREADCRUMB,
        SoluteConfig::UM_GEN_IMAGE_URL,
        SoluteConfig::UM_GEN_PRODUCT_URL,
        SoluteConfig::UM_GEN_DELIVERY_TIME
    ];

    private TableViewNameGenerator $tableViewNameGenerator;

    /**
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function __construct()
    {
        require_once(__DIR__ . '/../../Core/SoluteConfig.php');
        $fieldDefinitionBase = new FieldDefinitionBase();
        $this->attributeLabelList = $fieldDefinitionBase->getAttributeLabelList();
        $this->tableViewNameGenerator = oxNew(TableViewNameGenerator::class);
        parent::__construct();
    }

    /**
     * @return string
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    public function run(): string
    {
        $this->objectId = (string) Registry::get(Request::class)->getRequestParameter('objectId') ? : '';

        $dataRessource = new DataRessource();
        $fieldSelectionRow = $this->getDefinition($this->objectId);
        $definition = json_decode($fieldSelectionRow[SoluteConfig::UM_COL_DATA_RESSOURCE], true) ?: [];
        $dataRessource->setValues($definition);


        $response = [
            'attributeGroupList'    => $this->getAttributeGroupList(),
            'attributeLabelList'    => $this->attributeLabelList,
            'validTypeList'         => $this->getValidTypeList($dataRessource),
            'primaryTableList'      => $this->getPrimaryTableList(),
            'relationTableList'     => $this->getRelatedTableList(),
            'converterList'         => $this->getConverterList(),
            'generatedFieldList'    => $this->getGeneratedFieldList(),
            'fieldSelectionRow' => [
                'objectId'              => $this->objectId,
                'attributeGroupId'      => $fieldSelectionRow[SoluteConfig::UM_COL_ATTRIBUTE_GROUP_ID] ?: '',
                'fieldTitle'            => $fieldSelectionRow[SoluteConfig::UM_COL_FIELD_TITLE] ?: '',
            ],
            'dataRessource' => [
                'fieldTitle'            => $dataRessource->getFieldTitle(),
                'type'                  => $dataRessource->getType(),
                'attributeValue'        => $dataRessource->getAttributeValue(),
                'primaryTable'          => $dataRessource->getPrimaryTable(),
                'primaryField'          => $dataRessource->getPrimaryField(),
                'primaryId'             => $dataRessource->getPrimaryId(),
                'relationTable'         => $dataRessource->getRelationTable(),
                'relationField'         => $dataRessource->getRelationField(),
                'relationId'            => $dataRessource->getRelationId(),
                'converter'             => $dataRessource->getConverter(),
                'generated'             => $dataRessource->getGenerated()
            ],
            'translations' => [
                'formHeader'                    => Registry::getLang()->translateString('UMSOLUTE_DEFINITION_FORM_HEADER'),
                'saveForm'                      => Registry::getLang()->translateString('UMSOLUTE_FORM_SAVE'),
                'label_fieldTitle'              => Registry::getLang()->translateString('UMSOLUTE_LABEL_FIELDTITLE'),
                'label_attributeGroup'          => Registry::getLang()->translateString('UMSOLUTE_LABEL_ATTRIBUTEGROUP'),
                'label_type'                    => Registry::getLang()->translateString('UMSOLUTE_LABEL_TYP'),
                'label_attributeValue'          => Registry::getLang()->translateString('UMSOLUTE_LABEL_ATTRIBUTEVALUE'),
                'label_primaryTable'            => Registry::getLang()->translateString('UMSOLUTE_LABEL_PRIMARYTABLE'),
                'label_primaryTableField'       => Registry::getLang()->translateString('UMSOLUTE_LABEL_PRIMARYTABLEFIELD'),
                'label_primaryTableIdField'     => Registry::getLang()->translateString('UMSOLUTE_LABEL_PRIMARYTABLEIDFIELD'),
                'label_relationTable'           => Registry::getLang()->translateString('UMSOLUTE_LABEL_RELATIONTABLE'),
                'label_relationTableField'      => Registry::getLang()->translateString('UMSOLUTE_LABEL_RELATIONTABLEFIELD'),
                'label_relationTableIdField'    => Registry::getLang()->translateString('UMSOLUTE_LABEL_RELATIONTABLEIDFIELD'),
                'label_generatedField'          => Registry::getLang()->translateString('UMSOLUTE_LABEL_GENERATEDFIELD'),
                'label_converter'               => Registry::getLang()->translateString('UMSOLUTE_LABEL_CONVERTER')
            ]
        ];
        echo json_encode($response);
        die;
    }

    /**
     * @return array
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    private function getAttributeGroupList(): array
    {
        $tableAttributeGroups = $this->tableViewNameGenerator->getViewName(SoluteConfig::UM_TABLE_ATTRIBUTE_GROUPS);
        $select = "SELECT * FROM `" . $tableAttributeGroups . "` ORDER BY `" . SoluteConfig::UM_COL_SORT . "`";
        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($select);
         $list = [];

         foreach ($result as $row) {
             $list[] = [
                 'title' => $row[SoluteConfig::UM_COL_TITLE],
                 'value' => $row[SoluteConfig::OX_COL_ID]
             ];
         }

         return $list;
    }

    /**
     * @param string $objectId
     * @return array
     * @throws DatabaseConnectionException
     */
    private function getDefinition(string $objectId): array
    {
        if (empty($objectId)) {
            return [];
        }

        $tableNameFieldSelection = $this->tableViewNameGenerator->getViewName(SoluteConfig::UM_TABLE_FIELD_SELECTION);

        $select = "
            SELECT 
                * 
            FROM 
                `" . $tableNameFieldSelection . "` 
            WHERE 
                `" . SoluteConfig::OX_COL_ID . "` = '" . $objectId . "';";
        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getRow($select);
    }

    /**
     * @param DataRessource $dataRessource
     * @return array
     */
    private function getValidTypeList(DataRessource $dataRessource): array
    {
        return $this->getValueList($dataRessource->getValidTypeList());
    }

    /**
     * @return array
     */
    private function getConverterList(): array
    {
        return $this->getValueList($this->converterList);
    }

    /**
     * @return array
     */
    private function getGeneratedFieldList(): array
    {
        return $this->getValueList($this->generatedFieldList);
    }

    /**
     * @param array $rawList
     * @return array
     */
    private function getValueList(array $rawList): array
    {
        $list = [];
        foreach ($rawList as $item) {
            $list[] = [
                'title' => $item,
                'value' => $item            ];
        }

        return $list;
    }

    /**
     * @return array
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    private function getPrimaryTableList(): array
    {
        $tableFields = new FieldDefinitionBase();
        return $tableFields->getTableList($this->primaryTableList);
    }

    /**
     * @return array
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    private function getRelatedTableList(): array
    {
        $tableFields = new FieldDefinitionBase();
        return $tableFields->getTableList($this->relationTableList);
    }
}
