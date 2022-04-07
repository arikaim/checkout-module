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
     * @param mixed $planId    
     * @param string|null $title
     * @param string|null $description    
     * @param array|null $data
     * @return ApiResult
    */
    public function create($planId, ?string $title = null, ?string $description = null, ?array $data = null);

    /**
     * Confirm (execute) subscription
     *
     * @param string $token
     * @param array|null $data
     * @return ApiResult
    */
    public function confirm($token, ?array $data = null);

    /**
     * Get subscription details
     *
     * @param mixed $id
     * @return ApiResult
     */
    public function details($id);

    /**
     * Cancel subscription
     *
     * @param mixed $id
     * @return ApiResult
     */
    public function cancel($id);
}
