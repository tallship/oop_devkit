<?php

/**
 * This is sample dynamic Addon class.
 * 
 * Dynamic addons should be located in related hosting module directory, and their class/filename
 * should begin with related hosting module name.
 * i.e.: Additional ip addon for cpanel to be found in : /includes/modules/Hosting/cpanel/class.cpanel_add_ip.php
 *
 * @see http://dev.hostbillapp.com/dev-kit/provisioning-modules/provisioning-addon-modules/
 * @author HostBill
 */
class AdvancedExample_Addon extends ModuleAddon {
    
    /**
     * Provide addon description
     * @var string
     */
    protected $description = 'Automatically adds IP address to account';
    
    /**
     * Provide addon name
     * @var string
     */
    protected $name = 'Example:Add Extra IP';
    
    /**
     * As like with your Hosting module, addon module can provide configuration options.
     * For $options array format description visit  http://dev.hostbillapp.com/dev-kit/provisioning-modules/
     * @var array
     */
     protected $options = array(
         'Count' => array(  //how many IPs should we add to account after addon creation ?
            'default' => '1',
            'type' => 'input',
            'default' => '1'
     ));

    
    /**
     * Here you can provide list of functions this module supports. 
     * Ie. if you wish only to use create, remove all except it
     * @var array
     */
    protected $adminFunctions = array('Create', 'Terminate','Suspend','Unsuspend');
    
    /**
     * Under $account_module variable HostBill will load instance of your hosting module class.
     * So you will be able to access its public method directly from this addon class
     * 
     * @var AdvancedExample
     */
    protected $account_module = null;
    
    /**
     * Before calling any method from addon module, HostBill will call connect first.
     * 
     * @param array $connect Connection details array, contains same keys as one passed
     * in Yourmodule::connect 
     */
    public function connect($connect) {
         //in this example we will just make sure related hosting module is connected.
            $this->account_module->connect($connect);
    }
    
    /**
     * Addon module can have its own create method, called everytime admin 
     * (or system/automation)choose to create addon.
     * Usually called right after related account creation
     * @return boolean
     */
    public function Create() {
        $how_many_ips = $this->options['Count']['value']; //get related addon configuration value
        
        if($this->account_module->Send('AddIp', array('ip_count'=>$how_many_ips))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Addon module can have its own terminate method, called everytime admin 
     * (or system/automation)choose to terminate addon
     * @return boolean
     */
    public function Terminate() {
        return true;
    }

    /**
     * Addon module can have its own suspend method, called everytime admin 
     * (or system/automation)choose to suspend addon
     * @return boolean
     */
    public function Suspend() {
        return true;
    }
    
    /**
     * Addon module can have its own unsuspend method, called everytime admin 
     * (or system/automation)choose to unsuspend addon
     * @return boolean
     */
    public function Unsuspend() {
        return true;
    }
}
