<?php

namespace PHPML\Exception;

/**
 * RuntimeException class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage exception
 */
class RuntimeException extends \RuntimeException
{
    public function __construct($file, $line, $message)
    {
        parent::__construct();

        $this->file    = $file;
        $this->line    = $line;
        $this->message = $message;
    }
}
