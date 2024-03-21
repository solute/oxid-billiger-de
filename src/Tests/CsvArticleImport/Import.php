<?php

namespace UnitM\Solute\Tests\CsvArticleImport;

use UnitM\Solute\Tests\CsvArticleImport\Model\Attribute;
use UnitM\Solute\Tests\CsvArticleImport\Model\CsvConverter;
use UnitM\Solute\Tests\CsvArticleImport\Model\Article;
use UnitM\Solute\Tests\CsvArticleImport\Model\Category;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;

class Import
{
    /**
     * @var string
     */
    private string $importFile = 'ArticleImport.csv';

    /**
     * @return void
     */
    public function run()
    {
        $this->log('## Start csv article import');
        $this->readCsv();
        $this->log('## Csv article import end');
    }

    /**
     * @return void
     */
    private function readCsv()
    {
        $file = __DIR__ . '/ImportFile/' . $this->importFile;

        if (($handle = fopen($file, "r")) === false) {
            $this->log('   Could not read import file ' . $file . '. End of Import');
            return;
        }

        $countArticle = -4;
        while (($data = fgetcsv($handle, 8192, ';')) !== false) {
            if ($countArticle >= 0) {
                $this->import($data);
            }
            $countArticle++;
        }

        fclose($handle);
        $this->log('   Import ' . $countArticle . ' articles from ' . $file . '.');
    }

    /**
     * @param array $data
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function import(array $data): void
    {
        $csvConverter = new CsvConverter($data);
        $article = new Article($csvConverter);
        $articleOxid = $article->importData();

        $logMessage = '   Import article with article number ' . $article->getArticleNumber() . ' ... ';
        $attribute = new Attribute($articleOxid, $csvConverter);
        $attribute->importData();

        $category = new Category($articleOxid, $csvConverter);
        $category->importData();
        $logMessage .= 'done.';
        $this->log($logMessage);
    }

    /**
     * @param string $message
     * @return void
     */
    private function log(string $message): void
    {
        if (!empty($message)) {
            $date = '[' . date('Y-m-d H:i:s') . '] ';
            echo $date . $message . "\n";
        }
    }
}

if (php_sapi_name() === 'cli') {
    require_once(__DIR__ . '/../../../../../../bootstrap.php');
    $send = new Import();
    $send->run();
}
