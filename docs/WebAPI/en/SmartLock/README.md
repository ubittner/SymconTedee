[![Image](../../../../imgs/tedee_logo.png)](https://tedee.com)

### Smart Lock Web API

This module integrates your tedee Smart Lock.

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

* Lock and unlock lock incl. other functions
* Display device status (various)
* Display log

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

- In IP-Symcon select `Add instance` at any place and select `Tedee Smart Lock Web API` which is listed under the manufacturer `tedee`.
- A new `Tedee Smart Lock Web API` instance will be created.

__Configuration__:

Name                                | Description
----------------------------------- | --------------------------------------------
Device ID                           | Device ID
Serialnumber                        | Sewrialnumber  
Name                                | Device name
Update interval                     | Update interval
Use activity log                    | Activity log 
Number of maximum activity entries  | Number of maximum activity entries
Use daily lock                      | Use daily lock
Lock time                           | Lock time
Use daily unlock                    | Use daily unlock
Unlock time                         | Lock time

### 5. Statevariables and profiles

The state variables/categories are created automatically.  
Deleting individual ones can lead to malfunctions.

##### Statusvariables

Name            | Type      | Description
--------------- | --------- | -------------------------
SmartLock       | integer   | Lock / unlock / pull
DeviceState     | integer   | Device state
Connection      | boolean   | Connection to bridge
BatteryLevel    | integer   | Battery level
BatteryCharging | boolean   | Battery charging
ActivityLog     | Protokoll | Activity log

#### Profile

TEDEESLW.InstanceID.Name

Name                    | Typ
----------------------- | -------
SmartLock               | integer
DeviceState             | integer
Connection              | boolean
BatteryLevel            | integer
BatteryCharging         | boolean

If the instance is deleted, the profiles listed above are automatically deleted.

### 6. WebFront

The functionality provided by the module in the WebFront.

* Lock and unlock lock incl. other functions
* Display device status (various)
* Display log

[![Image](../../../../imgs/smartlock_webfront_en.png)]()

[![Image](../../../../imgs/smartlock_mobile_en.png)]()

### 7. PHP command reference

```text
Smart Lock Actions (lock / unlock / pull)

TEDEESLW_SetLockAction(integer $InstanceID, integer $Action);

Switches a specific action of the smart lock.  
Returns a json encoded string with the result.

$Action:
0   =   lock
1   =   unlock
2   =   pull

Example:

//Lock
$action = TEDEESLW_SetSmartLockAction(12345, 0);
//Result
print_r(json_decode($action, true));
```

```text
Update device state

TEDEESLW_ UpdateDeviceData(integer $InstanceID);

Queries the current status of the device and updates the values of the corresponding variables. 
Does not return a return value.

Example:

TEDEESLW_UpdateDeviceData(12345);
```