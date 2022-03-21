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

use Arikaim\Modules\Checkout\ApiResult;
use Arikaim\Modules\Checkout\Subscriptions\SubscriptionApi;
use Arikaim\Modules\Checkout\Interfaces\SubscriptionPlanInterface;
use Exception;

/**
 * Subscriptions plan class
 */
class SubscriptionPlan extends SubscriptionApi implements SubscriptionPlanInterface
{   
   /**
     * Create subscription pan
     *
     * @param string|null $title
     * @param string?null $description
     * @param float $price
     * @param string $currencyCode
     * @param string $billingType
     * @param array|null $data
     * @return ApiResult
    */
    public function create(
        ?string $title, 
        ?string $description, 
        $price, 
        string $currencyCode, 
        string $billingType, 
        ?array $data = null
    )
    {
        $price = \number_format($price,2);
       
        $response = $this->apiCient->plans->create([
            'amount'    => $price,
            'currency'  => $currencyCode,
            'interval'  => 'month',
            'product'   => 'prod_HKMMzoes8kusMZ',
        ]);

      
        
        return ApiResult::error('Create subscription plan error',[]);
    }
    
    /**
     * Get subscription plans list
     *
     * @param int $pageSize
     * @return ApiResult
    */
    public function getList(?int $pageSize = 20)
    {
        

        //return ApiResult::success($response->getPlans());
    }

    /**
     * Get plan details
     *
     * @param mixed $planId
     * @return ApiResult
     */
    public function getDetails($planId)
    {
       

        //return ApiResult::success($response);
    }

    /**
     * Update plan
     *
     * @param mixed $planId
     * @param string|array $data
     * @return ApiResult
     */
    public function update($planId, $data)
    {
      
        //return ApiResult::success($response);
    }

    /**
     * Delete plan
     *
     * @param mixed $planId
     * @return ApiResult
    */
    public function delete($planId)
    {
        //return ApiResult::success($response);
    }
}
