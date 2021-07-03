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
 * Checkout interface
 */
interface CheckoutDriverInterface 
{
    /**
     * Get checkout gateway
     *
     * @return mixed
     */
    public function getGateway();
    
     /**
     * Checkout rquest
     *
     * @param CheckoutData $data
     * @return object|null
     */
    public function checkout($data);

    /**
     * Create transaction obj ref
     *
     * @param array $params
     * @return TransactionInterface|null
     */
    public function completeCheckout(array $params);  

    /**
     * Resolve transaction status
     *
     * @param mixed $data
     * @return integer
     */
    public function resolveTransactionStatus($data): int;
}
