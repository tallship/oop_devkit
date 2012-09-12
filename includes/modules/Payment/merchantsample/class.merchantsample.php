<?php

/*
 * HostBill example cc processing module
 * @see http://dev.hostbillapp.com/dev-kit/payment-gateway-modules/merchant-gateways-modules/
 *
 * 2012 HostBill -  Complete Client Management, Support and Billing Software
 * http://hostbillapp.com
 */

class MerchantSample extends CreditCardModule {

    /**
     * Default module name to be displayed in adminarea
     */
    protected $modname = 'Sample Merchant Gateway Module';
    /**
     * Description to be displayed in admianrea
     */
    protected $description = 'Credit Card processing custom module';

    /**
     * List of currencies supported by gateway - if module supports all currencies - leave empty
     * @var array
     */
    protected $supportedCurrencies = array('USD', 'CAD', 'EUR', 'GBP');
   
    /**
     * Configuration array - types allowed: check, input, select
     */
    protected $configuration = array(
        'API Login' => array(
            'value' => '',
            'type' => 'input'
        ),
        'Transaction Key' => array(
            'value' => '',
            'type' => 'input'
        ),
        'MD5 Hash' => array(
            'value' => '',
            'type' => 'input'
        ),
        'Enable Test Mode' => array(
            'value' => '1',
            'type' => 'check'
        )
    );

    /**
     *  HostBill will call this method to attempt to charge/capture payment from credit card
     *
     * @param array $ccdetails An array with credit card details, contains following keys:
     * $ccdetails['cardnum'] - credit card number
     * $ccdetails['expdate'] - expiration date in format MMYY - i.e. 1112
     * $ccdetails['cardtype'] - CC type, ie. 'Visa'
     * If CVV is passed it will be available under:
     * $ccdetails['cvv']
     * @return boolean True if card was charged
     */
    public function capture($ccdetails) {


        $options['x_login'] = $this->configuration['API Login']['value'];
        $options['x_tran_key'] = $this->configuration['Transaction Key']['value'];


        /* CUSTOMER INFORMATION */
        $options['x_first_name'] = $this->client['firstname'];
        $options['x_last_name'] = $this->client['lastname'];
        $options['x_address'] = $this->client['address1'];
        $options['x_city'] = $this->client['city'];
        $options['x_state'] = $this->client['state'];
        $options['x_zip'] = $this->client['postcode'];
        $options['x_country'] = $this->client['country'];
        $options['x_phone'] = $this->client['phonenumber'];
        $options['x_email'] = $this->client['email'];
        $options['x_cust_id'] = $this->client['client_id'];


        /* ORDER INFORMATION */
        $options['x_invoice_num'] = $this->invoice_id;
        $options['x_description'] = $this->subject;
        $options['x_amount'] = $this->amount;




        /* CREDIT CARD INFORMATION */
        $options['x_card_num'] = $ccdetails['cardnum'];
        $options['x_exp_date'] = $ccdetails['expdate'];    //MMYY
         if($ccdetails['cvv']) {
           //this is manual payment, client passed cvv code
          $options['x_card_code'] = $ccdetails['cvv'];
        }


        //
        //SEND details to your credit card processor to validate and attempt to charge
        //
        $response = $this->processData($options);

        switch ($response['code']) {
            case 1:
                //charge succeeded, add transaction and log it

                $this->logActivity(array(
                    'output' => $response,
                    'result' => PaymentModule::PAYMENT_SUCCESS
                ));


                $this->addTransaction(array(
                    'client_id' => $this->client['client_id'],
                    'invoice_id' => $this->invoice_id,
                    'description' => "Payment for invoice ".$this->invoice_id,
                    'number' => $response['Transaction ID'],
                    'in' => $this->amount,
                    'fee' => '0'
                ));
                return true;

                break;

            case 2:

                $this->logActivity(array(
                    'output' => $response,
                    'result' => PaymentModule::PAYMENT_FAILURE
                ));
                return false;

                break;

         
        }
    }

    /**
     * This OPTIONAL helper function can be called from capture method,
     * i.e. connect to gateway API using CURL
     */
    private function processData($options) {
        //send data to cc processor, parse response
    }

}

