<?php
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'log.php';

/**
 * @desc all function is same class `log`, except truncate file log before logging
 */
class logt extends log
{
    public function __construct()
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = get_class($this);
        $expressions = func_get_args();
        $expressionNames = array();
        foreach ($expressions as $index => $arg) {
            $expressionNames[] = '$expressions[' . $index . ']';
        }
        eval($className . '::setMode("w");');
        eval('parent::__construct(' . implode(',', $expressionNames) . ');');
    }
}
 
