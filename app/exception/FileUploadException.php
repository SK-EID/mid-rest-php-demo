<?php
namespace Sk\Mid\Demo\Exception;

use RuntimeException;
use Throwable;
class FileUploadException extends RuntimeException {
    public function __construct($message = "", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }


}
