<?php

/** @noinspection DuplicatedCode */

declare(strict_types=1);

class TedeeConfiguratorWebAPI extends IPSModule
{
    //Constants
    private const TEDEE_LIBRARY_GUID = '{108EA9EC-FA0F-BCBB-9644-1D19E770CE3B}';
    private const TEDEE_SPLITTER_GUID = '{5D366804-5DF3-FE00-F4C3-F00CB093848E}';
    private const TEDEE_SPLITTER_DATA_GUID = '{BE7F44AC-CE78-7863-C18B-88CD4D2E4B30}';
    private const TEDEE_SMARTLOCK_GUID = '{EBD2F6F5-BCB6-4E11-383A-5529BBFE4EC5}';

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        //Properties
        $this->RegisterPropertyInteger('CategoryID', 0);

        ##### Splitter

        //Connect to parent (Tedee Splitter)
        $this->ConnectParent(self::TEDEE_SPLITTER_GUID);
    }

    public function Destroy()
    {
        //Never delete this line!
        parent::Destroy();
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
    }

    public function GetConfigurationForm()
    {
        $formData = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $library = IPS_GetLibrary(self::TEDEE_LIBRARY_GUID);
        $formData['elements'][2]['caption'] = 'ID: ' . $this->InstanceID . ', Version: ' . $library['Version'] . '-' . $library['Build'] . ', ' . date('d.m.Y', $library['Date']);
        //Get all device instances first
        $connectedInstanceIDs = [];
        foreach (IPS_GetInstanceListByModuleID(self::TEDEE_SMARTLOCK_GUID) as $instanceID) {
            if (IPS_GetInstance($instanceID)['ConnectionID'] === IPS_GetInstance($this->InstanceID)['ConnectionID']) {
                //Add the instance ID to a list for the given Lock ID. Even though Lock ID should be unique, users could break things by manually editing the settings
                $connectedInstanceIDs[IPS_GetProperty($instanceID, 'DeviceID')][] = $instanceID;
            }
        }
        $location = $this->GetCategoryPath($this->ReadPropertyInteger(('CategoryID')));
        $values = [];
        $devices = json_decode($this->GetDevicesWithDetails(), true);
        if (is_array($devices)) {
            foreach ($devices as $device) {
                if (array_key_exists('id', $device)) {
                    $deviceID = $device['id'];
                    $deviceName = $device['name'];
                    $deviceSerialNumber = $device['serialNumber'];
                    $deviceType = $device['type'] . ' = Lock';
                    $value = [
                        'DeviceID'           => $deviceID,
                        'DeviceSerialNumber' => $deviceSerialNumber,
                        'DeviceType'         => $deviceType,
                        'create'             => [
                            'moduleID'      => self::TEDEE_SMARTLOCK_GUID,
                            'name'          => $deviceName . ' (Web API)',
                            'configuration' => [
                                'DeviceID'           => (integer) $deviceID,
                                'DeviceSerialNumber' => (string) $deviceSerialNumber,
                                'DeviceName'         => (string) $deviceName
                            ],
                            'location' => $location
                        ]
                    ];
                    if (isset($connectedInstanceIDs[$deviceID])) {
                        $value['name'] = IPS_GetName($connectedInstanceIDs[$deviceID][0]);
                        $value['instanceID'] = $connectedInstanceIDs[$deviceID][0];
                    } else {
                        $value['name'] = $device['name'];
                        $value['instanceID'] = 0;
                    }
                    $values[] = $value;
                }
            }
        }
        foreach ($connectedInstanceIDs as $deviceID => $instanceIDs) {
            foreach ($instanceIDs as $index => $instanceID) {
                //The first entry for each device id was already added as valid value
                $existing = false;
                foreach ($devices as $device) {
                    if ($device['id'] == $deviceID) {
                        $existing = true;
                    }
                }
                if ($index === 0 && $existing) {
                    continue;
                }
                //However, if a device id is not found or has multiple instances, they are erroneous
                $values[] = [
                    'DeviceID'           => $deviceID,
                    'name'               => IPS_GetName($instanceID),
                    'DeviceSerialNumber' => IPS_GetProperty($instanceID, 'DeviceSerialNumber'),
                    'DeviceType'         => '2 = Lock',
                    'instanceID'         => $instanceID,
                ];
            }
        }
        $formData['actions'][0]['values'] = $values;
        return json_encode($formData);
    }

    #################### Private

    private function GetCategoryPath(int $CategoryID): array
    {
        if ($CategoryID === 0) {
            return [];
        }
        $path[] = IPS_GetName($CategoryID);
        $parentID = IPS_GetObject($CategoryID)['ParentID'];
        while ($parentID > 0) {
            $path[] = IPS_GetName($parentID);
            $parentID = IPS_GetObject($parentID)['ParentID'];
        }
        return array_reverse($path);
    }

    private function GetDevicesWithDetails(): string
    {
        $devices = [];
        if (!$this->HasActiveParent()) {
            return json_encode($devices);
        }
        $data = [];
        $buffer = [];
        $data['DataID'] = self::TEDEE_SPLITTER_DATA_GUID;
        $buffer['Command'] = 'GetDevicesWithDetails';
        $buffer['Params'] = '';
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $response = $this->SendDataToParent($data);
        if (is_string($response) && is_array(json_decode($response, true)) && (json_last_error() == JSON_ERROR_NONE)) {
            $responseData = json_decode($response, true);
            if (array_key_exists('httpCode', $responseData)) {
                if ($responseData['httpCode'] != 200) {
                    return json_encode($devices);
                }
            }
            if (array_key_exists('body', $responseData)) {
                if (is_string($responseData['body']) && is_array(json_decode($responseData['body'], true)) && (json_last_error() == JSON_ERROR_NONE)) {
                    $this->SendDebug(__FUNCTION__, 'Actual data: ' . $responseData['body'], 0);
                    $actualData = json_decode($responseData['body'], true);
                    if (array_key_exists('result', $actualData)) {
                        $result = $actualData['result'];
                        if (is_array($result)) {
                            if (array_key_exists('locks', $result)) {
                                $locks = $result['locks'];
                                if (is_array($locks)) {
                                    foreach ($locks as $lock) {
                                        if (array_key_exists('id', $lock)) {
                                            //Device type: 0 = Nofo, 1 = Bridge, 2 = Lock
                                            $devices[] = [
                                                'id'           => $lock['id'],
                                                'serialNumber' => $lock['serialNumber'],
                                                'name'         => $lock['name'],
                                                'type'         => 2];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return json_encode($devices);
    }
}