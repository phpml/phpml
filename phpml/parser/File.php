<?php

namespace phpml\parser;

use phpml\exception\util\ExceptionFactory;

class File
{
    protected $filePointer;
    protected $currentPos;
    protected $currentLine;
    protected $savedState;
    protected $name;

    public function __construct($name)
    {
        $this->filePointer = null;
        $this->currentPos  = 0;
        $this->currentLine = 1;
        $this->savedState  = null;
        $this->name        = $name;

        $this->openFile();
    }

    public function openFile()
    {
        if (! is_readable($this->name))
            throw ExceptionFactory::createOpenFile(__FILE__, __LINE__, $this->getFileName(), 'reading');

        $this->filePointer = fopen($this->name, 'r');
    }

    public function isEOF()
    {
        return feof($this->filePointer);
    }

    public function nextChar()
    {
        $char = fgetc($this->filePointer);

        // EOF
        if ($char === false)
            return false;

        $this->currentPos++;

        if ($this->isNewLine($char))
            $this->currentLine++;

        return $char;
    }

    public function goBack()
    {
        // Empty file or file that wasn't read yet
        if (fseek($this->filePointer, -1, SEEK_CUR) == -1)
            return false;

        $char = fgetc($this->filePointer);

        if ($this->isNewLine($char))
            $this->currentLine--;

        fseek($this->filePointer, -1, SEEK_CUR);
        $this->currentPos--;
    }

    public function readAll()
    {
        return stream_get_contents($this->filePointer);
    }

    public function find($needle)
    {
        $needles = array();
        $needle  = (array) $needle;
        
        foreach ($needle as $n) {
            $needles[] = array(
                $n,
                strlen($n),
                0
            );
        }

        $readPos = 0;
        $found   = false;

        while (($char = fgetc($this->filePointer)) !== false) {

            $readPos++;

            foreach ($needles as &$n) {
                if ( ($n[2] < $n[1]) && ($char == $n[0][$n[2]]) )
                    $n[2]++;
                else
                    $n[2] = 0;
             
                if ( ($n[1] > 0) && ($n[2] == $n[1]) ) {
                    $found = true;
                    break 2;
                }
            }
        }
        
        // Back the cursor to the inicial position
        fseek($this->filePointer, -$readPos, SEEK_CUR);
        
        if ($found)
            return $readPos - $n[1];

        return false;
    }

    public function saveState()
    {
        $this->savedState = new \StdClass();
        $this->savedState->currentLine = $this->currentLine;
        $this->savedState->currentPos  = $this->currentPos;
    }

    public function restoreState()
    {
        $this->currentLine = $this->savedState->currentLine;
        $this->currentPos  = $this->savedState->currentPos;
        $this->savedState  = null;
        fseek($this->filePointer, $this->currentPos);
    }

    protected function isNewLine($char)
    {
        // Unix
        if ($char == "\n")
            return true;

        // Win or Mac
        if ($char == "\r") {

            $nextChar = fgetc($this->filePointer);
            fseek($this->filePointer, -1, SEEK_CUR);

            // Win
            if ($nextChar == "\n")
                return false;

            // Mac
            return true;
        }
    }

    public function getCurrentPos()
    {
        return $this->currentPos;
    }

    public function getCurrentLine()
    {
        return $this->currentLine;
    }

    public function getFilePointer()
    {
        return $this->filePointer;
    }

    public function getFileName()
    {
        return realpath($this->name);
    }

}