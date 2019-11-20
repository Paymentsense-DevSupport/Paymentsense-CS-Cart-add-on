<?php
/**
 * Copyright (C) 2019 Paymentsense Ltd.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @package     CS-Cart Paymentsense add-on
 * @version     2.0
 * @author      Paymentsense
 * @copyright   2019 Paymentsense Ltd.
 * @license     https://www.gnu.org/licenses/gpl-3.0.html
 */

/**
 * CS-Cart Paymentsense payment method class
 */
class PaymentsensePaymentMethod
{
    /**
     * Paymentsense Request Types
     */
    const REQ_NOTIFICATION      = '0';
    const REQ_CUSTOMER_REDIRECT = '1';

    /**
     * Paymentsense Transaction Result Codes
     */
    const TRX_RESULT_CODE_SUCCESS   = '0';
    const TRX_RESULT_CODE_REFERRED  = '4';
    const TRX_RESULT_CODE_DECLINED  = '5';
    const TRX_RESULT_CODE_DUPLICATE = '20';
    const TRX_RESULT_CODE_FAILED    = '30';

    /**
     * Paymentsense Response Status Codes
     */
    const STATUS_CODE_OK    = '0';
    const STATUS_CODE_ERROR = '30';

    /**
     * Paymentsense Hosted Payment Form URL
     */
    const HPF_URL = 'https://mms.paymentsensegateway.com/Pages/PublicPages/PaymentForm.aspx';

    /**
     * CS-Cart Order Statuses
     */
    const CSCART_ORDER_STATUS_PROCESSED = 'P';
    const CSCART_ORDER_STATUS_FAILED    = 'F';

    /**
     * @var array
     * Stores the superglobal variable $_REQUEST
     */
    protected $request_data = [];

    /**
     * @var array
     * Stores the payment processor data
     */
    protected $processor_data = [];

    /**
     * @var array
     * Stores the variables for the response of the payment notification
     */
    protected $response_vars = array(
        'status_code' => '',
        'message'     => ''
    );

    /**
     * PaymentsensePaymentMethod Class Constructor
     * @param array $processor_data Payment processor data
     */
    public function __construct($processor_data)
    {
        $this->request_data   = $_REQUEST;
        $this->processor_data = $processor_data;
    }

    /**
     * Gets payment form URL
     *
     * @return string
     */
    public function getPaymentFormUrl()
    {
        return self::HPF_URL;
    }

    /**
     * Builds the fields for the Hosted Payment Form as an associative array
     *
     * @param array $order_info Order info
     * @return array An associative array containing the Required Input Variables for the API of the Hosted Payment Form
     */
    public function buildHpfFields($order_info)
    {
        $processor_params = $this->processor_data['processor_params'];

        $fields = $this->buildPaymentFields($order_info);

        $fields = array_map(
            function ($value) {
                return $value === null ? '' : $value;
            },
            $fields
        );

        $data  = 'MerchantID=' . $processor_params['merchant_id'];
        $data .= '&Password=' . $processor_params['password'];

        foreach ($fields as $key => $value) {
            $data .= '&' . $key . '=' . $value;
        };

        $additional_fields = [
            'HashDigest' => $this->calculateHashDigest(
                $data,
                $processor_params['hash_method'],
                $processor_params['preshared_key']
            ),
            'MerchantID' => $processor_params['merchant_id'],
        ];

        $fields = array_merge($additional_fields, $fields);

        return $fields;
    }

    /**
     * Checks whether the hash digest received from the payment gateway is valid
     *
     * @param array $data POST/GET data received with the request from the payment gateway
     * @return bool
     */
    public function isHashDigestValid($data=null)
    {
        $result = false;
        if ($data === null) {
            $data = $this->request_data;
        }
        $request_type = $this->getRequestType();
        $data_string  = $this->buildPostString($request_type, $data);
        if ($data_string) {
            $hash_digest_received   = $data['HashDigest'];
            $hash_digest_calculated = $this->calculateHashDigest(
                $data_string,
                $this->getHashMethod(),
                $this->getPresharedKey()
            );
            $result = strToUpper($hash_digest_received) === strToUpper($hash_digest_calculated);
        }
        return $result;
    }

    /**
     * Gets the transaction status and message received by the Hosted Payment Form
     *
     * @return array
     */
    public function getPaymentResponse()
    {
        $reason_text    = $this->request_data['Message'];
        $order_status   = '';
        $transaction_id = $this->request_data['CrossReference'] ;
        switch ($this->request_data['StatusCode']) {
            case self::TRX_RESULT_CODE_SUCCESS:
                $order_status = self::CSCART_ORDER_STATUS_PROCESSED;
                break;
            case self::TRX_RESULT_CODE_DUPLICATE:
                if (self::TRX_RESULT_CODE_SUCCESS === $this->request_data['PreviousStatusCode']) {
                    if (array_key_exists('PreviousMessage', $this->request_data)) {
                        $reason_text = $this->request_data['PreviousMessage'];
                    }
                    $order_status = self::CSCART_ORDER_STATUS_PROCESSED;
                } else {
                    $order_status = self::CSCART_ORDER_STATUS_FAILED;
                }
                break;
            case self::TRX_RESULT_CODE_REFERRED:
            case self::TRX_RESULT_CODE_DECLINED:
            case self::TRX_RESULT_CODE_FAILED:
            $order_status = self::CSCART_ORDER_STATUS_FAILED;
                break;
        }

        return [
            'reason_text'    => $reason_text,
            'order_status'   => $order_status,
            'transaction_id' => $transaction_id
        ];
    }

    /**
     * Sets the success response message and status code
     */
    public function setSuccessResponse()
    {
        $this->setResponse(self::STATUS_CODE_OK);
    }

    /**
     * Sets the error response message and status code
     *
     * @param string $message Response message
     *
     */
    public function setErrorResponse($message)
    {
        $this->setResponse(self::STATUS_CODE_ERROR, $message);
    }

    /**
     * Outputs the response
     */
    public function outputResponse()
    {
        echo "StatusCode={$this->response_vars['status_code']}&Message={$this->response_vars['message']}";
    }

    /**
     * Gets payment gateway Merchant ID
     *
     * @return string
     */
    protected function getMerchantId()
    {
        return $this->processor_data['processor_params']['merchant_id'];
    }

    /**
     *Gets payment gateway Password
     *
     * @return string
     */
    protected function getPassword()
    {
        return $this->processor_data['processor_params']['password'];
    }

    /**
     * Gets payment gateway Hash Method
     *
     * @return string
     */
    protected function getHashMethod()
    {
        return $this->processor_data['processor_params']['hash_method'];
    }

    /**
     * Gets payment gateway Pre-shared Key
     *
     * @return string
     */
    protected function getPresharedKey()
    {
        return $this->processor_data['processor_params']['preshared_key'];
    }

    /**
     * Gets the request type (notification or customer redirect)
     *
     * @return string
     */
    protected function getRequestType()
    {
        return array_key_exists('StatusCode', $this->request_data) && is_numeric($this->request_data['StatusCode'])
            ? self::REQ_NOTIFICATION
            : self::REQ_CUSTOMER_REDIRECT;
    }

    /**
     * Gets CallbackUrl
     *
     * @param string $processor_script Payment processor script
     * @return string
     */
    protected function getCallbackUrl($processor_script)
    {
        return fn_payment_url(
            'current',
            $processor_script
        );
    }

    /**
     * Gets ServerResultURL
     *
     * @return string
     */
    protected function getServerResultURL()
    {
        return fn_url(
            "payment_notification.process?payment=paymentsense",
            AREA,
            'current'
        );
    }

    /**
     * Gets Country Numeric Dode
     *
     * @param string $country_code Country alpha-2 code
     * @return string
     */
    protected function getCountryNumericCode($country_code)
    {
        return db_get_field('SELECT code_N3 FROM ?:countries WHERE code=?s', $country_code);
    }

    /**
     * Builds the redirect form variables for the Hosted Payment Form
     *
     * @param array $order_info Order info
     * @return array
     */
    protected function buildPaymentFields($order_info)
    {
        $processor_params = $this->processor_data['processor_params'];

        return [
            'Amount'                    => intval($order_info['total'] * 100),
            'CurrencyCode'              => PaymentsenseIso4217::getCurrencyIsoCode($processor_params['currency']),
            'OrderID'                   => ($order_info['repaid']) ? ($order_info['order_id'] . '_' . $order_info['repaid']) : $order_info['order_id'],
            'TransactionType'           => $processor_params['transaction_type'],
            'TransactionDateTime'       => date('Y-m-d H:i:s P'),
            'CallbackURL'               => $this->getCallbackUrl($this->processor_data['processor_script']),
            'OrderDescription'          => '',
            'CustomerName'              => $order_info['b_firstname'] . ' ' . $order_info['b_lastname'],
            'Address1'                  => $order_info['b_address'],
            'Address2'                  => $order_info['b_address_2'],
            'Address3'                  => '',
            'Address4'                  => '',
            'City'                      => $order_info['b_city'],
            'State'                     => $order_info['b_state_descr'],
            'PostCode'                  => $order_info['b_zipcode'],
            'CountryCode'               => $this->getCountryNumericCode($order_info['b_country']),
            'EmailAddress'              => $order_info['email'],
            'PhoneNumber'               => $order_info['phone'],
            'EmailAddressEditable'      => 'false',
            'PhoneNumberEditable'       => 'false',
            'CV2Mandatory'              => $processor_params['cv2_mandatory'],
            'Address1Mandatory'         => $processor_params['address_mandatory'],
            'CityMandatory'             => $processor_params['city_mandatory'],
            'PostCodeMandatory'         => $processor_params['postcode_mandatory'],
            'StateMandatory'            => $processor_params['state_mandatory'],
            'CountryMandatory'          => $processor_params['country_mandatory'],
            'ResultDeliveryMethod'      => 'SERVER',
            'ServerResultURL'           => $this->getServerResultURL(),
            'PaymentFormDisplaysResult' => 'false'
        ];
    }

    /**
     * Builds a string containing the expected fields from the request received from the payment gateway
     *
     * @param string $request_type Type of the request (notification or customer redirect)
     * @param array $data POST/GET data received with the request from the payment gateway
     * @return bool
     */
    protected function buildPostString($request_type, $data)
    {
        $result = false;
        $fields = [
            // Variables for hash digest calculation for notification requests (excluding configuration variables)
            self::REQ_NOTIFICATION      => [
                'StatusCode',
                'Message',
                'PreviousStatusCode',
                'PreviousMessage',
                'CrossReference',
                'Amount',
                'CurrencyCode',
                'OrderID',
                'TransactionType',
                'TransactionDateTime',
                'OrderDescription',
                'CustomerName',
                'Address1',
                'Address2',
                'Address3',
                'Address4',
                'City',
                'State',
                'PostCode',
                'CountryCode',
                'EmailAddress',
                'PhoneNumber'
            ],
            // Variables for hash digest calculation for customer redirects (excluding configuration variables)
            self::REQ_CUSTOMER_REDIRECT => [
                'CrossReference',
                'OrderID',
            ],
        ];

        if (array_key_exists($request_type, $fields)) {
            $result = 'MerchantID=' . $this->getMerchantId() . '&Password=' . $this->getPassword();
            foreach ($fields[$request_type] as $field) {
                $result .= '&' . $field . '=' . str_replace('&amp;', '&', $data[$field]);
            }
        }

        return $result;
    }

    /**
     * Calculates the hash digest.
     * Supported hash methods: MD5, SHA1, HMACMD5, HMACSHA1
     *
     * @param string $data Data to be hashed.
     * @param string $hash_method Hash method.
     * @param string $key Secret key to use for generating the hash.
     * @return string
     */
    protected function calculateHashDigest($data, $hash_method, $key)
    {
        $result     = '';
        $include_key = in_array($hash_method, ['MD5', 'SHA1'], true);
        if ($include_key) {
            $data = 'PreSharedKey=' . $key . '&' . $data;
        }
        switch ($hash_method) {
            case 'MD5':
                $result = md5($data);
                break;
            case 'SHA1':
                $result = sha1($data);
                break;
            case 'HMACMD5':
                $result = hash_hmac('md5', $data, $key);
                break;
            case 'HMACSHA1':
                $result = hash_hmac('sha1', $data, $key);
                break;
        }
        return $result;
    }

    /**
     * Sets the response variables
     *
     * @param string $status_code Response status code
     * @param string $message Response message
     */
    protected function setResponse($status_code, $message='')
    {
        $this->response_vars['status_code'] = $status_code;
        $this->response_vars['message']     = $message;
    }
}
