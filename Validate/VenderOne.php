<?php
namespace BatchProcess\Validate;

class VendorOne extends AbstractValidator
{
    public function validateUniqueId($id)
    {
        $clean = preg_replace('/[^\d]/', '', trim($id));
        return preg_match('/^[\d]{1,30}$/', $clean);
    }
    
    public function validateFirstName($firstName)
    {
        $clean = preg_replace('/[\-\(\)\/]/', ' ', trim($firstName));
        $clean = preg_replace('/[^a-zA-Z\ ]/', '', $clean);
        return preg_match('/^[a-zA-Z\ ]{1,25}$/', $clean);
    }

    public function validateLastName($lastName)
    {
        $clean = preg_replace('/[\-\(\)\/]/', ' ', trim($lastName));
        $clean = preg_replace('/[^a-zA-Z\ ]/', '', $clean);
        return preg_match('/^[a-zA-Z\ ]{1,25}$/', $clean);
    }

    public function validateAddress($address)
    {
        $clean = preg_replace('/[^a-zA-Z\d\ \#]/', '', trim($address));
        return preg_match('/^[a-zA-Z\d\ \#]{1,35}$/', $clean);
    }

    public function validateAddress2($address2)
    {
        if($address2 != '') {
            $clean = preg_replace('/[^a-zA-Z\d\ \#]/', '', trim($address2));
            return preg_match('/^[a-zA-Z\d\ \#]{1,35}$/', $clean);
        } else {
            return true;
        }
    }

    public function validateCity($city)
    {
        $clean = preg_replace('/[^a-zA-Z\ ]/', '', trim($city));
        return preg_match('/^[a-zA-Z\ ]{1,35}$/', $clean);
    }

    public function validateCountry($country)
    {
        // This vendor only ships to US only
        return true;
    }

    public function validateRetailStore($retailStore)
    {
        return trim($retailStore);
    }

    public function validatePromotion($promotion)
    {
        return trim($promotion);
    }

    public function validateProductName($productName)
    {
        return trim($productName);
    }

    public function validateTransactionDate($transactionDate)
    {
        return trim($transactionDate);
    }
}

