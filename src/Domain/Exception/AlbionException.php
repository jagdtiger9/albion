<?php

namespace Aljerom\Albion\Domain\Exception;

use Exception;

class AlbionException extends Exception
{
    public function __construct($message = '', $code = 0)
    {
        parent::__construct($message, $code);
    }
}
