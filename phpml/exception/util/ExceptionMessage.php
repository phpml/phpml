<?php

namespace phpml\exception\util;
/**
 * Description of ExceptionMessage
 *
 * @author Thiago
 */
class ExceptionMessage {

    private static $messages = array(
        ExceptionType::UNEXPECTED_CHAR => 'Unexpected char %s'
    );

    public static function getMessage($type, $details)
    {
        return vsprintf(self::$messages[$type], (array) $details);
    }
}
?>
