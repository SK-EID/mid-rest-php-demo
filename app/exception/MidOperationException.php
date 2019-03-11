<?php
namespace Sk\Middemo\Exception;

use RuntimeException;
use Throwable;

class MidOperationException extends RuntimeException {

    protected $message;

    public function __construct($message = "", $code = 0, Throwable $previous = null, $errors = array())
    {
        parent::__construct($message, $code, $previous);
        if ($previous != null) {
            $this->message = $message . " Cause: " . $previous->getMessage();
        } else {
            if ($errors != null) {
                $this->message = "MID service returned validation errors: ";
                foreach ($errors as $error) {
                    $this->message .= ", ".$error;
                }
            } else {
                $this->message = $message;
            }
        }
    }

}
