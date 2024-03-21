<?php

namespace UnitM\Solute\Core;

use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;

trait EventTrait
{
    /**
     * @param array $valueSet
     * @param string $table
     * @param DatabaseInterface $database
     * @return string
     */
    private function getSqlForInsertFromDataArray(array $valueSet, string $table, DatabaseInterface $database): string
    {
        if (empty($valueSet) || empty($table)) {
            return '';
        }

        $keys = '';
        $isFirstRun = true;
        $sqlCache = 'REPLACE INTO `' . $table . '` ';
        $values = '';
        foreach ($valueSet as $set) {
            foreach ($set as $key => $value) {
                if ($isFirstRun) {
                    if (!empty($keys)) {
                        $keys .= ',';
                    }
                    $keys .= '`' . $key . '`';
                }

                if (!empty($values)) {
                    $values .= ',';
                }
                $values .= $database->quote($value);
            }

            if ($isFirstRun) {
                $sqlCache .= '(' . $keys . ') VALUES ';
            }
            if (!$isFirstRun) {
                $sqlCache .= ',';
            }
            $sqlCache .= '(' . $values . ')';
            $isFirstRun = false;
            $values = '';
        }

        $sqlCache .= ';';

        return $sqlCache;
    }
}
