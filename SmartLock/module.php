<?php

/** @noinspection DuplicatedCode */

declare(strict_types=1);

class TedeeSmartLockWebAPI extends IPSModule
{
    //Constants
    private const TEDEE_LIBRARY_GUID = '{108EA9EC-FA0F-BCBB-9644-1D19E770CE3B}';
    private const MODULE_PREFIX = 'TEDEESLW';
    private const TEDEE_SPLITTER_GUID = '{5D366804-5DF3-FE00-F4C3-F00CB093848E}';
    private const TEDEE_SPLITTER_DATA_GUID = '{BE7F44AC-CE78-7863-C18B-88CD4D2E4B30}';

    public function Create()
    {
        ########## Properties
        $this->RegisterPropertyInteger('DeviceID', 0);
        $this->RegisterPropertyString('DeviceSerialNumber', '');
        $this->RegisterPropertyString('DeviceName', '');
        $this->RegisterPropertyInteger('UpdateInterval', 300);
        $this->RegisterPropertyBoolean('UseActivityLog', true);
        $this->RegisterPropertyInteger('ActivityLogMaximumEntries', 10);
        $this->RegisterPropertyBoolean('UseDailyLock', false);
        $this->RegisterPropertyString('DailyLockTime', '{"hour":23,"minute":0,"second":0}');
        $this->RegisterPropertyBoolean('UseDailyUnlock', false);
        $this->RegisterPropertyString('DailyUnlockTime', '{"hour":6,"minute":0,"second":0}');

        //Never delete this line!
        parent::Create();

        ########## Variables

        //Smart Lock
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.SmartLock';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileAssociation($profile, 0, $this->Translate('Lock'), 'LockClosed', 0xFF0000);
        IPS_SetVariableProfileAssociation($profile, 1, $this->Translate('Unlock'), 'LockOpen', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 2, $this->Translate('Pull'), 'Door', 0x0000FF);
        $this->RegisterVariableInteger('SmartLock', $this->Translate('Smart lock'), $profile, 100);
        $this->EnableAction('SmartLock');

        //Device state
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.DeviceState';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileIcon($profile, '');
        IPS_SetVariableProfileAssociation($profile, 0, $this->Translate('Uncalibrated'), 'Warning', 0xFF0000);
        IPS_SetVariableProfileAssociation($profile, 1, $this->Translate('Calibrating'), 'TurnLeft', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 2, $this->Translate('Unlocked'), 'LockOpen', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 3, $this->Translate('SemiLocked'), 'LockClosed', 0xFF0000);
        IPS_SetVariableProfileAssociation($profile, 4, $this->Translate('Unlocking'), 'LockOpen', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 5, $this->Translate('Locking'), 'LockClosed', 0xFF0000);
        IPS_SetVariableProfileAssociation($profile, 6, $this->Translate('Locked'), 'LockClosed', 0xFF0000);
        IPS_SetVariableProfileAssociation($profile, 7, $this->Translate('Pulled'), 'Door', 0x0000FF);
        IPS_SetVariableProfileAssociation($profile, 8, $this->Translate('Pulling'), 'Door', 0x0000FF);
        IPS_SetVariableProfileAssociation($profile, 9, $this->Translate('Unknown'), 'Information', -1);
        IPS_SetVariableProfileAssociation($profile, 18, $this->Translate('Updating'), 'Gear', 0x00FF00);
        $id = @$this->GetIDForIdent('DeviceState');
        $this->RegisterVariableInteger('DeviceState', $this->Translate('Device state'), $profile, 200);
        if ($id == false) {
            $this->SetValue('DeviceState', 9);
        }

        //Connection
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.Connection';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, '');
        IPS_SetVariableProfileAssociation($profile, false, $this->Translate('Not connected'), 'Warning', 0xFF0000);
        IPS_SetVariableProfileAssociation($profile, true, $this->Translate('Connected'), 'Ok', 0x00FF00);
        $this->RegisterVariableBoolean('Connection', $this->Translate('Connection'), $profile, 210);

        //Battery level
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.BatteryLevel';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileValues($profile, 0, 100, 1);
        IPS_SetVariableProfileText($profile, '', '%');
        IPS_SetVariableProfileIcon($profile, 'Battery');
        $this->RegisterVariableInteger('BatteryLevel', $this->Translate('Battery level'), $profile, 220);

        //Battery charging
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.BatteryCharging';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, 'Battery');
        IPS_SetVariableProfileAssociation($profile, 0, $this->Translate('Inactive'), '', 0xFF0000);
        IPS_SetVariableProfileAssociation($profile, 1, $this->Translate('Active'), '', 0x00FF00);
        $this->RegisterVariableBoolean('BatteryCharging', $this->Translate('Battery charging'), $profile, 230);

        ##### Timer
        $this->RegisterTimer('Update', 0, self::MODULE_PREFIX . '_UpdateDeviceData(' . $this->InstanceID . ');');
        $this->RegisterTimer('DailyLock', 0, self::MODULE_PREFIX . '_SetLockAction(' . $this->InstanceID . ', 0);');
        $this->RegisterTimer('DailyUnlock', 0, self::MODULE_PREFIX . '_SetLockAction(' . $this->InstanceID . ', 1);');

        ##### Splitter

        //Connect to parent (Tedee Splitter)
        $this->ConnectParent(self::TEDEE_SPLITTER_GUID);
    }

    public function Destroy()
    {
        //Never delete this line!
        parent::Destroy();

        //Delete profiles
        $profiles = ['SmartLock', 'Connection', 'DeviceState', 'BatteryLevel', 'BatteryCharging'];
        foreach ($profiles as $profile) {
            $this->DeleteProfile($profile);
        }
    }

    public function ApplyChanges()
    {
        //Wait until IP-Symcon is started
        $this->RegisterMessage(0, IPS_KERNELSTARTED);

        //Never delete this line!
        parent::ApplyChanges();

        //Check kernel runlevel
        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }

        ##### Maintain variables

        //Activity log
        if ($this->ReadPropertyBoolean('UseActivityLog')) {
            $id = @$this->GetIDForIdent('ActivityLog');
            $this->MaintainVariable('ActivityLog', $this->Translate('Activity log'), 3, 'HTMLBox', 300, true);
            if ($id == false) {
                IPS_SetIcon($this->GetIDForIdent('ActivityLog'), 'Database');
            }
        } else {
            $this->MaintainVariable('ActivityLog', $this->Translate('Activity log'), 3, '', 0, false);
        }

        $this->SetDailyLockTimer();
        $this->SetDailyUnlockTimer();
        $this->UpdateDeviceData();
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        $this->SendDebug(__FUNCTION__, $TimeStamp . ', SenderID: ' . $SenderID . ', Message: ' . $Message . ', Data: ' . print_r($Data, true), 0);
        if (!empty($Data)) {
            foreach ($Data as $key => $value) {
                $this->SendDebug(__FUNCTION__, 'Data[' . $key . '] = ' . json_encode($value), 0);
            }
        }
        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;

        }
    }

    public function GetConfigurationForm()
    {
        $formData = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $library = IPS_GetLibrary(self::TEDEE_LIBRARY_GUID);
        $formData['elements'][2]['caption'] = 'ID: ' . $this->InstanceID . ', Version: ' . $library['Version'] . '-' . $library['Build'] . ', ' . date('d.m.Y', $library['Date']);
        return json_encode($formData);
    }

    public function ReceiveData($JSONString)
    {
        //Received data from splitter, not used at the moment
        $this->SendDebug(__FUNCTION__, 'Incoming data: ' . $JSONString, 0);
        $data = json_decode($JSONString);
        $this->SendDebug(__FUNCTION__, 'Buffer data:  ' . json_encode($data->Buffer), 0);
    }

    #################### Request Action

    public function RequestAction($Ident, $Value): void
    {
        switch ($Ident) {
            case 'SmartLock':
                $this->SetLockAction($Value);
                break;

        }
    }

    public function UpdateDeviceData(): void
    {
        $this->SetTimerInterval('Update', $this->ReadPropertyInteger('UpdateInterval') * 1000);
        $this->UpdateDeviceStatus();
        $this->UpdateActivityLog();
    }

    public function SetLockAction(int $Action): string
    {
        $this->SetDailyLockTimer();
        $this->SetDailyUnlockTimer();
        $deviceID = $this->ReadPropertyInteger('DeviceID');
        if (!$this->HasActiveParent() || $deviceID == 0) {
            return json_encode(['httpCode' => 0, 'body' => '']);
        }
        $this->SetTimerInterval('Update', 0);
        $actualValue = $this->GetValue('SmartLock');
        $this->SetValue('SmartLock', $Action);
        switch ($Action) {
            case 0: # Lock
                $command = 'LockDoor';
                break;

            case 1: # Unlock
                $command = 'UnlockDoor';
                break;

            case 2: # Pull
                $command = 'PullDoor';
                break;

            default:
                $this->SendDebug(__FUNCTION__, 'Unknown action: ', 0);
                $this->SetTimerInterval('Update', 5000);
                return json_encode(['httpCode' => 0, 'body' => '']);
        }
        $data = [];
        $buffer = [];
        $data['DataID'] = self::TEDEE_SPLITTER_DATA_GUID;
        $buffer['Command'] = $command;
        $buffer['Params'] = ['id' => $deviceID];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $this->SendDebug(__FUNCTION__, 'Data: ' . $data, 0);
        $response = $this->SendDataToParent($data);
        if (is_string($response) && is_array(json_decode($response, true)) && (json_last_error() == JSON_ERROR_NONE)) {
            $responseData = json_decode($response, true);
            if (array_key_exists('httpCode', $responseData)) {
                $httpCode = $responseData['httpCode'];
                if ($httpCode != 202) {
                    $this->SendDebug(__FUNCTION__, 'Abort, response http code: ' . $httpCode . ', must be 202!', 0);
                    $this->SetTimerInterval('Update', 5000);
                    return $response;
                }
                if (array_key_exists('body', $responseData)) {
                    if (is_string($responseData['body']) && is_array(json_decode($responseData['body'], true)) && (json_last_error() == JSON_ERROR_NONE)) {
                        $this->SendDebug(__FUNCTION__, 'Actual data: ' . $responseData['body'], 0);
                        /*
                            Example:
                            {
                               "success":true,
                               "errorMessages":[
                                  "string"
                               ],
                               "statusCode":0,
                               "result":{
                                  "operationId":"string",
                                  "lastStateChangedDate":"2022-03-15T09:57:41.469Z"
                               }
                            }
                         */
                        $actualData = json_decode($responseData['body'], true);
                        if (!empty($actualData)) {
                            if (array_key_exists('success', $actualData)) {
                                if (!$actualData['success']) {
                                    //Revert
                                    $this->SetValue('SmartLock', $actualValue);
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->SetTimerInterval('Update', 5000);
        return $response;
    }

    #################### Private

    private function KernelReady(): void
    {
        $this->ApplyChanges();
    }

    private function DeleteProfile(string $ProfileName): void
    {
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.' . $ProfileName;
        if (@IPS_VariableProfileExists($profile)) {
            IPS_DeleteVariableProfile($profile);
        }
    }

    private function UpdateDeviceStatus(): void
    {
        $deviceID = $this->ReadPropertyInteger('DeviceID');
        if (!$this->HasActiveParent() || $deviceID == 0) {
            return;
        }
        $data = [];
        $buffer = [];
        $data['DataID'] = self::TEDEE_SPLITTER_DATA_GUID;
        $buffer['Command'] = 'GetLockStatus';
        $buffer['Params'] = ['id' => $deviceID];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $this->SendDebug(__FUNCTION__, 'Data: ' . $data, 0);
        $response = $this->SendDataToParent($data);
        if (is_string($response) && is_array(json_decode($response, true)) && (json_last_error() == JSON_ERROR_NONE)) {
            $responseData = json_decode($response, true);
            if (array_key_exists('httpCode', $responseData)) {
                $httpCode = $responseData['httpCode'];
                if ($httpCode != 200) {
                    $this->SendDebug(__FUNCTION__, 'Abort, response http code: ' . $httpCode . ', must be 200!', 0);
                    return;
                }
                if (array_key_exists('body', $responseData)) {
                    if (is_string($responseData['body']) && is_array(json_decode($responseData['body'], true)) && (json_last_error() == JSON_ERROR_NONE)) {
                        $this->SendDebug(__FUNCTION__, 'Actual data: ' . $responseData['body'], 0);
                        /*
                            Example:
                            {
                               "success":true,
                               "errorMessages":[
                                  "string"
                               ],
                               "statusCode":0,
                               "result":{
                                  "id":0,
                                  "isConnected":true,
                                  "lockProperties":{
                                     "state":0,
                                     "isCharging":true,
                                     "batteryLevel":0,
                                     "stateChangeResult":0,
                                     "lastStateChangedDate":"2022-03-12T21:01:51.437Z"
                                  }
                               }
                            }
                         */
                        $actualData = json_decode($responseData['body'], true);
                        if (!empty($actualData)) {
                            //Result
                            if (array_key_exists('result', $actualData)) {
                                $result = $actualData['result'];
                                //Device ID
                                if (array_key_exists('id', $result)) {
                                    if ($this->ReadPropertyInteger('DeviceID') != $result['id']) {
                                        $this->SendDebug(__FUNCTION__, 'Abort, data is not for this device!', 0);
                                        return;
                                    }
                                }
                                //Connection
                                if (array_key_exists('isConnected', $result)) {
                                    $this->SetValue('Connection', $result['isConnected']);
                                }
                                //Properties
                                if (array_key_exists('lockProperties', $result)) {
                                    $lockProperties = $result['lockProperties'];
                                    //State
                                    if (array_key_exists('state', $lockProperties)) {
                                        $this->SetValue('DeviceState', $lockProperties['state']);
                                        /*
                                            0	Uncalibrated
                                            1	Calibrating
                                            2	Unlocked
                                            3	SemiLocked
                                            4	Unlocking
                                            5	Locking
                                            6	Locked
                                            7	Pulled
                                            8	Pulling
                                            9	Unknown
                                            18	Updating
                                         */
                                        switch ($lockProperties['state']) {
                                            case 2: # unlocked
                                                $this->SetValue('SmartLock', 1);
                                                break;

                                            case 6: # locked
                                                $this->SetValue('SmartLock', 2);
                                                break;

                                            case 7: # locked
                                                $this->SetValue('SmartLock', 3);
                                                break;
                                        }
                                    }
                                    //Charging
                                    if (array_key_exists('isCharging', $lockProperties)) {
                                        $this->SetValue('BatteryCharging', $lockProperties['isCharging']);
                                    }
                                    //Battery level
                                    if (array_key_exists('batteryLevel', $lockProperties)) {
                                        $this->SetValue('BatteryLevel', $lockProperties['batteryLevel']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function UpdateActivityLog(): void
    {
        $deviceID = $this->ReadPropertyInteger('DeviceID');
        if (!$this->HasActiveParent() || $deviceID == 0) {
            return;
        }
        if (!$this->ReadPropertyBoolean('UseActivityLog')) {
            return;
        }
        $data = [];
        $buffer = [];
        $data['DataID'] = self::TEDEE_SPLITTER_DATA_GUID;
        $buffer['Command'] = 'GetDeviceActivity';
        $buffer['Params'] = ['id' => $deviceID, 'elements' => $this->ReadPropertyInteger('ActivityLogMaximumEntries')];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $response = $this->SendDataToParent($data);
        if (is_string($response) && is_array(json_decode($response, true)) && (json_last_error() == JSON_ERROR_NONE)) {
            $responseData = json_decode($response, true);
            if (array_key_exists('httpCode', $responseData)) {
                $httpCode = $responseData['httpCode'];
                if ($httpCode != 200) {
                    $this->SendDebug(__FUNCTION__, 'Abort, response http code: ' . $httpCode . ', must be 200!', 0);
                    return;
                }
                if (array_key_exists('body', $responseData)) {
                    if (is_string($responseData['body']) && is_array(json_decode($responseData['body'], true)) && (json_last_error() == JSON_ERROR_NONE)) {
                        $this->SendDebug(__FUNCTION__, 'Actual data: ' . $responseData['body'], 0);
                        $actualData = json_decode($responseData['body'], true);
                        if (!empty($actualData)) {
                            //Result
                            if (array_key_exists('result', $actualData)) {
                                $elements = $actualData['result'];
                                if (!empty($elements)) {
                                    //Header
                                    $string = "<table style='width: 100%; border-collapse: collapse;'>";
                                    $string .= '<tr> <td><b> ' . $this->Translate('Date') . '</b></td> <td><b>' . $this->Translate('Action') . '</b></td> <td><b>Name</b></td> </tr>';
                                    foreach ($elements as $element) {
                                        if (array_key_exists('deviceId', $element)) {
                                            if ($element['deviceId'] != $deviceID) {
                                                continue;
                                            }
                                        }
                                        //Date
                                        if (array_key_exists('date', $element)) {
                                            $date = $element['date'];
                                            $date = new DateTime($date, new DateTimeZone('UTC'));
                                            $date->setTimezone(new DateTimeZone(date_default_timezone_get()));
                                            $date = $date->format('d.m.Y H:i:s');
                                        }
                                        //Event
                                        if (array_key_exists('event', $element)) {
                                            $action = $element['event'];
                                            /*
                                                32	LockedRemote	            locked via mobile app
                                                33	UnlockedRemote	            unlocked via mobile app
                                                34	LockedButton	            locked by pressing the button on lock device
                                                35	UnlockedButton	            unlocked by pressing the button on lock device
                                                36	LockedAuto	                successfully performed auto lock feature
                                                37	UnlockedAuto	            successfully performed auto unlock feature
                                                38	LockedManual	            lock was rotated manually into locked position
                                                39	UnlockedManual	            lock was rotated manually into unlocked position
                                                40	Jammed	                    lock got stuck during locking/unlocking/pulling action
                                                41	PowerOff	                lock registered long button push and it will power off
                                                42	PowerOn	                    lock registered power up
                                                43	Calibration	                user successfully calibrate or recalibrate the lock
                                                44	Dismounted	                not used
                                                45	Alarm	                    not used
                                                46	BatteryCharging	            device detect start charging process
                                                47	PartiallyOpenManual	        user rotated the lock from locked or unlocked position into semi-locked position
                                                48	PartiallyOpenButton	        operation of locking or unlocking performed from button failed and lock stopped in semi-locked position
                                                49	PartiallyOpenAuto	        operation of auto locking failed and lock stopped in semi-locked position
                                                50	BatteryStopCharging	        device detect stop charging process
                                                51	PulledRemote	            spring was pulled via mobile app
                                                52	PulledAuto	                spring was pulled automatically after receiving auto unlock command from mobile app
                                                53	PulledManual	            lock was rotated manually to perform pull spring action
                                                54	PartiallyOpenRemote	        mobile app sent open or close request and received lock status changed to partially open
                                                55	PulledAutoBy	            mobile app sent auto unlock request when lock was already in unlocked position and only pull spring was performed
                                                56	PostponedLock	            locked by pressing and holding the button on lock device
                                                57	UnlockedHomeKit	            unlocked via HomeKit app
                                                58	PartiallyOpenHomeKit	    HomeKit app sent open or close request and received lock status changed to partially open
                                                59	LockedHomeKit	            locked via HomeKit app
                                                60	PulledHomeKit	            spring was pulled via HomeKit app
                                                61	UnlockByPin	                unlocked from keypad by pin
                                                62	IncorrectPin	            incorrect pin typen on keypad
                                                63	PullSpringByPin	            keypad sent unlock request when pull spring is enabled and lock was open and only pull spring was performed
                                                64	PartiallyOpenByPin	        keypad sent unlock request and received lock status changed to partially open
                                                65	LockedByKeypadWithPin	    locked from keypad by pin
                                                66	LockedByKeypadWithoutPin    locked from keypad by button (without pin)
                                                224	FirmwareUpdateByBridge	    device was updated by bridge
                                                225	FirmwareUpdateByMobile	    device was updated by mobile app
                                             */
                                            switch ($action) {
                                                case 32:
                                                    $action = $this->Translate('locked via mobile app');
                                                    break;

                                                case 33:
                                                    $action = $this->Translate('unlocked via mobile app');
                                                    break;

                                                case 34:
                                                    $action = $this->Translate('locked by pressing the button on lock device');
                                                    break;

                                                case 35:
                                                    $action = $this->Translate('unlocked by pressing the button on lock device');
                                                    break;

                                                case 36:
                                                    $action = $this->Translate(' successfully performed auto lock feature');
                                                    break;

                                                case 37:
                                                    $action = $this->Translate('successfully performed auto unlock feature');
                                                    break;

                                                case 38:
                                                    $action = $this->Translate('lock was rotated manually into locked position');
                                                    break;

                                                case 39:
                                                    $action = $this->Translate('lock was rotated manually into unlocked position');
                                                    break;

                                                case 40:
                                                    $action = $this->Translate('lock got stuck during locking/unlocking/pulling action');
                                                    break;

                                                case 41:
                                                    $action = $this->Translate('lock registered long button push and it will power off');
                                                    break;

                                                case 42:
                                                    $action = $this->Translate('lock registered power up');
                                                    break;

                                                case 43:
                                                    $action = $this->Translate('user successfully calibrate or recalibrate the lock');
                                                    break;

                                                case 44:
                                                    $action = $this->Translate('Status Code 44, not used');
                                                    break;

                                                case 45:
                                                    $action = $this->Translate('Status Code 55, not used');
                                                    break;

                                                case 46:
                                                    $action = $this->Translate('device detect start charging process');
                                                    break;

                                                case 47:
                                                    $action = $this->Translate('user rotated the lock from locked or unlocked position into semi-locked position');
                                                    break;

                                                case 48:
                                                    $action = $this->Translate(' operation of locking or unlocking performed from button failed and lock stopped in semi-locked position');
                                                    break;

                                                case 49:
                                                    $action = $this->Translate('operation of auto locking failed and lock stopped in semi-locked position');
                                                    break;

                                                case 50:
                                                    $action = $this->Translate('device detect stop charging process');
                                                    break;

                                                case 51:
                                                    $action = $this->Translate('spring was pulled via mobile app');
                                                    break;

                                                case 52:
                                                    $action = $this->Translate('spring was pulled automatically after receiving auto unlock command from mobile app');
                                                    break;

                                                case 53:
                                                    $action = $this->Translate('lock was rotated manually to perform pull spring action');
                                                    break;

                                                case 54:
                                                    $action = $this->Translate('mobile app sent open or close request and received lock status changed to partially open');
                                                    break;

                                                case 55:
                                                    $action = $this->Translate('mobile app sent auto unlock request when lock was already in unlocked position and only pull spring was performed');
                                                    break;

                                                case 56:
                                                    $action = $this->Translate('locked by pressing and holding the button on lock device');
                                                    break;

                                                case 57:
                                                    $action = $this->Translate('unlocked via HomeKit app');
                                                    break;

                                                case 58:
                                                    $action = $this->Translate('HomeKit app sent open or close request and received lock status changed to partially open');
                                                    break;

                                                case 59:
                                                    $action = $this->Translate('locked via HomeKit app');
                                                    break;

                                                case 60:
                                                    $action = $this->Translate('spring was pulled via HomeKit app');
                                                    break;

                                                case 61:
                                                    $action = $this->Translate('unlocked from keypad by pin');
                                                    break;

                                                case 62:
                                                    $action = $this->Translate('incorrect pin typen on keypad');
                                                    break;

                                                case 63:
                                                    $action = $this->Translate('keypad sent unlock request when pull spring is enabled and lock was open and only pull spring was performed');
                                                    break;

                                                case 64:
                                                    $action = $this->Translate('keypad sent unlock request and received lock status changed to partially open');
                                                    break;

                                                case 65:
                                                    $action = $this->Translate('locked from keypad by pin');
                                                    break;

                                                case 66:
                                                    $action = $this->Translate('locked from keypad by button (without pin)');
                                                    break;

                                                case 224:
                                                    $action = $this->Translate('device was updated by bridge');
                                                    break;

                                                case 225:
                                                    $action = $this->Translate('device was updated by mobile app');
                                                    break;

                                                default:
                                                    $action = 'Event: ' . $action;
                                            }
                                        }
                                        //Name
                                        if (array_key_exists('username', $element)) {
                                            $name = $element['username'];
                                            if (empty($name)) {
                                                $name = $this->Translate('Unknown');
                                            }
                                        }
                                        if (isset($date) && isset($action) && isset($name)) {
                                            $string .= '<tr><td>' . $date . '</td><td>' . $action . '</td><td>' . $name . '</td></tr>';
                                        }
                                    }
                                    $string .= '</table>';
                                    $this->SetValue('ActivityLog', $string);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function SetDailyLockTimer(): void
    {
        $now = time();
        $lockTime = json_decode($this->ReadPropertyString('DailyLockTime'));
        $hour = $lockTime->hour;
        $minute = $lockTime->minute;
        $second = $lockTime->second;
        $definedTime = $hour . ':' . $minute . ':' . $second;
        if (time() >= strtotime($definedTime)) {
            $timestamp = mktime($hour, $minute, $second, (int) date('n'), (int) date('j') + 1, (int) date('Y'));
        } else {
            $timestamp = mktime($hour, $minute, $second, (int) date('n'), (int) date('j'), (int) date('Y'));
        }
        $interval = ($timestamp - $now) * 1000;
        if (!$this->ReadPropertyBoolean('UseDailyLock')) {
            $interval = 0;
        }
        $this->SetTimerInterval('DailyLock', $interval);
    }

    private function SetDailyUnlockTimer(): void
    {
        $now = time();
        $unlockTime = json_decode($this->ReadPropertyString('DailyUnlockTime'));
        $hour = $unlockTime->hour;
        $minute = $unlockTime->minute;
        $second = $unlockTime->second;
        $definedTime = $hour . ':' . $minute . ':' . $second;
        if (time() >= strtotime($definedTime)) {
            $timestamp = mktime($hour, $minute, $second, (int) date('n'), (int) date('j') + 1, (int) date('Y'));
        } else {
            $timestamp = mktime($hour, $minute, $second, (int) date('n'), (int) date('j'), (int) date('Y'));
        }
        $interval = ($timestamp - $now) * 1000;
        if (!$this->ReadPropertyBoolean('UseDailyUnlock')) {
            $interval = 0;
        }
        $this->SetTimerInterval('DailyUnlock', $interval);
    }
}