<?php

/*************************************************************
 *
 * Hosting Module Class - Advanced Example
 *
 * You can read more about the hosting modules development at:
 * http://dev.hostbillapp.com/dev-kit/provisioning-modules/
 *
 * This module is based on the extended functionality
 *  of the Hosting Modules in HostBill. If you want to read more
 *  about the basic functionality
 *
 ************************************************************/

 class advancedexample extends HostingModule {

     protected $description = 'Your Control Panel provisioning module';
     protected $options = array(
                'option1' =>array (
			'name'=> 'Plan Name',
			'value' => false,
			'type'=> 'loadable',  // This field type tells hostbill to exectute method mentioned in 'default' key ->getPlans
			'default'=>"getPlans" // HostBill will call this method to load select box, it should return list of plans
		),
		'option2' =>array (
			'name'=> 'Is Reseller Account',
			'value' => '0',
			'type'=> 'select',
			'default'=>array('Yes', 'No')
		),
		'option3' =>array (
			'name'=> 'SSH Access',
			'value' => '0',
			'type'=> 'check',
			'default'=>false
		)
    );

   

     /**
      * You can choose which fields to display in Settings->Apps section
      * by defining this variable
      * @var array
      */
     protected $serverFields = array( // 
        'hostname' => false,
        'ip' => true,
        'maxaccounts' => false,
        'status_url' => false,
        'field1' => true,
        'field2' => true,
        'username' => true,
        'password' => true,
        'hash' => false,
        'ssl' => true,
        'nameservers' => false,
    );

     /**
      * HostBill will replace default labels for server fields
      * with this variable configured
      * @var array
      */
    protected $serverFieldsDescription = array( 
        'username' => 'User ID',
        'password' => 'Password',
        'field1'=>'Port',
        'field2'=>'Alternative Port'
    );

     protected $details = array(
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
                    'type'=> 'hidden',        // this field is required, so we have set the type to 'hidden' and its not visible.
                    'default'=>false
            ),
            'option4' =>array (               // NEW FIELD declared here
                    'name'=> 'Client ID',     // this name will be displayed as a label of this field
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
    public function connect($connect) { 
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
    public function Create() {
        $action = 'createAccount';
        $post = '&username='.$this->details['option1']['value'];
        $post .= '&password='.$this->details['option2']['value'];
        $post .= '&domain='.$this->details['option3']['value'];
        $post .= '&plan='.$this->options['option1']['value'];
        $post .= '&reseller='.($this->options['option2']['value'] == 'Yes' ? '1' : '0');
        $post .= '&ssh='.$this->options['option3']['value'];
        $out = $this->Send($action, $post);
        if(isset($out['password'], $out['clientid'])) {
            $this->details['option2']['value'] = $out['password'];   // Password saved
            $this->details['option4']['value'] = $out['clientid'];   // Client ID saved
            $this->addInfo('Account has been created.');
            return true;
        } else
            return false;
    }


    /**
     * This method is invoked automatically when suspending an account.
     * @return boolean true if suspend succeeds
     */
    public function Suspend() {
        return true;
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
        return true;
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
     * This method is invoked when account should have password changed
     * @param string $newpassword New password to set account with
     * @return boolean true if action succeeded
     */
    public function ChangePassword($newpassword)
    {
        $action = 'changePassword';
        $post = '&username='.$this->details['option1']['value'];
        $post .= '&newpassword='.$newpassword;                  // we are providing new password for the account

        if($this->Send($action, $post)) {
            return true;
        } else
            return false;
    }

    /**
     * This method is invoked when account should be upgraded/downgraded
     * $options variable is loaded with new package configuration
     * @return boolean true if action succeeded
     */
    public function ChangePackage()  
    {
        $action = "changePackage";
        $post = '&username='.$this->details['option1']['value'];
        $post .= '&plan='.$this->options['option1']['value'];
        $post .= '&reseller='.($this->options['option2']['value'] == 'Yes' ? '1' : '0');
        $post .= '&ssh='.$this->options['option3']['value'];
        if($this->Send($action, $post)) {
            return true;
        } else
            return false;
    }


     /**
     * Auxilary method that HostBill will load to get plans from server:
     * @see $options variable above
     * @return array - list of plans to display in product configuration
     */
    public function getPlans() {
        $action = 'getPlans';
        $post = false;

        $out = $this->Send($action, $post);
        if(isset($out['plans'])) {
            $return = array();
            foreach($out['plans'] as $plan) {
                $return[] = array(           // we are returning "Array of Two-Element" arrays here
                    $plan['id'],
                    $plan['name']
                );
            }
            return $return;
        } else {
            $this->addError('Unable to get plans from the server.');
            return false;
        }
    }

    /**
     * This method is OPTIONAL. in this example it is used to connect to the server and manage all the modules action with the API.
     * 
     * Its public, because we can call it from addon class
     *  @ignore
     */
    public function Send($action, $post) {

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