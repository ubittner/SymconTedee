<?php

declare(strict_types=1);

trait TedeeWebAPI
{
    /**
     * Shows all devices without details.
     * @return string
     */
    public function GetDevices(): string
    {
        $endpoint = 'https://api.tedee.com/api/' . self::API_VERSION . '/my/device';
        $response = $this->SendDataToTedee($endpoint, 'GET', '');
        $this->SendDebug(__FUNCTION__, 'Response:' . $response, 0);
        return $response;
    }

    /**
     * Shows all devices with details.
     * @return string
     */
    public function GetDevicesWithDetails(): string
    {
        $endpoint = 'https://api.tedee.com/api/' . self::API_VERSION . '/my/device/details';
        $response = $this->SendDataToTedee($endpoint, 'GET', '');
        $this->SendDebug(__FUNCTION__, 'Response:' . $response, 0);
        return $response;
    }

    /**
     * Sends lock door request for specific Lock
     * @param int $DeviceID
     * @return string
     */
    public function LockDoor(int $DeviceID): string
    {
        $endpoint = 'https://api.tedee.com/api/' . self::API_VERSION . '/my/lock/' . $DeviceID . '/operation/lock';
        $response = $this->SendDataToTedee($endpoint, 'POST', '');
        $this->SendDebug(__FUNCTION__, 'Response:' . $response, 0);
        return $response;
    }

    /**
     * Sends unlock door request for specific Lock
     * @param int $DeviceID
     * @return string
     */
    public function UnlockDoor(int $DeviceID): string
    {
        $endpoint = 'https://api.tedee.com/api/' . self::API_VERSION . '/my/lock/' . $DeviceID . '/operation/unlock';
        $response = $this->SendDataToTedee($endpoint, 'POST', '');
        $this->SendDebug(__FUNCTION__, 'Response:' . $response, 0);
        return $response;
    }

    /**
     * Sends pull spring of door request for specific Lock
     * @param int $DeviceID
     * @return string
     */
    public function PullDoor(int $DeviceID): string
    {
        $endpoint = 'https://api.tedee.com/api/' . self::API_VERSION . '/my/lock/' . $DeviceID . '/operation/pull';
        $response = $this->SendDataToTedee($endpoint, 'POST', '');
        $this->SendDebug(__FUNCTION__, 'Response:' . $response, 0);
        return $response;
    }

    /**
     * Shows status of single Lock device
     * @param int $DeviceID
     * @return string
     */
    public function GetLockStatus(int $DeviceID): string
    {
        $endpoint = 'https://api.tedee.com/api/' . self::API_VERSION . '/my/lock/' . $DeviceID . '/sync';
        $response = $this->SendDataToTedee($endpoint, 'GET', '');
        $this->SendDebug(__FUNCTION__, 'Response: ' . $response, 0);
        return $response;
    }

    /**
     * Shows all device activities ordered by date paged
     * @param int $DeviceID
     * @param int $Elements
     * @return string
     */
    public function GetDeviceActivity(int $DeviceID, int $Elements): string
    {
        $endpoint = 'https://api.tedee.com/api/' . self::API_VERSION . '/my/deviceactivity?DeviceId=' . $DeviceID . '&Elements=' . $Elements;
        $response = $this->SendDataToTedee($endpoint, 'GET', '');
        $this->SendDebug(__FUNCTION__, 'Response: ' . $response, 0);
        return $response;
    }

    public function SendDataToTedee(string $Endpoint, string $CustomRequest, string $Postfields): string
    {
        $this->SendDebug(__FUNCTION__, 'Endpoint: ' . $Endpoint, 0);
        $this->SendDebug(__FUNCTION__, 'CustomRequest: ' . $CustomRequest, 0);
        $this->SendDebug(__FUNCTION__, 'Postfields: ' . $Postfields, 0);
        $active = $this->ReadPropertyBoolean('Active');
        if (!$active) {
            $this->SendDebug(__FUNCTION__, 'Abort, instance is inactive!', 0);
            return json_encode(['httpCode' => 0, 'body' => '']);
        }
        $personalAccessKey = $this->ReadPropertyString('PersonalAccessKey');
        if (empty($personalAccessKey)) {
            return json_encode(['httpCode' => 401, 'body' => '']);
        }
        $body = '';
        $timeout = round($this->ReadPropertyInteger('Timeout') / 1000);
        //Enter semaphore
        if (!IPS_SemaphoreEnter('Tedee_' . $this->InstanceID . '_SendDataToTedee', 5000)) {
            $this->SendDebug(__FUNCTION__, 'Abort, Semaphore reached!', 0);
            return json_encode(['httpCode' => 503, 'body' => '']);
        }
        //Send data to endpoint
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST   => $CustomRequest,
            CURLOPT_URL             => $Endpoint,
            CURLOPT_HEADER          => true,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FAILONERROR     => true,
            CURLOPT_CONNECTTIMEOUT  => $timeout,
            CURLOPT_TIMEOUT         => 60,
            CURLOPT_POSTFIELDS      => $Postfields,
            CURLOPT_HTTPHEADER      => [
                'Authorization: PersonalKey ' . $personalAccessKey,
                'Accept: application/json']]);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $this->SendDebug(__FUNCTION__, 'Response http code: ' . $httpCode, 0);
        if (!curl_errno($curl)) {
            switch ($httpCode) {
                case 200:
                case 201:
                case 202:
                    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
                    $header = substr($response, 0, $header_size);
                    $body = substr($response, $header_size);
                    $this->SendDebug(__FUNCTION__, 'Response header: ' . $header, 0);
                    $this->SendDebug(__FUNCTION__, 'Response body: ' . $body, 0);
                    break;

            }
        } else {
            $error_msg = curl_error($curl);
            $this->SendDebug(__FUNCTION__, 'An error has occurred: ' . json_encode($error_msg), 0);
        }
        curl_close($curl);
        //Leave semaphore
        IPS_SemaphoreLeave('Tedee_' . $this->InstanceID . '_SendDataToTedee');
        $result = ['httpCode' => $httpCode, 'body' => $body];
        $this->SendDebug(__FUNCTION__, 'Result: ' . json_encode($result), 0);
        return json_encode($result);
    }
}