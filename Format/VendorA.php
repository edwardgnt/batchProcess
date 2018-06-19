<?php
namespace BatchProcess\Format;

class VendorA extends AbstractFile
{
    public static $counter;
    protected $dbRow;
    private $filePath = 'vendor_a/batch_files/new/';
    private $deliveredPath = 'vendor_a/batch_files/delivered';
    private $date;
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
        if(is_a($this->db, 'Pdo')) {
            unset($this->db);
        }   
    }

    public function addCard($batchRow)
    {
        $this->format($batchRow);

        $isAdded = true;

    }

    /**
     * Sets the date to today in VendorA's file name format
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
        foreach($scannedFiles as $file) {
                if(preg_match($yearFilterPattern, $file)) {
                array_push($scannedFilteredFiles, $file);
            }
        }

        // Passing an array to get the max, which has been filtered with the year so that it will not mismatch
        // 12 of 2016 greater than 01 of 2017
        $lastFileCreated = max($scannedFilteredFiles);

        // This sets up the file name suffix to increment beginning at 100. ex: filename_100.txt
        $lastFileName = pathinfo($lastFileCreated, PATHINFO_FILENAME);
        if(trim(substr($lastFileName, 11, 10)) == $this->date) {
            $fileCounter = intval(substr($lastFileName, -2));

            self::$counter = $fileCounter + 1;
        } else {
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
        for($i = $this->count; $i<1000; $i++) {
            $name = $this->filePrefix . $this->date . '_' . str_pad($i, 2, '0');
            $this->fileName = $name . '.txt';
            if(!file_exists($this->basePath . $this->fileName)) {
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
        $this->filePrefix = 'VendorA_';
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




}