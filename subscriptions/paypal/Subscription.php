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
use PayPal\Api\AgreementStateDescriptor;

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
     * Cancel subscription
     *
     * @param mixed $id
     * @return ApiResult
     */
    public function cancel($id)
    {
        $agreementStateDescriptor = new AgreementStateDescriptor();
        $agreementStateDescriptor->setNote("Suspending the agreement");

        try {
            $agreement = Agreement::get($id,$this->getApiClient());
            $response = $agreement->cancel($agreementStateDescriptor,$this->getApiClient());
        } catch (PayPalConnectionException $e) {
            return ApiResult::error($e->getMessage(),$e->getData());
        } catch(Exception $e) {
            return ApiResult::error($e->getMessage(),[]);
        }
       
        return ApiResult::success([
            'id'                => $id,
            'response'          => $response
        ]); 
    }

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
        $startDate = DateTime::addInterval('10 hours')->format(DateTime::ISO8601ZULU_FORMAT);
        //$startDate = DateTime::getDateTime()->format(DateTime::ISO8601ZULU_FORMAT);

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
     * @param array|null $data
     * @return ApiResult
    */
    public function confirm($token, ?array $data = null)
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
            return ApiResult::success([
                'id'                => $response->getId(),
                'next_billing_date' => $response->getAgreementDetails()->getNextBillingDate(),
                'response'          => $response
            ]);    
        }
        
        return ApiResult::error('Error activate subscripton',$response);
    }

    /**
     * Get subscription details
     *
     * @param mixed $id
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
