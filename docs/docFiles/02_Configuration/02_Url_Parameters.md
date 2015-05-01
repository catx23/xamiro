Many options of the chosen profile can be overriden by appending the Url to xamiro:

[Profile](#Profile)

[Layout](#Layout)

[Theme](#Theme)

[Open folder or file](#Open)

[Special parameters](#Special)

### <a name="Profile">Profile</a>

You can switch between profiles (by default located in xamiro/conf/) by appending your Url with ['?profile=custom'](http://localhost/xamiro/?profile=custom)

### <a name="Layout">Layout</a>


xamiro comes with 4 general layouts:

1. Single (default)

    This layout consists out of a single file view and if not disabled:

    - an info panel
    - an bottom panel
    <br/>

    Activate this layout by appending your Url with ['?layout=single'](http://localhost/xamiro/?layout=single)

2. Dual 

    This layout consists out of a dual file view and if not disabled:

    - an info panel
    - an bottom panel
    <br/>

    Activate this layout by appending your Url with ['?layout=dual'](http://localhost/xamiro/?layout=dual)
      
3. Preview 

  This layout consists out of horizontal splitted view:
  
  top: preview (shows the right editor or multimedia view basing on the selected file)
  
  bottom: file view
  
   and if not disabled:
  
      - an info panel
      - an bottom panel
      <br/>
  
    Activate this layout by appending your Url with ['?layout=preview'](http://localhost/xamiro/?layout=preview)
    
4. Cover flow gallery

  This layout consists out of horizontal splitted view:
  
  top: cover flow gallery
  
  bottom: file view
  
   and if not disabled:
  
      - an info panel
      - an bottom panel
      <br/>
  
    Activate this layout by appending your Url with ['?layout=gallery'](http://localhost/xamiro/?layout=gallery)


### <a name="Theme">Theme</a>

xamiro comes with a number of jQuery themes which you can activate by append your Url with ['?theme=dflat'](http://localhost/xamiro/?theme=dflat)

Currently these themes are possible 

 - dflat
 - bootstrap
 - dot-luv
 - black-tie
 - blitzer
 - cupertino
 - dark-hive
 - eggplant
 - excite-bike
 - flick 
 - hot-sneaks,
 - humanity
 - le-frog 
 - mint-choc
 - overcast
 - pepper-grinder
 - redmond
 - smoothness
 - south-street 
 - start
 - sunny
 - swanky-purse,
 - trontastic
 - ui-darkness
 - ui-lightness,vader
 
 See [http://jqueryui.com/themeroller](http://jqueryui.com/themeroller) for more!

### <a name="Open">Open files or folders</a>

You can instruct xamiro to a folder or file by appending your Url with ['?open=Pictures'](http://localhost/xamiro/?open=Pictures)
In case the file sits in a deeper folder, you must use '@' instead of '/'. Be aware it might open an editor by file extension.

Examples

- open folder Pictures/Vacation: ['?open=Pictures@Vacation'](http://localhost/xamiro/?open=Pictures@Vacation)

- open a picture Pictures/Vacation/001.jpg: ['?open=Pictures@Vacation@001.jpg'](http://localhost/xamiro/?open=Pictures@Vacation@001.jpg)


### <a name="Special">Special Parameters</a>

#### Hide info, bottom panel and main menu: ['?minimal=true'](http://localhost/xamiro/?minimal=true)


<hr/>


