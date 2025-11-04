<?php
declare (strict_types = 1);

namespace app\event;

class AdminOplogEvent
{
    private $msg;

    public function __construct($msg)
    {
        $this->msg = $msg;
    }

    /**
     * @return string
     */
    public function getMsg()
    {
        return $this->msg;
    }
}
