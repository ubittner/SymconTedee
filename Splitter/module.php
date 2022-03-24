<?php

/** @noinspection DuplicatedCode */

declare(strict_types=1);

include_once __DIR__ . '/helper/autoload.php';

class TedeeSplitterWebAPI extends IPSModule
{
    //Helper
    use TedeeWebAPI;

    //Constants
    private const TEDEE_LIBRARY_GUID = '{108EA9EC-FA0F-BCBB-9644-1D19E770CE3B}';
    private const MODULE_PREFIX = 'TEDEESW';
    private const API_VERSION = 'v1.25';

    public function Create()
    {
        ########## Properties
        $this->RegisterPropertyBoolean('Active', true);
        $this->RegisterPropertyString('PersonalAccessKey', '');
        $this->RegisterPropertyInteger('Timeout', 5000);

        //Never delete this line!
        parent::Create();
    }

    public function Destroy()
    {
        //Never delete this line!
        parent::Destroy();
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

        //Check configuartion
        if ($this->ValidateConfiguration()) {
            $this->SendDebug(__FUNCTION__, 'Configuration is valid', 0);
        }
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

    public function ForwardData($JSONString): string
    {
        $this->SendDebug(__FUNCTION__, $JSONString, 0);
        $data = json_decode($JSONString);
        switch ($data->Buffer->Command) {
            case 'GetDevices':
                $response = $this->GetDevices();
                break;

            case 'GetDevicesWithDetails':
                $response = $this->GetDevicesWithDetails();
                break;

            case 'LockDoor':
                $params = (array) $data->Buffer->Params;
                $response = $this->LockDoor($params['id']);
                break;

            case 'UnlockDoor':
                $params = (array) $data->Buffer->Params;
                $response = $this->UnlockDoor($params['id']);
                break;

            case 'PullDoor':
                $params = (array) $data->Buffer->Params;
                $response = $this->PullDoor($params['id']);
                break;

            case 'GetLockStatus':
                $params = (array) $data->Buffer->Params;
                $response = $this->GetLockStatus($params['id']);
                break;

            case 'GetDeviceActivity':
                $params = (array) $data->Buffer->Params;
                $response = $this->GetDeviceActivity($params['id'], $params['elements']);
                break;

            default:
                $this->SendDebug(__FUNCTION__, 'Invalid Command: ' . $data->Buffer->Command, 0);
                $response = '';
        }
        $this->SendDebug(__FUNCTION__, 'Response: ' . $response, 0);
        return $response;
    }

    #################### Private

    private function KernelReady(): void
    {
        $this->ApplyChanges();
    }

    private function ValidateConfiguration(): bool
    {
        $status = 102;
        $result = true;
        if ($this->ReadPropertyString('PersonalAccessKey') == '') {
            $this->SendDebug(__FUNCTION__, 'Personal Access Key (PAK) is missing!', 0);
            $status = 201;
            $result = false;
        }
        if (!$this->ReadPropertyBoolean('Active')) {
            $this->SendDebug(__FUNCTION__, 'Instance is inactive!', 0);
            $result = false;
            $status = 104;
        }
        $this->SetStatus($status);
        return $result;
    }
}