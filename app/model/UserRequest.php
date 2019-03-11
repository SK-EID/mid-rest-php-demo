<?php
namespace Sk\Mid\Demo\Model;

class UserRequest
{
    /** @var string $phoneNumber */
    private $phoneNumber;
    /** @var string $nationalIdentityNumber */
    private $nationalIdentityNumber;
    /** @var \Symfony\Component\HttpFoundation\File\File $file */
    private $file;
    /**
     * @return string
     */
    public function getPhoneNumber(): string
    {
        return $this->phoneNumber != null &&
        preg_match('/^\+37[0-9]{5,10}$/', $this->phoneNumber) ?
            $this->phoneNumber : null;
    }
    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }
    /**
     * @return string
     */
    public function getNationalIdentityNumber(): string
    {
        return $this->nationalIdentityNumber != null &&
        preg_match('/^[0-9]{11}/', $this->nationalIdentityNumber) ?
            $this->nationalIdentityNumber : null;
    }
    /**
     * @param string $nationalIdentityNumber
     */
    public function setNationalIdentityNumber(string $nationalIdentityNumber): void
    {
        $this->nationalIdentityNumber = $nationalIdentityNumber;
    }
    /**
     * @return \Symfony\Component\HttpFoundation\File\File
     */
    public function getFile(): \Symfony\Component\HttpFoundation\File\File
    {
        return $this->file;
    }
    /**
     * @param \Symfony\Component\HttpFoundation\File\File $file
     */
    public function setFile(\Symfony\Component\HttpFoundation\File\File $file): void
    {
        $this->file = $file;
    }
}
