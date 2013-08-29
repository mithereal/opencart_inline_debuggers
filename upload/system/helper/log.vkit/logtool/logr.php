<?php
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'log.php';

/**
 * @desc all function is same log, except file name corresponding to SERVER REQUEST URI and uniqid
 */
class logr extends log
{
    public function __construct()
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = get_class($this);
        $expressions = func_get_args();
        $expressionNames = array();
        $count = count($expressions);

        switch (true) {
            case 0 === $count:
                $expressions = array('_error_' . __CLASS__ . '0param');
                break;
            case 1 === $count:
                $expressions = array('_error_' . __CLASS__ . '1param', $expressions[0]);
                break;
            case !is_string($expressions[0]):
                $expressions = array_merge(array('_error_' . __CLASS__ . 'nostringparam'), $expressions);
                break;
            default:
                break;
        }
        $prefix = '';
        foreach ($expressions as $index => $arg) {
            // set prefix or project name
            if (0 === $index) {
                $prefix = $expressions[0];
                continue;
            }

            $expressionNames[] = '$expressions[' . $index . ']';
        }
        # if prefix join unique id of which length greater than 255, then there is an error
        $prefixbackup = '';
        if (255 <= strlen($prefix . log::$___uniqid)) {
            // constant $prefix and append it to $expressionNames as data than prefix of log name
            $prefixbackup = $prefix;
            $expressionNames = array_merge(array('$prefixbackup'), $expressionNames);

            // set new value for $prefix
            $prefix = '_error_' . __CLASS__ . '3';
        }

        $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $pathInfo = preg_replace('/[,\\/\\?:@&=\\+\\$#\\\\]/', '_', $requestUri);

        # 255 is max length of file name
        $pathInfo = substr($pathInfo, 0, 255 - strlen($prefix . log::$___uniqid));
        log::setLogFileName($prefix . log::$___uniqid . $pathInfo);

        eval('parent::__construct(' . implode(',', $expressionNames) . ');');
    }
}
 
