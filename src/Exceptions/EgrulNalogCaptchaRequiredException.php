<?php


namespace EgrulNalogClient\Exceptions;


use Throwable;

class EgrulNalogCaptchaRequiredException extends EgrulNalogException {

    public function __construct($message = 'Captcha required!', $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}
