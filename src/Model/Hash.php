<?php

namespace UnitM\Solute\Model;

use OxidEsales\Eshop\Core\DatabaseProvider;
use UnitM\Solute\Core\SoluteConfig;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;

class Hash
{
    /**
     * @var array
     */
    private array $hashList = [];

    /**
     * @var string
     */
    private string $sqlCache = '';

    /**
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function __construct()
    {
        $this->hashList = $this->readHashList();
    }

    /**
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function readHashList(): array
    {
        $select = "SELECT * FROM `" . SoluteConfig::UM_TABLE_HASH . "`;";
        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($select);
        $list = [];
        foreach ($result as $row) {
            $list[$row[SoluteConfig::OX_COL_ID]] = $row[SoluteConfig::UM_COL_FEED_HASH];
        }

        return $list;
    }

    /**
     * @param string $articleId
     * @param string $hashValue
     * @return bool
     */
    public function existsHashValue(string $articleId, string $hashValue): bool
    {
        if (    empty($articleId)
            ||  empty($hashValue)
            ||  !array_key_exists($articleId, $this->hashList)
            ||  $this->hashList[$articleId] !== $hashValue
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param string $articleId
     * @param string $hashValue
     * @return void
     */
    public function saveValue(string $articleId, string $hashValue): void
    {
        if (empty($articleId) || empty($hashValue)) {
            return;
        }

        $replace = "
            REPLACE INTO `" . SoluteConfig::UM_TABLE_HASH . "` 
                (`" . SoluteConfig::OX_COL_ID . "`, `" . SoluteConfig::UM_COL_FEED_HASH . "`)
            VALUES
                ('" . $articleId . "', '" . $hashValue . "');";
        $this->sqlCache .= $replace;
    }

    /**
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function persist(): void
    {
        if (!empty($this->sqlCache)) {
            DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->execute($this->sqlCache);
            $this->sqlCache = '';
        }
    }

    /**
     * @param array $response
     * @param array $articleList
     * @return void
     */
    public function saveHashesForValidSendArticles(array $response, array $articleList): void
    {
        if (empty($response) || empty($articleList)) {
            return;
        }

        foreach ($articleList as $item) {
            if (    array_key_exists(SoluteConfig::UM_AJAX_ARTILCEID, $item)
                &&  array_key_exists($item[SoluteConfig::UM_AJAX_ARTILCEID], $response)
                &&  $response[$item[SoluteConfig::UM_AJAX_ARTILCEID]]['result'] === true
                &&  array_key_exists(SoluteConfig::UM_AJAX_FEED_HASH, $item)
            ){
                $this->saveValue($item[SoluteConfig::UM_AJAX_ARTILCEID], $item[SoluteConfig::UM_AJAX_FEED_HASH]);
            }
        }
    }

    /**
     * @param string $articleId
     * @return void
     */
    public function deleteHash(string $articleId): void
    {
        if (empty($articleId)) {
            return;
        }

        $delete = "DELETE FROM `" . SoluteConfig::UM_TABLE_HASH . "` WHERE `" . SoluteConfig::OX_COL_ID . "` = '"
            . $articleId . "';";
        $this->sqlCache .= $delete;
    }
}
