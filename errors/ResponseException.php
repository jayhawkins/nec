<?php

/**
 * Handles HttpResponse Exception
 * 
 * @author euecheruo
 *
 */
class ResponseException extends Exception
{
    
    /**
     * Constructor
     * 
     * @param string $message The Exception message to throw.
     * @param int $code The Exception code.
     * @param Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct(string $message, int $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
    
}