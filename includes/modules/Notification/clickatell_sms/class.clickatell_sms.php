<?php

/**
 * Clickatell notification module for HostBill
 *
 * required methods:
 * notifyClient
 * notifyAdmin
 *
 * everything else is optional
 *
 * HostBill 2012
 */
class Clickatell_SMS extends NotificationModule {

    protected $modname = 'Clickatell SMS Notifications';
    protected $description = 'Notify staff members and/or customers about events in HostBill trough Clickatell SMS gateway.
        <br> Phone number for staff member can be set in his profile';
    /**
     * Module configuration, visible in Settings->modules
     * @var array
     */
    protected $configuration = array(
        'Api ID' => array(
            'value' => '',
            'type' => 'input',
            'description' => 'Clickatell HTTP API ID'
        ), 'Username' => array(
            'value' => '',
            'type' => 'input',
            'description' => 'Clickatell Username'
        ), 'Password' => array(
            'value' => '',
            'type' => 'input',
            'description' => 'Clickatell Password'
        ),
        'Client Field' => array(
            'value' => 'mobilephone',
            'type' => 'input',
            'description' => 'Provide variable name from Clients->Registration fields responsible for holding client mobile phone number.
                If this field is empty no sms notifications will be sent to client trough Clickatell'
        )
    );

    /**
     * Install module.
     * We need to add custom admin field for keeping his mobile number
     * We also need add custom client field (it can be later removed / updated by admin)
     */
    public function install() {

        $admin_field = array(
            'name' => 'Mobile phone number',
            'code' => 'mobilephone',
            'type' => 'input'
        );
        $fieldsmanager = HBLoader::LoadModel('EditAdmins/AdminFields');
        $fieldsmanager->addField($admin_field);


        $client_field = array(
            'name' => 'Mobile phone number',
            'code' => 'mobilephone',
            'field_type' => 'input',
            'editable'=>true,
            'type'=>'All',
            'description' => 'To receive SMS notifications about your services with us please provide your mobile phone number, starting with country code prefix, ie. +1'
        );
        $clientfieldsmanager = HBLoader::LoadModel('Clients');
        $clientfieldsmanager->addCustomField($client_field);
    }

    /**
     * Send notification to admin.
     * HostBill will automatically execute this function if admin needs
     * to be notified and is allowed to be notified about something
     *
     * @param integer $admin_id Administrator ID to notify (see hb_admin_* tables)
     * @param string $subject Subject (for sms it may be omited)
     * @param string $message Message to send
     */
    public function notifyAdmin($admin_id, $subject, $message) {

        //1. get related admin details, and check if he have mobile phone added
        $editadmins = HBLoader::LoadModel('EditAdmins');
        $admin = $editadmins->getAdminDetails($admin_id);

        if (!$admin) { //admin not found
            return false;
        } elseif (!$admin['mobilephone']) { //admin mobile phone not found
            return false;
        }

        //send message
        return $this->_send($admin['mobilephone'], $message);
    }

    /**
     * Send notification to client
     * HostBill will automatically execute this function if client needs
     * to be notified and is allowed to be notified about something
     *
     *
     * @param integer $client_id Client ID to notify  (see hb_client_* tables)
     * @param string $subject Subject (for sms it may be omited)
     * @param string $message Message to send
     */
    public function notifyClient($client_id, $subject, $message) {

        $mobile_phone_field = $this->configuration['Client Field']['value'];

        if (!$mobile_phone_field) { //no client field configured->do not notify clients
            return false;
        }

        //. get client details and check for mobile phone field
        $clients = HBLoader::LoadModel('Clients');
        $client_details = $clients->getClient($client_id);

        if (!$client_details) {
            return false;
        } elseif (!$client_details[$mobile_phone_field]) {
            //no mobile phone num provided
            return false;
        }

        //send message
        return $this->_send($client_details[$mobile_phone_field], $message);
    }

    /**
     * Helper function to send actual SMS message to clickatell
     * @param string $number Phone number
     * @param string $message SMS message to send
     */
    private function _send($number, $message) {
        $ch = curl_init();

        $params = '';

        $number = str_replace(array('+', ' ', '-', '.'), "", $number);

        $params.="&to={$number}&text=" . urlencode($message);

        $url = 'http://api.clickatell.com/http/sendmsg';
        $fullparams = "api_id={$this->configuration['Api ID']['value']}&user={$this->configuration['Username']['value']}&password={$this->configuration['Password']['value']}";
        $fullparams .= $params;


        $chOptions = array(
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => $fullparams
        );
        curl_setopt_array($ch, $chOptions);
        $response = curl_exec($ch);
        curl_close($ch);

        if (substr($response, 0, 3) == 'ERR') {
            $this->addError(substr($response, 4));
            return false;
        }
        return true;
    }

}
