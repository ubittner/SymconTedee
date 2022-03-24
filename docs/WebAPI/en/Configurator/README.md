[![Image](../../../../imgs/tedee_logo.png)](https://tedee.com)

### Configurator Web API

This module manages the existing devices.  
The user can create the selected devices automatically.

For this module there is no claim for further development, other support or can include errors.  
Before installing the module, a backup of IP-Symcon should be performed.  
The developer is not liable for any data loss or other damages.  
The user expressly agrees to the above conditions, as well as the license conditions.

### Table of contents

1. [Scope of functions](#1-scope-of-functions)
2. [Requirements](#2-requirements)
3. [Software installation](#3-software-installation)
4. [Setting up the instance](#4-setting-up-the-instance)
5. [Statevariables and profiles](#5-statevariables-and-profiles)
6. [WebFront](#6-webfront)
7. [PHP command reference](#7-php-command-reference)

### 1. Scope of functions

* List of the available devices
* Automatic creation of the devices

### 2. Requirements

- IP-Symcon at least version 6.0
- tedee Smart Lock
- tedee Bridge
- Personal Access Token (PAK)
- Internet connection

### 3. Software installation

* For commercial use (e.g. as an integrator), please contact the author first.
* Use the `Module Store` for installing the `tedee` Module.

### 4. Setting up the instance

- In IP-Symcon select `Add instance` at any place and select `Tedee Konfigurator Web API` which is listed under the manufacturer `tedee`.
- A new `Tedee Konfigurator Web API` instance will be created.

__Configuration__:

Name        | Description
----------- | -----------------------------
Category    | category for the devices
Devices     | list of the available devices

__Buttons in the action area__:

Name        | Description
------------| ---------------------------------------------------
Create all  | Creates one instance for each of the listed devices
Create      | Creates an instance for the selected device

__Procedure__:

You can use the `UPDATE` button in the configurator to update the list of available devices at any time.  
Select 'CREATE ALL' or select a device from the list and then press the 'CREATE' button to create the device automatically.  
If no [Tedee Splitter Web API](../../../../docs/WebAPI/en/Splitter/README.md) instance has been created yet, you have to configure the splitter instance once when creating the configurator instance.  
Enter your personal access key (PAK) and the network timeout.  
Then select 'NEXT'.

### 5. Statevariables and profiles

The state variables/categories are created automatically.  
Deleting individual ones can lead to malfunctions.

##### Statusvariables

No status variables are used.

##### Profile:

No prfiles are used.

### 6. WebFront

The configurator has no functionality in the WebFront.

### 7. PHP command reference

There is no command reference available.