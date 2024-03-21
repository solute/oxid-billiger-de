<?php

namespace UnitM\Solute\Controller\Ajax;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use UnitM\Solute\Core\SoluteConfig;
use UnitM\Solute\Model\ArticleEventLog;
use UnitM\Solute\Model\ArticleEventLogList;
use UnitM\Solute\Model\FeedItem;
use UnitM\Solute\Model\mapData;
use UnitM\Solute\Controller\Ajax\AjaxTrait;

class AjaxBase
{
    use AjaxTrait;

    /**
     * @var string
     */
    private string $tableNameAttributeSchema;

    /**
     * @var string
     */
    private string $tableNameOxarticles;

    /**
     * @var TableViewNameGenerator
     */
    private TableViewNameGenerator $tableViewNameGenerator;

    /**
     * @var array
     */
    private array $attributeSchema = [];

    /**
     *
     */
    public function __construct()
    {
        require_once(__DIR__ . '/../../Core/SoluteConfig.php');
        $this->tableViewNameGenerator = oxNew(TableViewNameGenerator::class);
        $this->tableNameAttributeSchema = $this->tableViewNameGenerator->getViewName(SoluteConfig::UM_TABLE_ATTRIBUTE_SCHEMA);
        $this->tableNameOxarticles = $this->tableViewNameGenerator->getViewName(SoluteConfig::OX_TABLE_ARTICLE);
    }

    /**
     * @param array $data
     * @return array
     */
    public function convertMessage(array $data): array
    {
        foreach ($data as $key => $item) {
            $message = '';
            $state = true;
            $firstDataSet = true;
            $log = json_decode($item[SoluteConfig::UM_COL_LOG], true);
            if ($log  === null) {
                $data[$key][SoluteConfig::UM_COL_LOG] = '';
                continue;
            }

            krsort($log);
            foreach ($log as $unixTimeStamp => $event) {
                if (!empty($message)) {
                    $message .= '<br>';
                }
                $message .= '[' . date('Y-m-d H:i:s', $unixTimeStamp) . '] ';
                switch ($event['event']) {
                    case SoluteConfig::UM_LOG_SHOP_VALIDATION:
                        $message .= Registry::getLang()->translateString('UMSOLUTE_LOG_VALIDATION');
                        break;
                    case SoluteConfig::UM_LOG_API_COMMIT:
                        $message .= Registry::getLang()->translateString('UMSOLUTE_LOG_API_COMMIT');
                        break;
                    case SoluteConfig::UM_LOG_API_DELETE:
                        $message .= Registry::getLang()->translateString('UMSOLUTE_LOG_API_DELETE');
                        break;
                    case SoluteConfig::UM_LOG_API_CHECK:
                        $message .= Registry::getLang()->translateString('UMSOLUTE_LOG_API_CHECK');
                        break;
                    case SoluteConfig::UM_LOG_API_NOT_SEND:
                        $message .= Registry::getLang()->translateString('UMSOLUTE_LOG_API_NOTSEND');
                        break;
                }

                $message .= '. ' . Registry::getLang()->translateString('UMSOLUTE_LOG_RESULT') . ': ';
                if ($event['state'] === false) {
                    $message .= Registry::getLang()->translateString('UMSOLUTE_LOG_STATE_FALSE') . '. ';
                } else {
                    $message .= Registry::getLang()->translateString('UMSOLUTE_LOG_STATE_TRUE');
                }
                $message .= '. ' . $event['message'];

                if ($firstDataSet) {
                    $state = $event['state'];
                }
                $firstDataSet = false;
            }

            $data[$key][SoluteConfig::UM_COL_LOG] = $message;
            $data[$key][SoluteConfig::UM_LOG_ID_STATE] = $state;
        }

        return $data;
    }

    /**
     * @param ArticleEventLogList $eventList
     * @return string
     * @throws DatabaseConnectionException
     */
    public function getArticleEventListLog(ArticleEventLogList $eventList): string
    {
        $log = $this->convertMessage($eventList->getEventListAsJson());
        return $log[0][SoluteConfig::UM_COL_LOG];
    }

    /**
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function getAttributeSchema(): array
    {
        if (empty($this->attributeSchema)) {
            $select = "
                SELECT 
                    `" . SoluteConfig::OX_COL_ID . "`, `" . SoluteConfig::UM_COL_PRIMARY_NAME . "` 
                FROM 
                    `" . $this->tableNameAttributeSchema . "`;
                    ";
            $this->attributeSchema = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($select);
        }

        return $this->attributeSchema;
    }

    /**
     * @param array $response
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function convertResponse(array $response): array
    {
        $list = [];
        foreach ($response as $responses) {
            foreach ($responses as $item) {
                $result = true;
                $errors = [];
                $message = '';
                $eventId = SoluteConfig::UM_LOG_API_COMMIT;
                $articleId = mb_substr($item['document_id'], 0, 32); // = oxarticle.OXID
                if (array_key_exists('errors', $item)) {
                    $errors = $this->checkRestApiResponseError($item['errors']);
                    if (count($errors) > 0) {
                        $result = false;
                    }
                }

                if (array_key_exists('message', $item)) {
                    $message = $item['message'];
                    $eventId = SoluteConfig::UM_LOG_API_NOT_SEND;
                }

                $list[$articleId] = [
                    'result'    => $result,
                    'errors'    => $errors,
                ];
                $eventList = $this->logEvent($result, $errors, $message, $articleId, $eventId);
                $list[$articleId]['log'] = $this->getArticleEventListLog($eventList);
            }
        }

        return $list;
    }

    /**
     * @param bool $result
     * @param array $errors
     * @param string $message
     * @param string $articleId
     * @param int $eventId
     * @return ArticleEventLogList
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function logEvent(
        bool $result,
        array $errors,
        string $message,
        string $articleId,
        int $eventId
    ): ArticleEventLogList
    {
        foreach ($errors as $item) {
            if (!empty($message)) {
                $message .= ', ';
            }
            $message .= $item['key'] . ': ' . $item['text'];
        }
        $event = new ArticleEventLog();
        $event->setState($result);
        $event->setMessage($message);
        $event->setEvent($eventId);

        $eventList = new ArticleEventLogList($articleId);
        $eventList->push($event);

        return $eventList;
    }

    /**
     * @param mapData $mapData
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function executeValidation(mapData $mapData): array
    {
        $data = $mapData->getData();

        $validator = new FeedItem($this->getSchemaList());
        $validator->setFeedLine($data);
        $validator->setSingleTest(false);

        $response = [
            'result' => $validator->checkData(),
            'errorLog' => $validator->getErrorLog(),
            'valueHash' => $validator->getValueHash()
        ];

        $eventList = $this->logEvent(
            $response['result'],
            [],
            $response['errorLog'],
            $mapData->getArticleId(),
            SoluteConfig::UM_LOG_SHOP_VALIDATION
        );
        $response['log'] = $this->getArticleEventListLog($eventList);

        return [
            'response' => $response,
            'data' => $data,
        ];
    }

    /**
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function getSchemaList(): array
    {
        $select = "SELECT * FROM `" . $this->tableNameAttributeSchema . "`";
        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($select);
        $list = [];
        foreach ($result as $item) {
            $list[$item[SoluteConfig::UM_COL_PRIMARY_NAME]] = $item;
        }
        return $list;
    }

    /**
     * @param int $shopId
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function getArticleList(int $shopId, int $limit, int $offset): array
    {
        $articleList = $this->getArticles($shopId, $limit, $offset);
        $logList = $this->getColumnContentOrderedByIndex(
            SoluteConfig::UM_TABLE_LOG,
            SoluteConfig::OX_COL_ID,
            SoluteConfig::UM_COL_LOG
        );
        $hashList = $this->getColumnContentOrderedByIndex(
            SoluteConfig::UM_TABLE_HASH,
            SoluteConfig::OX_COL_ID,
            SoluteConfig::UM_COL_FEED_HASH
        );

        $result = $this->mergeArticleListWithLogAndHash($articleList, $logList, $hashList);

        return $this->appendMainCategories($result);
    }

    /**
     * @param array $articleList
     * @param array $logList
     * @param array $hashList
     * @return array
     */
    private function mergeArticleListWithLogAndHash(array $articleList, array $logList, array $hashList): array
    {
        $result = [];

        foreach ($articleList as $article) {
            if (array_key_exists($article[SoluteConfig::OX_COL_ID], $logList)) {
                $article[SoluteConfig::UM_COL_LOG] = $logList[$article[SoluteConfig::OX_COL_ID]];
            } else {
                $article[SoluteConfig::UM_COL_LOG] = '';
            }

            if (array_key_exists($article[SoluteConfig::OX_COL_ID], $hashList)) {
                $article[SoluteConfig::UM_COL_FEED_HASH] = $hashList[$article[SoluteConfig::OX_COL_ID]];
            } else {
                $article[SoluteConfig::UM_COL_FEED_HASH] = '';
            }

            $result[] = $article;
        }

        return $result;
    }

    /**
     * @param int $shopId
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function getArticles(int $shopId, int $limit, int $offset): array
    {
        $select = "
            SELECT 
                `a`.`" . SoluteConfig::OX_COL_ID . "`,
                `a`.`" . SoluteConfig::OX_COL_ARTICLENUMBER . "`,
                `a`.`" . SoluteConfig::OX_COL_TITLE . "`,
                `a`.`" . SoluteConfig::OX_COL_SHORTDESCRIPTION . "`,
                `a`.`" . SoluteConfig::OX_COL_PARENTID . "`                
            FROM 
                `" . $this->tableNameOxarticles . "` AS `a`                
            WHERE
                    `a`.`" . SoluteConfig::OX_COL_ACTIVE . "` = '1'
                AND `a`.`" . SoluteConfig::OX_COL_HIDDEN . "` = '0'
                AND `a`.`" . SoluteConfig::OX_COL_SHOPID . "` = '" . (string) $shopId . "'
            ORDER BY 
                  `a`.`" . SoluteConfig::OX_COL_ARTICLENUMBER . "`
            LIMIT " . (string) $limit . " 
            OFFSET " . (string) $offset . ";";
        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($select);
    }

    /**
     * @param string $table
     * @param string $id
     * @param string $column
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function getColumnContentOrderedByIndex(string $table, string $id, string $column): array
    {
        $select = "SELECT * FROM `" . $table . "`;";
        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($select);

        $list = [];

        foreach ($result as $item) {
            $list[$item[$id]] = $item[$column];
        }

        return $list;
    }

    /**
     * @param array $articleList
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function appendMainCategories(array $articleList): array
    {
        if (empty($articleList)) {
            return [];
        }

        $where = '';
        foreach ($articleList as $article) {
            if (!empty($where)) {
                $where .= ' OR ';
            }
            if (!empty($article[SoluteConfig::OX_COL_PARENTID])) {
                $where .= " `o2c`.`" . SoluteConfig::OX_COL_OBJECT_ID . "` = '"
                    . $article[SoluteConfig::OX_COL_PARENTID] . "' ";
            } else {
                $where .= " `o2c`.`" . SoluteConfig::OX_COL_OBJECT_ID . "` = '"
                    . $article[SoluteConfig::OX_COL_ID] . "' ";
            }
        }

        $tableNameOxcategory = $this->tableViewNameGenerator->getViewName(SoluteConfig::OX_TABLE_CATEGORY);

        $select = "
            SELECT
                `o2c`.`" . SoluteConfig::OX_COL_OBJECT_ID . "`,
                `o2c`.`" . SoluteConfig::OX_COL_CATEGORY_ID . "`,
                `o2c`.`" . SoluteConfig::OX_COL_TIMESTAMP . "`,
                `c`.`" . SoluteConfig::OX_COL_TITLE . "`
            FROM
                `" . SoluteConfig::OX_TABLE_OBJECT2CATEGORY . "` AS `o2c`,
                `" . $tableNameOxcategory . "` AS `c`
            WHERE
                (" . $where . ") AND
                `c`.`" . SoluteConfig::OX_COL_ID . "` =  `o2c`.`" . SoluteConfig::OX_COL_CATEGORY_ID . "`
            ORDER BY 
                `o2c`.`" . SoluteConfig::OX_COL_OBJECT_ID . "`,
                `o2c`.`" . SoluteConfig::OX_COL_TIMESTAMP . "` DESC
        ;";

        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($select);
        $relationList = [];
        foreach ($result as $relation) {
            $relationList[$relation[SoluteConfig::OX_COL_OBJECT_ID]] = $relation;
        }

        foreach ($articleList as $key => $article) {
            if (!empty($article[SoluteConfig::OX_COL_PARENTID])) {
                $articleList[$key][SoluteConfig::OX_COL_CATEGORY_ID] = $relationList[$article[SoluteConfig::OX_COL_PARENTID]][SoluteConfig::OX_COL_CATEGORY_ID];
                $articleList[$key]['OXCATTITLE'] = $relationList[$article[SoluteConfig::OX_COL_PARENTID]][SoluteConfig::OX_COL_TITLE];
            } else {
                $articleList[$key][SoluteConfig::OX_COL_CATEGORY_ID] = $relationList[$article[SoluteConfig::OX_COL_ID]][SoluteConfig::OX_COL_CATEGORY_ID];
                $articleList[$key]['OXCATTITLE'] = $relationList[$article[SoluteConfig::OX_COL_ID]][SoluteConfig::OX_COL_TITLE];
            }
        }

        return $articleList;
    }

    /**
     * @param int $shopId
     * @return int
     * @throws DatabaseConnectionException
     */
    public function getArticleCount(int $shopId): int
    {
        $select = "
            SELECT 
                COUNT(*) 
            FROM 
                `" . $this->tableNameOxarticles . "` 
            WHERE
                    `" . SoluteConfig::OX_COL_ACTIVE . "` = '1'
                AND `" . SoluteConfig::OX_COL_HIDDEN . "` = '0'
                AND `" . SoluteConfig::OX_COL_SHOPID . "` = '" . (string) $shopId . "';";
        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getOne($select);
    }
}
