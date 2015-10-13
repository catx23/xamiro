###Overview

This document is about the options set in the index.php which might get overridden by a profile.
<br/>


#### [General Settings](#General)
#### [Directory Settings](#files)
<hr/>
#### <a name="General">General Settings</a>

##### **$CONF_DIRECTORY**

Default: FolderTo_xamiro/conf/

This variable sets the absolute path to the profile directory. 


##### **$XF_PATH**

Default: FolderTo_xamiro

This variable sets the absolute path to default file path for xamiro. This folder can be outside of the httpdocs folder
anywhere on a disc.

##### **$XF_DEFAULT_PROFILE**

Default: 'default'

This variable sets the default profile to be loaded which overrides all options set in index.php

##### **$XF_CUSTOM_PROFILE**

Default: 'custom'

Optional profile to loaded in top of index.php and conf/default.php. Only if this file exists (conf/custom.php)

 
##### $XAPP_SALT_KEY
 
The magic password to encrypt all sign Ajax calls.
 
#### <a name="files">Directory Settings</a>

##### **$XF_VISIBLE_FILE_EXTENSIONS**

Default: *

is a comma separated list of visible file extensions,ie: css,html,png. If you want to show 'hidden' folders or files, you need to add '.*'

##### **$XF_HIDDEN_FILE_EXTENSIONS**

Default: .svn,.git,.idea

is a comma separated list of hidden file extensions,ie: php,sh
