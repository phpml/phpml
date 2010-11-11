<?php

namespace phpml\exception;

/**
 * Base IOException class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package phpml
 * @subpackage exception
 */
class IOException extends \Exception {

    public function __construct($file, $line, $message)
    {
        parent::__construct();

        $this->file    = $file;
        $this->line    = $line;
        $this->message = $message;
    }
}
?>
