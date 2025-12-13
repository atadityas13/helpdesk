<?php
/**
 * Input Validation Helper Functions
 */

/**
 * Validate required field
 * @param mixed $value
 * @param string $fieldName
 * @return bool
 */
function validateRequired($value, $fieldName = 'Field') {
    if (empty($value)) {
        throw new Exception("$fieldName tidak boleh kosong");
    }
    return true;
}

/**
 * Validate min length
 * @param string $value
 * @param int $minLength
 * @param string $fieldName
 * @return bool
 */
function validateMinLength($value, $minLength, $fieldName = 'Field') {
    if (strlen($value) < $minLength) {
        throw new Exception("$fieldName minimal " . $minLength . " karakter");
    }
    return true;
}

/**
 * Validate max length
 * @param string $value
 * @param int $maxLength
 * @param string $fieldName
 * @return bool
 */
function validateMaxLength($value, $maxLength, $fieldName = 'Field') {
    if (strlen($value) > $maxLength) {
        throw new Exception("$fieldName maksimal " . $maxLength . " karakter");
    }
    return true;
}

/**
 * Validate email
 * @param string $email
 * @param string $fieldName
 * @return bool
 */
function validateEmail($email, $fieldName = 'Email') {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("$fieldName tidak valid");
    }
    return true;
}

/**
 * Validate phone
 * @param string $phone
 * @param string $fieldName
 * @return bool
 */
function validatePhone($phone, $fieldName = 'Telepon') {
    if (!preg_match('/^(\+62|0)8[0-9]{8,11}$/', preg_replace('/[^0-9+]/', '', $phone))) {
        throw new Exception("$fieldName tidak valid");
    }
    return true;
}

/**
 * Validate ticket number format
 * @param string $ticketNumber
 * @return bool
 */
function validateTicketNumber($ticketNumber) {
    if (!preg_match('/^TK-\d{8}-\d{5}$/', $ticketNumber)) {
        throw new Exception('Format nomor ticket tidak valid');
    }
    return true;
}

/**
 * Validate enum value
 * @param string $value
 * @param array $allowedValues
 * @param string $fieldName
 * @return bool
 */
function validateEnum($value, $allowedValues, $fieldName = 'Field') {
    if (!in_array($value, $allowedValues)) {
        throw new Exception("$fieldName tidak valid");
    }
    return true;
}

/**
 * Validate numeric value
 * @param mixed $value
 * @param string $fieldName
 * @return bool
 */
function validateNumeric($value, $fieldName = 'Field') {
    if (!is_numeric($value)) {
        throw new Exception("$fieldName harus berupa angka");
    }
    return true;
}

/**
 * Validate integer
 * @param mixed $value
 * @param string $fieldName
 * @return bool
 */
function validateInteger($value, $fieldName = 'Field') {
    if (!is_int($value) && !ctype_digit((string)$value)) {
        throw new Exception("$fieldName harus berupa angka bulat");
    }
    return true;
}

/**
 * Validate date format
 * @param string $date
 * @param string $format
 * @param string $fieldName
 * @return bool
 */
function validateDateFormat($date, $format = 'Y-m-d', $fieldName = 'Tanggal') {
    $d = DateTime::createFromFormat($format, $date);
    if (!$d || $d->format($format) !== $date) {
        throw new Exception("$fieldName tidak valid");
    }
    return true;
}

/**
 * Sanitize HTML
 * @param string $html
 * @return string
 */
function sanitizeHtml($html) {
    return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate and sanitize all POST data
 * @param array $requirements Contoh: ['name' => ['required', 'minLength:3', 'maxLength:255']]
 * @return array
 */
function validatePostData($requirements) {
    $validated = [];
    
    foreach ($requirements as $field => $rules) {
        if (!isset($_POST[$field])) {
            if (in_array('required', $rules)) {
                throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' tidak boleh kosong');
            }
            $validated[$field] = null;
            continue;
        }
        
        $value = $_POST[$field];
        
        // Apply rules
        foreach ($rules as $rule) {
            if ($rule === 'required') {
                validateRequired($value, ucfirst(str_replace('_', ' ', $field)));
            } elseif (strpos($rule, 'minLength:') === 0) {
                $length = (int)substr($rule, 10);
                validateMinLength($value, $length, ucfirst(str_replace('_', ' ', $field)));
            } elseif (strpos($rule, 'maxLength:') === 0) {
                $length = (int)substr($rule, 10);
                validateMaxLength($value, $length, ucfirst(str_replace('_', ' ', $field)));
            } elseif ($rule === 'email') {
                validateEmail($value, ucfirst(str_replace('_', ' ', $field)));
            } elseif ($rule === 'phone') {
                validatePhone($value, ucfirst(str_replace('_', ' ', $field)));
            } elseif ($rule === 'integer') {
                validateInteger($value, ucfirst(str_replace('_', ' ', $field)));
            } elseif ($rule === 'numeric') {
                validateNumeric($value, ucfirst(str_replace('_', ' ', $field)));
            }
        }
        
        // Sanitize
        $validated[$field] = sanitizeHtml($value);
    }
    
    return $validated;
}
