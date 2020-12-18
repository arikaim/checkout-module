<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Modules\Checkout;

use Arikaim\Modules\Checkout\Interfaces\TransactionInterface;

/**
 * Checkout module class
 */
class Transaction implements TransactionInterface
{   
    const SUBSCRIPTION_PAYMENT = 'subscription_payment';
    const SUBSCRIPTION_EXPIRED = 'subscription_expired';
    const SUBSCRIPTION_CANCEL  = 'subscription_cancel';
    const SUBSCRIPTION_START   = 'subscription_start';
    
    /**
     * Payer email
     *
     * @var string
     */
    protected $payerEmail;

    /**
     * Payer name
     *
     * @var string
     */
    protected $payerName;

    /**
     * Transaction id
     *
     * @var string
     */
    protected $transactionId;

    /**
     * Transaction type
     *
     * @var string
     */
    protected $type;

    /**
     * Amount paid
     *
     * @var float|string
     */
    protected $amount;

    /**
     * Currency code
     *
     * @var string
     */
    protected $currency;

    /**
     * Checkout driver name
     *
     * @var string
     */
    protected $driverName;

    /**
     * Transaction status 
     *
     * @var integer
     */
    protected $status;
    
    /**
     * TRansaction details
     *
     * @var string
     */
    protected $details;

    /**
     * Constructor
     *
     * @param string $transactionId
     * @param string $payerEmail  
     * @param string $payerName  
     * @param float|string $amount
     * @param string $currency
     * @param string $type
     * @param string $driverName
     * @param array $details
     */
    public function __construct($transactionId, $payerEmail, $payerName, $amount, $currency, $type, $driverName, $details = [])
    {
        $this->transactionId = $transactionId;
        $this->payerEmail = $payerEmail;
        $this->payerName = $payerName;

        $this->amount = $amount;
        $this->currency = $currency;
        $this->type = $type;
        $this->driverName = $driverName;
        $this->details = $details;
    }

    /**
     * Get field value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getFiledValue($key, $default = null)
    {
        return $this->details[$key] ?? $default;
    }

    /**
     * Check if transaction data is valid
     *
     * @return boolean
     */
    public function isValid()
    {
        if (empty($this->transactionId) == true) {
            return false;
        }

        if (empty($this->type) == true) {
            return false;
        }

        if (empty($this->amount) == true) {
            return false;
        }

        if (empty($this->currency) == true) {
            return false;
        }

        return true;
    } 

    /**
     * Get transaction details
     *
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set details
     *
     * @param string $details
     * @return void
     */
    public function setDetails($details)
    {
        $this->details = $details;
    }

    /**
     * Create from array
     *
     * @param array $data
     * @return TransactionInterface
     */
    public static function createFromArray(array $data)
    {
        $transactionId = $data['transaction_id'] ?? null;
        $payerEmail = $data['payer_email'] ?? null;
        $payerName = $data['payer_name'] ?? null;  
        $amount = $data['amount'] ?? null;
        $currency = $data['currency'] ?? null;
        $type = $data['type'] ?? null;
        $driverName = $data['driver_name'] ?? null;
        $details = $data['details'] ?? [];

        return new Self($transactionId,$payerEmail,$payerName,$amount,$currency,$type,$driverName,$details);
    }

    /**
     * Get array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'payer_email'  => $this->getPayerEmail(),
            'payer_name'   => $this->getPayerName(),
            'transaction'  => $this->getTransactionId(),
            'type'         => $this->getType(),
            'amount'       => $this->getAmount(),
            'currency'     => $this->getCurrency(),
            'driver'       => $this->geetCheckoutDriver(),
            'details'      => $this->getDetails()
        ];     
    }

    /**
     * Get currency
     *    
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Get checkout driver
     *
     * @return string
     */
    public function geetCheckoutDriver()
    {
        return $this->driverName;
    }
    
    /**
     * Gte payer email
     *
     * @return string
     */
    public function getPayerEmail()
    {
        return $this->payerEmail;
    }

    /**
     * Get payer name
     *
     * @return string
     */
    public function getPayerName()
    {
        return $this->payerName;
    }

    /**
     * Gte transaction id
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * Get date time
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }
}
