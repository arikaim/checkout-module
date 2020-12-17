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

use Arikaim\Modules\Checkout\ApiResult;

/**
 * Checkout subscription interface
 */
interface SubscriptionInterface 
{      
    /**
     * Create subscription 
     *
     * @param string $title
     * @param string $description
     * @param float $price    
     * @return ApiResult
    */
    public function create($planId, $title = null, $description = null);

    /**
     * Confirm (execute) subscription
     *
     * @param string $token
     * @return ApiResult
     */
    public function confirm($token);

    /**
     * Get subscription details
     *
     * @param string $id
     * @return ApiResult
     */
    public function details($id);
}
