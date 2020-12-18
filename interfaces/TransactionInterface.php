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
    /**
     * Get currency
     *    
     * @return string
     */
    public function getCurrency();

    /**
     * Get payer email
     *
     * @return string
     */
    public function getPayerEmail();

    /**
     * Get payer name
     *
     * @return string
     */
    public function getPayerName();

    /**
     * Gte transaction id
     *
     * @return string
     */
    public function getTransactionId();

    /**
     * Get type
     *
     * @return string
     */
    public function getType();

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
    public function geetCheckoutDriver();

    /**
     * Get transaction details
     *
     * @return array
     */
    public function getDetails();

    /**
     * Get order id
     *
     * @return string|null
     */
    public function getOrderId();
}
