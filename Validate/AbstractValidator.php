<?php
namespace BatchProces\Validate;

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
        if (is_a($this->db, 'Pdo')) {
            unset($this->db);
        }
    }

    /**
     * Get database connection
     *
     * @return void
     */
    protected function getDb()
    {
        if (!is_a($this->db, 'Pdo')) {
            $this->setDb();
        }
        return $this->db;
    }

    /**
     * Set the database connection
     *
     * @return void
     */
    protected function setDb()
    {
        $this->db = \path\to\Database::connection();
    }

    /**
     * Set validation fields array coming in from a csv file
     */
    abstract public function validateUniqueId($id);
     abstract public function validateFirstName($firstName);
    abstract public function validateLastName($lastName);
    abstract public function validateAddress($address);
    abstract public function validateAddress2($address2);
    abstract public function validateCity($city);
    abstract public function validateCountry($country);
    abstract public function validateEmail($email);
    abstract public function validateRetailStore($retailStore);
    abstract public function validatePromotion($promotion);
    abstract public function validateProductName($productName);
    abstract public function validateTransactionDate($transactionDate);
    abstract public function validateCardAmount($cardAmount);

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

            while ($batchFile->valid()) {
                $rowErrors = $this->validateRow($batchFile->current());

                if (count($rowErrors) > 0) {
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
     * Validate batch row
     */
    public function validateRow($batchRow)
    {
        $errors = [];

            // Check if it's a Canadian zip code
        $pattern = '/^([a-zA-Z]\d[a-zA-Z])\ {0,1}(\d[a-zA-Z]\d)$/';
        $clean = preg_replace('/[^A-Z\d]/', '', strtoupper(trim($batchRow->zip)));
        if (!preg_match($pattern, $clean)) {

                // If not a match, invoke this function to check for zip code state mismatch via database. U.S. only
            $zipStateMatch = $this->validateZipState($batchRow->zip, $batchRow->state);
            if ($zipStateMatch == false) {
                $errors[] = "Mismatch for zip code and state";
            }
        }

        foreach ($batchRow as $key => $value) {
            $status = true;

            switch ($key) {
                case "uniqueId":
                    $status = $this->validateUniqueId($value);
                    break;
                case "firstName":
                    $status = $this->validateFirstName($value);
                    break;
                case "lastName":
                    $status = $this->validateLastName($value);
                    break;
                case "address":
                    $status = $this->validateAddress($value);
                    break;
                case "address2":
                    $status = $this->validateAddress2($value);
                    break;
                case "city":
                    $status = $this->validateCity($value);
                    break;
                case "state":
                    $status = $this->validateState($value);
                    break;
                case "zipCode":
                    $status = $this->validateZipCode($value);
                    break;
                case "country":
                    $status = $this->validateCountry($value);
                    break;
                case "email":
                    $status = $this->validateEmail($value);
                    break;
                case "retailStore":
                    $status = $this->validateRetailStore($value);
                    break;
                case "promotion":
                    $status = $this->validatePromotion($value);
                    break;
                case "productName":
                    $status = $this->validateProductName($value);
                    break;
                case "transactionDate":
                    $status = $this->validateTransactionDate($value);
                    break;
                case "cardAmount":
                    $status = $this->validateCardAmount($value);
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

    /**
     * Checks if there is a match of Zip and State fields
     *
     * @param [string] $zip - Five digit zip code
     * @param [string] $state - Two letter abbreviation
     * @param boolean $strict
     * @return void
     */
    public function validateZipState($zip, $state, $strict = true)
    {
        if (!preg_match('/^\d{3,5}$/', $zip) || !$this->validateState($state)) {
            return false;
        }

        //Pads left for the zipcode with leading zeros that have been cleaned
        $zipCode = str_pad($zip, 5, "0", STR_PAD_LEFT);

        $query = "SELECT zip FROM zipcodes WHERE zip=?";
        $db = $this->getDb();
        $statement = $db->prepare($query);
        $isOk = $statement->execute([$zip]);

        $zipState = null;
        if ($isOk) {
            $zipState = $statement->fetchColumn();
        }

        $isValid = false;
        if (strtoupper($state) == strtoupper($zipState)) {
            // Zip and State are a match
            $isValid = true;
        }
        elseif ($isOk && !$strict && $zipState == '') {
            $isValid = true;
        }
        return $isValid;
    }

    /**
     * Validates the state
     *
     * @param [string] $state
     * @return void
     */
    public function validateState($state)
    {
        // Regex for US states, territories, and military states. Along with Canadian territories
        $regexPattern = '/^(?:(A[ABEKLPRZ]|BC|C[AOT]|D[CE]|FL|GA|HI|I[ADLN]|K[SY]|LA|M[ABDEINOST]|N[BCDEHJLMSVY]|O[HKNR]|P[AER]|QC|RI|S[CDK]|T[NX]|UT|V[AIT]|W[AIVY]))$/';
        return preg_match($regexPattern, strtoupper(trim($state)));
    }

    /**
     * Validate Zip Code
     *
     * @param [string] $zip
     * @return void
     */
    public function validateZip($zip)
    {
        $patternMatch = '/(^\d{3,5}$|^\d{5}(-\d{4})?$)|(^[ABCEGHJKLMNPRSTVXY]{1}\d{1}[A-Z]{1} *\d{1}[A-Z]{1}\d{1}$)/';
        $clean = preg_replace('/[^A-Z\d]/', '', strtoupper(trim($zip)));
        return preg_match($patternMatch, $clean);
    }
}
