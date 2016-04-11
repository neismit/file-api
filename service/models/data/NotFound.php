<?php

namespace app\models\data;

/**
 * Exception for not found file, metadata
 *
 * @author andrey
 */
class NotFound extends \Exception {

    public function __construct($message = NULL, $code = NULL, $previous = NULL) {
        $message = 'Resource not found';
        parent::__construct($message, $code, $previous);
    }
}
