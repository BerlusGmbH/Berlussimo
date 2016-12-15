<?php

namespace App\Exceptions;

use App\Messages\Message;
use Exception;

class MessageException extends Exception
{
    protected $messageObject;
    protected $redirectTo = null;

    /**
     * Construct the exception. Note: The message is NOT binary safe.
     * @link http://php.net/manual/en/exception.construct.php
     * @param Message $message The message to show.
     * @param int $code [optional] The Exception code.
     * @param Exception $previous [optional] The previous exception used for the exception chaining. Since 5.3.0
     * @param string $redirectTo
     * @since 5.1.0
     */
    public function __construct(Message $message, $code = 0, Exception $previous = null, $redirectTo = null) {
        $this->redirectTo = $redirectTo;
        $this->messageObject = $message;
        parent::__construct($message->getMessage(), $code, $previous);
    }

    /**
     * @return Message
     */
    public function getMessageObject()
    {
        return $this->messageObject;
    }

    /**
     * @param Message $messageObject
     */
    public function setMessageObject(Message $messageObject)
    {
        $this->messageObject = $messageObject;
    }

    /**
     * @return mixed
     */
    public function getRedirectTo()
    {
        return $this->redirectTo;
    }

    /**
     * @param mixed $redirectTo
     */
    public function setRedirectTo($redirectTo)
    {
        $this->redirectTo = $redirectTo;
    }
}