<?php

namespace App\Exceptions;

use Exception;

class TransactionException extends Exception
{
    // create a new instance of this class accepting a message 
    // normally a string to be shown to the user as error message

    public function __construct(string $message){
        parent::__construct($message);
    }

}
