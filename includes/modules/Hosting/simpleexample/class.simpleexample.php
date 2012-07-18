<?php
/*************************************************************
 *
 * Provisioning Module Class - Simple Example
 *
 * You can read more about the hosting modules development at:
 * http://dev.hostbillapp.com/dev-kit/provisioning-modules/
 *
 * This simple module is a basic implementation.
 * If you want to get more extended functionality
 * please download the Example 2 file from the article above
 *
 ************************************************************/

 class simpleexample extends HostingModule {  // class name MUST be the same like the filename. In this example class.simpleexample.php

     protected $description = 'Your Control Panel provisioning module'; // this is simple info for the other users

     protected $options = array(         // options for the product configuration from Settings => Products & Services => Product => Connect with Module
                'option1' =>array (
			'name'=> 'Plan Name',
			'value' => false,
			'type'=> 'input', // allowed types are 'input', 'check' and 'select'
			'default'=>""
		),
		'option2' =>array (
			'name'=> 'Is Reseller Account',
			'value' => '0',
			'type'=> 'select',
			'default'=>array('Yes', 'No') // for 'select' type, we need to declare array with the values
		),
		'option3' =>array (
			'name'=> 'SSH Access',
			'value' => '0',
			'type'=> 'check',
			'default'=>false
		)
    );



     protected $details = array( // $details are a specific data for each account. 
            'option1' =>array (
                    'name'=> 'username',
                    'value' => false,
                    'type'=> 'input',
                    'default'=>false
            ),
            'option2' =>array (
                    'name'=> 'password',
                    'value' => false,
                    'type'=> 'input',
                    'default'=>false
            ),
            'option3' =>array (
                    'name'=> 'domain',
                    'value' => false,
                    'type'=> 'input',
                    'default'=>false
            )
    );

 

    private $server_username;
    private $server_password;
    private $server_hostname;
    private $server_ip;

    /**
     * HostBill will call this method before calling any other function from your module
     * It will pass remote  app details that module should connect with
     *
     * @param array $connect Server details configured in Settings->Apps
     */
    public function connect($connect) { // this is the method to load the Server Info configured at Apps Section.

        /* In the $connect variable you will find following details:
         * array(
         *  'username',
         *  'password',
         *  'hostname',
         *  'ip',
         *  'ssl', // 1 or 0
         * );
         */

        $this->server_username = $connect['username'];
        $this->server_password = $connect['password'];
        $this->server_hostname = $connect['hostname'];
        $this->server_ip = $connect['ip'];
    }


    /**
     * HostBill will call this method when admin clicks on "test Connection" in settings->apps
     * It should test connection to remote app using details provided in connect method
     *
     * Use $this->addError('message'); to provide errors details (if any)
     *
     * @see connect
     * @return boolean true if connection suceeds
     */
    public function testConnection() {
        $action = 'adminLogin';
        $post = false;
        if($this->Send($action, $post)) {
            return true;
        } else
            return false;
    }



    /**
     * This method is invoked automatically when creating an account.
     * @return boolean true if creation succeeds
     */
    public function Create() {  // 
        $action = 'createAccount';
        $post = '&username='.$this->details['option1']['value'];
        $post .= '&password='.$this->details['option2']['value'];
        $post .= '&domain='.$this->details['option3']['value'];
        $post .= '&plan='.$this->options['option1']['value'];
        $post .= '&reseller='.($this->options['option2']['value'] == 'Yes' ? '1' : '0');
        $post .= '&ssh='.$this->options['option3']['value'];
        if($this->Send($action, $post)) {
            $this->addInfo('Account has been created.');  // addInfo() method to display information  in the HostBill
            return true;
        } else
            return false;
    }

    /**
     * This method is invoked automatically when suspending an account.
     * @return boolean true if suspend succeeds
     */
    public function Suspend() {
        $action = 'suspendAccount';
        $post = '&username='.$this->details['option1']['value'];
        if($this->Send($action, $post)) {
            $this->addInfo('Account has been suspended.');
            return true;
        } else
            return false;
    }


    /**
     * This method is invoked automatically when unsuspending an account.
     * @return boolean true if unsuspend succeeds
     */
    public function Unsuspend() {
        $action = 'unsuspendAccount';
        $post = '&username='.$this->details['option1']['value'];
        if($this->Send($action, $post)) {
            $this->addInfo('Account has been unsuspended.');
            return true;
        } else
            return false;
    }

     /**
     * This method is invoked automatically when terminating an account.
     * @return boolean true if termination succeeds
     */
    public function Terminate() {
        $action = 'terminateAccount';
        $post = '&username='.$this->details['option1']['value'];
        if($this->Send($action, $post)) {
            $this->addInfo('Account has been removed.');
            return true;
        } else
            return false;
    }

    /**
     * This method is OPTIONAL. in this example it is used to connect to the server and manage all the modules action with the API.
     * @ignore
     * @param <type> $action
     * @param <type> $post
     * @return <type>
     */
    private function Send($action, $post) {

        $post = 'adminusername='.$this->server_username.'&adminpassword='.$this->server_password;
        $post .= $post;
        $curl = curl_init();                                // we are using cURL library here
        curl_setopt($curl, CURLOPT_URL, $this->server_ip);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 90);
        curl_setopt($curl, CURLOPT_POST, true);             // API uses POST method
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        $out = curl_exec($curl);
        if($out === false) {
            $this->addError(ucwords(curl_error($curl)));
        }
        curl_close($curl);
        $resp = json_decode($out);                          // API returns data encoded in JSON
        if($resp['success'] == true) {
           return true;
        } else {
           $this->addError('An error occured: '.$resp['error']); // addError() method is used to display error in the HostBill.
           return false;
        }
    }

 }