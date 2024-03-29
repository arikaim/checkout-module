<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Modules\Checkout\Drivers;

use Stripe\StripeClient;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Driver\Traits\Driver;
use Arikaim\Core\Http\Url;
use Arikaim\Core\Interfaces\Driver\DriverInterface;
use Arikaim\Modules\Checkout\Transaction;
use Arikaim\Modules\Checkout\Interfaces\SubscriptionPlanInterface;
use Arikaim\Modules\Checkout\Interfaces\SubscriptionInterface;
use Arikaim\Modules\Checkout\Interfaces\SubscriptionsProviderInteface;
use Arikaim\Modules\Checkout\Subscriptions\PayPal\SubscriptionPlan;
use Arikaim\Modules\Checkout\Subscriptions\PayPal\Subscription;

/**
 * Stripe subscriptions driver class
 */
class StripeSubscriptionsDriver implements DriverInterface, SubscriptionsProviderInteface
{   
    use Driver;

    /**
     * Stripe api client
     *
     * @var StripeClient
     */
    protected $client;

    /**
     * Plans
     *
     * @var SubscriptionPlanInterface
     */
    protected $plan;

    /**
     * Subscriptions
     *
     * @var SubscriptionInterface
     */
    protected $subscription;

    /**
     * Options
     *
     * @var array
     */
    protected $options;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setDriverParams('stripe-subscriptions','subscriptions','Stripe Subscriptions','Driver for Stripe subscriptions payments.');
    }

    /**
     * Initialize driver
     *
     * @return void
     */
    public function initDriver($properties)
    {       
        $config = $properties->getValues(); 
        $mode = $config['mode'] ?? 'test';

        $publicKey = $properties->getValueAsText('client_id',$mode);
        $privateKey = $properties->getValueAsText('client_secret',$mode);
        
        $this->client = new StripeClient($privateKey);

        $this->options = [
            'notify_url' => Url::BASE_URL . $config['notify_url'],
            'return_url' => Url::BASE_URL . $config['return_url'],
            'cancel_url' => Url::BASE_URL . $config['cancel_url'],
            'locale'     => $config['locale'] ?? '',
        ];
        
        $this->plan = new SubscriptionPlan($this->client,$this->options);
        $this->subscription = new Subscription($this->client,$this->options);
    }

    /**
     * Resolve transaction type
     *
     * @param string $type
     * @return string|null
     */
    public function resolveTransactionType($type)
    {
        switch($type) {
            case 'recurring_payment_profile_created':
                $type = Transaction::SUBSCRIPTION_CREATE;
                break;
            case 'subscr_signup':
                $type = Transaction::SUBSCRIPTION_CREATE;
                break;
            case 'recurring_payment':
                $type = Transaction::SUBSCRIPTION_PAYMENT;
                break;
            case 'subscr_payment':
                $type = Transaction::SUBSCRIPTION_PAYMENT;
                break;
            case 'subscr_cancel':
                $type = Transaction::SUBSCRIPTION_CANCEL;
                break;
            case 'recurring_payment_profile_cancel':
                $type = Transaction::SUBSCRIPTION_CANCEL;
                break;
            case 'subscr_eot':
                $type = Transaction::SUBSCRIPTION_EXPIRED;
                break;
            case 'recurring_payment_expired':
                $type = Transaction::SUBSCRIPTION_EXPIRED;
                break;
            default:
                $type = null;
        }

        return $type;
    }

    /**
     * Create transaction obj ref
     *
     * @return TransactionInterface
    */
    public function createTransaction(array $details)
    {
        $type = $this->resolveTransactionType($details['txn_type']);

        $transaction = new Transaction(
            $details['txn_id'] ?? $details['ipn_track_id'] ?? null,
            $details['EMAIL'] ?? null,
            $details['FIRSTNAME'] . ' ' . $details['LASTNAME'],
            $details['AMT'] ?? $details['PAYMENTREQUEST_0_AMT'],
            $details['CURRENCYCODE'] ?? $details['PAYMENTREQUEST_0_CURRENCYCODE'],
            $type,
            'paypal-subscriptions',
            $details           
        );

        $subscriptionId = $details['recurring_payment_id'] ?? null;
        $transaction->setOrderId($subscriptionId);

        return $transaction;
    }

    /**
     * Get subscription plans ref
     *
     * @return SubscriptionPlanInterface
     */
    public function plan()
    {
        return $this->plan;
    }

    /**
     * Get subscriptions ref
     *
     * @return SubscriptionInterface
     */
    public function subscription()
    {
        return $this->subscription;
    }

    /**
     * Get IPN url
     *
     * @return string
    */
    public function getIpnUrl()
    {
        return $this->options['notify_url'] ?? '';
    }

    /**
     * Create driver config properties array
     *
     * @param Arikaim\Core\Collection\Properties $properties
     * @return void
     */
    public function createDriverConfig($properties)
    {
        $properties->property('mode',function($property) {
            $property
                ->title('Mode')
                ->type('list')
                ->items(['test','live'])
                ->required(true)
                ->default('test');             
        });

        // Test mode Credentials
        $properties->property('test_group',function($property) {
            $property
                ->title('Test Mode API Credentials')
                ->type('group')
                ->required(true)              
                ->default('test');               
        });

        $properties->property('public_key',function($property) {
            $property
                ->title('Public Key')
                ->type('text')
                ->group('test')
                ->default('');              
        });
       
        $properties->property('private_key',function($property) {
            $property
                ->title('Private Key')
                ->type('text')
                ->group('test')
                ->default('');               
        });

        // Live Credentials
        $properties->property('live_group',function($property) {
            $property
                ->title('Live Mode API Credentials')
                ->type('group')
                ->required(true)              
                ->default('live');               
        });

        $properties->property('private_key',function($property) {
            $property
                ->title('Private Key')
                ->type('key')
                ->group('live')
                ->default('');              
        });

        $properties->property('public_key',function($property) {
            $property
                ->title('Public Key')
                ->type('text')
                ->group('live')
                ->default('');             
        });

        $properties->property('cancel_url',function($property) {
            $property
                ->title('Cancel Url')
                ->type('url')
                ->readonly(true)
                ->default('/subscription/cancel/');
        });

        $properties->property('return_url',function($property) {
            $property
                ->title('Success Url')
                ->type('url')
                ->readonly(true)
                ->default('/subscription/success/');
        });

        $properties->property('notify_url',function($property) {
            $property
                ->title('Notify Url')
                ->type('url')
                ->readonly(true)
                ->default('/api/subscription/notify');
        });
    } 
}
