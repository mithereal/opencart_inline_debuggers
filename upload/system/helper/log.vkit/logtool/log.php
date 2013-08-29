<?php

/**
 * @desc log - a best alternative for var_dump, a stand-alone library
 * @author vinhnv@live.com
 */
class log
{
    # property is prefixed PREFIX_NOT_RESET that isn't reset to NULL
    public static $___uniqid = NULL; # uniqid string
    public static $separate = NULL; # between values that is logged
    public static $logFilePath = NULL; # log filepath
    public static $logFileName = NULL; # log filename
    public static $hasDatetime = NULL; # whether log datetime information
    public static $showLogPath = NULL; # whether echo log filepath
    public static $noLog = NULL; # no output any information by any way, for creating object sample
    public static $isEcho = NULL; # priority DESC: noLog > echo > write file
    public static $mode = NULL; # mode for write log file ex. a, w, ...
    public static $nlog = NULL; # [log logt logdie logtdie]...[logn lognt logndie logntdie]
    public static $___configs = NULL;
    public static $___sample = NULL;
    public $author = NULL;

    /**
     * @desc for constructing also logging values
     */
    public function __construct()
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);

        $logData = array();
        $allAttributes = NULL;
        $separate = NULL;
        $noLog = NULL;
        $isEcho = NULL;
        $totalArgument = func_num_args();
        $className = get_class($this);

        eval($className . '::init($this);'); # init all properties of class and object
        eval($className . '::setVar($this, NULL, $allAttributes);'); # get all properties of class, object
        extract($allAttributes, EXTR_OVERWRITE); # create local variables

        if ($totalArgument > 0) {
            $expressions = func_get_args();
            for ($i = 0; $i < $totalArgument; $i++) {
                # handle error 'Nesting level too deep - recursive dependency' > var_export is fail
                # handle circular references > var_export is fail
                # handle simultaneously many expression > var_export, print_r is fail
                $logData[] = print_r($expressions[$i], true);
            }
        } else {
            $logData[] = 'logging by class ' . $className;
        }
        $logData = implode($separate, $logData);

        switch (true) {
            case $noLog == true: # no log no echo no write
                break;
            case $isEcho == true: # echo
                echo $logData;
                break;
            default: # write log file
                eval(
                    $className . '::writeLogs(
                        $logData,
                        $logFilePath,
                        $hasDatetime,
                        $showLogPath,
                        $mode,
                        $logFileName
                    );'
                );
                break;
        }
        eval($className . '::reset($this);'); # reset all properties of class & object after using
    }

    /**
     * @desc init all properties of class and object from config
     * @param $object
     * @param string $actionMode get only properties of class or of object or all
     */
    public static function init(&$object, $actionMode = NULL)
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $filenameExt = basename(__FILE__);
        $filename = basename(__FILE__, PHP_EXTENSION);
        $staticAttributes = array();
        $objectAttributes = array();

        firstValidFilePath(CONFIG_SOURCE_PATH . DIRECTORY_SEPARATOR . $filename . CONFIG_EXTENSION, $logConfigPath);

        if (empty($logConfigPath)) {
            echo sprintf(MESSAGE_NO_CONFIG_FOR, __CLASS__) . PHP_EOL;
            exit;
        }
        file2config($logConfigPath, $config);

        switch (true) {
            case NULL === $actionMode:
                is_array($config['static']) ? $staticAttributes = $config['static'] : NULL;
                is_array($config['object']) ? $objectAttributes = $config['object'] : NULL;
                break;
            case 'static' === $actionMode:
                is_array($config['static']) ? $staticAttributes = $config['static'] : NULL;
                break;
            case 'object' === $actionMode:
                is_array($config['object']) ? $objectAttributes = $config['object'] : NULL;
                break;
            default:
                break;
        }

        foreach ($staticAttributes as $name => $value) {
            eval('$null_' . $name . ' = is_null(' . $className . '::$' . $name . ');'); # whether property is null
            $continue = false;

            eval('if(!$null_' . $name . ') {$continue = true;};'); # if property isn't NULL, skip this property

            if ($continue) {
                continue;
            }
            eval($className . '::$' . $name . ' = $value;');
        }

        foreach ($objectAttributes as $name => $value) {
            eval('$null_' . $name . ' = is_null($object->' . $name . ');'); # whether property is null
            $continue = false;

            eval('if(!$null_' . $name . ') {$continue = true;}'); # if property isn't NULL, skip this property
            if ($continue) {
                continue;
            }

            eval('$object->' . $name . ' = $value;');
        }
    }

    /**
     * @desc reset properties of class & object
     * @param $object reset object
     * @param $actionMode get only properties of class or of object or all
     */
    public static function reset(&$object = NULL, $actionMode = NULL)
    {
        Stack::reset();

        $allAttributes = array();
        $___sample = NULL;
        $className = __CLASS__;

        $classAttributes = get_class_vars($className);
        eval($className . '::setVar($object, NULL, $allAttributes);'); # get all attributes of class

        extract($allAttributes, EXTR_OVERWRITE); # create local variable

        $objectAttributes = get_object_vars(!!$object ? $object : $___sample); # get attributes of object of object

        !$objectAttributes ? $objectAttributes = array() : NULL;

        $staticAttributes = $classAttributes;
        foreach ($objectAttributes as $key => $value) {
            unset($staticAttributes[$key]); # split static & object attributes
        }

        switch (true) {
            case NULL === $actionMode:
                # do nothing
                break;
            case 'static' === $actionMode: # only static attributes
                $objectAttributes = array();
                break;
            case 'object' === $actionMode: # only object attributes
                $staticAttributes = array();
                break;
            default:
                break;
        }

        foreach ($staticAttributes as $name => $value) {
            # only reset properties that don't prefix by PREFIX_NOT_RESET
            if (0 !== strpos($name, PREFIX_NOT_RESET)) {
                eval($className . '::$' . $name . ' = NULL;');
            }
        }
        foreach ($objectAttributes as $name => $value) {
            # only reset properties that don't prefix by PREFIX_NOT_RESET
            if (0 !== strpos($name, PREFIX_NOT_RESET)) {
                eval('$object->' . $name . ' = NULL;');
            }
        }
    }

    /**
     * @desc create variable from attributes of class and object
     * @param &$object
     * @param $actionMode get only properties of class or of object or all
     * @param array $allAttributes
     */
    public static function setVar($object = NULL, $actionMode = NULL, &$allAttributes)
    {
        $className = __CLASS__;
        $allAttributes = array();
        $___sample = NULL;
        $classAttributes = get_class_vars($className);
        eval('$___sample = ' . $className . '::$___sample;');

        $objectAttributes = get_object_vars(!!$object ? $object : $___sample);
        !$objectAttributes ? $objectAttributes = array() : NULL;

        $staticAttributes = $classAttributes;
        foreach ($objectAttributes as $key => $value) {
            unset($staticAttributes[$key]);
        }
        switch (true) {
            case NULL === $actionMode:
                # do nothing
                break;
            case 'static' === $actionMode: # only static attributes
                $objectAttributes = array();
                break;
            case 'object' === $actionMode: # only object attributes
                $staticAttributes = array();
                break;
            default:
                break;
        }

        foreach ($staticAttributes as $name => $value) {
            eval('$allAttributes["' . $name . '"] = ' . $className . '::$' . $name . ';');
        }
        foreach ($objectAttributes as $name => $value) {
            eval('$allAttributes["' . $name . '"] = $object->' . $name . ';');
        }
    }

    /**
     * @desc write message to log file
     * @param string $_message
     * @param string $_logPath
     * @param boolean $_hasDatetime
     * @param boolean $_showLogPath
     * @param string $_mode
     * @param string $_logFileName
     */
    public static function writeLogs($_message, $_logPath = null, $_hasDatetime = FALSE, $_showLogPath = FALSE, $_mode = 'a', $_logFileName = null)
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $filtered = Stack::filterConstruct();
        reset($filtered['classes']);
        $className = current($filtered['classes']); # get first class that be called
        !!$className ? NULL : $className = __CLASS__;

        $filename = !!$_logFileName ? $_logFileName : basename($className, 'die');
        $eol = PHP_EOL;
        $datetime = date(DATE_TIME_FORMAT2);
        $modes = array('r', 'r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+'); # supported mode of writing log file
        $defaultMode = 'a'; # default mode
        $_message = $eol . $_message;
        if (!!$_hasDatetime) {
            $_message = $eol . ' - [' . $datetime . '] - ' . $_message;
        }

        $defaultPath = TOOLS_OUTPUT_DIRECTORY . DIRECTORY_SEPARATOR . LOG_DIRECTORY_NAME . DIRECTORY_SEPARATOR . $filename . LOG_EXTENSION;

        $pathList = array(
            $_logPath,
            $defaultPath
        );
        in_array($_mode, $modes) ? NULL : $_mode = $defaultMode;

        foreach ($pathList as $index => $path) {
            if (!$path) {
                continue;
            }

            $isFile = is_file($path);
            !is_dir(dirname($path)) ? mkdir(dirname($path)) : NULL;

            $filePointer = fopen($path, $_mode);
            if ($filePointer) {
                echo !!$_showLogPath ? PHP_EOL . sprintf(MESSAGE_PATH_OF_LOG_FILE, $path) : '';
                # try to creating utf8 file if it's not exist
                !$isFile ? fwrite($filePointer, pack("CCC", 0xef, 0xbb, 0xbf)) : NULL;
                fwrite($filePointer, $_message); # write message
                fclose($filePointer);
                break;
            }
        }
    }

    public static function setSeparate($_separate = PHP_EOL)
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = 'separate';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }

    public static function setLogFilePath($_logFilePath = '')
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = 'logFilePath';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }

    public static function setLogFileName($_logFileName = '')
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = 'logFileName';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }

    public static function logDatetime($_hasDatetime = false)
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = 'hasDatetime';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }

    public static function showLogPath($_showLogPath = false)
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = 'showLogPath';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }


    public static function onlyEcho($_isEcho = false)
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = 'isEcho';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }

    public static function noLog($_noLog = false)
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = 'noLog';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }

    public static function setMode($_mode = 'a')
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = 'mode';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }

    public static function setConfig($____configs = NULL)
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = '___configs';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }

    public static function setNlog($_nlog = NULL)
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = 'nlog';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }

    public function setAuthor($_author = '')
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $name = 'author';
        eval('$this->' . $name . ' = $_' . $name . ';');
    }

    public static function getUniqid()
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = '___uniqid';
        $____uniqid = NULL;
        eval('$_' . $name . ' = ' . $className . '::$' . $name . ';');
        return $____uniqid;
    }

    public static function nlog()
    {
        $nlog = 0;

        log::init($tmp, 'static');
        $className = __CLASS__;
        eval('$nlog = (int)' . $className . '::$nlog;');

        if (!$nlog) {
            return;
        }

        $phpCode = ''; # store php code that create other versions of log

        $samplet = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logt.php'); # sample logt
        $sampledie = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logdie.php'); # sample logdie
        $sampletdie = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logtdie.php'); # sample logtdie

        $samplet = substr($samplet, 5); # remove php open tag
        $sampledie = substr($sampledie, 5);
        $sampletdie = substr($sampletdie, 5);

        # create log{i}t
        for ($i = 1; $i <= $nlog; $i++) {
            $tmp = str_replace('logt', 'log' . $i . 't', $samplet);
            $phpCode .= preg_replace('/include_once.*$/m', '', $tmp); # remove include
        }
        # create log{i}tdie
        for ($i = 1; $i <= $nlog; $i++) {
            $tmp = str_replace('logt', "log{$i}t", $sampletdie);
            $phpCode .= preg_replace('/include_once.*$/m', '', $tmp); # remove include
        }

        # create log{i}
        for ($i = 1; $i <= $nlog; $i++) {
            $tmp = str_replace('logdie extends log', 'log' . $i . 'die extends log', $sampledie);
            $tmp = str_replace('die();', '', $tmp); # remove die();
            $tmp = str_replace('die', '', $tmp); # remove die in class name
            $tmp = preg_replace('/include_once.*$/m', '', $tmp); # remove include
            $phpCode .= $tmp;
        }

        # create log{i}die
        for ($i = 1; $i <= $nlog; $i++) {
            $tmp = str_replace('logdie extends log', "log{$i}die extends log{$i}", $sampledie);
            $tmp = str_replace("'log.php'", "'log{$i}.php'", $tmp);
            $tmp = preg_replace('/include_once.*$/m', '', $tmp); # remove include
            $phpCode .= $tmp;

        }

        eval(' ?><?php ' . $phpCode . ' ?><?php ');
    }

}

log::noLog(true); # no log
log::$___sample = new log(); # init sample for getting object properties
log::nlog(); # create versions of log
log::$___uniqid = uniqid('_', true); # creating unique string that using in logr

