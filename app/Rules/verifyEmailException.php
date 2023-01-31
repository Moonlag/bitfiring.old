<?php


namespace App\Rules;
use Exception;

/**
 * verifyEmail exception handler
 */
class verifyEmailException extends Exception {

    /**
     * Prettify error message output
     * @return string
     */
    public function errorMessage() {
        $errorMsg = $this->getMessage();
        return $errorMsg;
    }

}
