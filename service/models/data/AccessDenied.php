<?php

namespace app\models\data;

/**
 * Exception for acess denied user on file
 *
 * @author andrey
 */
class AccessDenied extends \Exception {

    public function __construct($message = NULL, $code = NULL, $previous = NULL) {
        $message = 'Access is denied';
        parent::__construct($message, $code, $previous);
    }
}
