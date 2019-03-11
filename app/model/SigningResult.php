<?php
namespace Sk\Middemo\Model;

use DateTime;

class SigningResult
{
    /** @var string $result */
    private $result;
    /** @var boolean $valid */
    private $valid;
    /** @var DateTime $timestamp */
    private $timestamp;
    /** @var string $containerFilePath */
    private $containerFilePath;
    public function __construct(Builder $builder)
    {
        $this->result = $builder->getResult();
        $this->valid = $builder->getValid();
        $this->timestamp = $builder->getTimestamp();
        $this->containerFilePath = $builder->getContainerFilePath();
    }
    /**
     * @return string
     */
    public function getResult(): string
    {
        return $this->result;
    }
    /**
     * @return bool
     */
    public function getValid(): bool
    {
        return $this->valid;
    }
    /**
     * @return DateTime
     */
    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }
    /**
     * @return string
     */
    public function getContainerFilePath(): string
    {
        return $this->containerFilePath;
    }
    public static function newBuilder() : Builder
    {
        return new Builder();
    }
}
class Builder
{
    /** @var string $result */
    private $result;
    /** @var boolean $valid */
    private $valid;
    /** @var DateTime $timestamp */
    private $timestamp;
    /** @var string $containerFilePath */
    private $containerFilePath;
    /**
     * Builder constructor.
     */
    public function __construct()
    {
    }
    /**
     * @return string
     */
    public function getResult(): string
    {
        return $this->result;
    }
    /**
     * @return bool
     */
    public function getValid(): bool
    {
        return $this->valid;
    }
    /**
     * @return DateTime
     */
    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }
    /**
     * @return string
     */
    public function getContainerFilePath(): string
    {
        return $this->containerFilePath;
    }
    public function withResult(string $result) : Builder
    {
        $this->result = $result;
        return $this;
    }
    public function withValid(bool $valid) : Builder
    {
        $this->valid = $valid;
        return $this;
    }
    public function withTimestamp(DateTime $dateTime) : Builder
    {
        $this->datetime = $dateTime;
        return $this;
    }
    public function withContainerFilePath(string $containerFilePath) : Builder
    {
        $this->containerFilePath = $containerFilePath;
        return $this;
    }
    public function build(): SigningResult
    {
        return new SigningResult($this);
    }
}
