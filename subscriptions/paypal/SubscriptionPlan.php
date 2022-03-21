<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Modules\Checkout\Subscriptions\PayPal;

use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Plan;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Common\PayPalModel;
use PayPal\Exception\PayPalConnectionException;

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
        $frequency = ($billingType == SubscriptionPlanInterface::ANNUAL_BILLING) ? 'YEAR' : 'MONTH';
        $interval = '1';
        $plan = new Plan();
        $plan
            ->setName($title)
            ->setDescription($description)
            ->setState('ACTIVE')
            ->setType('INFINITE');

        $currency = new Currency([
            'value'    => $price, 
            'currency' => $currencyCode
        ]);

        $paymentDefinition = new PaymentDefinition();
        $paymentDefinition
            ->setName('Regular Payments')
            ->setType('REGULAR')
            ->setFrequency($frequency)
            ->setFrequencyInterval($interval)
            ->setCycles('0')
            ->setAmount($currency);
        $plan->setPaymentDefinitions([$paymentDefinition]);

        $merchantPreferences = new MerchantPreferences();
        $merchantPreferences
            ->setReturnUrl($this->getOption('return_url'))
            ->setCancelUrl($this->getOption('cancel_url'))
            ->setNotifyUrl($this->getOption('notify_url'))
            ->setAutoBillAmount('yes')
            ->setInitialFailAmountAction('CANCEL')
            ->setMaxFailAttempts('0');
        $plan->setMerchantPreferences($merchantPreferences);

        try {
            $response = $plan->create($this->getApiClient());           
        } catch (PayPalConnectionException $e) {
            return ApiResult::error($e->getMessage(),$e->getData());
        } catch (Exception $e) {
            return ApiResult::error($e->getMessage(),[]);
        }
      
        if ($response->state == 'CREATED') {
            return ApiResult::success([
                'id'       => $response->id,
                'response' => $response
            ]);
        }
        
        return ApiResult::error('Create subscription plan error',[]);
    }
    
    /**
     * Get subscription plans list
     *
     * @param int|null $pageSize
     * @return ApiResult
    */
    public function getList(?int $pageSize = 20)
    {
        try {
            $response = Plan::all([
                'page_size' => $pageSize
            ], $this->getApiClient());
           
        } catch (PayPalConnectionException $e) {
            return ApiResult::error($e->getMessage(),$e->getData());
        } catch (Exception $e) {
            return ApiResult::error($e->getMessage(),[]);
        }

        return ApiResult::success($response->getPlans());
    }

    /**
     * Get plan details
     *
     * @param mixed $planId
     * @return ApiResult
     */
    public function getDetails($planId)
    {
        try {
            $response = Plan::get($planId,$this->getApiClient());
        } catch (PayPalConnectionException $e) {
            return ApiResult::error($e->getMessage(),$e->getData());
        } catch (Exception $e) {
            return ApiResult::error($e->getMessage(),[]);
        }

        return ApiResult::success($response);
    }

    /**
     * Update plan
     *
     * @param string $planId
     * @param string|array $data
     * @return ApiResult
     */
    public function update($planId, $data)
    {
        try {
            $data = (\is_array($data) == true) ? \json_encode($data) : $data;
            $value = new PayPalModel($data);

            $patch = new Patch();
            $patch
                ->setOp('replace')
                ->setPath('/')
                ->setValue($value);

            $patchRequest = new PatchRequest();
            $patchRequest->addPatch($patch);

            $plan = new Plan();
            $plan->setId($planId);

            $response = $plan->update($patchRequest,$this->getApiClient());
           
        } catch (PayPalConnectionException $e) {
            return ApiResult::error($e->getMessage(),$e->getData());
        } catch (Exception $e) {
            return ApiResult::error($e->getMessage(),[]);
        }

        return ApiResult::success($response);
    }

    /**
     * Delete plan
     *
     * @param mixed $planId
     * @return ApiResult
    */
    public function delete($planId)
    {
        try {
            $plan = new Plan();
            $plan->setId($planId);

            $response = $plan->delete($this->getApiClient());
                         
        } catch (PayPalConnectionException $e) {
            return ApiResult::error($e->getMessage(),$e->getData());
        } catch (Exception $e) {
            return ApiResult::error($e->getMessage(),[]);
        }

        return ApiResult::success($response);
    }
}
