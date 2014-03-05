WP-Helpers [![Build Status](https://travis-ci.org/rtCamp/wp-helpers.png)](https://travis-ci.org/rtCamp/wp-helpers)
==========

WP Helpers is library of class that are required in development of any WordPress plugins.

Following are some classes

* RT_DB_Model
* RT_DB_Update
* RT_Plugin_Info
* RT_Plugin_Update_Checker
* RT_WP_Autoloader
* RT_Theme_Update_Checker

**NOTE**: Development in progress

Inspired from https://github.com/zendframework/zf2/tree/master/library/Zend/

To add it in your plugin/theme
```
git subtree add --prefix app/lib https://github.com/rtCamp/wp-helpers.git master  --squash
```

To update the library
```
git subtree pull --prefix app/lib https://github.com/rtCamp/wp-helpers.git master  --squash
```

Add following line in plugin loader file

```
include_once 'app/lib/wp-helpers.php';
```

Alternatively you can add as a plugin also

License
========

Same [GPL] (http://www.gnu.org/licenses/gpl-2.0.txt) that WordPress uses!
