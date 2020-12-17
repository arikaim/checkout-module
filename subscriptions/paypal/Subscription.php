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

use PayPal\Api\Plan;
use PayPal\Api\Agreement;
use PayPal\Api\Payer;
use PayPal\Exception\PayPalConnectionException;

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
     * @param string $title
     * @param string $description
     * @param float $price    
     * @return ApiResult
    */
    public function create($planId, $title = null, $description = null)
    {
        $title = $title ?? 'Subscription Agreement';
        $description = $description ?? 'Subscription Agreement';
        $startDate = DateTime::toString(DateTime::ISO8601ZULU_FORMAT);

        $plan = new Plan();
        $plan->setId($planId);
        
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
     
        $agreement = new Agreement();
        $agreement
            ->setName($title)
            ->setDescription($description)
            ->setStartDate($startDate)
            ->setPlan($plan)
            ->setPayer($payer);
       
        try {
            $response = $agreement->create($this->getApiClient());   
        } catch (PayPalConnectionException $e) {
            return ApiResult::error($e->getMessage(),$e->getData());
        } catch(Exception $e) {
            return ApiResult::error($e->getMessage(),[]);
        }
        
        $token = Url::getUrlParam($response->getApprovalLink(),'token');
        
        return ApiResult::success([
            'token'    => $token,
            'plan_id'  => $response->plan->getId(), 
            'response' => $response
        ]);                        
    }

    /**
     * Confirm (execute) subscription
     *
     * @param string $token
     * @return ApiResult
     */
    public function confirm($token)
    {
        try {
            $agreement = new Agreement();          
            $response = $agreement->execute($token,$this->getApiClient());   

        } catch (PayPalConnectionException $e) {
            return ApiResult::error($e->getMessage(),$e->getData());
        } catch(Exception $e) {
            return ApiResult::error($e->getMessage(),[]);
        }

        if ($response->state == 'Active') {
            return ApiResult::success($response);    
        }
        
        return ApiResult::error('Error activate subscripton',$response);
    }

    /**
     * Get subscription details
     *
     * @param string $id
     * @return ApiResult
     */
    public function details($id)
    {
        try {
            $response = Agreement::get($id,$this->getApiClient());

        } catch (PayPalConnectionException $e) {
            return ApiResult::error($e->getMessage(),$e->getData());
        } catch(Exception $e) {
            return ApiResult::error($e->getMessage(),[]);
        }

        return ApiResult::success($response);    
    }
}
