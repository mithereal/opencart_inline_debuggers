<?php
error_reporting(E_ALL | E_STRICT); # enable all warning & error

/**
 * @desc scan php files in landmark & auto include if needed
 * @param string $landmark directory or file
 * @param string, array $excepts item name that is not care
 * @param boolean $recursive whether scan subdirectory
 * @param boolean $including whether including valid items
 * @return array php files is included
 * @author vinhnv@live.com
 */
function scanPhpFile($landmark = EMPTY_STRING, $excepts = EMPTY_STRING, $recursive = false, $including = true)
{
    $validPhpFiles = array();

    # if landmark is not exist, stop by return empty array
    if (!file_exists($landmark)) {
        return array();
    }

    $isFile = is_file($landmark) ? true : false;
    $isDir = !$isFile;

    $landmarkDir = $isFile ? dirname($landmark) : $landmark; # directory landmark base landmark

    is_string($excepts) ? $excepts = explode(DELIMITER, $excepts) : null; # validate except

    foreach (scandir($landmarkDir) as $index => $item) {
        switch (true) {
            case $item == VIRTUAL_DIRECTORY_CURRENT: # virtual directory is skipped
            case $item == VIRTUAL_DIRECTORY_PARENT: # virtual directory is skipped
                break;
            case is_dir($landmarkDir . DIRECTORY_SEPARATOR . $item) && $recursive: # if is subdirectory, scan it
                $subFiles = scanPhpFile(
                    $landmarkDir . DIRECTORY_SEPARATOR . $item,
                    $excepts,
                    $recursive,
                    $including
                );
                $validPhpFiles = array_merge($validPhpFiles, $subFiles);
                break;
            case $item == basename($isFile ? $landmark : EMPTY_STRING): # current file can't be again included
            case $item == basename($item, PHP_EXTENSION): # item is not php file
            case in_array($item, $excepts): # ignore item if it is excepted
                break;

            default: # it is valid php files, include it if needed
                $path = $landmarkDir . DIRECTORY_SEPARATOR . $item; # path to item

                $validPhpFiles[] = $path; # store path

                !!$including ? include_once $path : null; # try to including
                break;
        }
    }

    return $validPhpFiles; # return valid files
}

/**
 * @desc correct file name
 * @param string $filename
 * @param string $replacement
 * @link en.wikipedia.org/wiki/Filename
 */
function correctFilename(&$filename, $replacement = DEFAULT_REPLACEMENT)
{
    # replace invalid characters by default valid characters for replacement
    $replacement = preg_replace(INVALID_CHARACTER_IN_FILE_NAME, DEFAULT_REPLACEMENT, $replacement);
    # replace invalid characters by replacement for file name
    $filename = preg_replace(INVALID_CHARACTER_IN_FILE_NAME, $replacement, $filename);
    $filename = trim($filename);
    $filename = mb_substr($filename, 0, 255, 'UTF-8'); # max length of file name of windows is 255
}

/**
 * @desc correct Uri ex. replace character space by '%20'
 * @param string $url uri
 * @author vinhnv@live.com
 */
function correctUrl(&$url)
{
    $url = str_replace(' ', '%20', $url);
}

/**
 * @desc convert underscored name to CamelCased name
 * @param string $underscoredString
 * @author vinhnv@live.com
 * @return string
 */
function underscored2camelCased($underscoredString)
{
    # Step 1: replace underscore by space
    $underscoredString = str_replace(CHARACTER_UNDERSCORE, CHARACTER_SPACE, $underscoredString);
    # Step 2: uppercase first character
    $underscoredString = ucwords($underscoredString);
    # Step 3: remove space
    $camelCasedString = str_replace(CHARACTER_SPACE, EMPTY_STRING, $underscoredString);

    return $camelCasedString;
}

/**
 * @desc whether is config line
 * @param string $line
 * @param string $separate
 * @return return array if it is config line, other return false
 */
function isConfigLine($line, $separate = CONFIG_SEPARATOR)
{
    $ret = false;
    $line = trim($line);
    $parts = array();
    switch (true) {
        case empty($line): # is empty line
        case strpos($line, MARK_OF_COMMENT_LINE) === 0: # is comment line
        case count($parts = explode($separate, $line)) <= 1: # don't contain $separate
            return FALSE; # exit
            break;
        default:
            break;
    }
    $count = count($parts);
    foreach ($parts as $index => $part) {
        $parts[$index] = trim($parts[$index]);
        # if empty key (not value) is exist, exit
        if ($parts[$index] === EMPTY_STRING && $index < $count - 1) {
            return FALSE;
        }
    }

    return $parts;
}

/**
 * @desc convert config line into array
 * @param string $configPath
 * @param array $configs
 */
function file2config($configPath, &$configs)
{
    $configs = array();
    $lines = array();
    ${USE_ABOVE_KEY} = NULL;
    ${CREATE_NEW_AT_LAST_VAR} = NULL;

    if (is_file($configPath)) {
        $lines = file($configPath, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
    }
    is_array($lines) ? null : $lines = array();
    array_walk($lines, USER_TRIM_FUNCTION); # trim lines

    foreach ($lines as $index => $line) {
        $parts = isConfigLine($line, CONFIG_SEPARATOR); # split into keys, value
        if (empty($parts)) {
            continue;
        }

        $exePHP = false; # execute line as php code, reset at each line
        if (EXE_AS_PHP_KEY == $parts[0]) {
            $exePHP = true;
            unset($parts[0]);
            $parts = array_values($parts); # reindex values
        }
        $startPoint = isset($configs[START_POINT_KEY]) ? $configs[START_POINT_KEY] : NULL;
        if (!empty($startPoint)) {
            $startPointKeys = array();
            # get newest start point
            browseKeyValue($startPoint, $startPointKeys, array(USE_NEWEST => true));

            # create variable start_point
            !empty($startPointKeys) ? ${START_POINT_KEY} = implode(CONFIG_SEPARATOR, $startPointKeys) : NULL;
            # push start point at begin of current parts
            # startPoint is invalid for command startPoint and command variable
            if (START_POINT_KEY != $parts[0] && VARIABLE_KEY != $parts[0]) {
                $parts = array_merge($startPointKeys, $parts);
            }
        }

        # push data to variable ex. {variable}
        foreach ($parts as $index => $string) {
            $opens = explode(OPEN_BRACKET, $string); # split `..{string}..` into `string}..`
            if (count($opens) > 1) {
                foreach ($opens as $ondex => $open) {
                    $closes = explode(CLOSE_BRACKET, $open); # split `string}..` => `string` or variable
                    if (count($closes) > 1) {
                        $closes[0] = ${$closes[0]}; # replace by value
                        $opens[$ondex] = implode(EMPTY_STRING, $closes); # composite parts
                    }
                }
                $parts[$index] = implode(EMPTY_STRING, $opens); # composite parts
            }
        }

        # ensure, in start point key, any no empty value is key
        if (!!$parts && START_POINT_KEY == $parts[0] && !!$parts[count($parts) - 1]) {
            $parts[] = EMPTY_STRING;
        }
        $newParts = array();
        $stop_here = false;
        $count = count($parts);

        ## "\x7C" ~ '|'
        # PHP|variable|ab|"11\x7C22"
        # c|{ab}|value ~ c|11|C22|value
        for ($i = 0; $i < $count; $i++) {
            $string = trim($parts[$i]);

            # empty key is exist
            if ($i < $count - 1 && !$string) {
                $stop_here = true;
                break;
            }

            $subConfig = isConfigLine($string, CONFIG_SEPARATOR);
            !!$subConfig ? $newParts = array_merge($newParts, $subConfig) : $newParts[] = $string;
        }

        if (!!$stop_here) {
            continue; # to new line
        }
        $parts = $newParts;

        $i = 0;
        $count = count($parts);
        $ref = & $configs; # fore dynamic assigning
        while ($i < $count) {
            switch (true) {
                case $i == $count - 1: # current element is value
                    if ($exePHP) {
                        eval("\$tmp = {$parts[$i]};");
                    } else {
                        $tmp = $parts[$i];
                    }
                    $ref = $tmp;
                    break;
                case $parts[$i] === EMPTY_STRING: # empty key is exist
                    $i = $count; # stop browsing
                    break;
                case $i == $count - 2: # is valid key that point to scalar value (int, string, ..)
                    switch (true) {
                        # append mode, not use above key
                        case $parts[$i] == APPEND_KEY && !${USE_ABOVE_KEY}:
                            # append mode, use above key, create new at last
                        case $parts[$i] == APPEND_KEY && !!${USE_ABOVE_KEY} && !!${CREATE_NEW_AT_LAST_VAR}:
                            $ref[] = NULL;

                        # append mode, use above key, turn off create new at last
                        case $parts[$i] == APPEND_KEY:
                            # end key mode
                        case $parts[$i] == TO_END_KEY:
                            !count($ref) ? $ref[] = NULL : NULL; # empty array is not allowed
                            end($ref); # move to end
                            $curKey = key($ref);
                            break;


                        default : # is valid key that point to value
                            # ensure the key is exist
                            isset($ref[$parts[$i]]) ? NULL : $ref[$parts[$i]] = NULL;
                            $curKey = $parts[$i];
                            break;

                    }

                    $tmp = $ref[$curKey];
                    unset($ref[$curKey]);
                    $ref[$curKey] = $tmp; # update current key to latest
                    $ref = & $ref[$curKey]; # re point to current key
                    break;

                case $i <= $count - 3: # is valid key that point to compound value (array)
                    switch (true) {
                        # append mode, not use above key
                        case $parts[$i] == APPEND_KEY && !${USE_ABOVE_KEY}:
                            $ref[] = array();

                        # append mode
                        case $parts[$i] == APPEND_KEY:
                            # end key mode
                        case $parts[$i] == TO_END_KEY:
                            !count($ref) ? $ref[] = array() : NULL; # empty array is not allowed
                            end($ref); # move to end
                            $curKey = key($ref);
                            is_array($ref[$curKey]) ? NULL : $ref[$curKey] = array(); # ensure type of value is array
                            break;

                        default: # is valid key that point to value
                            # ensure type of value is array
                            isset($ref[$parts[$i]]) && is_array($ref[$parts[$i]]) ? NULL : $ref[$parts[$i]] = array();
                            $curKey = $parts[$i];
                            break;
                    }

                    $tmp = $ref[$curKey];
                    unset($ref[$curKey]);
                    $ref[$curKey] = $tmp; # update current key to latest
                    $ref = & $ref[$curKey]; # re point to current key
                    break;
                default:
                    break;
            }
            $i++; # next part
        }

        # command create variable
        if (VARIABLE_KEY == $parts[0]) {
            end($configs[VARIABLE_KEY]); # move to latest variable
            $key = key($configs[VARIABLE_KEY]); # get variable name
            $value = current($configs[VARIABLE_KEY]); # get value of variable
            $tmp = array();
            browseKeyValue($value, $tmp, array(USE_NEWEST => true)); # get all latest keys, value
            ${$key} = implode(CONFIG_SEPARATOR, $tmp); # create variable with restore value
            unset($configs[VARIABLE_KEY]); # remove keys & value for just created variable
        }
        if (UNSET_KEY == $parts[0]) {
            unset(${$configs[UNSET_KEY]}); # remove key & corresponding value
        }
    }
    unset($configs[START_POINT_KEY]); # remove start point before exit function
}

/**
 * @desc try to get firstly valid file path, try to creating file by path if it's not exist
 * @param array or string $paths candidate paths
 * @param string $validPath firstly file path
 * @param boolean $tryCreate try to creating file if it's not exist
 */
function firstValidFilePath($paths, &$validPath, $tryCreate = false)
{
    $level = error_reporting();
    error_reporting(0); # turn off reporting

    $paths = (array)$paths;
    $validPath = EMPTY_STRING;

    foreach ($paths as $index => $path) {
        switch (true) {
            case is_file($path):
            case $tryCreate && FALSE !== file_put_contents($path, EMPTY_STRING):
                $validPath = $path;
                break 2; # break switch and outer foreach
            default:
                break;
        }
    }

    error_reporting($level); # restore reporting
}

/**
 * @desc try to get firstly valid dir path, try to creating dir by path if it's not exist
 * @param array or string $paths candidate paths
 * @param string $validPath firstly dir path
 * @param boolean $tryCreate try to creating dir if it's not exist
 * @author vinhnv@live.com
 */
function firstValidDirPath($paths, &$validPath, $tryCreate = true)
{
    $paths = (array)$paths;

    $level = error_reporting();
    error_reporting(0); # turn off reporting

    $validPath = EMPTY_STRING;
    foreach ($paths as $index => $path) {
        switch (true) {
            case is_dir($path):
            case $tryCreate && mkdir($path, 0777, true):
                $validPath = $path;
                break 2; # break switch and outer foreach
            default:
                break;
        }
    }

    error_reporting($level); # restore reporting
}

/**
 * @desc generate unique file path
 * @param string $folderContainer container directory path
 * @param string $filename
 * @param string $fileType
 * @return unique file path that can be ended with .1, .2, .3 ...
 */
function uniqueFilePath($folderContainer, $filename, $fileType)
{
    $filePath = $folderContainer . DIRECTORY_SEPARATOR . $filename;
    $i = 1;
    while (file_exists($filePath . $fileType) && is_file($filePath . $fileType)) {
        # append by index
        $filePath = $folderContainer . DIRECTORY_SEPARATOR . $filename . "." . $i;
        $i++;
    }
    return $filePath . '.' . $fileType;
}

/**
 * @desc generate unique dir path
 * @param string $folderContainer container directory path
 * @param string $dirName
 * @return unique dir path that can be ended with .1, .2, .3 ...
 */
function getDirOutputPath($folderContainer, $dirName)
{
    $folderPath = $folderContainer . DIRECTORY_SEPARATOR . $dirName;
    $i = 1;
    while (file_exists($folderPath) && is_dir($folderPath)) {
        $folderPath = $folderContainer . DIRECTORY_SEPARATOR . $dirName . '.' . $i;
        $i++;
    }

    return $folderPath;
}

/**
 * @desc remove directory recursively
 * @param string $dir dir path for deleting
 * @param boolean $recursive
 * @note remove directory and items in it
 * @link php.net/manual/en/function.rmdir.php
 */
function removeDir($dir, $recursive = true)
{
    if (!is_dir($dir)) {
        return; # ensure item for deleting, is directory
    }
    $objects = scandir($dir);
    foreach ($objects as $object) {
        # virtual directory can't be processed
        if ('.' === $object || '..' === $object) {
            continue;
        }

        if ('dir' == filetype($dir . DIRECTORY_SEPARATOR . $object)) {
            # recursively remove subdirectory
            $recursive ? removeDir($dir . DIRECTORY_SEPARATOR . $object, $recursive) : null;
        } else {
            # remove file
            unlink($dir . DIRECTORY_SEPARATOR . $object);
        }
    }
    reset($objects);
    rmdir($dir); # remove current directory
}

/**
 * @desc try to creating directory
 * @param $path directory to be created
 */
function ensureExistDir($path)
{
    if (file_exists($path) && is_dir($path)) {
        return; # directory is exist
    } else {
        mkdir($path, 0777, true); # try to creating directory if it's not exist
    }
}

/**
 * @desc clean all items in the directory
 * @param $path directory path
 */
function cleanDir($path)
{
    ensureExistDir($path);
    removeDir($path, true);
    ensureExistDir($path);
}

/**
 * @desc write content to file with encoding UTF-8
 * @param string $path
 * @param string $content
 */
function writeFile($path, $content = EMPTY_STRING)
{
    $fp = fopen($path, 'w');
    if (!!$fp && !!$content) {
        # encode in UTF-8 - Add byte order mark
        fwrite($fp, pack("CCC", 0xef, 0xbb, 0xbf));
        fwrite($fp, $content);
    }
    fclose($fp);
}

/**
 * @desc alert windows
 * @param string $message
 * @param int $time in seconds
 */
function alertWindows($message = EMPTY_STRING, $time = 0)
{
    $time = (int)$time;
    exec('msg * /time:' . $time . ' "' . $message . '"');
}

/**
 * @desc get keys, value by browsing in depth
 * @param array $array keys, value
 * @param array $result
 * @param array configs additional condition
 */
function browseKeyValue($array, &$result, $configs = array())
{
    $data = array();
    # whether only latest keys, value
    ${USE_NEWEST} = false;
    if (isset($configs[USE_NEWEST])) {
        ${USE_NEWEST} = (bool)$configs[USE_NEWEST];
    }

    is_array($array) ? NULL : $array = array($array => NULL); # convert into readable format

    $data = $array; # default is get all keys, value

    if (${USE_NEWEST}) {
        end($array); # move to latest key
        # rebuild data with only latest key, value
        $data = array(
            key($array) => current($array)
        );
    }
    foreach ($data as $key => $value) {
        0 < strlen($key) ? $result[] = $key : NULL; # stored key into result

        if (is_array($value)) {
            browseKeyValue($value, $result, $configs); # recursively browse
        } elseif (0 < strlen($value)) {
            $result[] = $value;
        }
    }
}

include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'constant' . DIRECTORY_SEPARATOR . 'logic.php';
scanPhpFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'constant');

if (!defined('CONFIG_SOURCE_PATH')) {
    echo sprintf(MESSAGE_NO_DIRECTORY_CONFIG) . PHP_EOL;
    exit;
}

scanPhpFile(__FILE__);

eval(USER_TRIM_FUNCTION_CODE); # create function user_trim for array_walk
$filename = basename(__FILE__, PHP_EXTENSION);
firstValidFilePath(CONFIG_SOURCE_PATH . DIRECTORY_SEPARATOR . $filename . CONFIG_EXTENSION, $coreConfigPath);

if (empty($coreConfigPath)) {
    echo sprintf(MESSAGE_NO_CONFIG_FOR, 'root') . PHP_EOL;
    exit;
}

file2config($coreConfigPath, $coreConfigs);

if (!!$coreConfigs['TOOLS_OUTPUT_DIRECTORY']) {
    define('TOOLS_OUTPUT_DIRECTORY', $coreConfigs['TOOLS_OUTPUT_DIRECTORY']);
} else {
    define('TOOLS_OUTPUT_DIRECTORY', DRIVE_C);
}

ensureExistDir(TOOLS_OUTPUT_DIRECTORY);