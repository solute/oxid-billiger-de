<?php

namespace UnitM\Solute\Model;

use UnitM\Solute\Core\SoluteConfig;
use DateTime;

class ArticleEventLog implements SoluteConfig
{
    /**
     * @var DateTime
     */
    private DateTime $datetime;

    /**
     * @var int
     */
    private int $event;

    /**
     * @var string
     */
    private string $message;

    /**
     * @var bool
     */
    private bool $state;

    /**
     *
     */
    public function __construct()
    {
        $this->datetime = new DateTime();
    }

    /**
     * @return bool
     */
    public function load(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        return true;
    }

    /**
     * @return DateTime
     */
    public function getDatetime(): DateTime
    {
        return $this->datetime;
    }

    /**
     * @param DateTime $datetime
     */
    public function setDatetime(DateTime $datetime): void
    {
        $this->datetime = $datetime;
    }

    /**
     * @return int
     */
    public function getEvent(): int
    {
        return $this->event;
    }

    /**
     * @param int $event
     */
    public function setEvent(int $event): void
    {
        $this->event = $event;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return bool
     */
    public function isState(): bool
    {
        return $this->state;
    }

    /**
     * @param bool $state
     */
    public function setState(bool $state): void
    {
        $this->state = $state;
    }
}
