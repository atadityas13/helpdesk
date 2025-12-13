<?php
/**
 * Input Validation Helper
 * Helpdesk MTsN 11 Majalengka
 */

class Validator {
    private $errors = [];
    private $data = [];
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    /**
     * Validate required field
     */
    public function required($field, $message = null) {
        if (empty($this->data[$field])) {
            $this->errors[$field] = $message ?? "$field harus diisi";
        }
        return $this;
    }
    
    /**
     * Validate email format
     */
    public function email($field, $message = null) {
        if (!empty($this->data[$field])) {
            if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field] = $message ?? "$field harus berupa email yang valid";
            }
        }
        return $this;
    }
    
    /**
     * Validate minimum length
     */
    public function min($field, $length, $message = null) {
        if (!empty($this->data[$field])) {
            if (strlen($this->data[$field]) < $length) {
                $this->errors[$field] = $message ?? "$field minimal harus $length karakter";
            }
        }
        return $this;
    }
    
    /**
     * Validate maximum length
     */
    public function max($field, $length, $message = null) {
        if (!empty($this->data[$field])) {
            if (strlen($this->data[$field]) > $length) {
                $this->errors[$field] = $message ?? "$field maksimal $length karakter";
            }
        }
        return $this;
    }
    
    /**
     * Validate field value is in allowed list
     */
    public function in($field, $values, $message = null) {
        if (!empty($this->data[$field])) {
            if (!in_array($this->data[$field], $values)) {
                $this->errors[$field] = $message ?? "$field nilai tidak valid";
            }
        }
        return $this;
    }
    
    /**
     * Validate numeric
     */
    public function numeric($field, $message = null) {
        if (!empty($this->data[$field])) {
            if (!is_numeric($this->data[$field])) {
                $this->errors[$field] = $message ?? "$field harus berupa angka";
            }
        }
        return $this;
    }
    
    /**
     * Validate phone number (basic)
     */
    public function phone($field, $message = null) {
        if (!empty($this->data[$field])) {
            $phone = preg_replace('/[^0-9+\-\s]/', '', $this->data[$field]);
            if (strlen($phone) < 10) {
                $this->errors[$field] = $message ?? "$field harus berupa nomor telepon yang valid";
            }
        }
        return $this;
    }
    
    /**
     * Check apakah ada errors
     */
    public function isValid() {
        return empty($this->errors);
    }
    
    /**
     * Get semua errors
     */
    public function errors() {
        return $this->errors;
    }
    
    /**
     * Get error untuk field tertentu
     */
    public function getError($field) {
        return $this->errors[$field] ?? null;
    }
    
    /**
     * Get validated data (sanitized)
     */
    public function getData() {
        $sanitized = [];
        foreach ($this->data as $key => $value) {
            $sanitized[$key] = is_string($value) ? sanitizeInput($value) : $value;
        }
        return $sanitized;
    }
}

?>
