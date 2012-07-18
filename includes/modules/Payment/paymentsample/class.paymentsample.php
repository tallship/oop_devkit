<?php

/*
 * HostBill example gateway module
 * @see http://dev.hostbillapp.com/dev-kit/payment-gateway-modules/
 *
 * 2012 HostBill -  Complete Client Management, Support and Billing Software
 * http://hostbillapp.com
 */

class PaymentSample extends PaymentModule {

    /**
     * @var string Default module name to be displayed in adminarea
     */
    protected $modname = 'Sample Gateway';


    /**
     * @var string Default module name to be displayed in adminarea
     */
    protected $description = 'Sample payment gateway module for HostBill';


    /**
     * List of currencies supported by gateway - if module supports all currencies - leave empty
     * @var array
     */
    protected $supportedCurrencies = array('USD', 'CAD', 'EUR', 'GBP');

    
    /**
     * Configuration array - types allowed: check, input, select
     */
    protected $configuration = array(
        'Username' => array(
            'value' => '',
            'type' => 'input',
            'description' => 'Place your description here'
        ),
        'Password' => array(
            'value' => '',
            'type' => 'input',
            'description' => 'Place your description here'
        ),
        'Checkbox' => array(
            'value' => '0',
            'type' => 'check',
            'description' => 'You can also use checkboxes'
        )
    );

    /**
     * Return HTML code that should be displayed in clientarea for client to pay (ie. in invoice details)
     * @return string
     */
    public function drawForm() {
        $string = "<form action='http://url.to.your.gateway.com/process.do' method='POST'>";
            //draw submit button
        $string.="<input type='submit' value='Pay now!' />";

            //draw hidden fields with payment details
        $string.="<input type='hidden' name='invoice_id' value='{$this->invoice_id}'/>";
        $string.="<input type='hidden' name='amount' value='{$this->amount}'/>";
        $string.="<input type='hidden' name='currency_code' value='" . $this->getCurrency() . "'/>";

            //draw hidden field with return url
        $string.="<input type='hidden' name='callaback_url' value='{$this->callback_url}'/>";

            //draw hidden fields with client details
        $string.="<input type='hidden' name='firstname' value='{$this->client['firstname']}'/>";
        $string.="<input type='hidden' name='lastname' value='{$this->client['lastname']}'/>";
        $string.="<input type='hidden' name='country' value='{$this->client['country']}'/>";
        $string.="<input type='hidden' name='address' value='{$this->client['address1']}'/>";
        $string.="</form>";

        return $string;
    }

    //data coming in from payment gateway
    function callback() {
        $verified = true;
        //1. verify data
        
        if ($verified) {
            //2. log incoming payment
            $this->logActivity(array(
                'result' => 'Successfull',
                'output' => $_POST
            ));

            //3. add transaction to invoice
            $invoice_id = $_POST['invoiceid'];
            $amount = $_POST['amount'];
            $fee = $_POST['fee'];
            $transaction_id = $_POST['txn_id'];
            
            $this->addTransaction(array(
                'in' => $amount,
                'invoice_id' => $invoice_id,
                'fee' => $fee,
                'transaction_id' => $transaction_id
            ));
        } else {
             $this->logActivity(array(
                'result' => 'Failed',
                'output' => $_POST
            ));
        }
    }

}
