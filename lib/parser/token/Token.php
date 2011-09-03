<?php

namespace phpml\lib\parser\token;

/**
 * Token main class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser.token
 */
class Token
{
    /**
     * <php:Label
     */
    const T_OPEN_TAG  = 1;
    
    /**
     * id=
     */
    const T_ATTRIBUTE = 2;
    
    /**
     * "value"|'value'
     */
    const T_VALUE     = 4;
    
    /**
     * >
     */
    const T_END       = 8;
    
    /**
     * />
     */
    const T_CLOSE     = 16;
    
    /**
     * </php:Label>
     */
    const T_CLOSE_TAG = 32;
    
    /**
     * *
     */
    const T_TEXT      = 64;

    protected $type;

    //--------------------------

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }
    
}
