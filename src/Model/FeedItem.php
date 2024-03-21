<?php

namespace UnitM\Solute\Model;

use UnitM\Solute\Core\SoluteConfig;

class FeedItem implements SoluteConfig
{
    /**
     * @var array
     */
    private array $feedLine = [];

    /**
     * @var array
     */
    private array $schemaList;

    /**
     * @var bool
     */
    private bool $singleTest;

    /**
     * @var string
     */
    private string $errorLog = '';

    /**
     * @var string
     */
    private string $valueHash = '';

    /**
     * @var array
     */
    private array $requiredFieldList;

    /** refer to SOLUTE-56
     *  if module setting is neccessary, it is only need to change this value
     *
     * @var bool
     */
    private bool $sendNotRequieredFalseValuesToApi = true;

    /**
     * @param array $schemaList
     */
    public function __construct(
        array $schemaList
    ) {
        $this->schemaList = $schemaList;
        $this->requiredFieldList = $this->getRequiredFieldList();
    }

    /**
     * @return bool
     */
    public function checkData(): bool
    {
        if (empty($this->feedLine)) {
            $this->addError('Feed line is empty');
            return false;
        }

        if (!$this->checkRequiredFields($this->feedLine)) {
            return false;
        }

        foreach ($this->feedLine as $key => $item) {
            if (!array_key_exists($key, $this->schemaList)) {
                $this->addError("Key '" . $key . "' not found in schema list.");
                return false;
            }

            $itemIsRequired = (bool) $this->schemaList[$key][SoluteConfig::UM_COL_REQUIRED];
            if ($item === '') {
                if ($itemIsRequired === true) {
                    $this->addError("ERROR check property '" . $key . "': Value is required but empty.");
                    return false;
                }

                continue;
            }

            // check values at itselfs
            if (!$this->checkFixedValues($key, $item)) {
                if ($itemIsRequired || !$this->sendNotRequieredFalseValuesToApi) return false;
            }

            if (!$this->checkValidator($key, $item)) {
                if ($itemIsRequired || !$this->sendNotRequieredFalseValuesToApi) return false;
            }

            if (!$this->checkSpecialData($key, $item)) {
                if ($itemIsRequired || !$this->sendNotRequieredFalseValuesToApi) return false;
            }
        }

        if (count($this->feedLine) > 1 && !$this->checkFieldConditionValues($this->feedLine)) {
            return false;
        }

        $this->valueHash = md5(json_encode($this->feedLine));

        return true;
    }

    /**
     * @param array $feedLine
     * @return bool
     */
    private function checkRequiredFields(array $feedLine): bool
    {
        $result = true;
        if (!$this->singleTest) {
            foreach ($this->requiredFieldList as $requiredField) {
                if (!array_key_exists($requiredField, $feedLine)) {
                    $this->addError("ERROR. Required field '" . $requiredField . "' is not set.");
                    $result = false;
                }

                if (empty($feedLine[$requiredField])) {
                    $this->addError("ERROR. Required field '" . $requiredField . "' is empty.");
                    $result = false;
                }
            }
        }
        return $result;
    }

    /**
     * @param string $key
     * @param string $item
     * @return bool
     */
    private function checkValidator(string $key, string $item): bool
    {
        $validatorList = json_decode($this->schemaList[$key][SoluteConfig::UM_COL_VALIDATOR], true);
        $resultList = [];
        $errorText = [];

        foreach ($validatorList as $validatorKey => $validator) {
            $resultList[$validatorKey] = true;
            if (!empty($validator[Soluteconfig::UM_ID_TYPE])) {
                switch ($validator[Soluteconfig::UM_ID_TYPE]) {
                    case SoluteConfig::UM_TYPE_FLOAT:
                        if (!is_numeric($item)) {
                            $errorText[] = "ERROR check property '" . $key . "' with validator '" . $validatorKey
                                . "': Value '" . $item . "' is not type FLOAT.";
                            $resultList[$validatorKey] = false;
                        }
                        break;

                    case SoluteConfig::UM_TYPE_INT:
                        if (!ctype_digit((string) $item)) {
                            $errorText[] = "ERROR check property '" . $key . "' with validator '" . $validatorKey
                                . "': Value '" . $item . "' is not type INTEGER.";
                            $resultList[$validatorKey] = false;
                        }
                        break;

                    case SoluteConfig::UM_TYPE_VARCHAR_NOHTML:
                        $strLengthRaw = strlen($item);
                        $strLengthFiltered = strlen(strip_tags($item));
                        if ($strLengthRaw !== $strLengthFiltered) {
                            $errorText[] = "ERROR check property '" . $key . "' with validator '" . $validatorKey
                                . "': Value '" . $item . "' has not allowed HTML tags.";
                            $resultList[$validatorKey] = false;
                        }
                        break;

                    default:
                    case SoluteConfig::UM_TYPE_VARCHAR:
                    case SoluteConfig::UM_TYPE_BOOL:
                    case SoluteConfig::UM_TYPE_DATE:
                        break;
                }
            }

            if (!$resultList[$validatorKey]) {
                continue;
            }

            if (    !empty($validator[SoluteConfig::UM_ID_MAX_LENGTH])
                &&  mb_strlen($item) > $validator[SoluteConfig::UM_ID_MAX_LENGTH]
            ) {
                $errorValue = strip_tags($item);
                if (mb_strlen($errorValue) > 64) {
                    $errorValue = mb_substr($errorValue, 0, 64) . '... ';
                }

                $errorText[] = "ERROR check property '" . $key . "' with validator '" . $validatorKey
                    . "': Value '"  . $errorValue .  "' exceeds maximum length of "
                    . $validator[SoluteConfig::UM_ID_MAX_LENGTH] . " with " . mb_strlen($item);
                $resultList[$validatorKey] = false;
                continue;
            }

            if (    !empty($validator[SoluteConfig::UM_ID_REGEX])
                &&  !mb_ereg_match($validator[SoluteConfig::UM_ID_REGEX], $item)
            ) {
                $errorText[] = "ERROR check property '" . $key . "' with validator '" . $validatorKey
                    . "': Value '" . $item . "' matches not the given pattern.";
                $resultList[$validatorKey] = false;
                continue;
            }

            if (    !empty($validator[SoluteConfig::UM_ID_VALUE_MIN])
                &&  (float) $item < (float) $validator[SoluteConfig::UM_ID_VALUE_MIN]
            ) {
                $errorText[] = "ERROR check property '" . $key . "' with validator '" . $validatorKey
                    . "': Value '" . $item . "' less than required minimum of "
                    . $validator[SoluteConfig::UM_ID_VALUE_MIN];
                $resultList[$validatorKey] = false;
                continue;
            }

            if (    !empty($validator[SoluteConfig::UM_ID_VALUE_MAX])
                &&  (float) $item > (float) $validator[SoluteConfig::UM_ID_VALUE_MAX]
            ) {
                $errorText[] = "ERROR check property '" . $key . "' with validator '" . $validatorKey
                    . "': Value '" . $item . "' more than required maximum of "
                    . $validator[SoluteConfig::UM_ID_VALUE_MAX];
                $resultList[$validatorKey] = false;
                continue;
            }

            if (!empty($validator[SoluteConfig::UM_ID_VALUE_STEP])) {
                $step = (float) $validator[SoluteConfig::UM_ID_VALUE_STEP];
                if ($step !== 0.0) {
                    $division = abs((float) $item / $step);
                    if (!ctype_digit((string) $division)) {
                        $errorText[] = "ERROR check property '" . $key . "' with validator '" . $validatorKey
                            . "': Value '" . $item . "' matches not required value step of "
                            . $validator[SoluteConfig::UM_ID_VALUE_STEP];
                        $resultList[$validatorKey] = false;
                    }
                }
            }
        }

        foreach ($resultList as $singleTest) {
            if ($singleTest) {
                return true;
            }
        }

        $message = '';
        foreach ($errorText as $singleMessage) {
            if (!empty($message)) {
                $message .= "\n";
            }
            $message .= $singleMessage;
        }
        $this->addError($message);

        return false;
    }

    /**
     * @param string $key
     * @param string $item
     * @return bool
     */
    private function checkFixedValues(string $key, string $item): bool
    {
        // Check Fix Values
        $fixedValueList = json_decode($this->schemaList[$key][self::UM_COL_VALID_VALUES], true);
        if (empty($fixedValueList)) {
            return true;
        }

        if (!in_array($item, $fixedValueList)) {
            $this->addError("ERROR check property '" . $key . "': Value '" . $item
                . "' matches not the list of allowed values");
            return false;
        }

        return true;
    }

    /**
     * @param string $key
     * @param string $item
     * @return bool
     */
    private function checkSpecialData(string $key, string $item): bool
    {
        switch ($key) {
            case SoluteConfig::UM_ATTR_MODIFIED_DATE:
                if (!$this->checkDateValue(substr($item, 0, 10))) {
                    $this->addError("ERROR check property '" . $key . "': Value '" . $item
                        . "' contains an invalid date");
                    return false;
                }

                if (!$this->checkTimeValue(substr($item, 11, 8))) {
                    $this->addError("ERROR check property '" . $key . "': Value '" . $item
                        . "' contains an invalid time");
                    return false;
                }
                if (!$this->checkTimeZone(substr($item, 19, 6))) {
                    $this->addError("ERROR check property '" . $key . "': Value '" . $item
                        . "' contains an invalid timezone");
                    return false;
                }
                break;

            case SoluteConfig::UM_ATTR_IMAGES:
                if (!$this->checkImages($item)) {
                    $this->addError("ERROR check property '" . $key . "': Value '" . $item
                        . "' contains invalid image types or has invalid list divider.");
                    return false;
                }
                break;

            default:
                return true;
        }

        return true;
    }

    /**
     * @param string $images
     * @return bool
     */
    private function checkImages(string $images): bool
    {
        if (empty($images)) {
            return false;
        }

        $imageList = explode(SoluteConfig::SOLUTE_LINK_DIVIDER, $images);
        foreach ($imageList as $item) {
            $extension = mb_substr($item, -4, 4);
            if ($extension !== '.jpg' && $extension !== 'jpeg' && $extension !== '.png' && $extension !== '.gif') {
                $message = "ERROR with image link '" . $item . "': Extension '" . $extension
                    . "' is not a valid one of .jpg, .jpeg, .gif or .png.";
                $this->addError($message);
                return false;
            }

            if (!mb_ereg_match(SoluteConfig::UM_REGEX_URL, $item)) {
                $this->addError("ERROR with imgage link '" . $item . "': Url is not a valid url");
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $date  YYYY-MM-DD
     * @return bool
     */
    private function checkDateValue(string $date): bool
    {
        if (empty($date) || strlen($date) < 10) {
            return false;
        }

        $day = (int) substr($date, 8, 2);
        $month = (int) substr($date, 5, 2);
        $year = (int) substr($date, 0, 4);

        return checkdate($month, $day, $year);
    }

    /**
     * @param string $time HH:ii:ss
     * @return bool
     */
    private function checkTimeValue(string $time): bool
    {
        if (empty($time) || strlen($time) < 8) {
            return false;
        }

        $hour = (int) substr($time, 0, 2);
        $minute = (int) substr($time, 3, 2);
        $second = (int) substr($time, 6, 2);

        if (
            $hour < 0 || $hour > 23
            ||  $minute < 0 || $minute > 59
            ||  $second < 0 || $second > 59
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param string $timezone  +/-HH:ii with -12:00 to +14:00 or 'Z'
     * @return bool
     */
    private function checkTimeZone(string $timezone): bool
    {
        if (empty($timezone)) {
            return false;
        }
        if ($timezone === 'Z') {
            return true;
        }

        if (in_array($timezone, $this->getValidTimeZoneList())) {
            return true;
        }

        return false;
    }

    /**
     * @param array $feedLine
     * @return bool
     */
    private function checkFieldConditionValues(array $feedLine): bool
    {
        // #01
        if (    empty($feedLine[SoluteConfig::UM_ATTR_GTIN])
            &&  empty($feedLine[SoluteConfig::UM_ATTR_MPN])
        ) {
            $this->addError("ERROR. Field '" . SoluteConfig::UM_ATTR_MPN . "' may not be empty if field '"
                . SoluteConfig::UM_ATTR_GTIN . "' is not set.");

            return false;
        }

        // #02
        if (!   empty($feedLine[SoluteConfig::UM_ATTR_VOUCHER_TEXT])
            &&  empty($feedLine[SoluteConfig::UM_ATTR_VOUCHER_PRICE])
        ) {
            $this->addError("ERROR. Field '" . SoluteConfig::UM_ATTR_VOUCHER_PRICE . "' may not be empty if field '"
                . SoluteConfig::UM_ATTR_VOUCHER_TEXT . "' is set.");

            return false;
        }

        // #03
        if (!$this->checkEngeryClass($feedLine)) {
            if (!$this->sendNotRequieredFalseValuesToApi) return false;
        }

        // #04
        if (!$this->checkWheels($feedLine)) {
            if (!$this->sendNotRequieredFalseValuesToApi) return false;
        }

        // #05
        if (!$this->checkEyelens($feedLine)) {
            if (!$this->sendNotRequieredFalseValuesToApi) return false;
        }

        return true;
    }

    /**
     * @param array $feedLine
     * @return bool
     */
    private function checkEyelens(array $feedLine): bool
    {
        $fieldList = [
            SoluteConfig::UM_ATTR_SPH,
            SoluteConfig::UM_ATTR_DIA,
            SoluteConfig::UM_ATTR_BC
        ];

        $checkResult = $this->checkMultipleFields($fieldList, $feedLine, 'eyelens');
        if (!$checkResult['result']) {
            return false;
        }

        return true;
    }

    /**
     * @param array $feedLine
     * @return bool
     */
    private function checkWheels(array $feedLine): bool
    {
        $fieldList = [
            SoluteConfig::UM_ATTR_WET_GRIP,
            SoluteConfig::UM_ATTR_FUEL_EFFICIENCY,
            SoluteConfig::UM_ATTR_ROLLING_NOISE,
            SoluteConfig::UM_ATTR_ROLLING_NOISE_CLASS
        ];

        $checkResult = $this->checkMultipleFields($fieldList, $feedLine, 'wheels');
        if (!$checkResult['result']) {
            return false;
        }

        return true;
    }

    /**
     * @param array $feedLine
     * @return bool
     */
    private function checkEngeryClass(array $feedLine): bool
    {
        $fieldList = [
            SoluteConfig::UM_ATTR_ENERGY_CLASS,
            SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN,
            SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX
        ];
        $checkResult = $this->checkMultipleFields($fieldList, $feedLine, 'energy class label');
        if (!$checkResult['result']) {
            return false;
        }

        if ($checkResult['count'] > 0) {
            // check range
            $schemaList = $this->schemaList[SoluteConfig::UM_ATTR_ENERGY_CLASS][SoluteConfig::UM_COL_VALID_VALUES];
            $list = json_decode($schemaList, true);
            $range = array_flip(array_reverse($list));
            $value = $range[$feedLine[SoluteConfig::UM_ATTR_ENERGY_CLASS]];
            $worst = $range[$feedLine[SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN]];
            $best = $range[$feedLine[SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX]];

            if ($worst > $best) {
                $this->addError('ERROR. Minimum energy class <i>' . $feedLine[SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN]
                    . '</i> must be better than maximum energy class <i>'
                    . $feedLine[SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX] . '</i>.');
                return false;
            }

            if ($value < $worst) {
                $this->addError('ERROR. Energy class <i>' . $feedLine[SoluteConfig::UM_ATTR_ENERGY_CLASS]
                    . '</i> must be worst or equal than minimum energy class <i>'
                    . $feedLine[SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN] . '</i>.');
                return false;
            }

            if ($value > $best) {
                $this->addError('ERROR. Energy class <i>' . $feedLine[SoluteConfig::UM_ATTR_ENERGY_CLASS]
                    . '</i> must be better or equal than maximum energy class <i>'
                    . $feedLine[SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX] . '</i>.');
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $fieldList
     * @param array $feedLine
     * @param string $fieldClass
     * @return array
     */
    private function checkMultipleFields(array $fieldList, array $feedLine, string $fieldClass): array
    {
        if (empty($fieldList)) {
            return [
                'count' => 0,
                'result' => false
            ];
        }

        $countFieldSet = 0;
        foreach ($fieldList as $item) {
            if (!empty($feedLine[$item])) {
                $countFieldSet++;
            }
        }

        if ($countFieldSet !== 0 && $countFieldSet !== count($fieldList)) {
            $message = 'ERROR. Fields for <i>' . $fieldClass
                . '</i> have to be all filled. At least one is missing. Values: ';
            $messageFieldValue = '';
            foreach ($fieldList as $item) {
                if (!empty($messageFieldValue)) {
                    $messageFieldValue .= ', ';
                }
                $messageFieldValue .= $item . ": '" . $feedLine[$item] . "'";
            }
            $this->addError($message . $messageFieldValue . '.');
            return [
                'count' => $countFieldSet,
                'result' => false
            ];
        }

        return [
            'count' => $countFieldSet,
            'result' => true
        ];
    }

    /**
     * @param array $feedLine
     */
    public function setFeedLine(array $feedLine): void
    {
        $this->feedLine = $feedLine;
    }

    /**
     * @return string
     */
    public function getErrorLog(): string
    {
        return $this->errorLog;
    }

    /**
     * @param string $message
     * @return void
     */
    private function addError(string $message): void
    {
        $this->errorLog .= $message . " ";
    }

    /**
     * @param bool $singleTest
     */
    public function setSingleTest(bool $singleTest): void
    {
        $this->singleTest = $singleTest;
    }

    /**
     * @return string[]
     * source: https://de.wikipedia.org/wiki/Zeitzone#Zonenzeit_UTC+12h_bis_UTC+14h
     */
    private function getValidTimeZoneList(): array
    {
        return [
            '-12:00',
            '-11:00',
            '-10:00',
            '-09:30',
            '-09:00',
            '-08:00',
            '-07:00',
            '-06:00',
            '-05:00',
            '-04:00',
            '-03:30',
            '-03:00',
            '-02:00',
            '-01:00',
            '-00:00',
            '+00:00',
            '+01:00',
            '+02:00',
            '+03:00',
            '+03:30',
            '+04:00',
            '+04:30',
            '+05:00',
            '+05:30',
            '+05:45',
            '+06:00',
            '+06:30',
            '+07:00',
            '+08:00',
            '+09:00',
            '+09:30',
            '+10:00',
            '+10:30',
            '+11:00',
            '+12:00',
            '+12:45',
            '+13:00',
            '+14:00'
        ];
    }

    /**
     * @return array
     */
    private function getRequiredFieldList(): array
    {
        $list = [];
        foreach ($this->schemaList as $key => $attribute) {
            if ((int) $attribute[SoluteConfig::UM_COL_REQUIRED] === 1) {
                $list[] = $key;
            }
        }

        return $list;
    }

    /**
     * @return string
     */
    public function getValueHash(): string
    {
        return $this->valueHash;
    }
}
