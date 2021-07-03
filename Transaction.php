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

use Arikaim\Core\Utils\DateTime;
use Arikaim\Modules\Checkout\Interfaces\TransactionInterface;

/**
 * Checkout module class
 */
class Transaction implements TransactionInterface
{   
    const CHECKOUT             = 'checkout';
    const SUBSCRIPTION_PAYMENT = 'subscription_payment';
    const SUBSCRIPTION_EXPIRED = 'subscription_expired';
    const SUBSCRIPTION_CANCEL  = 'subscription_cancel';
    const SUBSCRIPTION_CREATE  = 'subscription_create';
    
    /**
     * Payer email
     *
     * @var string|null
     */
    protected $payerEmail;

    /**
     * Payer name
     *
     * @var string|null
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
     * @var float
     */
    protected $amount = 0.00;

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
     * @var int
     */
    protected $status = 0;
    
    /**
     * Transaction details
     *
     * @var array
     */
    protected $details = [];

    /**
     * Order id
     *
     * @var string|null
     */
    protected $orderId = null;

    /**
     * Date time created
     *
     * @var int|null
     */
    protected $dateCreated = null;

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
    public function __construct(
        $transactionId, 
        string $payerEmail, 
        string $payerName, 
        $amount, 
        string $currency, 
        string $type, 
        string $driverName, 
        array $details = []
    )
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
     * Set order id
     *
     * @param string|null $id
     * @return void
     */
    public function setOrderId(?string $id): void
    {
        $this->orderId = $id;
    }

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param mixed $status
     * @return void
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * Get date time created timestamp
     *
     * @return integer
    */
    public function getDateTimeCreated(): int
    {
        return $this->dateCreated ?? DateTime::getTimestamp();
    }

    /**
     * Get date time created timestamp
     *
     * @param int $dateCreated
     * @return void
    */
    public function setDateTimeCreated(int $dateCreated): void
    {
        $this->dateCreated = $dateCreated;
    }

    /**
     * Get order id
     *
     * @return string|null
     */
    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    /**
     * Get field value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getFiledValue(string $key, $default = null)
    {
        return $this->details[$key] ?? $default;
    }

    /**
     * Check if transaction data is valid
     *
     * @return boolean
     */
    public function isValid(): bool
    {
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
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * Set details
     *
     * @param array $details
     * @return void
     */
    public function setDetails(array $details): void
    {
        $this->details = $details;
    }

    /**
     * Get array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'payer_email' => $this->getPayerEmail(),
            'payer_name'  => $this->getPayerName(),
            'transaction' => $this->getTransactionId(),
            'type'        => $this->getType(),
            'status'      => $this->getStatus(),
            'amount'      => $this->getAmount(),
            'order_id'    => $this->getOrderId(),
            'currency'    => $this->getCurrency(),
            'driver_name' => $this->getCheckoutDriver(),
            'details'     => $this->getDetails()
        ];     
    }

    /**
     * Get currency
     *    
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Get checkout driver
     *
     * @return string
     */
    public function getCheckoutDriver(): string
    {
        return $this->driverName;
    }
    
    /**
     * Gte payer email
     *
     * @return string|null
     */
    public function getPayerEmail(): ?string
    {
        return $this->payerEmail;
    }

    /**
     * Get payer name
     *
     * @return string|null
     */
    public function getPayerName(): ?string
    {
        return $this->payerName;
    }

    /**
     * Gte transaction id
     *
     * @return string
     */
    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * Get date time
     *
     * @return string
     */
    public function getType(): string
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
