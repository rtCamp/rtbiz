<!-- DO NOT EDIT THIS FILE; it is auto-generated from readme.txt -->
# rtLib [![Build Status](https://travis-ci.org/rtCamp/rt-lib.svg?branch=master)](https://travis-ci.org/rtCamp/rt-lib)




**Contributors:** [rtcamp](http://profiles.wordpress.org/rtcamp), [rahul286](http://profiles.wordpress.org/rahul286), [faishal](http://profiles.wordpress.org/faishal), [desaiuditd](http://profiles.wordpress.org/desaiuditd)  
**Tags:** [library](http://wordpress.org/plugins/tags/library), [autoloader](http://wordpress.org/plugins/tags/autoloader), [database model](http://wordpress.org/plugins/tags/database model), [database updater](http://wordpress.org/plugins/tags/database updater), [attributes](http://wordpress.org/plugins/tags/attributes), [user groups](http://wordpress.org/plugins/tags/user groups)  
**Requires at least:** 3.6  
**Tested up to:** 3.9.1  
**Stable tag:** master  
**License:** [GPLv2 or later](http://www.gnu.org/licenses/gpl-2.0.html)  

## Description ##

rtLib is library of class that are required in development of any WordPress plugins.

Following are some classes:

 * RT_DB_Model
 * RT_DB_Update
 * RT_Plugin_Info
 * RT_Plugin_Update_Checker
 * RT_WP_Autoloader
 * RT_Theme_Update_Checker
 * RT_Email_Template
 * RT_Attributes

**NOTE**: Development in progress

Inspired from https://github.com/zendframework/zf2/tree/master/library/Zend/

To add it in your plugin/theme
```
git subtree add --prefix app/lib https://github.com/rtCamp/rt-lib.git master  --squash
```

To update the library
```
git subtree pull --prefix app/lib https://github.com/rtCamp/rt-lib.git master  --squash
```

Add following line in plugin loader file

```
include_once 'app/lib/rt-lib.php';
```

Alternatively you can add as a plugin also

** License **

Same [GPL] (http://www.gnu.org/licenses/gpl-2.0.txt) that WordPress uses!

**Coming soon:**

 * Private Attributes Support

**See room for improvement?**

Great! There are several ways you can get involved to help make Stream better:

1. **Report Bugs:** If you find a bug, error or other problem, please report it! You can do this by [creating a new topic](https://github.com/rtCamp/rt-lib/issues) in the issue tracker.
2. **Suggest New Features:** Have an awesome idea? Please share it! Simply [create a new topic](https://github.com/rtCamp/rt-lib/issues) in the issure tracker to express your thoughts on why the feature should be included and get a discussion going around your idea.

[![Build Status](https://travis-ci.org/rtCamp/rt-lib.png?branch=master)](https://travis-ci.org/rtCamp/rt-lib)

## Changelog ##

### 0.9 ###
Rt_Offerings Refactor & Bug Fixes

### 0.8 ###
RT Product Sync Library Added
Travis Config updated for WordPress Coding Standards
User Group Bug Fixes & additional method added to get users by term id.

### 0.7 ###
A Few bug fixes for RT_LIB_FILE constant
DB Update Key changed for User Groups

### 0.6 ###
Test Cases updated & Code Sniffer Config updated & pre-commit hook updated

### 0.5 ###
Initial Basic Libraries.


