Driver Programming Guide
========================

##Overview

[General](#General)

[Requirements](#Requirements)

[Definitions](#Definitions)

[Terminology](#Terminology)

[Lifecycle](#Lifecycle)

[Programming](#Programming)

[FAQ](#FAQ)


### <a name="General"></a>General

This guide is for developers who would like to add support for more devices to the Control Freak IDE. the IDE provides all necessary tools and assistance to minimize 
this task best as possible. 

### <a name="Requirements"></a>Requirements

 - Basic understanding of programming or simple script languages. Currently only Javascript is to be used.

### <a name="Definitions"></a>Definitions

 - A **Driver** is a system entity which writes and handles device messages but also contains information about it self. 
 - **Driver Meta database** is a collection of settings, fully editable in the IDE.
 - A **Device** is a system entity which contains information only about the network details of a device and the used *driver*, for instance IP address and port.
 - A **Block** is a system entity which contains data and/or code. A 'block' represents the base unit in the driver system. A block is used for *Variables* 
   and *Commands*. 
 - *Run-Time-Configuration* sets the name of the configuration within the applications: IDE or Run-Time. There is currently
   the "debug" and "release" configuration. When running a driver in the IDE, its set to "debug" and when running fully deployed,
   its running with the "release" configuration. 


### <a name="Terminology"></a>Terminology

####Drivers

A *driver* consists out of:

 - A Javascript file, more precisely a 'class' inheriting some basic functionality
 - A meta database, currently stored as a 'JSON' file. This meta database contains information such as the name of the driver but also the link to the Javascript file.
 - A collection of *blocks* which do the logic but also contain data as *variables*. Since a block can be entire program in it self, it may call other blocks as well.
 


####Devices

A *device* consists out of:

 - A meta database, currently stored as a 'JSON' file. This meta database contains information such as the name of the device, the IP address or the port.
 
####Blocks

A *block* consists out of:

 - A Javascript file or *class*, holding data but also running code. A driver for instance contains variables and commands which are all blocks. 
   The system runs these blocks in a specific order and logic. There are blocks which will be called upon start and there are blocks which be called
   when a device message comes in. Blocks and driver code do work hand in hand and are 100% compatible to each other. 



### <a name="Lifecycle"></a>Lifecycle

**The begin of a driver**

The life of driver begins as soon a device is marked as active and references this driver. 



**The end of a driver**

It ends when no device referencing this driver.
 
 
**Run-Time Notes**

 - When developing the driver in the IDE, the "debug" configuration is being used. In this case, the driver runs in the 
   browser, thus enabling easy debugging with the browser's built-in developer tools. Currently Firefox, Firebug and Chrome console is supported.
   

 - When running a driver in a fully deployed application, the driver runs on the server (Node.JS Device-Control-Server)


### <a name="FAQ"></a>FAQ

<hr/>

## <a name="Programming"></a>Programming Overview

###General 

The Control-Freak IDE and Run-time does already some legwork:

- Creating the Javascript class file from a template, ready to be completed by your code. 
- Instantiating the class when a device is referencing this driver
- Cleanup and stop the driver when not needed anymore
- Calling the driver API for each device or system event
- adding useful attributes to the instance like quick access the drivers meta database or more methods to access other drivers, devices and also calling xblox. 

###The Driver base class 

[Please find its API documentation here](DriverBase.html)

All driver code is written in OOP. The system inherits your driver class automatically 
from a driver base class which provides the most wanted functions. It also enforces a
a strict API (signature) when it comes to device communication but its entirely up to you to you to make use of it.
 

###General Entry Points
 

The most important methods being called by the system are:

- sendMessage(string,now)
- [onMessage(data)](../../modules/xcfnode/out/DriverBase.html#onMessage)
- start()
- stop()

You may override or complete this methods. If you don't override it, then the methods of the base class will be called. 


###Tools 

There are more utils functions in your driver instance to have direct access to variables, commands or xBlox. Here some of them:

####Variables

- getVariable(title)
- setVariable(title,value)


####Commands

- callCommand('PowerOn')

####xBlox

- runBlock('BlockTitle',args)
