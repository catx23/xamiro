PHP/JS based and full featured file manager
===========================================

##Features
 
 - One page application & full 'Ajax'
 - Full keyboard support ala Midnight or Total-Commander 
 - Multi tab 
 - Code editor with auto-completion for CSS,PHP and Javascript 
 - Multi selection
 - Advanced search
 - Drag’n drop for copy, move and upload into any panel
 - Standard actions : Edit, Move, Rename, Info, Delete,Compress and Download
 - 2 image editors : Pixlr and Aviary
 - Javascript & Bash Shell
 - Built- in Javascript and Bash shell
 - Basic support for SFTP, FTP, WEBDAV, DROPBOX
 - 6 display modes for file panels : thumbnails, tree, image cover flow (ala Mac Finder), list, preview, split horizontal and split vertical
 - 5 main layouts : Dual panel, single panel, preview (ideal for media browsing), gallery and browser (classic : tree and thumbs)  
 - Supports folder locations outside of the Apache http docs folder
 - Logging panel with filters
 - Over 20 themes for the file manager and 20 editor themes
 - Strong security using signed client call similar to oauth. You can limit the access to IP and domains
 - Thumb resizing and caching for fast browsing large image folders
 - quite some stuff which i am too lazy to write down


 
## Additional components

- _XBlox_, a Visual programming language for extending the file manager and other things easier. Open test/blox.xblox example file (this feature is pre alpha!). You need to open the file manager
  explicitly via url parameter: index.php?profile=xblox&debug=true or index.php?profile=xode&debug=true. Currently there is are release versions of it. 
- Visual HTML editor for Dojo (aka Maqetta), currently support also _XBlox_ for widget/node properties. Same as xblox: You need to open the file manager
  explicitly via url parameter: index.php?profile=xblox&debug=true or index.php?profile=xode&debug=true. Currently there is are release versions of it.

- Node.JS service management and shell. This feature is not activated yet. 
- Hot-code replacement for AMD/Dojo apps. For those folks who know :-)

##Status


File manager: little unstable! There is still a lot of old and rubbish code to be removed. However, this project is 
under active development and not yet official released. 

##Screenshots

[See here] (misc/screenshots/SCREENSHOTS.md)

##Demos

[See here a dedicated demo page] (http://pearls-media.com:89/demo)

##Boiler-plate for AMD applications

[See here a dedicated repo](https://github.com/mc007/dojo-boilerplate)


##Important

This a development version. It has many sub modules and needs around 1 GB on the disc. You may download a pre-built version from [here](https://github.com/mc007/xbox-app/releases) 

## Requirements 

- PHP 5.3+ (better 5.4!)
- PHP : MBString extension
- PHP : Curl/SSL when using SFTP/WebDav/Dropbox




## Installation 

### Download latest stable release as zip file (uncompress in a folder, adjust all in index.php and xapp/Commander/Users.php): [here](https://github.com/mc007/xbox-app/releases/download/v0.6/xbox.zip)

### Download latest version as zip file (uncompress in a folder, adjust all in index.php and xapp/Commander/Users.php): [here](https://raw.githubusercontent.com/mc007/xbox-app/master/DIST/xbox.zip)

### Quick installation without any Git
 
[See here] (docs/installation.md)


### Soft Checkout (Uses pre-compiled Javascript version)

    
    $git clone https://github.com/mc007/xbox-app
    $cd xbox-app
    $git submodule init
    $git submodule update    

### Soft Checkout in one line

    $git clone https://github.com/mc007/xbox-app && cd xbox-app && git submodule init && git submodule update

### Full Checkout (Checks out full tree: 1GB+)

    $git clone --recursive https://github.com/mc007/xbox-app
    $git submodule foreach --recursive git checkout master

###Installation via one-click installer (downloads zip and un-compresses it)

1. Upload via FTP this file into any directory on your server https://raw.githubusercontent.com/mc007/xbox-app/master/install/remote/install.php
2. Make the parent directory writeable (755)!
3. Open the url http://mysite/install.php in your browser!
4. wait !
5. The installer downloaded https://raw.githubusercontent.com/mc007/xbox-app/master/DIST/xbox.zip and unzipped it in http://mysite/xbox-app/ by default
6. Open http://localhost/xbox-xapp/ 
7. Delete the install.php from your server!

### Usage

Navigate on your web server to : **http://localhost/xbox-app/index.php**

Open in different layout modes, append the entry URL by: 

index.php?**layout=single**: Single panel

index.php?**layout=dual**: Dual panel

index.php?**layout=preview**:  Preview layout (split view with media preview)

index.php?**layout=preview&theme=dot-luv**: Preview layout in dark theme (split view with media preview)

index.php?**layout=preview&open=Pictures**: Auto open picture folder in preview mode (split view with media preview)

index.php?**layout=gallery&open=BurningMan&minimal=true&theme=dot-luv**: Auto open picture folder in gallery mode (split view with cover flow view)

index.php?**layout=browser&open=Pictures**: Auto open picture folder in browser mode (Dual view with tree for navigation, classics!)

index.php?layout=gallery&open=BurningMan@Art-of-Burning-Man-2014_6-640x266.jpg&minimal=true&theme=dot-luv**: Open in gallery mode and open folder BurningMan and open a picture


#### Controls : Keyboard

- SPACE : Open Preview
- CTRL+F1 : Open Mounts
- F2 : Rename
- F4 : Edit file
- F5 : Copy (If main window is open, the destination is set automatically) 
- F6 : Move
- F7 : Create directory
- F8 : Delete
- F9 : Create file


- CTRL/CMD + ENTER : Open selection in main window

- BACKSPACE (Firefox) : Go back in history
- SHIFT + BACKSPACE (Chrome) : Go back in history
- DEL : Delete selection
- CTRL+W (Firefox) : Close last window
- SHIFT+W (Chrome) : Close last window 
- SHIFT+UP/DOWN : Multi-Selection 
- CTRL+A : Select all
- CTRL+C : Copy selection to clipboard 
- CTRL+X : Cut selection to clipboard 
- CTRL+V : Paste selection
- CTRL+S : Save current editor's content 
- CTRL+F : Open search
- CTRL-L : Reload current panel


#### Controls Editor

- Ctrl-F / Cmd-F : Start searching 
- Ctrl-G / Cmd-G : Find next 
- Ctrl -/+ Change Font Size
- Shift-Ctrl-G / Shift-Cmd-G : Find previous 
- Shift-Ctrl-F / Cmd-Option-F : Replace
- Shift-Ctrl-R / Shift-Cmd-Option-F : Replace all 


#### Controls : Mouse

- Right-Click : Open context menu
- CTRL : Enable copy mode for drag and drop 
- Uploading : Simply drag files from your file manager into the file panel, you can also just drag an url into file panel for remote downloads

#### Uploading

- Currently only supported via drag'n drop


  
### Open debug version 

http://localhost/xbox-app/index.php?debug=true

### Build Javascript main file

    
    $cd client/src
    $sh buildstandalone.sh    

This will create single layer file which contains all Dojo based in src/xfile/dojo/xbox.js!

### Resources (Javascript/CSS/Fonts,...)
- Client-Debug version : see in client/src/lib/xbox/resources-debug.json
- Client-Release version : see in client/src/xfile/xbox/resources-release.json

#### Footprint
- Javascript total: 2.8MB (1.4MB Core + 1MB plugins) unzipped or 0.6 MB when gzipped
- CSS total: 50KB
- Images: 20KB - 50KB. We do optimize it further
- XHR: 50KB for the initial load(gzipped)
- Total: 750kb gzipped

There are further optimizations in progress but we don't think we can get the minimal version below 700KB!



### The Index.html
There is no such thing! HTML related parts are pulled through the resource configuration mentioned in "Resources" above.
This technique has been proven for us since many years in many projects as we develop authoring software and those resources
needed to be managed in a more fashioned manner. However,

the **HEAD** comes from:
- Client-Debug version : client/src/lib/xbox/Header.js
- Client-Release version : client/src/xfile/xbox/Header.js

the **BODY** part comes from:

- Client-Debug version : client/src/lib/xbox/index.html
- Client-Release version : client/src/xfile/xbox/index.html

### The index.php

 1. Is more of interest to you, it basically does setup a 'xfile' configuration and fires the PHP framework
 2. Handles RPC and client rendering


### Update all

    $bash
    $cd xbox-app
    $git submodule foreach git pull origin master


### Contribute
open a Github issue and let me know :-)

###Roadmap
1. Update to ibm-js framework: in progress
2. remove all Dijit stuff: postponed
3. Code cleanup
4. Create a Node.js server version
5. Add proper test units 
6. ...


###Others

http://localhost/xbox-app/?theme=blitzer&rtConfig=debug&pConfig=run-release-release&profile=xide