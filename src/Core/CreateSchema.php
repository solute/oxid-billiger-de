<?php

namespace UnitM\Solute\Core;

use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;
use UnitM\Solute\Core\EventTrait;
use UnitM\Solute\Model\Logger;

class CreateSchema implements SoluteConfig
{
    use EventTrait;

    /**
     * @var DatabaseInterface
     */
    private DatabaseInterface $database;

    /**
     * @var array
     */
    private array $shopIdList;

    /**
     * @var string
     */
    private string $sqlCache;

    /**
     * @param DatabaseInterface $database
     * @param array $shopIdList
     */
    public function __construct(
        DatabaseInterface $database,
        array $shopIdList
    ) {
        $this->database = $database;
        $this->shopIdList = $shopIdList;
        $this->sqlCache = '';
    }

    /**
     * @return string
     */
    public function run(): string
    {
        if (empty($this->shopIdList)) {
            $message = 'No shop found in oxid system.';
            Logger::addLog($message, SoluteConfig::UM_LOG_ERROR);
            die;
        }

        $this->sqlCache = $this->createSql($this->getDefinitionList(), $this->database, $this->shopIdList);
        return $this->sqlCache;
    }

    /**
     * @param array $valueList
     * @param DatabaseInterface $database
     * @param array $shopIdList
     * @return string
     */
    private function createSql(array $valueList, DatabaseInterface $database, array $shopIdList): string
    {
        if (empty($valueList)) {
            return '';
        }

        $i = 1;
        $values = [];
        $attributeList = [];
        $lastGroupId = '';
        foreach ($valueList as $item) {
            $groupId = md5($item[SoluteConfig::UM_ID_GROUP_NAME]);
            if ($groupId !== $lastGroupId) {
                $i = 1;
            }

            $row = [
                SoluteConfig::OX_COL_ID                 => md5($item[SoluteConfig::UM_ID_PRIMARY_NAME]),
                SoluteConfig::UM_COL_PRIMARY_NAME       => $item[SoluteConfig::UM_ID_PRIMARY_NAME],
                SoluteConfig::UM_COL_SECONDARY_NAME     => $item[SoluteConfig::UM_ID_SECONDARY_NAME],
                SoluteConfig::UM_COL_THIRD_NAME         => $item[SoluteConfig::UM_ID_THIRD_NAME],
                SoluteConfig::UM_COL_ATTRIBUTE_GROUP_ID => $groupId,
                SoluteConfig::UM_COL_SORT               => $i * 10,
                SoluteConfig::UM_COL_REQUIRED           => $item[SoluteConfig::UM_ID_REQUIRED],
                SoluteConfig::UM_COL_VALID_VALUES       => json_encode($item[SoluteConfig::UM_ID_FIX_VALUE]),
                SoluteConfig::UM_COL_VALIDATOR          => json_encode($item[SoluteConfig::UM_ID_VALIDATOR]),
                SoluteConfig::UM_COL_DESCRIPTION        => $item[SoluteConfig::UM_ID_DESCRIPTION],
            ];
            $values[] = $row;

            $attributeName = 'SOLUTE_' . mb_strtoupper($item[SoluteConfig::UM_ID_PRIMARY_NAME]);
            foreach ($shopIdList as $shopId) {
                $attributeList[] = [
                    SoluteConfig::OX_COL_ID                 => md5($attributeName . $shopId),
                    SoluteConfig::OX_COL_SHOPID             => $shopId,
                    SoluteConfig::OX_COL_TITLE              => $attributeName,
                    SoluteConfig::OX_COL_DISPLAY_IN_BASKET  => 0,
                    SoluteConfig::UM_COL_VISIBILITY         => false
                ];
            }

            $i++;
            $lastGroupId = $groupId;
        }
        $sqlCache = $this->getSqlForInsertFromDataArray($values, SoluteConfig::UM_TABLE_ATTRIBUTE_SCHEMA, $database);
        $sqlCache .= $this->getSqlForInsertFromDataArray($attributeList, SoluteConfig::OX_TABLE_ATTRIBUTE, $database);

        return $sqlCache;
    }

    /**
     * @return array[]
     */
    private function getDefinitionList(): array
    {
        return [
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_MODIFIED_DATE,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_COMMON,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_MODIFIED_DATE => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://schema.org/Date',
                        SoluteConfig::UM_ID_MAX_LENGTH => '25',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Gibt das Datum (und die Uhrzeit) der letzten Änderung am einzelnen Angebot an. Im ISO 8601 Format angeben.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_AID,
                SoluteConfig::UM_ID_SECONDARY_NAME => 'sku',
                SoluteConfig::UM_ID_THIRD_NAME => 'ID',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_COMMON,
                SoluteConfig::UM_ID_REQUIRED => true,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_AID => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '[a-zA-Z0-9]{1,50}',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://schema.org/sku',
                        SoluteConfig::UM_ID_MAX_LENGTH => '50',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Für jeden Artikel und seine Varianten muss ein eindeutiger Wert verwendet werden. Darf nicht wiederverwendet werden. Nur gültige Unicode-Zeichen.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_ASIN,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_COMMON,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_ASIN => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '[A-Z0-9]{10}',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://de.wikipedia.org/wiki/Amazon_Standard_Identification_Number',
                        SoluteConfig::UM_ID_MAX_LENGTH => '10',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Amazon-Standard-Identifikationsnummer. Für jeden Artikel und seine Varianten muss ein eindeutiger Wert verwendet werden. Darf nicht wiederverwendet werden.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_GTIN,
                SoluteConfig::UM_ID_SECONDARY_NAME => 'EAN',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_COMMON,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    'EAN' => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '[0-9]{13}',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://schema.org/gtin13',
                        SoluteConfig::UM_ID_MAX_LENGTH => '13',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE =>  'GTIN-13'
                    ],
                    'UPC' => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '[0-9]{12}',
                        SoluteConfig::UM_ID_SCHEMA => 'https://schema.org/gtin12',
                        SoluteConfig::UM_ID_MAX_LENGTH => '12',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => 'GTIN-12'
                    ],
                    'ISBN' => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '[7-9]{3}-[0-9]{10}',
                        SoluteConfig::UM_ID_SCHEMA => 'https://schema.org/isbn',
                        SoluteConfig::UM_ID_MAX_LENGTH => '14',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                    'ITF-14' => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '[0-9]{14}',
                        SoluteConfig::UM_ID_SCHEMA => 'https://schema.org/gtin14',
                        SoluteConfig::UM_ID_MAX_LENGTH => '14',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => 'GTIN-14'
                    ]
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Die GTIN Ihres Artikels (EAN (GTIN-13), ISBN, ITF-14 (GTIN-14), UPC (GTIN-12)'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_MPN,
                SoluteConfig::UM_ID_SECONDARY_NAME => 'mpnr',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_COMMON,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_MPN => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://schema.org/mpn',
                        SoluteConfig::UM_ID_MAX_LENGTH => '64',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Die MPN Ihres Artikels (Modellnummer, Herstellerartikelnummer). Nur vom Hersteller zugewiesene MPNs. Möglichst genaue MPNs. Gleiche Artikel in z.B. verschiedenen Farben sollten mit verschiedenen MPNs gekennzeichnet werden.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_NAME,
                SoluteConfig::UM_ID_SECONDARY_NAME => 'title',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_COMMON,
                SoluteConfig::UM_ID_REQUIRED => true,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_NAME => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://schema.org/name',
                        SoluteConfig::UM_ID_MAX_LENGTH => '200',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Artikelbezeichnung. Folgende Formel dient zur Orientierung: (<Serie>) <Modellname> <Modellnummer> <Produkttyp> (<Stückzahl>) <Eigenschaften>. Bei Produktvarianten sollte mindestens die minimale Menge an Eigenschaften wie Farbe und Größe angegeben werden, um die verschiedenen Varianten unterscheiden zu können. Bei Varianten mit Unterscheidungsmerkmal wie color oder size.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_BRAND,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_COMMON,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_BRAND => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://schema.org/brand',
                        SoluteConfig::UM_ID_MAX_LENGTH => '70',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Der Markenname Ihres Artikels. Verwenden Sie Ihren Geschäftsnamen für das Attribut "brand", wenn Sie selbst den Artikel herstellen oder wenn es sich bei Ihrem Artikel um einen White-Label-Artikel handelt. Beispielsweise können Sie Ihren Geschäftsnamen als Marke angeben, wenn Sie personalisierten Schmuck oder Artikel verkaufen, die von jemand anderem hergestellt wurden und von Ihnen einen neuen Markennamen erhalten. Für kompatible Artikel muss die Marke des Herstellers, der den kompatiblen Artikel hergestellt hat, übergeben werden. Nicht die Marke des Erstausrüsters angeben, um auf Produktkompatibilität hinzuweisen.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_DESCRIPTION,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_COMMON,
                SoluteConfig::UM_ID_REQUIRED => true,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_DESCRIPTION => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://schema.org/description',
                        SoluteConfig::UM_ID_MAX_LENGTH => '4000',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Beschreibung des Artikels. Artikelbeschreibung, frei von werblichen Texten Geben Sie nur beschreibende Informationen über den Artikel an (kein html-Code, Links)'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_LINK,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_COMMON,
                SoluteConfig::UM_ID_REQUIRED => true,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_LINK => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => SoluteConfig::UM_REGEX_URL,
                        SoluteConfig::UM_ID_SCHEMA =>  'https://schema.org/url',
                        SoluteConfig::UM_ID_MAX_LENGTH => '750',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Link zum Angebot im Shop (mit Parametern). Link mit Tracking-Parametern für die Traffic-Auswertung. Keine Anmeldepflicht oder Suchseite um Preis und Details auf der Landingpage des Angebots zu sehen. Alle Informationen auf der Landingpage müssen mit denen im Feed übereinstimmen'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_TARGET_URL,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_COMMON,
                SoluteConfig::UM_ID_REQUIRED => true,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_LINK => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => SoluteConfig::UM_REGEX_URL,
                        SoluteConfig::UM_ID_SCHEMA =>  'https://schema.org/url',
                        SoluteConfig::UM_ID_MAX_LENGTH => '750',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Link zum Angebot im Shop (inkl. Parameter). Muss der Shop URL entsprechen Link inkl. Tracking-Parameter. Parameter zur Steuerung spezifischer Preise dürfen ebenfalls übergeben werden. Keine Anmeldepflicht oder Suchseite um Preis und Details auf der Landingpage des Angebots zu sehen. Alle Informationen auf der Landingpage müssen mit denen im Feed übereinstimmen'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_IMAGES,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_COMMON,
                SoluteConfig::UM_ID_REQUIRED => true,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_IMAGES => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://schema.org/image',
                        SoluteConfig::UM_ID_MAX_LENGTH => '500',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Die URL des Hauptbilds. Optional: Die URLs weiterer Bilder mit Semikolon getrennt. JPEG (.jpg/.jpeg), nicht animiertes GIF (.gif), PNG (.png).'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_PRICE,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_PRICE,
                SoluteConfig::UM_ID_REQUIRED => true,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_PRICE => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_FLOAT,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://schema.org/PriceSpecification',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Preis des Artikels. Preis inkl. Mehrwertsteuer. Muss mit dem Preis auf der Zielseite übereinstimmen.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_OLD_PRICE,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_PRICE,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_PRICE => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_FLOAT,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://schema.org/PriceSpecification',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Der Streichpreis des Artikels. Ursprünglich für den Artikel im Shop aufgerufener Preis. Wird optisch hervorgehoben und mit prozentualer Ersparnis angezeigt.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_VOUCHER_PRICE,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_PRICE,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_PRICE => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_FLOAT,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://schema.org/PriceSpecification',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Der um den Wert des Gutscheins reduzierte Preis'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_SALE_PRICE,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_PRICE,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_PRICE => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_FLOAT,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://schema.org/PriceSpecification',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Sonderangebotspreis. Preisangabe von Artikeln im Ausverkauf/Sonderaktion'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_SALE_PRICE_EFFECTIVE,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_PRICE,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_SALE_PRICE_EFFECTIVE => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?(\/([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$)?',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://schema.org/Date',
                        SoluteConfig::UM_ID_MAX_LENGTH => '43',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Sonderangebotszeitraum. Zeitraum (gemäß ISO 8601-Norm). Angabe des Start- und Enddatums durch einen Schrägstrich getrennt'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_SALE_PRICE_PUBLISHING,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_PRICE,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_SALE_PRICE_PUBLISHING => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://schema.org/Date',
                        SoluteConfig::UM_ID_MAX_LENGTH => '21',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Sonderangebotsveröffentlichungsdatum. Zeitpunkt (gemäß ISO 8601-Norm), ab dem der Sonderpreis für Marketingzwecke verwendet werden kann. Z.B. Versendung eines Newsletters, dass morgen Artikel XY zum Sonderpreis erhältlich ist'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_PPU,
                SoluteConfig::UM_ID_SECONDARY_NAME => 'base_price',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_PRICE,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_PPU => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://schema.org/PriceSpecification',
                        SoluteConfig::UM_ID_MAX_LENGTH => '32',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Preis je Mengeneinheit. Der Grundpreis dient dazu, dass bei verschiedenen Packungsgrößen vergleichbare Werte anhand eines Einheitsmaßes angezeigt werden'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_PRICING_MEASURE,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_PRICE,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_PRICING_MEASURE => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '^(([1-9][0-9]{0,})(\.[0-9]{1,3})?)[ ]{0,1}(oz|lb|mg|g|kg|floz|pt|qt|gal|ml|cl|l|cbm|in|ft|yd|cm|m|sqft|sqm|qm|Stück|ct)',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://schema.org/PriceSpecification',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Maß für Grundpreis im Format [Zahl] [Maßeinheit]. Gibt die Inhaltsmenge oder Abmessung des Artikels an. Wird zu Vergleichszwecken mit einem Einheitsmaß angegeben'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_PRICING_BASE_MEASURE,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_PRICE,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_PRICING_BASE_MEASURE => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '(^(1|10|100|2|4|8)[ ]{0,1}(oz|lb|mg|g|kg|floz|pt|qt|gal|ml|cl|l|cbm|in|ft|yd|cm|m|sqft|sqm|qm|Stück|ct))|(75[ ]{0,1}cl|750[ ]{0,1}ml|50[ ]{0,1}kg|1000[ ]{0,1}kg)',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://schema.org/UnitPriceSpecification',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Maß für Grundpreis im Format [Zahl] [Maßeinheit]. '
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_SUBSCRIPTION_PERIOD,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_PERIOD_PRICE,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => ['month', 'year'],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_SUBSCRIPTION_PERIOD => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://support.google.com/merchants/answer/7437904?hl=de&ref_topic=6324338',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Abrechnungsperiode. Dauer einer einzelnen Abrechnungsperiode (entweder month oder year).'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_SUBSCRIPTION_LENGTH,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_PERIOD_PRICE,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_SUBSCRIPTION_LENGTH => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_INT,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://support.google.com/merchants/answer/7437904?hl=de&ref_topic=6324338',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Abolaufzeit. Anzahl der Abrechnungsperioden (Monate oder Jahre).'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_SUBSCRIPTION_VALUE,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_PERIOD_PRICE,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_SUBSCRIPTION_VALUE => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_FLOAT,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://support.google.com/merchants/answer/7437904?hl=de&ref_topic=6324338',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Monatlicher Betrag. Betrag, den der Käufer monatlich zahlen muss.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_INSTALLMENT,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_PERIOD_PRICE,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_INSTALLMENT => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '(^[1-9]\d*):[0-9]*(([.]{1}[0-9]{0,2}))',
                        SoluteConfig::UM_ID_SCHEMA =>  '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'monatliche Rate und Laufzeit. Angabe von Details zur Ratenhöhe und -laufzeit bei Artikeln mit Ratenzahlung'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_DLV_COST,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DELIVERY,
                SoluteConfig::UM_ID_REQUIRED => true,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_PRICE => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_FLOAT,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '10',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Versandkosten des Artikels in das Zielland'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_DLV_COST_AT,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DELIVERY,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_PRICE => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_FLOAT,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Die Versandkosten des Artikels vom Lager nach Österreich.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_DLV_TIME,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DELIVERY,
                SoluteConfig::UM_ID_REQUIRED => true,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_DLV_TIME => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '80',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Lieferzeit des Artikels. Lieferzeit des Artikels ab dem Zeitpunkt der Bestellannahme bzw. vollständigen Bezahlung. Immer mit Einheit angeben, ansonsten werden Tage angenommen.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_AVAILABILITY,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DELIVERY,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => ['in stock', 'out of stock', 'preorder'],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_AVAILABILITY => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://schema.org/availability',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Verfügbarkeit des Artikelss. Angabe muss mit der Verfügbarkeit auf der Zielseite übereinstimmen'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_AVAILABILITY_DATE,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DELIVERY,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_AVAILABILITY_DATE => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_DATE,
                        SoluteConfig::UM_ID_REGEX => '((20)\d{2})(-)(0[1-9]|1[0-2])(-)(0[1-9]|[12][0-9]|3[01])', // JJJJ-MM-TT
                        SoluteConfig::UM_ID_SCHEMA =>  'https://pending.schema.org/availabilityStarts',
                        SoluteConfig::UM_ID_MAX_LENGTH => '10',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Datum, ab dem ein vorbestellter Artikel lieferbar ist'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_STOCK_QUANTITY,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DELIVERY,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_STOCK_QUANTITY => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_INT,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Anzahl der Artikel, die Sie auf Lager haben'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_SHOP_CAT,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_CATEGORY,
                SoluteConfig::UM_ID_REQUIRED => true,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_SHOP_CAT => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '^(\X*)((>|;)(\X*[^>;]))$',
                        SoluteConfig::UM_ID_SCHEMA =>  '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Eindeutige Kategorie des Artikels im Shop in der Sprache des Absatzlandes'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_CATEGORY,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '^(\X*)((>|;)(\X*[^>;]))$',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://support.google.com/merchants/answer/6324436?hl=de&ref_topic=6324338',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Eindeutige Kategorie des Artikels bei Google in der Sprache des Absatzlandes'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_CATEGORY,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_INT,
                        SoluteConfig::UM_ID_REGEX => '^[1-9]{1,1}\d{2,5}',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://support.google.com/merchants/answer/6324436?hl=de&ref_topic=6324338',
                        SoluteConfig::UM_ID_MAX_LENGTH => '6',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Die von Google definierte numerische ID der Produktkategorie für Ihren Artikel'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_PROMO_TEXT,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_VOUCHER,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_PROMO_TEXT => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR_NOHTML,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '100',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Werbetext. Anzeige von z.B.: Gutscheinaktionen, Neukundenrabatt, spezielle Lieferkonditionen (z.B. Lieferung bis zum Aufstellungsort, Zwei-Mann-Service), besondere Zahlarten (Finanzierung, etc.)'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_VOUCHER_TEXT,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_VOUCHER,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_VOUCHER_TEXT => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR_NOHTML,
                        SoluteConfig::UM_ID_REGEX => '^([^ ]*)( \()(\X*)(\))$',
                        SoluteConfig::UM_ID_SCHEMA =>  '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '100',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Dient ausschließlich zum Anzeigen von Gutscheincodes. Der Gutscheincode muss am Anfang stehen, gefolgt von einem Leerzeichen und dem Gutscheintext in Klammern. Keine Formatierungen und Zeilenumbrüche'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_SPECIAL,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_VOUCHER,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_SPECIAL => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Das Kennzeichnen von Artikeln über dieses Feld/Spalte ermöglicht der solute ein fokussiertes Bewerben und Pushen von ausgewählten Artikeln. Nur ein Wert je Artikel. Werte müssen sprechend sein. Werte wie "x" oder "1" werden nicht unterstützt.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_ENERGY_CLASS,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_ENERGY,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => ['A+++', 'A++', 'A+', 'A', 'B', 'C', 'D', 'E', 'F', 'G'], // Have to be set in descend order
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_ENERGY_CLASS => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Die Energieeffizienzklasse Ihres Artikels'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_ENERGY,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => ['A+++', 'A++', 'A+', 'A', 'B', 'C', 'D', 'E', 'F', 'G'],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Die schlechteste Energieeffizienzklasse für die Produktkategorie Ihres Artikels'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_ENERGY,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => ['A+++', 'A++', 'A+', 'A', 'B', 'C', 'D', 'E', 'F', 'G'],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Die beste Energieeffizienzklasse für die Produktkategorie Ihres Artikels'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_ENERGY_CLASS_ILLUMIN,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_ENERGY,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => ['A+++', 'A++', 'A+', 'A', 'B', 'C', 'D', 'E', 'F', 'G'],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_ILLUMIN => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Bei Lampen mit eingebautem Leuchtmittel die Energieeffizienzklasse des Leuchtmittels.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_CONDITION,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => ['new', 'refurbished', 'used'],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_CONDITION => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Zustand. Wenn keine Angaben gemacht wird, wird standardmäßig con Neuware ausgegangen.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_ITEM_GROUP_ID,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_ITEM_GROUP_ID => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '^[a-zA-Z0-9\-]{3,}[^ ]',
                        SoluteConfig::UM_ID_SCHEMA =>  'https://support.google.com/merchants/answer/6324507?hl=de&ref_topic=6324338',
                        SoluteConfig::UM_ID_MAX_LENGTH => '50',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'ID für eine Gruppe von Varianten, die in unterschiedlichen Ausführungen verfügbar ist.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_COMPATIBLE_PRODUCT,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_COMPATIBLE_PRODUCT => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Kompatible Artikel'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_QUANTITY_NUMBER,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_QUANTITY_NUMBER => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Die Anzahl identischer Artikel in einem Multipack. Ganze Zahl, größer als 1 (Multipacks müssen mehr als einen Artikel enthalten)'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_IS_BUNDLE,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => ['yes', 'no'],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_IS_BUNDLE => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Gibt an, dass es sich bei dem Artikel um eine zusammengestellte Gruppe mit unterschiedlichen Artikeln, darunter einem Hauptartikel, handelt'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_SIZE,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_SIZE => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '100',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Die Größe Ihres Artikels'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_SIZE_SYSTEM,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => ['US', 'UK', 'EU', 'DE', 'FR', 'IT', 'INT.'],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_SIZE_SYSTEM => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA =>  '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Das Größensystem Ihres Artikels'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_COLOR,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_COLOR => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => 'https://schema.org/color',
                        SoluteConfig::UM_ID_MAX_LENGTH => '100',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Die Farbe Ihres Artikels. Mehreren Farben mit ; trennen.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_GENDER,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => ['male','female','unisex'],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_GENDER => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => 'https://schema.org/suggestedGender',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Geschlecht, für das Ihr Artikel bestimmt ist'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_MATERIAL,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_MATERIAL => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '200',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Der Stoff oder das Material Ihres Artikels'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_PATTERN,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_PATTERN => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '100',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Das Muster oder das grafische Druckdesign Ihres Artikels'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_AGE_RATING,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_AGE_RATING => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '100',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Altersfreigabe. Für Angebote mit Altersfreigabe wie Spiele oder Filme. Wenn dieses Feld nicht befüllt wird, muss die Altersfreigabe in der Angebotsbezeichnung name angegeben werden. Angabe in Jahren.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_AGE_GROUP,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => ['newborn','infant','toddler','kids','adult'],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_AGE_GROUP => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Die demografische Zielgruppe / Altersgruppe, für die Ihr Artikel bestimmt ist'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_ADULT,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => ['yes','no'],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_ADULT => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Weist auf einen Artikel mit sexuell anzüglichen Inhalten hin'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_PLATFORM,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_PLATFORM => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Für Games: auf welcher Plattform das Spiel verwendet werden kann'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_PRODUCT_TYPE,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_PRODUCT_TYPE => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Spezifiziert die Artikel innerhalb einer Kategorie bzw. Artikelgruppe, z.B. Bei Mode: Kleidertyp, Schuhtyp, Taschentyp, Sonnenbrillentyp, Uhrentyp'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_STYLE,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_STYLE => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Stil des Artikels'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_PROPERTIES,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_PROPERTIES => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Eigenschaften des Artikels'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_FUNCTIONS,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_FUNCTIONS => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Funktionen des Artikels'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_EQUIPMENT,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_EQUIPMENT => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Ausstattung des Artikels'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_HEIGHT,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_MESSUREMENT,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_HEIGHT => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Höhe des Artikels mit Angabe der Einheit.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_WIDTH,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_MESSUREMENT,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_WIDTH => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Breite des Artikels mit Angabe der Einheit.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_LENGTH,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_MESSUREMENT,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_LENGTH => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Länge des Artikels mit Angabe der Einheit.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_DEPTH,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_MESSUREMENT,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_DEPTH => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Tiefe des Artikels mit Angabe der Einheit.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_PZN,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_MEDICAL,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_PZN => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_INT,
                        SoluteConfig::UM_ID_REGEX => '[0-9]{8}',
                        SoluteConfig::UM_ID_SCHEMA => 'https://www.ifaffm.de/de/ifa-codingsystem.html',
                        SoluteConfig::UM_ID_MAX_LENGTH => '8',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Pharmazentralnummer'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_WET_GRIP,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_WHEELS,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => ['A','B','C','E','F'],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_WET_GRIP => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Nasshaftung'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_FUEL_EFFICIENCY,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_WHEELS,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => ['A','B','C','E','F'],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_FUEL_EFFICIENCY => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Kraftstoffeffizienz'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_ROLLING_NOISE,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_WHEELS,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_ROLLING_NOISE => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '^([1-9]{1,1}[0-9]{1,2})( {0,1})(dB)$',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Externes Rollgeräusch'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_ROLLING_NOISE_CLASS,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_WHEELS,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [1,2,3],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_ROLLING_NOISE_CLASS => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_INT,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Rollgeräuschklasse'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_HSN_TSN,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_CAR,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_HSN_TSN => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_VARCHAR,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'HSN (Herstellerschlüsselnummer) und TSN (Typschlüsselnummer). Kommaseparierte Liste möglich.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_SPH,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_EYELENS,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_SPH => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_FLOAT,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '-30',
                        SoluteConfig::UM_ID_VALUE_MAX => '30',
                        SoluteConfig::UM_ID_VALUE_STEP => '0.25',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Dioptrien, auch Sphäres. Wert von -30.0 bis 30.0, Schrittweite 0.25. Angabe ohne Einheit.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_DIA,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_EYELENS,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_DIA => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_FLOAT,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '8',
                        SoluteConfig::UM_ID_VALUE_MAX => '15',
                        SoluteConfig::UM_ID_VALUE_STEP => '0.1',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Durchmesser in mm. Wert von 8.0 bis 15.0, Schrittweise 0.1. Angabe ohne Einheit.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_BC,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_EYELENS,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_BC => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_FLOAT,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '8.3',
                        SoluteConfig::UM_ID_VALUE_MAX => '9',
                        SoluteConfig::UM_ID_VALUE_STEP => '0.1',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Basiskurve, oder auch Radius in mm. Wert von 8.3 bis 9.0 mm, Schrittweite 0.1. Angabe ohne Einheit.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_CYL,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_EYELENS,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_CYL => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_FLOAT,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '-10',
                        SoluteConfig::UM_ID_VALUE_MAX => '-0.25',
                        SoluteConfig::UM_ID_VALUE_STEP => '0.25',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Zylinder in dpt. Wert von -0.25 bis -10.0. Schrittweite 0.25. Angabe ohne Einheit.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_AXIS,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_EYELENS,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_AXIS => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_INT,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '0',
                        SoluteConfig::UM_ID_VALUE_MAX => '180',
                        SoluteConfig::UM_ID_VALUE_STEP => '10',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => ''
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Achse in Grad. Wert von 0 bis 180. Schrittweite: 10. Angabe ohne Einheit.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_ADD,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_EYELENS,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_ADD => [
                        SoluteConfig::UM_ID_TYPE => SoluteConfig::UM_TYPE_FLOAT,
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '1',
                        SoluteConfig::UM_ID_VALUE_MAX => '4',
                        SoluteConfig::UM_ID_VALUE_STEP => '0.5',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => '',
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Nahzusatz in ADD. Wert von +1.00 bis +4.00. Schrittweite: 0.50. Angabe ohne Einheit.'
            ],
            [
                SoluteConfig::UM_ID_PRIMARY_NAME => SoluteConfig::UM_ATTR_WEIGHT,
                SoluteConfig::UM_ID_SECONDARY_NAME => '',
                SoluteConfig::UM_ID_THIRD_NAME => '',
                SoluteConfig::UM_ID_GROUP_NAME => SoluteConfig::UM_ATTR_GROUP_MESSUREMENT,
                SoluteConfig::UM_ID_REQUIRED => false,
                SoluteConfig::UM_ID_FIX_VALUE => [],
                SoluteConfig::UM_ID_VALIDATOR => [
                    SoluteConfig::UM_ATTR_WEIGHT => [
                        SoluteConfig::UM_ID_TYPE => '',
                        SoluteConfig::UM_ID_REGEX => '',
                        SoluteConfig::UM_ID_SCHEMA => '',
                        SoluteConfig::UM_ID_MAX_LENGTH => '',
                        SoluteConfig::UM_ID_VALUE_MIN => '',
                        SoluteConfig::UM_ID_VALUE_MAX => '',
                        SoluteConfig::UM_ID_VALUE_STEP => '',
                        SoluteConfig::UM_ID_ALTERNATIVE_TITLE => '',
                    ],
                ],
                SoluteConfig::UM_ID_DESCRIPTION => 'Artikelgewicht. Angabe als [Wert] [Einheit]'
            ],
        ];
    }
}
