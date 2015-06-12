<?php

namespace Siso\Bundle\ContentLoaderBundle\Exceptions;

use Exception;

/**
 * Exception for data load errors
 */
class NodeDataLoadException extends Exception
{
    public function __construct($message, Exception $previous = null)
    {
        parent::__construct($message . ':' . $previous->getMessage(), 0, $previous);
    }
}