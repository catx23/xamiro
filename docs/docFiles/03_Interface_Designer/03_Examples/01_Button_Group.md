## <a name="Widget"></a>Tutorial "Making Button Group"


###Goals

<img src="../../assets/examples/bg_0.png" class="img-responsive pull-right"  style="">

1. Change the visuals of all other buttons when a certain button is clicked

    In particular we want for the 'pressed' button background color green and for all non pressed buttons "Background color" gray.
   

2. Add effects for mouse over and mouse out events.

    <br/>
    <br/>
    <br/>
    <br/>

### The simple way, using block scripts and Ids and/or CSS classes

[Alternatively you can use a single script block](#Javascript)


In this variant we create for each button a script for the 'on click' event which 'resets' the visual style of all buttons and then
sets the styles as desired for the pressed state. 

<div class="alert alert-info" role="alert">
 Notice, that is not the most ideal and short solution but it demonstrates the very basics. There are more tips in this
 solution to do the same but shorter. 
</div>
 
<br/>
#### 1. First we need to give each button an unique 'Id'

<img src="../../assets/examples/bg_set_id.png" class="img-responsive center-block"  style="">
    
repeat this step for all other buttons and set the id as it makes sense: 
  
  - TV: btnTV
  - Radio: btnRadio
  - DVD: btnDVD
  - MediaPlayer: btnMediaPlayer

<div class="alert alert-info" role="alert">
Notice, you can also set the "CSS" class for each button to "inputSourceButton" for targeting buttons by CSS class instead of
Ids
</div>

#### 2. Then open the 'scripts' tab for "Media-Player"
  
<img src="../../assets/examples/bg_open_scripts.png" class="img-responsive center-block"  style="">
<br/>
#### 3. Now create the script for "Media-Player" for the 'on-click' event 
  
<img src="../../assets/examples/bg_createBGroup.png" class="img-responsive center-block"  style="">
<br/>
#### 4. Add the "Set Style" block for resetting all buttons background color to gray
    
<img src="../../assets/examples/bg_reset_background.png" class="img-responsive center-block"  style="">
<br/>
#### Repeat this for every other button as well (Step 3. you can copy & paste the block)

<div class="alert alert-info" role="alert">
Notice, you don't need to add that block to each other button when you target all other buttons via the CSS class:
 
 If every button has the CSS class "inputSourceButton", set in the 'Target' field "inputSourceButton" and set 'Mode' 
 to "By CSS Query". This will apply the "Set Style" block to all elements in the scene having this CSS class.  

</div>
#### 5. Now add a "Set Style" block to set the "pressed" state: 
    
<img src="../../assets/examples/bg_set_background.png" class="img-responsive center-block"  style="">

#### Repeat this for every other button as well (you can copy & paste the block)



### <a name="Javascript">The fast way, using Javascript and jQuery</a>

[Please find more documentation about jQuery here](../../../jQuery/index.html)

#### 1. This variant assumes you did read the basics of the method above. Please do the steps 1. & 2. and then create new 
block group for the 'load' event:

<img src="../../assets/examples/bg_createBGroup_load.png" class="img-responsive center-block"  style="">


#### 2. As next we add a 'RunScript' block to the Media-Player button, and then we edit the script:


<img src="../../assets/examples/bg_add_script_block.png" class="img-responsive center-block"  style="">

<div class="alert alert-info" role="alert">
Notice since the 'load' event will be emitted once the entire scene has been loaded, you must press the 'Reload' button
to run the 'RunScript' Block at least once.
</div>

#### 3. Now the code! 

The first variant does basically this, using ids:

1. reset the background to gray if any button has been clicked
2. set the background to green for any clicked button
 

```javascript

//  1. resetting background color
//  add a click handler to each button using Ids:

$('#btnDVD, #btnTV, #btnRadio, #btnMediaPlayer').on('click',function(evt){
	
    //update background-color, notice you select elements by Id by using the '#' prefix to the buttons id:    
    $('#btnDVD, #btnTV, #btnRadio, #btnMediaPlayer').css('background-color','gray');
    
    //get the clicked button
    var buttonClicked = $(evt.target);
    
    //2. set the clicked button to green:
    buttonClicked.css('background-color','green');
		
});
```


The second variant does basically this, using CSS classes:

1. reset the background to gray if any button has been clicked
2. set the background to green for any clicked button
 

```javascript

//  1. resetting background color
//  add a click handler to each button using CSS class query, make sure all buttons have the CSS class 'inputSourceButton':

$('.inputSourceButton').on('click',function(evt){	
    
    //update background-color, notice you select elements by CSS class using the '.' prefix
    $('.inputSourceButton').css('background-color','gray');    
    //get the clicked button
    var buttonClicked = $(evt.target);
    
    //2. set the clicked button to green:
    buttonClicked.css('background-color','green');
		
});
```




