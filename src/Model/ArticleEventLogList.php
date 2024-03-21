<?php

namespace UnitM\Solute\Model;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use UnitM\Solute\Core\SoluteConfig;
use UnitM\Solute\Model\ArticleEventLog;
use UnitM\Solute\Service\ModuleSettingsInterface;
use UnitM\Solute\Traits\ServiceContainer;
use DateTime;

class ArticleEventLogList implements SoluteConfig
{
    use ServiceContainer;

    /**
     * @var string
     */
    private string $articleId;

    /**
     * @var array
     * array of ArticleEventLog-classes
     */
    private array $eventList;

    /**
     * @var int
     */
    private int $eventListCountLimit;

    /**
     * @param string $articleId
     */
    public function __construct(
        string $articleId
    ) {
        $this->articleId = $articleId;
        $moduleSettings = $this->getServiceFromContainer(ModuleSettingsInterface::class);
        $this->eventListCountLimit = $moduleSettings->getCountEventEntries();
    }

    /**
     * @return bool
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function saveList(): bool
    {
        if (empty($this->eventList)) {
            return false;
        }

        $value = json_encode($this->convertObjectToArray($this->eventList));
        $update = "
            REPLACE INTO 
                `" . SoluteConfig::UM_TABLE_LOG . "` (`" . SoluteConfig::OX_COL_ID . "`, `"
            . SoluteConfig::UM_COL_LOG . "`)
            VALUES ('" . $this->articleId . "', " . DatabaseProvider::getDb()->quote($value) . ");
        ";

        if (DatabaseProvider::getDb()->execute($update)) {
            return true;
        }

        return false;
    }

    /**
     * @param ArticleEventLog $event
     * @return bool
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function push(ArticleEventLog $event): bool
    {
        if (empty($this->eventList)) {
            $this->eventList = $this->loadEventList();
        }

        $unixTimeStamp = $event->getDatetime()->getTimestamp();
        if (array_key_exists($unixTimeStamp, $this->eventList)) {
            $unixTimeStamp++;
        }
        $this->eventList[$unixTimeStamp] = $event;
        krsort($this->eventList);

        while (count($this->eventList) > $this->eventListCountLimit) {
            array_pop($this->eventList);
        }

        return $this->saveList();
    }

    /**
     * @return ArticleEventLog
     * @throws DatabaseConnectionException
     */
    public function getLastEvent(): ArticleEventLog
    {
        if (empty($this->eventList)) {
            $this->eventList = $this->loadEventList();
        }

        if (empty($this->eventList)) {
            return new ArticleEventLog();
        }

        ksort($this->eventList);
        reset($this->eventList);

        return current($this->eventList);
    }

    /**
     * @return array
     * @throws DatabaseConnectionException
     */
    public function getEventList(): array
    {
        if (empty($this->eventList)) {
            $this->eventList = $this->loadEventList();
        }

        return $this->eventList;
    }

    /**
     * @return array
     * @throws DatabaseConnectionException
     */
    public function getEventListAsJson(): array
    {
        $eventList = $this->getEventList();
        if (empty($eventList)) {
            return [];
        }
        $result[][SoluteConfig::UM_COL_LOG] = json_encode($this->convertObjectToArray($this->eventList));

        return $result;
    }

    /**
     * @return array
     * @throws DatabaseConnectionException
     */
    private function loadEventList(): array
    {
        $select = "
            SELECT 
                `" . SoluteConfig::UM_COL_LOG . "` 
            FROM
                `" . SoluteConfig::UM_TABLE_LOG  . "`
            WHERE
                `" . SoluteConfig::OX_COL_ID . "` = '" . $this->articleId . "';";
        $jsonList = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getOne($select);

        return $this->convertArrayToObject($jsonList);
    }

    /**
     * @param string $jsonList
     * @return array
     */
    private function convertArrayToObject(string $jsonList): array
    {
        $list = json_decode($jsonList, true);
        if (empty($jsonList) || empty($list)) {
            return [];
        }

        $eventList = [];
        foreach ($list as $unixTimeStamp => $item) {
            $datetime = new DateTime();
            $datetime->setTimestamp($unixTimeStamp);

            $event = new ArticleEventLog();
            $event->setDatetime($datetime);
            $event->setEvent($item[SoluteConfig::UM_LOG_ID_EVENT]);
            $event->setMessage($item[SoluteConfig::UM_LOG_ID_MESSAGE]);
            $event->setState($item[SoluteConfig::UM_LOG_ID_STATE]);

            $eventList[$unixTimeStamp] = $event;
        }

        krsort($eventList);

        return $eventList;
    }

    /**
     * @param array $eventList
     * @return array
     */
    private function convertObjectToArray(array $eventList): array
    {
        $list = [];
        foreach ($eventList as $unixTimeStamp => $event) {
            $list[$unixTimeStamp] = [
                SoluteConfig::UM_LOG_ID_EVENT => $event->getEvent(),
                SoluteConfig::UM_LOG_ID_MESSAGE => $event->getMessage(),
                SoluteConfig::UM_LOG_ID_STATE => $event->isState(),
            ];
        }

        return $list;
    }
}
