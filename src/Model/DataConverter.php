<?php

namespace UnitM\Solute\Model;

use UnitM\Solute\Core\SoluteConfig;
use Exception;

class DataConverter implements SoluteConfig
{
    /**
     * @param string $converter
     * @param string $valueRaw
     * @return string
     * @throws \Exception
     */
    public function convert(
        string $converter,
        string $valueRaw
    ): string {
        if (!$this->checkConverter($converter) || empty($valueRaw)) {
            return '';
        }

        switch ($converter) {
            case SoluteConfig::UM_CONV_MYSQL2ISO8601:
                return $this->convertDateMysqlToIso8601($valueRaw);
            case SoluteConfig::UM_CONV_ERASE_SID:
                return $this->eraseSid($valueRaw);
            case SoluteConfig::UM_CONV_ADD_WEIGHT_UNIT:
                return $this->addWeightUnit($valueRaw);
            default:
                return '';
        }
    }

    /**
     * @param string $converter
     * @return bool
     */
    private function checkConverter(string $converter): bool
    {
        if (in_array($converter, $this->getConverterList())) {
            return true;
        }

        $message = 'Converter ' . $converter . ' not defined.';
        Logger::addLog($message, SoluteConfig::UM_LOG_ERROR);

        return false;
    }

    /**
     * @param string $valueRaw
     * @return string
     * @throws Exception
     */
    private function convertDateMysqlToIso8601(string $valueRaw): string
    {
        $date = date_create_from_format("Y-m-d H:i:s", $valueRaw);

        return $date->format('Y-m-d\TH:i:sP');
    }

    /**
     * @param string $valueRaw
     * @return string
     */
    private function eraseSid(string $valueRaw): string
    {
        $url = explode('?', $valueRaw);
        if (empty($url[1])) {
            return $valueRaw;
        }

        $getParameters = explode('&', $url[1]);
        foreach ($getParameters as $key => $get) {
            if (strstr($get, 'force_sid') !== false) {
                unset($getParameters[$key]);
                break;
            }
        }

        $newUrl = $url[0];
        $first = true;
        foreach ($getParameters as $get) {
            if (!$first) {
                $newUrl .= '&';
            } else {
                $newUrl .= '?';
            }
            $newUrl .= $get;
            $first = false;
        }

        return $newUrl;
    }

    /**
     * @param string $valueRaw
     * @return string
     */
    private function addWeightUnit(string $valueRaw): string
    {
        return $valueRaw . ' ' . SoluteConfig::UM_UNIT_KG;
    }

    /**
     * @return array
     */
    private function getConverterList(): array
    {
        return [
            SoluteConfig::UM_CONV_MYSQL2ISO8601,
            SoluteConfig::UM_CONV_ERASE_SID,
            SoluteConfig::UM_CONV_ADD_WEIGHT_UNIT,
        ];
    }
}
