<?php

namespace phpml\parser;

class File
{

    protected $filePointer;
    protected $currentPos;
    protected $currentLine;
    protected $savedState;
    protected $name;

    public function __construct($name, $open = true)
    {
        $this->filePointer = null;
        $this->currentPos  = 0;
        $this->currentLine = 1;
        $this->savedState  = null;
        $this->name        = $name;

        if ($open)
            $this->openFile();
    }

    public function openFile()
    {
        $this->filePointer = fopen($this->name, 'r');
    }

    public function isEOF()
    {
        return feof($this->filePointer);
    }

    public function getNextChar()
    {
        $char = fgetc($this->filePointer);
        $this->currentPos++;

        if ($this->isNewLine($char))
            $this->currentLine++;

        return $char;
    }

    public function readAll()
    {
        return fgets($this->filePointer, (filesize($this->name) - $this->currentPos) + 1);
    }

    public function find($needle)
    {
        $needles = array();
        $needle  = (array) $needle;
        
        foreach ($needle as $n) {
            $lastNeedle = $needles[] = array(
                $n,
                strlen($n),
                0
            );

            if ($lastNeedle[1] == 0)
                throw new \LengthException ('Needle cannot be empty');
        }

        $readPos = 0;
        $found   = false;

        while (($char = fgetc($this->filePointer)) !== false) {

            $readPos++;

            foreach ($needles as &$n) {
                if ( ($n[2] < $n[1]) && ($char == $n[0][$n[2]]) ) {
                    $n[2]++;
                } else if ($n[2] == $n[1]) {
                    $found = true;
                    break 2;
                } else {
                    $n[2] = 0;
                }
            }
        }

        // Back the cursor to the inicial position
        fseek($this->filePointer, -$readPos, SEEK_CUR);

        if ($found)
            return $readPos - ($n[1] + 1);

        return false;
    }

    public function saveState()
    {
        $this->savedState = new \StdClass();
        $this->savedState->currentLine = $this->currentLine;
        $this->savedState->currentPos  = $this->currentPos;
        $this->savedState->filePointer = $this->filePointer;
    }

    public function restoreState()
    {
        $this->currentLine = $this->savedState->currentLine;
        $this->currentPos  = $this->savedState->currentPos;
        $this->filePointer = $this->savedState->filePointer;
        fseek($this->filePointer, $this->currentPos);
        $this->savedState  = null;
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