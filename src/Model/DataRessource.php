<?php

namespace UnitM\Solute\Model;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use UnitM\Solute\Core\SoluteConfig;

class DataRessource implements SoluteConfig
{
    /**
     * @var array
     */
    private array $validTypeList = [
        SoluteConfig::UM_DR_TYPE_FIELD,
        SoluteConfig::UM_DR_TYPE_ATTRIBUTE,
        SoluteConfig::UM_DR_TYPE_RELATIONFIELD,
        SoluteConfig::UM_DR_TYPE_GENERATEDFIELD
    ];

    /**
     * @var string
     */
    private string $objectId = '';

    /**
     * @var string
     */
    private string $fieldTitle = '';

    /**
     * @var string
     */
    private string $attributeGroupId = '';

    /**
     * @var string
     */
    private string $type = '';

    /**
     * @var string
     */
    private string $primaryTable = '';

    /**
     * @var string
     */
    private string $primaryField = '';

    /**
     * @var string
     */
    private string $primaryId = '';

    /**
     * @var string
     */
    private string $relationTable = '';

    /**
     * @var string
     */
    private string $relationField = '';

    /**
     * @var string
     */
    private string $relationId = '';

    /**
     * @var string
     */
    private string $attributeValue = '';

    /**
     * @var string
     */
    private string $converter = '';

    /**
     * @var string
     */
    private string $generated = '';

    /**
     * @var int
     */
    private int $shopId = 1;

    /**
     * @param array $definition
     * @return void
     */
    public function setValues(array $definition): void
    {
        if (empty($definition)) {
            $message = 'No definition specified to set values.';
            Logger::addLog($message, SoluteConfig::UM_LOG_ERROR);

            return;
        }

        $type = key($definition);
        if (!in_array($type, $this->validTypeList)) {
            $message = 'Given key  ( ' . $type . ' ) in definition for value contains no valid type. | '
                .  'definition: ' . json_encode($definition);
            Logger::addLog($message, SoluteConfig::UM_LOG_ERROR);

            return;
        }
        $this->type = $type;

        $dataSet = $definition[$type];
        if (!$this->setConverterFromDataSet($dataSet)) {
            $message = 'No valid data converter defined in definition: ' . json_encode($definition);
            Logger::addLog($message, SoluteConfig::UM_LOG_ERROR);

            return;
        }

        $result = false;
        switch ($type) {
            case SoluteConfig::UM_DR_TYPE_FIELD:
                $result = $this->setField($dataSet);
                break;

            case SoluteConfig::UM_DR_TYPE_ATTRIBUTE:
                $result = $this->setAttribute($dataSet);
                break;

            case SoluteConfig::UM_DR_TYPE_RELATIONFIELD:
                $result = $this->setRelationFieldFromDataSet($dataSet);
                break;

            case SoluteConfig::UM_DR_TYPE_GENERATEDFIELD:
                $result = $this->setGeneratedfield($dataSet);
                break;
        }

        if (!$result) {
            $message = 'Given Definition could not be resolved to a valid set. | '
                .  'definition: ' . json_encode($definition);
            Logger::addLog($message, SoluteConfig::UM_LOG_ERROR);

            return;
        }

        $this->converter    = $dataSet[SoluteConfig::UM_DR_CONVERT];
    }

    /**
     * @param array $dataSet
     * @return bool
     */
    private function setConverterFromDataSet(array $dataSet): bool
    {
        if (!array_key_exists(SoluteConfig::UM_DR_CONVERT, $dataSet)) {
            return false;
        }

        $this->converter        = $dataSet[SoluteConfig::UM_DR_CONVERT];
        return true;
    }

    /**
     * @param array $dataSet
     * @return bool
     */
    private function setField(array $dataSet): bool
    {
        if (    !array_key_exists(SoluteConfig::UM_DR_PRIMARY_TABLE, $dataSet)
            ||  !array_key_exists(SoluteConfig::UM_DR_PRIMARY_FIELD, $dataSet)
            ||  !array_key_exists(SoluteConfig::UM_DR_PRIMARY_ID, $dataSet)
        ) {
            return false;
        }

        $this->primaryTable = $dataSet[SoluteConfig::UM_DR_PRIMARY_TABLE];
        $this->primaryField = $dataSet[SoluteConfig::UM_DR_PRIMARY_FIELD];
        $this->primaryId    = $dataSet[SoluteConfig::UM_DR_PRIMARY_ID];

        return true;
    }

    /**
     * @param array $dataSet
     * @return bool
     */
    private function setAttribute(array $dataSet): bool
    {
        if (!array_key_exists(SoluteConfig::UM_DR_LABEL, $dataSet)) {
            return false;
        }

        $this->attributeValue = $dataSet[SoluteConfig::UM_DR_LABEL];
        return true;
    }

    /**
     * @param array $dataSet
     * @return bool
     */
    private function setRelationFieldFromDataSet(array $dataSet): bool
    {
        if (    !array_key_exists(SoluteConfig::UM_DR_PRIMARY_TABLE, $dataSet)
            ||  !array_key_exists(SoluteConfig::UM_DR_PRIMARY_FIELD, $dataSet)
            ||  !array_key_exists(SoluteConfig::UM_DR_RELATION_TABLE, $dataSet)
            ||  !array_key_exists(SoluteConfig::UM_DR_RELATION_FIELD, $dataSet)
            ||  !array_key_exists(SoluteConfig::UM_DR_RELATION_ID, $dataSet)
        ) {
            return false;
        }

        $this->relationTable    = $dataSet[SoluteConfig::UM_DR_RELATION_TABLE];
        $this->relationField    = $dataSet[SoluteConfig::UM_DR_RELATION_FIELD];
        $this->relationId       = $dataSet[SoluteConfig::UM_DR_RELATION_ID];
        $this->primaryTable     = $dataSet[SoluteConfig::UM_DR_PRIMARY_TABLE];
        $this->primaryField     = $dataSet[SoluteConfig::UM_DR_PRIMARY_FIELD];

        return true;
    }

    /**
     * @param array $dataSet
     * @return bool
     */
    private function setGeneratedField(array $dataSet): bool
    {
        if (!array_key_exists(SoluteConfig::UM_DR_GENERATED, $dataSet)) {
            return false;
        }

        $this->generated = $dataSet[SoluteConfig::UM_DR_GENERATED];
        return true;
    }

    /**
     * @return void
     */
    public function getPostValues(): void
    {
        $this->objectId             = (string) Registry::get(Request::class)->getRequestParameter('objectId') ?: '';
        $this->fieldTitle           = (string) Registry::get(Request::class)->getRequestParameter('fieldTitle') ?: '';
        $this->attributeGroupId     = (string) Registry::get(Request::class)->getRequestParameter('attributeGroupId') ?: '';
        $this->type                 = (string) Registry::get(Request::class)->getRequestParameter('type') ?: '';
        $this->primaryTable         = (string) Registry::get(Request::class)->getRequestParameter('primaryTable') ?: '';
        $this->primaryField         = (string) Registry::get(Request::class)->getRequestParameter('primaryField') ?: '';
        $this->primaryId            = (string) Registry::get(Request::class)->getRequestParameter('primaryId') ?: '';
        $this->relationTable        = (string) Registry::get(Request::class)->getRequestParameter('relationTable') ?: '';
        $this->relationField        = (string) Registry::get(Request::class)->getRequestParameter('relationField') ?: '';
        $this->relationId           = (string) Registry::get(Request::class)->getRequestParameter('relationId') ?: '';
        $this->attributeValue       = (string) Registry::get(Request::class)->getRequestParameter('attributeValue') ?: '';
        $this->generated            = (string) Registry::get(Request::class)->getRequestParameter('generated') ?: '';
        $this->converter            = (string) Registry::get(Request::class)->getRequestParameter('converter') ?: '';
        $this->shopId               = (int)    Registry::get(Request::class)->getRequestParameter('shopId') ?: 1;
    }

    /**
     * @return void
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    public function save(): void
    {
        $um_data_ressource = [];

        switch ($this->type) {
            case SoluteConfig::UM_DR_TYPE_ATTRIBUTE:
                $um_data_ressource[SoluteConfig::UM_DR_TYPE_ATTRIBUTE] = [
                    SoluteConfig::UM_DR_LABEL           => $this->attributeValue,
                    SoluteConfig::UM_DR_CONVERT         => $this->converter
                ];
                break;

            case SoluteConfig::UM_DR_TYPE_FIELD:
                $um_data_ressource[SoluteConfig::UM_DR_TYPE_FIELD] = [
                    SoluteConfig::UM_DR_PRIMARY_TABLE   => $this->primaryTable,
                    SoluteConfig::UM_DR_PRIMARY_FIELD   => $this->primaryField,
                    SoluteConfig::UM_DR_PRIMARY_ID      => $this->primaryId,
                    SoluteConfig::UM_DR_CONVERT         => $this->converter
                ];
                break;

            case SoluteConfig::UM_DR_TYPE_RELATIONFIELD:
                $um_data_ressource[SoluteConfig::UM_DR_TYPE_RELATIONFIELD] = [
                    SoluteConfig::UM_DR_PRIMARY_TABLE   => $this->primaryTable,
                    SoluteConfig::UM_DR_PRIMARY_FIELD   => $this->primaryField,
                    SoluteConfig::UM_DR_PRIMARY_ID      => $this->primaryId,
                    SoluteConfig::UM_DR_RELATION_TABLE  => $this->relationTable,
                    SoluteConfig::UM_DR_RELATION_FIELD  => $this->relationField,
                    SoluteConfig::UM_DR_RELATION_ID     => $this->relationId,
                    SoluteConfig::UM_DR_CONVERT         => $this->converter
                ];
                break;

            case SoluteConfig::UM_DR_TYPE_GENERATEDFIELD:
                $um_data_ressource[SoluteConfig::UM_DR_TYPE_GENERATEDFIELD] = [
                    SoluteConfig::UM_DR_GENERATED       => $this->generated,
                    SoluteConfig::UM_DR_CONVERT         => $this->converter
                ];
                break;
        }

        if (empty($um_data_ressource)) {
            $message = 'Given type ' . $this->type . ' is not part of valid types.';
            Logger::addLog(SoluteConfig::UM_LOG_ERROR);
            return;
        }

        $row = [
            SoluteConfig::OX_COL_ID                 => $this->objectId ? : md5($this->fieldTitle),
            SoluteConfig::UM_COL_DATA_RESSOURCE     => json_encode($um_data_ressource),
            SoluteConfig::UM_COL_ATTRIBUTE_GROUP_ID => $this->attributeGroupId,
            SoluteConfig::UM_COL_FIELD_TITLE        => $this->fieldTitle,
            SoluteConfig::UM_COL_SHOP_ID            => $this->shopId,
        ];

        $this->insertArrayToTable($row);
    }

    /**
     * @param array $row
     * @return void
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    private function insertArrayToTable(array $row): void
    {
        if (empty($row)) {
            return;
        }

        $database = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);

        $keys = '';
        $sqlCache = 'REPLACE INTO `' . SoluteConfig::UM_TABLE_FIELD_SELECTION . '` ';
        $values = '';

        foreach ($row as $key => $value) {
            if (!empty($keys)) {
                $keys .= ',';
            }
            $keys .= '`' . $key . '`';

            if (!empty($values)) {
                $values .= ',';
            }
            $values .= $database->quote($value);
        }

        $sqlCache .= '(' . $keys . ') VALUES ';
        $sqlCache .= '(' . $values . ')';
        $sqlCache .= ';';

        $database->execute($sqlCache);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getValidTypeList(): array
    {
        return $this->validTypeList;
    }

    /**
     * @return string
     */
    public function getFieldTitle(): string
    {
        return $this->fieldTitle;
    }

    /**
     * @return string
     */
    public function getAttributeValue(): string
    {
        return $this->attributeValue;
    }

    /**
     * @return string
     */
    public function getPrimaryTable(): string
    {
        return $this->primaryTable;
    }

    /**
     * @return string
     */
    public function getPrimaryField(): string
    {
        return $this->primaryField;
    }

    /**
     * @return string
     */
    public function getPrimaryId(): string
    {
        return $this->primaryId;
    }

    /**
     * @return string
     */
    public function getRelationTable(): string
    {
        return $this->relationTable;
    }

    /**
     * @return string
     */
    public function getRelationField(): string
    {
        return $this->relationField;
    }

    /**
     * @return string
     */
    public function getRelationId(): string
    {
        return $this->relationId;
    }

    /**
     * @return string
     */
    public function getConverter(): string
    {
        return $this->converter;
    }

    /**
     * @return string
     */
    public function getGenerated(): string
    {
        return $this->generated;
    }
}
