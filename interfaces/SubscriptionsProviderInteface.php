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

use Arikaim\Modules\Checkout\Interfaces\SubscriptionPlanInterface;
use Arikaim\Modules\Checkout\Interfaces\SubscriptionInterface;
use Arikaim\Modules\Checkout\Interfaces\TransactionInterface;

/**
 * Subscription provider interface
 */
interface SubscriptionsProviderInteface 
{      
    /**
     * Get subscription plans ref
     *
     * @return SubscriptionPlanInterface
    */
    public function plan();
    
    /**
     * Get subscriptions ref
     *
     * @return SubscriptionInterface
    */
    public function subscription();   
    
    /**
     * Get IPN url
     *
     * @return string
    */
    public function getIpnUrl();   

    /**
     * Create transaction obj ref
     *
     * @return TransactionInterface
     */
    public function createTransaction(array $details);
}
