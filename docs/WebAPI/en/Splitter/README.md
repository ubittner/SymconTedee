[![Image](../../../../imgs/tedee_logo.png)](https://tedee.com)

### Splitter Web API

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

* Communication with the tedee Web API

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

- In IP-Symcon select `Add instance` at any place and select `Tedee Splitter Web API` which is listed under the manufacturer `tedee`.
- A new `Tedee Splitter Web API` instance will be created.

__Konfigurationsseite__:

Name                    | Beschreibung
----------------------- | ------------------------------------
Active                  | De- / Activates the splitter
Personal Acess Key      | Personal Acess Key (PAK)
Timeout                 | Network Timeout

To authenticate via the personal access key (PAK), you must first generate it in your account.  
You can use the [Tedee Portal](https://portal.tedee.com) to do this.  
For more information on how to generate the personal access key, see the [API Documentation](https://tedee-tedee-api-doc.readthedocs-hosted.com/en/latest/howtos/authenticate.html#personal-access-key).

### 5. Statevariables and profiles

The state variables/categories are created automatically.  
Deleting individual ones can lead to malfunctions.

##### Statusvariables

No status variables are used.

##### Profile:

No prfiles are used.

### 6. WebFront

The splitter has no functionality in the WebFront.

### 7. PHP command reference

```text
Get devices

TEDEESW_GetDevices(integer $InstanceID);

Gets the existing devices.
Returns a json encoded string with the result.

Example:

//Get devices
$devices = TEDEESW_GetDevices(12345);
//Result
print_r(json_decode($devices, true));  
```

```text
Get devices with details

TEDEESW_GetDevicesWithDetails(integer $InstanceID);

Gets the existing devices with details.
Returns a json encoded string with the result.

Example:

//Get devices
$devices = TEDEESW_GetDevicesWithDetails(12345);
//Result
print_r(json_decode($devices, true));  
```

```text
Lock

TEDEESW_LockDoor(integer $InstanceID, integer $DeviceID);

Sends lock door request for specific lock.
Returns a json encoded string with the result.

Example:

//Lock
$result = TEDEESW_LockDoor(12345, 98765);
//Result
print_r(json_decode($result, true));  
```

```text
Unlock

TEDEESW_UnlockDoor(integer $InstanceID, integer $DeviceID);

Sends unlock door request for specific lock.
Returns a json encoded string with the result.

Example:

//Unlock
$result = TEDEESW_UnlockDoor(12345, 98765);
//Result
print_r(json_decode($result, true));  
```

```text
Pull door

TEDEESW_PullDoor(integer $InstanceID, integer $DeviceID);

Sends pull spring of door request for specific lock.
Returns a json encoded string with the result.

Depending on the configuration of the device, it may be necessary to open the lock first and then pull the latch.

Example:

//Pull door
$result = TEDEESW_PullDoor(12345, 98765);
//Result
print_r(json_decode($result, true));  
```

```text
Get lock status

TEDEESW_GetLockStatus(integer $InstanceID, integer $DeviceID);

Gets a lock status of a specific lock. 
Returns a json encoded string with the result.

Example:

//Get status
$result = TEDEESW_GetLockStatus(12345, 98765);
//Result
print_r(json_decode($result, true));  
```

```text
Get device activity

TEDEESW_GetDeviceActivity(integer $InstanceID, integer $DeviceID, integer $Elements);

Gets the device activities for a specific lock.
Returns a json encoded string with the result.

Example:

//Get activities
$result = TEDEESW_GetDeviceActivity(12345, 98765, 10);
//Result
print_r(json_decode($result, true));  
```







