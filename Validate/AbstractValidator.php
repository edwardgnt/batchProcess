<?php
namespace Validate;

abstract class AbstractValidator
{

    private $db;
    protected $emailBatchGroup;
    protected $errorMessage;
    protected $errors;

    public function __construct()
    {
        $this->setDb();
    }

    public function __destruct()
    {
        if (is_a($this->Db, 'Pdo')) {
            unset($this->Db);
        }
    }

    /**
     * Get database connection
     *
     * @return void
     */
    protected function getDb()
    {
        if (!is_a($this->Db, 'Pdo')) {
            $this->setDb();
        }
        return $this->Db;
    }

    /**
     * Set the database connection
     *
     * @return void
     */
    protected function setDb()
    {
        $this->Db = \path\to\Database::connection();
    }

    /**
     * Set validation fields
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
    abstract public function validateEmail($email);
    abstract public function validateRetailStore($retailStore);
    abstract public function validatePromotion($promotion);
    abstract public function validateTransactionDate($transactionDate);

    /**
     * Get errors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    public function validate($batchFile)
    {
        $fileOk = true;

        try {
            // Prevent checking the first row
            $batchFile->next();

            while($batchFile->valid()) {
                $rowErrors = $this->validateRow($batchFile->current());

                if(count($rowErrors) > 0) {
                    $fileOk = false;
                    $position = $batchFile->current();
                    $this->errors[] = "On row: {$position} there was an error with: " . implode(", ", $rowErrors);
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
        $clean = preg_replace('/[^A-Z\d]/', '', strtoupper(trim($batchRow->zip)));
        if (!preg_match($pattern, $clean)) {

                // If not a match, invoke this function to check for zip code state mismatch via database. U.S. only
            $zipStateMatch = $this->validateZipAndState($batchRow->zip, $batchRow->state);
            if ($zipStateMatch == false) {
                $errors[] = "Mismatch for zip code and state";
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
                    $status = $this->validateAddress2($value);
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
                case "country" :
                    $status = $this->validateCountry($value);
                    break;
                case "phoneNumber" :
                    $status = $this->validatePhoneNumber($value);
                    break;
                case "email" :
                    $status = $this->validateEmail($value);
                    break;
                case "retailStore" :
                    $status = $this->validateRetailStore($value);
                    break;
                case "promotion" :
                    $status = $this->validatePromotion($value);
                    break;
                case "transactionDate" :
                    $status = $this->validateTransactionDate($value);
                    break;
                default :
                    $status = false;
            }

            if (!$status) {
                $errors[] = $key;
            }
        }
        return $errors;
    }
}
