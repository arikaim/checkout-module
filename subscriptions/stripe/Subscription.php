<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Modules\Checkout\Subscriptions\Stripe;

use Arikaim\Core\Http\Url;
use Arikaim\Core\Utils\DateTime;

use Arikaim\Modules\Checkout\ApiResult;
use Arikaim\Modules\Checkout\Subscriptions\SubscriptionApi;
use Arikaim\Modules\Checkout\Interfaces\SubscriptionInterface;
use Exception;

/**
 * Subscription class
 */
class Subscription extends SubscriptionApi implements SubscriptionInterface
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
    public function create($planId, ?string $title = null, ?string $description = null, ?array $data = null)
    {
        $title = $title ?? 'Subscription Agreement';
        $description = $description ?? 'Subscription Agreement';
        
        $response = $this->apiClient->subscriptions->create([
            'customer' => 'cus_KX5UPEcK42ZmqT',
            'items'    => [
                ['price' => $planId]
            ],
        ]);
       
        return ApiResult::success([
           
            'plan_id'  => $planId, 
            'response' => $response
        ]);                        
    }

    /**
     * Confirm (execute) subscription
     *
     * @param string $token
     * @param array|null $data
     * @return ApiResult
    */
    public function confirm($token, ?array $data = null)
    {
       
    }

    /**
     * Get subscription details
     *
     * @param string $id
     * @return ApiResult
     */
    public function details($id)
    {
        
    }
}
