<?php

/**
 * IssuerA class formats and cleans the data fields and prepares the file for delivery to the vendor
 */
namespace BatchProcess\Logic\Create;

use BatchProcess\Helpers\BatchRow;

class IssuerA extends AbstractCard
{
    public static $counter;
    protected $dbRow;
    private $filePath = 'issuer_a/batch_files/new/';
    private $deliveredPath = 'issuer_a/batch_files/delivered';
    private $count;
    private $delimiter = '|';
    private $retailerId;
    private $db;

    public function __construct()
    {
        $this->setDate();
        $this->setFileCounter();
        $this->count = self::$counter++;
        $this->setFilePrefix();
        $this->setFileName();
        $this->setDb();

        parent::__construct();
    }

    /**
     * Clean up database connection
     */
    public function __destruct()
    {
        if (is_a($this->db, 'Pdo')) {
            unset($this->db);
        }
    }

    /**
     * Formats the file according to the Issuer's file specs
     *
     * @param BatchRow $batchRow
     * @return bool
     */
    public function addCard(BatchRow $batchRow)
    {
        $this->format($batchRow);

        $isAdded = true;

        $data[] = $batchRow->id;
        $data[] = '';
        $data[] = $batchRow->firstName;
        $data[] = '';
        $data[] = $batchRow->lastName;
        $data[] = '';
        $data[] = '';
        $data[] = $batchRow->address;
        $data[] = $batchRow->address2;
        $data[] = $batchRow->city;
        $data[] = $batchRow->state;
        $data[] = $batchRow->zipCode;
        $data[] = '';
        $data[] = '';
        $data[] = '';
        $data[] = '';
        $data[] = '';
        $data[] = '';
        $data[] = '';
        $data[] = $batchRow->cardAmount;
        $data[] = '';
        $data[] = '';
        $data[] = '';
        $data[] = '';
        $data[] = '';

        $bytesWritten = $this->addRow($data);

        if ($bytesWritten > 0) {
            $this->count++;
        }
        else {
            $isAdded = false;
        }
        return $isAdded;
    }

    /**
     * Add rows to the file per card
     *
     * @param array $cards
     * @return bool
     */
    public function addCards(array $cards)
    {
        $fileOk = true;

        foreach ($cards as $card) {
            $isAdded = $this->addCard($card);

            if (!$isAdded) {
                $fileOk = false;
            }
        }
        return $fileOk;
    }

    /**
     * Adds a row to the file using VendorA's formatting
     * Using fputs instead of fputcsv because I don't want to use field enclosures.
     *
     * @param array $row
     * @return int
     */
    private function addRow(array $row)
    {
        return fputs($this->tmpFileHandle, implode($this->delimiter, $row) . '\n');
    }

    /**
     * Creates the file to be delivered to the card vendor
     *
     * @return int
     */
    public function createFile()
    {
        $fullPath = $this->basePath . $this->path;
        fclose($this->tmpFileHandle);

        return rename($this->tmpFileHandle, $fullPath . $this->fileName);
        chmod($fullPath . $this->fileName, 0644);
    }

    /**
     * Format and clean the properties of the Batch Row needed for the vendor
     *
     * @param BatchRow $batchRow
     * @return mixed
     */
    public function format(BatchRow &$batchRow)
    {
        $batchRow->firstName = $this->cleanName($batchRow->firstName);
        $batchRow->lastName = $this->cleanName($batchRow->lastName);
        $batchRow->address = $this->cleanAddress($batchRow->address);
        $batchRow->address2 = $this->cleanAddress($batchRow->address2);
        $batchRow->city = $this->cleanCity($batchRow->city);
        $batchRow->zip = $this->cleanZipCode($batchRow->zipCode);
        $batchRow->zip = $this->cleanCardAmount($batchRow->cardAmount);
    }

    /**
     * Sets the date to today in IssuerA file name format
     *
     * @return void
     */
    public function setDate()
    {
        $this->date = date('m_d_Y');
    }


    public function setFileCounter()
    {
        // Scan the new and the delivered directories
        $scanNewDir = scandir($this->basePath . $this->filePath);
        $scanDeliveredDir = scandir($this->basePath . $this->deliveredPath);
        $scannedFilteredFiles = [];

        // Merge the two directories that were scanned
        $scannedFiles = array_merge($scanNewDir, $scanDeliveredDir);

        // Regex pattern to match with current year
        $yearFilterPattern = '/' . date('Y') . '/';

        // Loop to filter the files of this current year
        foreach ($scannedFiles as $file) {
            if (preg_match($yearFilterPattern, $file)) {
                array_push($scannedFilteredFiles, $file);
            }
        }

        // Passing an array to get the max, which has been filtered with the year so that it will not mismatch
        // 12 of 2016 greater than 01 of 2017
        $lastFileCreated = max($scannedFilteredFiles);

        // This sets up the file name suffix to increment beginning at 100. ex: filename_100.txt
        $lastFileName = pathinfo($lastFileCreated, PATHINFO_FILENAME);
        if (trim(substr($lastFileName, 11, 10)) == $this->date) {
            $fileCounter = intval(substr($lastFileName, -2));

            self::$counter = $fileCounter + 1;
        }
        else {
            self::$counter = 100;
        }
    }

    /**
     * Sets the file name according to the vendor's specs
     *
     * @return void
     */
    protected function setFileName()
    {
        for ($i = $this->count; $i < 1000; $i++) {
            $name = $this->filePrefix . $this->date . '_' . str_pad($i, 2, '0');
            $this->fileName = $name . '.txt';
            if (!file_exists($this->basePath . $this->fileName)) {
                break;
            }
        }

        return $this->fileName;
    }

    /**
     * Sets the prefix for the file
     *
     * @return void
     */
    protected function setFilePrefix()
    {
        $this->filePrefix = 'IssuerA_';
    }

    /**
     * Function below rechecks for invalid charactes and string lengths according to the Issuer's file specs
     * 
     * cleanName($name)
     * cleanAddress($address)
     * cleanCity($city)
     * cleanZipCode($zip)
     * cleanCardAmount($amount)
     */

    protected function cleanName($name)
    {
        $clean = preg_replace('/[^a-zA-Z\ ]/', '', trim($name));
        return strtoupper(substr($clean, 0, 25));
    }

    protected function cleanAddress($address)
    {
        $clean = preg_replace('/[^a-zA-Z\d\ \#]/', '', trim($address));

        if (strlen($clean) > 35) {
            throw new \LengthException("Address field can not be longer than 35 characters.");
        }

        return strtoupper(substr($clean, 0, 35));
    }

    protected function cleanCity($city)
    {
        $clean = preg_replace('/[^a-zA-Z\ ]/', '', trim($city));
        return strtoupper(substr($clean, 0, 20));
    }

    protected function cleanZipCode($zip)
    {
        $clean = preg_replace('/[^\d]/', '', trim($zip));

         // Pads left zip code with leading zeros that have been cleaned
        $clean = str_pad($clean, 5, '0', \STR_PAD_LEFT);
        return substr($clean, 0, 5);
    }

    protected function cleanCardAmount($amount)
    {
        $clean = preg_replace('/[^\d]/', '', trim($amount));
        return preg_replace('/[^0-9]/', '', $clean);
    }
}