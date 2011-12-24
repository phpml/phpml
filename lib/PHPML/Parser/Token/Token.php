<?php

namespace PHPML\Parser\Token;

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
    
    /**
     * <php:Register
     */
    const T_REGISTER  = 128;

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
    
    public function __toString()
    {
        $object = new \ReflectionClass($this);
        
        // Get the name of the constant type
        $type = array_search($this->getType(), $object->getConstants());
        
        // SimpleToken
        if ($object->hasProperty('value'))
            return sprintf('Token: %s Value: %s', $type, $this->getValue());
            
        // TagToken
        elseif ($object->hasProperty('name'))
            return sprintf('Token: %s Namespace: %s Name: %s', $type, $this->getNamespace(), $this->getName());
            
        // Token
        else
            return sprintf('Token: %s', $type);
    }
}
