<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Modules\Checkout\Interfaces;

/**
 * Transaction interface
 */
interface TransactionInterface 
{  
    const STATUS_PENDING   = 0;
    const STATUS_COMPLETED = 1;
    const STATUS_CANCELED  = 2;
    const STATUS_ERROR     = 3;

    /**
     * Get currency
     *    
     * @return string
     */
    public function getCurrency(): string;

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Get date time created timestamp
     *
     * @return integer
    */
    public function getDateTimeCreated(): int;

    /**
     * Get payer email
     *
     * @return string|null
     */
    public function getPayerEmail(): ?string;

    /**
     * Get payer name
     *
     * @return string
     */
    public function getPayerName(): ?string;

    /**
     * Gte transaction id
     *
     * @return string
     */
    public function getTransactionId(): string;

    /**
     * Get type
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount();

    /**
     * Get checkout driver
     *
     * @return string
     */
    public function getCheckoutDriver(): string;

    /**
     * Get transaction details
     *
     * @return array
     */
    public function getDetails(): array;

    /**
     * Get order id
     *
     * @return string|null
     */
    public function getOrderId(): ?string;
}
