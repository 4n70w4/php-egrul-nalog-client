<?php


namespace EgrulNalogClient\Exceptions;


use Throwable;

class EgrulNalogNotFoundException extends EgrulNalogException {

    public function __construct($message = 'Not found organization! Repeat request a bit later.', $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}
