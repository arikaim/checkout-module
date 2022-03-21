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

    const YEAR_INTERVAl = 'year';
    const MONTH_INTERVAl = 'month';
    const WEEK_INTERVAl = 'week';
    const DAY_INTERVAl = 'day';

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
    );

    /**
     * Get subscriptions plans list
     *
     * @param int|null $pageSize
     * @return ApiResult
     */
    public function getList(?int $pageSize = 20);

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
     * @param mixed $planId
     * @return ApiResult
    */
    public function delete($planId);

    /**
     * Update plan
     *
     * @param mixed $planId
     * @param string|array $data
     * @return ApiResult
    */
    public function update($planId, $data);
}
