<?php

/**
 * This is an abstract class for the three vendor classes that will format the file ready for delivery to the vendor
 */
namespace BatchProces\Logic\Create;

use BatchProcess\Helpers\BatchRow;

abstract class AbstractCard
{
    protected $basePath = '/files/batch_files';
    protected $fileName;
    protected $filePrefix;
    protected $tmpDir;
    protected $tmpFileHandle;
    protected $tmpFileName;
    protected $date;

    public function __construct()
    {
        $this->tmpFileName = tempnam($this->tmpDir, 'Batch_tmp_');
        $this->tmpFileHandle = fopen($this->tmpFileName, 'wb') or die('Could not open temp file');
    }

    abstract public function addCard(BatchRow $batchRow);

    /**
     * Adds formated cards to the file and returns a boolean
     *
     * @param array $cards
     * @return bool
     */
    abstract public function addCards(array $cards);

    /**
     * Formats the batch row according to the vendor's specs
     *
     * @param [array] $batchRow
     * @return mixed
     */
    abstract public function format(BatchRow &$batchRow);

    /**
     * Sets the date format
     *
     * @param [string] $date
     * @return string
     */
    abstract public function setDate();

    /**
     * Sets the file name
     *
     */
    abstract protected function setFileName();

    /**
     * Sets the prefix to the file
     *
     */
    abstract protected function setFilePrefix();

    /**
     * Checks for any invalid characters according to the Issuer's specs
     * Only allows for a certain length of characters to pass
     * Converts to uppercase
     *
     * @param [stirng] $name
     * @return string
     */
    abstract protected function cleanName($name);

    /**
     * Checks for any invalid characters according to the Issuer's specs
     * Only allows for a certain length characters to pass
     *
     * @param [string] $address
     * @return string
     */
    abstract protected function cleanAddress($address);

    /**
     * Checks for any invalid characters according the Issuer's specs
     * Converts to uppercase
     *
     * @param [string] $city
     * @return string
     */
    abstract protected function cleanCity($city);

    /**
     * Checks for invalid characters
     *
     * @param [string] $zip
     * @return string
     */
    abstract protected function cleanZipCode($zip);

    /**
     * Checks for invalid characters
     *
     * @param [string] $amount
     * @return string
     */
    abstract protected function cleanCardAmount($amount);

    /**
     * Cleans up the file handle
     */
    public function __destruct()
    {
        if (isset($this->tmpFileHandle) && is_resource($this->tmpFileHandle)) {
            fclose($this->tmpFileHandle);
        }
    }

    /**
     * Create the file
     *
     * @return void
     */
    public function createFile()
    {
        fclose($this->tmpFileHandle);
        return rename($this->tmpFileName, $this->basePath . $this->fileName);
    }

    /**
     * Be sure a two letter state code is passed. Otherwise throw an exception
     *
     * @param [string] $state
     * @return void
     */
    protected function cleanState($state)
    {
        $clean = trim($state);

        if (strlen($clean) > 2) {
            throw new UnexpectedValueException("Invalid state value being used to create batch");
        }

        return strtoupper($clean);
    }

    /**
     * Get the name of the file
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }
}