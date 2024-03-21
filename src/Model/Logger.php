<?php

namespace UnitM\Solute\Model;

use UnitM\Solute\Core\SoluteConfig;
use OxidEsales\Eshop\Core\Registry;

class Logger implements SoluteConfig
{
    /**
     * @param string $message
     * @param int $logLevel
     * @param array $dataList
     * @return void
     */
    public static function addLog(string $message, int $logLevel, array $dataList = []): void
    {
        if (empty($message)) {
            return;
        }

        $file = self::getLogPath() . 'soluteLog_' . date('Y-m-d') . '.txt';
        $fileHandler = fopen($file, 'a');

        $logEntry = '[' . date('Y-m-d H:i:s') . '] ';
        switch ($logLevel) {
            case SoluteConfig::UM_LOG_INFO:
                $logEntry .= '[INFO]  ';
                break;

            case SoluteConfig::UM_LOG_ERROR:
                $logEntry .= '[ERROR] ';
                break;
        }

        $logEntry .= $message;

        if (!empty($dataList)) {
            $dataLine = '';
            foreach ($dataList as $key => $value) {
                if (!empty($dataLine)) {
                    $dataLine .= ' | ';
                }
                $dataLine .= $key . ': ' . $value;
            }
            $logEntry .= ' { Data: ' . $dataLine . ' }';
        }

        $logEntry .= PHP_EOL;
        fwrite($fileHandler, $logEntry);
        fclose($fileHandler);
    }

    /**
     * @return string
     */
    private static function getLogPath(): string
    {
        $pathShop = Registry::getConfig()->getLogsDir();
        if (is_writable($pathShop)) {
            return $pathShop;
        }

        $pathRelative = __DIR__ . '/../../../../../log/';
        if (is_writable($pathRelative)) {
            return $pathRelative;
        }

        echo "No writable log directoy found. Tried paths: \n";
        echo $pathShop . "\n";
        echo $pathRelative . "\n";
        echo "END.";
        die;
    }
}
