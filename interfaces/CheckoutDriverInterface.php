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

use Arikaim\Core\Interfaces\Content\ContentItemInterface;

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
     * @param ContentItemInterface $data
     * @return object|null
     */
    public function checkout(ContentItemInterface $data);

    /**
     * Create transaction obj ref
     *
     * @param ContentItemInterface $params
     * @return TransactionInterface|null
     */
    public function completeCheckout(ContentItemInterface $data);  

    /**
     * Resolve transaction status
     *
     * @param mixed $data
     * @return integer
     */
    public function resolveTransactionStatus($data): int;
}
