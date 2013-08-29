<?php
    /**
     * @desc that last in first out
     * @author vinhnv@live.com
     * @since 2012
     */
    class Stack {
        public static $files = array(); # used fields
        public static $classes = array(); # used classes
        public static $functions = array(); # used functions
        /**
         * @desc store calling orders of called function by store the information of called method
         * @param string $_file used file name
         * @param string $_class used class name
         * @param string $_function used function name
         * @author vinhnv@live.com
         */
        public static function pushLog($_file = '', $_class = '', $_function = '') {
            $className = __CLASS__;
            $_file = basename($_file);
            $_file = trim($_file);
            eval ("{$className}::\$files[] = \$_file;");
            $_class = trim($_class);
            eval ("{$className}::\$classes[] = \$_class;");
            $_function = trim($_function);
            eval ("{$className}::\$functions[] = \$_function;");
        }
        /**
         * @desc get files stack
         */
        public static function getFiles() {
            $className = __CLASS__;
            $ret = NULL;
            eval ("\$ret = {$className}::\$files;");
            return $ret;
        }
        /**
         * @desc get classes stack
         */
        public static function getClasses() {
            $className = __CLASS__;
            $ret = NULL;
            eval ("\$ret = {$className}::\$classes;");
            return $ret;
        }
        /**
         * @desc get functions stack
         */
        public static function getFunctions() {
            $className = __CLASS__;
            $ret = NULL;
            eval ("\$ret = {$className}::\$functions;");
            return $ret;
        }
        /**
         * desc get files, classes, functions stacks
         */
        public static function getLogStack() {
            $className = __CLASS__;
            $ret = NULL;
            eval (
                '
                    $ret = array(
                        "files" => ' . $className . '::getFiles(),
                        "classes" => ' . $className . '::getClasses(),
                        "functions" => ' . $className . '::getFunctions()
                    );
                '
            );
            return $ret;
        }
        /**
         * @desc get files, classes, functions that belong to function '__construct'
         */
        public static function filterConstruct() {
            $className = __CLASS__;
            $functions = array();
            $files = array();
            $classes = array();

            # restore stacks into local variables
            eval ("\$files = {$className}::\$files;");
            eval ("\$classes = {$className}::\$classes;");
            eval ("\$functions = {$className}::\$functions;");

            $callback = create_function('$function', 'return $function == "__construct";'); # create filter
            $functions = array_filter($functions, $callback); # filter funtions stack
            $files = array_intersect_key($files, $functions); # get files corresponding to functions by index
            $classes = array_intersect_key($classes, $functions); # get classes corresponding to functions by index
            return array(
                'files' => $files,
                'classes' => $classes,
                'functions' => $functions,
            );
        }
        /**
         * @desc reset all properties to initial value
         */
        public static function reset() {
            $className = __CLASS__;
            $classAttributes = get_class_vars($className); # get class attributes
            $staticAttributes = $classAttributes;

            $level = error_reporting();
            error_reporting(0); # turn off reporting

            foreach($staticAttributes as $name => $value) {
                eval("{$className}::\${$name} = array();"); # reset each attribute
            }

            error_reporting($level); # restore reporting

        }
    }
 