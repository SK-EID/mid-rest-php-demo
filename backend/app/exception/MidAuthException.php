<?php
/**
 * Created by IntelliJ IDEA.
 * User: mikks
 * Date: 2/12/2019
 * Time: 12:04 PM
 */
class MidAuthException extends RuntimeException {

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

//    public function getMessage() {
//        return this
//    }
}
//public class MidAuthException extends RuntimeException {
//
//public MidAuthException(Exception e) {
//super(e);
//}
//
//public MidAuthException(UserCancellationException e) {
//
//}
//
//    public MidAuthException(List<String> errors) {
//    super("Invalid authentication. " + String.join(", ", errors));
//}
//
//    public String getMessage() {
//        return this.getCause().getMessage();
//    }
//
//}