Opencart Inline Debuggers 
Includes KRUMO and KINT

Krumo:
=====

Krumo Fork (with updates / fixes)

Installation:
-------------
Add this line to your composer.json "require" section:

### composer.json
```json
    "require": {
       ...
       "oodle/krumo": "*"
```

Updates:
--------

1. Proper Object Dumping (ala var_dump)
   * Fixes for object dumping - to be able to dump Public, Protected, and Private members of objects
1. Dynamic Config
   * Ability to set the Config dynamically.
   * inspired by: [justmoon/krumo@a8208...](https://github.com/justmoon/krumo/commit/a82082d52f9dd348510175b508d5b2c73d69d7ad)
1. Node Collapse Behavior
   * Ability to specify nodes open by default
   * largely copied from: [justmoon/krumo@bd1f3...](https://github.com/justmoon/krumo/commit/bd1f3efd476122b5d6c79e881b6f1017c8771713) (with fixes)
1. New Options
   * New options under [display] in ini file (whether to show the line number / call info, plus whether to show the krumo version)
   * inspired by: [justmoon/krumo@37e1b1...](https://github.com/justmoon/krumo/commit/37e1b1c07ca0266baad699565314b11b80410df2)
1. New Default Url
   * Default URL in krumo.ini is now /krumo/
1. Cleanup
   * A few @($...) to isset($...) changes in class.krumo.php
   * All source code has been run through dos2unix
1. composer.json
   * Allow to be loaded via composer
1. setLineNumberTestCallback
   * Allow a custom line number test

### Sample usage of setLineNumberTestCallback
```php
// Place this in a utilites.php file or whatever as a convenience method for calling krumo(...)
function k()
{
    if (function_exists('krumo'))
    {
        $args = func_get_args();
        \krumo::setLineNumberTestCallback(function ($d) {
            if (strtolower($d['function']) == 'k')
                return true;
        });
        return call_user_func_array(
            array('krumo', 'dump'), $args);
    }
}
```
   

Note: Thanks to [justmoon](https://github.com/justmoon/krumo) for inspiration / code on a few features

Documentation
-------------
Original Readme from krumo_0.2.1a_PHP5-Only:

=============================================================================

                               Krumo
                            version 0.2.1a

=============================================================================

[...]

If there is no licence agreement with this package please download
a version from the location above. You must read and accept that
licence to use this software. The file is titled simply LICENSE.

OVERVIEW
------------------------------------------------------------------------------
To put it simply, Krumo is a replacement for print_r() and var_dump(). By 
definition Krumo is a debugging tool (for PHP5), which displays structured 
information about any PHP variable.

A lot of developers use print_r() and var_dump() in the means of debugging 
tools. Although they were intended to present human readble information about a 
variable, we can all agree that in general they are not. Krumo is an 
alternative: it does the same job, but it presents the information beautified 
using CSS and DHTML. 

EXAMPLES
------------------------------------------------------------------------------
Here's a basic example, which will return a report on the array variable passed 
as argument to it:

 krumo(array('a1'=> 'A1', 3, 'red'));

You can dump simultaneously more then one variable - here's another example:

 krumo($_SERVER, $_REQUEST);

You probably saw from the examples above that some of the nodes are expandable, 
so if you want to inspect the nested information, click on them and they will 
expand; if you do not need that information shown simply click again on it to 
collapse it. Here's an example to test this:

 $x1->x2->x3->x4->x5->x6->x7->x8->x9 = 'X10';
 krumo($x1);

The krumo() is the only standalone function from the package, and this is 
because basic dumps about variables (like print_r() or var_dump()) are the most 
common tasks such functionality is used for. The rest of the functionality can 
be called using static calls to the Krumo class. Here are several more examples:

 // print a debug backgrace
 krumo::backtrace();

 // print all the included(or required) files
 krumo::includes();
 
 // print all the included functions
 krumo::functions();
 
 // print all the declared classes
 krumo::classes();
 
 // print all the defined constants
 krumo::defines();

 ... and so on, etc.

A full PHPDocumenter API documentation exists both in this package and at the 
project's website.

INSTALL
------------------------------------------------------------------------------
Read the INSTALL file.

DOCUMENTATION
------------------------------------------------------------------------------
As I said, a full PHPDocumenter API documentation can be found both in this
package and at the project's website.

SKINS
------------------------------------------------------------------------------
There are several skins pre-installed with this package, but if you wish you can 
create skins of your own. The skins are simply CSS files that are prepended to 
the result that Krumo prints. If you want to use images in your CSS (for 
background, list-style, etc), you have to put "%URL%" in front of the image URL 
in order hook it up to the skin folder and make the image web-accessible.

Here's an example:

 ul.krumo-first {background: url(%url%bg.gif);}

TODO
------------------------------------------------------------------------------
You can find the list of stuff that is going to be added to this project in the 
TODO file from this very package.

CONTRIBUTION
-----------------------------------------------------------------------------
If you download and use and possibly even extend this tool, please let us know. 
Any feedback, even bad, is always welcome and your suggestions are going to be 
considered for our next release. [...]

Kint:
=====

Kint Fork
see http://raveren.github.io/kint/ for more usage instructions

Virtually no installation and no dependencies.
<?php
require '/kint/Kint.class.php';
Kint::dump( $_SERVER );
Dump functions accept any number of parameters and have shorthands.
d( $variable1, $variable2 );
