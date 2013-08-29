<?php
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'log.php';

class logdie extends log
{
    public function __construct()
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $expressions = func_get_args();
        $expressionNames = array();
        foreach ($expressions as $index => $arg) {
            $expressionNames[] = '$expressions[' . $index . ']';
        }
        eval('parent::__construct(' . implode(',', $expressionNames) . ');');

        die();
    }
}
 
