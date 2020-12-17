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
 * Checkout subscription data interface
*/
interface SubscriptionPlanInterface
{  
    const ANNUAL_BILLING  = 'annual';
    const MONTHLY_BILLING = 'monthly';

    /**
     * Create subscription pan
     *
     * @param string $title
     * @param string $description
     * @param float $price
     * @param string $currencyCode
     * @param string $billingType
     * @return ApiResult
    */
    public function create($title, $description, $price, $currency, $billingType);

    /**
     * Get subscriptions plans list
     *
     * @param int $pageSize
     * @return ApiResult
     */
    public function getList($pageSize = 20);

    /**
     * Get plan details
     *
     * @param string $planId
     * @return ApiResult
     */
    public function getDetails($planId);

    /**
     * Delete plan
     *
     * @param string $planId
     * @return ApiResult
    */
    public function delete($planId);

    /**
     * Update plan
     *
     * @param string $planId
     * @param string|array $data
     * @return ApiResult
    */
    public function update($planId, $data);
}
