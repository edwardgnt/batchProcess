<?php

/**
 * BatchRow class structures the batch row
 */

namespace BatchProcess\Helpers;

class BatchRow
{
    public $id;
    public $firstName;
    public $lastName;
    public $address;
    public $address2;
    public $city;
    public $state;
    public $zipCode;
    public $country;
    public $email;
    public $retailStore;
    public $promotion;
    public $productName;
    public $transactionDate;
    public $cardAmount;

    public function __construct(array $row)
    {
        $this->initialize($row);
    }

    private function initialize(array $row)
    {
        if (!is_array($row)) {
            throw new \InvalidArgumentException('Need an array to be passed');
        }

        if (count($row) !== 15) {
            throw new \UnexpectedValueException('Expecting 15 total columns');
        }

        $this->id = $row[0];
        $this->firstName = $row[1];
        $this->lastName = $row[2];
        $this->address = $row[3];
        $this->address2 = $row[4];
        $this->city = $row[5];
        $this->state = $row[6];
        $this->zipCode = $row[7];
        $this->county = $row[8];
        $this->email = $row[9];
        $this->retailStore = $row[10];
        $this->promotion = $row[11];
        $this->productName = $row[12];
        $this->transactionDate = $row[13];
        $this->cardAmount = $row[14];
    }
}


