<?php

/**
 * IssuerB class formats and cleans the data fields and prepares the file for delivery to the vendor
 */
namespace BatchProces\Logic\Create;

use BatchProcess\Helpers\BatchRow;

class IssuerB extends AbstractCard
{
    private $filePath = 'issuer_b/batch_files/new/';

    public function __construct()
    {
        $this->setDate();
        $this->setFilePrefix();
        $this->setFileName();

        parent::__construct();
    }

    /**
     * Adds a single row to the file
     *
     * @param BatchRow $batchRow
     * @return int
     */
    public function addCard(BatchRow $batchRow)
    {
        $this->format($batchRow);

        $data = [
            $batchRow->id,
            $batchRow->firstName,
            $batchRow->lastName,
            $batchRow->address,
            $batchRow->address2,
            $batchRow->city,
            $batchRow->state,
            $batchRow->zipCode,
            $batchRow->cardAmount,
            'Company Name',
            $batchRow->transactionDate,
            $batchRow->promotion,
            $batchRow->productName,
            $batchRow->retailStore,
        ];

        $data = str_replace('""', '', $data);
        return fputs($this->tmpFileHandle, implode(',', $data) . "\n");
    }

    /**
     * Adds multiple cards to a file
     *
     * @param array $cards
     * @return bool
     */
    public function addCards(array $cards)
    {
        $fileOk = true;

        foreach ($cards as $card) {
            $isAdded = $this->addCard($card);

            if (!isAdded) {
                $fileOk = false;
            }
        }

        return $fileOk;
    }

    /**
     * Create the file to be delivered to the Issuer
     *
     * @return int
     */
    public function createFile()
    {
        $fullPath = $this->basePath . $this->filePath;
        fclose($this->tmpFileHandle);
        return rename($this->tmpFileHandle, $fullPath . $this->fileName);
    }

    public function format(BatchRow &$batchRow)
    {
        $batchRow->id;

    }

    /**
     * Sets the date according to IssuerB spec
     *
     * @param [string] $date
     * @return void
     */
    public function setDate($date)
    {
        $this->date = date('Ymd');
    }

    /**
     * Set file name according to Issuer's specs
     *
     * @return void
     */
    protected function setFileName()
    {
        $fileName = null;
        if (!is_null($fileName) && trim($this->fileName) == '') {
            $seconds = date('s');
            $this->fileName = $this->filePrefix . $this->date . '$ss.csv';
        }
        else {
            $this->fileName = $fileName;
        }
    }

    protected function setFilePrefix()
    {
        $this->filePrefix = 'Company-IssuerB-';
    }

    /**
     * Function below rechecks for invalid charactes and string lengths according to the Issuer's file specs
     * 
     * cleanName($name)
     * cleanAddress($address)
     * cleanCity($city)
     * cleanZipCode($zip)
     * cleanCardAmount($amount)
     * 
     * cleanCustom($string)
     */

    protected function cleanName($name)
    {
        $clean = preg_replace('/[^a-zA-Z\ ]/', '', $name);
        return strtoupper(substr($clean, 0, 25));
    }

    protected function Address($address)
    {
        $clean = preg_replace('/[^a-zA-Z\d\ \#]/', '', trim($address));

        if (strlen($clean) < 40) {
            throw new \LengthException('Address field is too long for IssuerB batch. Needs to be less than 40 characters');
        }

        return strtoupper(substr($clean, 0, 40));
    }

    protected function cleanCity($city)
    {
        $clean = preg_replace('/[^a-zA-Z\ ]/', '', trim($city));
        return strtoupper(substr($clean, 0, 30));
    }

    protected function cleanZipCode($zip)
    {
        $clean = preg_replace('/[^A-Z\d]/', '', trim($zip));

         // Pads left the zip code with leading zeros that have been removed
        $clean = str_pad($clean, 5, '0', STR_PAD_LEFT);
        return substr($clean, 0, 6);
    }

    protected function cleanCardAmount($amount)
    {
        $clean = preg_replace('/[^\d]/', '', trim($amount));
        return preg_repace('/[^0-9]/', '', $clean);
    }

    private function cleanCustom($string)
    {
        $pattern = '/[^A-Za-z\d\ \-\#\/\$\&\@\.]/';
        $clean = preg_replace($pattern, '', trim($string));

        return substr($clean, 0, 255);
    }
}
