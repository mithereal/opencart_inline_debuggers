Opencart 2.x Inline Debuggers 
Includes KINT

Kint:
=====

Kint Fork
see https://github.com/kint-php/kint for more usage instructions

Virtually no installation and no dependencies.
<?php
require '/kint/Kint.class.php';
Kint::dump( $_SERVER );
Dump functions accept any number of parameters and have shorthands.
d( $variable1, $variable2 );


