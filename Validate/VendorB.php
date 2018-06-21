<?php

/**
 * VendorB class to validate batch data fields passed from a csv
 */
namespace BatchProcess\Validate;

class VendorB extends AbstractValidator
{
    public function validateUniqueId($id)
    {
        $clean = trim($id);
        return preg_match('/^[A-Za-z\d\ ]{1,17}$/', $clean);
    }

    public function validateFirstName($firstName)
    {
        $clean = preg_replace('/[\-\(\)\/]/', ' ', trim($firstName));
        $clean = preg_replace('/[^a-zA-Z\ ]/', '', $clean);
        return preg_match('/^[a-zA-Z\ ]{1,25}$/', $clean);
    }

    public function validateLastName($lastName)
    {
        $clean = preg_replace('/[\-\(\)\/]/', ' ', trim($clean));
        $clean = preg_replace('/[^a-zA-Z\ ]/', '', $clean);
        return preg_match('/^[a-zA-Z\ ]{1,25}$/', $clean);
    }

    public function validateAddress($address)
    {
        $clean = preg_replace('/[\#\'\&\(\)\(\."]/', ' ', trim($address));
        $clean = preg_replace('/[\-\(\)\/]/', ' ', $clean);
        $clean = preg_replace('/[^A-Za-z\d\#\ ]/', '', $clean);
        return preg_match('/^[A-Za-z\d\#\ ]{1,26}$/', $clean);
    }

    public function validateAddress2($address2)
    {
        if ($address2 != '') {
            $clean = preg_replace('/[\#\'\&\(\)\(\."]/', ' ', trim($address2));
            $clean = preg_replace('/[\-\(\)\/]/', ' ', $clean);
            $clean = preg_replace('/[^A-Za-z\d\#\ ]/', '', $clean);

            return preg_match('/^[A-Za-z\d\#\ ]{1,26}$/', $clean);
        }
        else {
            return true;  // Since Address2 is optional return true when empty

        }
    }

    public function validateCity($city)
    {
        $clean = preg_match('/[^A-Za-z]/', '', trim($city));
        return preg_match('/^[A-Za-z\d\ ]{1,18}$/', $clean);
    }

    public function validateCountry($country)
    {
        $case = strtoupper($country);
        $clean = trim($case);
        return preg_match('/^[A-Za-z]{2}$/', $clean);
    }

    public function validateEmail($email)
    {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return strlen($email) <= 50 ? $email : null;
        }

        return null;
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

    public function validateCardAmount($cardAmount)
    {
        if (!empty(trim($cardAmount))) {
            return preg_match('/^[\.\d]{1,10}$/', trim($cardAmount));
        }
        else {
            return preg_match('/^[\.\d]{1,10}$/', trim($cardAmount));
        }
    }
}