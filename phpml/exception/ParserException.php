<?php

namespace phpml\exception;

use phpml\exception\util\ExceptionMessage;
/**
 * Description of ParserException
 *
 * @author Thiago
 */
class ParserException extends \Exception {

    public function __construct($type, $file, $line, $details)
    {
        parent::__construct();
        
        $this->file    = $file;
        $this->line    = $line;
        $this->code    = $type;
        $this->message = ExceptionMessage::getMessage($type, $details);        
    }

}
?>
