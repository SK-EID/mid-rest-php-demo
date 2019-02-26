<?php
/**
 * Created by IntelliJ IDEA.
 * User: mikks
 * Date: 2/12/2019
 * Time: 12:06 PM
 */
class MidSignException extends RuntimeException {
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
//public class MidSignException extends RuntimeException {
//
//public MidSignException(Exception e) {
//super(e);
//}
//
//public MidSignException(String message) {
//    super(message);
//}
//}
