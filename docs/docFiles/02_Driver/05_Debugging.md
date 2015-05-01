First things first: when developing drivers with the *Control Freak* IDE or external editors, **code changes are automatically applied**!
  
When being in the **debug mode** (IDE), you can debug your driver with the browser tools. Drivers are in that case like any
other Javascript. Just hit the F12 key whilst the IDE is open and see the console tab.

When being in the **release mode** (Run-time), since the driver runs on the server(*Device Control Server*) you can't debug the 
driver with the browser at the moment. There is a feature in progress to debug that driver from the IDE. 

###References
 
[Javascript API Documentation](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference)


###Console API

- [console.log](https://developer.mozilla.org/en-US/docs/Web/API/Console/log)
- [console.error](https://developer.mozilla.org/en-US/docs/Web/API/Console/error)
- [console.dir](https://developer.mozilla.org/en-US/docs/Web/API/Console/dir)

###Debugger API

You can stop the driver code at any position:

```js

    //halt the program and open the debugger here
    
    debugger;
    
```

There is currently also a private API to run a debug session for any driver. TBC...


###Examples when using the browser debugger

```js
    
    //print this driver instance in the console
    console.log('my instance',this);
    
    //print this driver instance more readable in the console
    console.dir(this);
    
    //print an error in the console, show the current instance as well 
    console.error('my error',this);
    

```
 
