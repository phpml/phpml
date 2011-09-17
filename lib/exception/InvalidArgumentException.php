<?php

namespace phpml\lib\exception;

/**
 * InvalidArgumentException class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage exception
 */
class InvalidArgumentException extends \InvalidArgumentException
{
    public function __construct($file, $line, $message)
    {
        parent::__construct();

        $this->file    = $file;
        $this->line    = $line;
        $this->message = $message;
    }
}