<?php
namespace Validate;

abstract class AbstractValidator
{
    private $db;
    protected $emailBathGroup;
    protected $errorMessage;
    protected $errors;

    public function __construct()
    {

    }

    public function __destruct()
    {

    }

    /**
     * Set fields to validate
     */
    abstract public function validateFirstName($firstName);
    abstract public function validateLastName($lastName);
    abstract public function validateAddress($address);
    abstract public function validateAddress2($address2);
    abstract public function validateCity($city);
    abstract public function validateState($state);
    abstract public function validateZipCode($zipCode);
    abstract public function validateCountry($country);
    abstract public function validatePhoneNumber($phoneNumber);
    abstract public function validatePromotionCode($promotionCode);
    abstract public function validateRetailStore($retailStore);
    abstract public function validateTransactionDate($date);

    /**
     * Get errors
     */
    public function getErrors()
    {
        return $this->errors;
    }


    /**
     * Validate batch file
     *
     * @param [type] $batchFile
     * @return void
     */
    public function validate($batchFile)
    {
        $fileOk = true;

        try {
            // prevent checking the first row
            $batchFile->next();

            while ($batchFile->valid()) {
                $rowErrors = $this->validateRow($batchFile->current());

                if (count($rowErrors) > 0) {
                    $fileOk = false;
                    $position = $batchFile->current();
                    $this->errors[] = "On batch row: {$position} there was an error with: " . implode(", ", $rowErrors);
                }
                $batchFile->next();
            }

            return $fileOk;
        } catch (\Exception $e) {
            // @todo handle error while downloading files







        }
    }

    /**
     * Validate row
     */
    public function validateRow($batchRow)
    {
        $errors = [];

        // Check if it's a Canadian zip code
        $pattern = '/^([a-zA-Z]\d[a-zA-Z])\ {0,1}(\d[a-zA-Z]\d)$/';
        $clean = preg_replace('/[^A-Z\d]/', '', strtoupper(trim($row->zip)));
        if (!preg_match($pattern, $clean)) {
            
            // If no match, invoke function that checks for zip code state mismatch. This is for US only. 
            $zipStateMatch = $this->validateZipState($row->zip, $row->state);
            if ($zipStateMatch == false) {
                $errors[] = "Zip code and State Mismatch";
            }
        }

        foreach ($batchRow as $key => $value) {

            $status = true;

            switch ($key) {
                case "firstName" :
                    $status = $this->validateFirstName($value);
                    break;
                case "lastName" :
                    $status = $this->validateLastName($value);
                    break;
                case "address" :
                    $status = $this->validateAddress($value);
                    break;
                case "address2" :
                    $tatus = $this->validateAddress2($value);
                    break;
                case "city" :
                    $status = $this->validateCity($value);
                    break;
                case "state" :
                    $status = $this->validateState($value);
                    break;
                case "zipCode" :
                    $status = $this->validateZipCode($value);
                    break;
                case "country";
                $status = $this->validateCountry($value);
                break;
            case "phoneNumber" :
                $status = $this->validatePhoneNumber($value);
                break;
            case "promo" :
                $status = $this->validateTransactionDate($value);
                break;
            case "retailStore" :
                $status = $this->validateRetailStore($value);
                break;
            case "date" :
                $status = $this->validateRetailStore($value);
                break;
            default :
                $status = false;
        }

        if (!status) {
            $errors[] = $key;
        }
    }

    return $errors;
}

}