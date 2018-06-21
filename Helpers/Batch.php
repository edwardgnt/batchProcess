<?php

/**
 * BatchProcess class opens, initializes, and structures the batch file
 * input - csv file
 */
namespace BatchProcess\Helpers;

class Batch implements \Iterator
{
    private $position = 0;
    private $rows = [];

    /**
     * Accepts an array coming from a csv file.
     *
     * @param [array] $file
     */
    public function __construct($file)
    {
        $this->initialize($file);
    }

    /**
     * Accepts an array of a csv file
     *
     * @param [array] $file
     * @return void
     */
    private function initialize($file)
    {
        if (!is_readable($file)) {
            throw new \RuntimeException("Unreadable file: {$file}");
        }

        $fileHandler = fopen($file, 'r');

        while ($row = fgetscsv($fileHandler)) {
            $this->rows[] = new BatchRow($row);
        }
    }

    /**
     * Return the current element
     *
     * @return mixed - any type
     */
    public function current()
    {
        $isValid = $this->valid();

        if ($isValid) {
            return $this->rows[$this->position];
        }
        else {
            throw new \OutOfRangeException();
        }
    }

    /**
     * Returns the current position and increments by 1 for the exact row for any errors
     *
     * @return void
     */
    public function currentPosition()
    {
        $isValid = $this->valid();

        if ($isValid) {
            return $this->position + 1;
        }
        else {
            throw new \OutOfRangeException();
        }
    }
}