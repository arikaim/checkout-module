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

use Omnipay\Omnipay;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Driver\Traits\Driver;
use Arikaim\Core\Http\Url;
use Arikaim\Core\Interfaces\Driver\DriverInterface;
use Arikaim\Modules\Checkout\Transaction;
use Arikaim\Modules\Checkout\Interfaces\CheckoutDriverInterface;
use Arikaim\Modules\Checkout\Interfaces\TransactionInterface;
use Arikaim\Modules\Checkout\CheckoutData;

/**
 * PayPal checkout driver class
 */
class PayPalCheckoutDriver implements DriverInterface, CheckoutDriverInterface
{   
    use Driver;

    /**
     * Options
     *
     * @var array
     */
    protected $options;

    /**
     * Chekout gateway 
     *
     * @var object
     */
    protected $gateway;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setDriverParams('paypal-express','checkout','Paypal Express Checkout','Driver for Paypal express checkout.');
    }

    /**
     * Initialize driver
     *
     * @return void
     */
    public function initDriver($properties)
    {       
        $config = $properties->getValues(); 
        $mode = $config['mode'] ?? 'sandbox';

        if ($mode == 'live') {
            $userName = $properties->getValueAsText('username','live');
            $password = $properties->getValueAsText('password','live');
            $signature = $properties->getValueAsText('signature','live');
        } else {
            $userName = $properties->getValueAsText('username','sandbox');
            $password = $properties->getValueAsText('password','sandbox');
            $signature = $properties->getValueAsText('signature','sandbox');
        }

        $this->gateway = Omnipay::create('PayPal_Express');

        $this->gateway->initialize([
            'username'  => $userName,
            'password'  => $password,          
            'testMode'  => ($mode == 'sandbox'),
            'signature' => $signature
        ]);     

        $this->gateway->setParameter('returnUrl',Url::BASE_URL . $config['return_url'] . $this->getDriverName() . '/');
        $this->gateway->setParameter('cancelUrl',Url::BASE_URL . $config['cancel_url']);
        $this->gateway->setParameter('notifyUrl',Url::BASE_URL . $config['notify_url']);
    }

    /**
     * Checkout rquest
     *
     * @param CheckoutData $data
     * @return object|null
     */
    public function checkout($data)
    {
        $response = $this->gateway->purchase([
            'amount'        => $data->getAmount(),  
            'currency'      => $data->getCurrency(),
            'transactionId' => $data->getOrderId()          
        ])->send();

        $token = $response->getTransactionReference();
        $data->save($token);

        return $response;
    }

    /**
     * Create transaction obj ref
     *
     * @param array $params
     * @return TransactionInterface|null
     */
    public function completeCheckout(array $params)
    {
        $token = $params['token'] ?? null;
        if (empty($token) == true) {
            return null;
        }

        $checkout = CheckoutData::get($token);

        $response = $this->gateway->completePurchase([
            'token'         => $token,
            'amount'        => $checkout->getAmount(),
            'currency'      => $checkout->getCurrency(),
            'transactionId' => $checkout->getOrderId()     
        ])->send();
       
        $transactionId = $response->getTransactionReference();
        if (empty($transactionId) == true) {
            return null;
        }

        $trResponse = $this->gateway->fetchTransaction(['transactionReference' => $transactionId])->send();
        $data = $trResponse->getData();

        $transaction = new Transaction(
            $transactionId,
            $data['EMAIL'] ?? null,
            $data['FIRSTNAME'] . ' ' . $data['LASTNAME'],
            $data['AMT'] ?? $data['L_AMT0'],
            $data['CURRENCYCODE'] ?? $data['currency_code'],
            $data['TRANSACTIONTYPE'] ?? Transaction::CHECKOUT,
            $this->getDriverName(),
            $data           
        );

        $status = $this->resolveTransactionStatus($data);
        $transaction->setStatus($status);
        $transaction->setOrderId($data['INVNUM'] ?? null);

        return $transaction;
    }

    /**
     * Resolve transaction status
     *
     * @param mixed $data
     * @return integer
     */
    public function resolveTransactionStatus($data): int
    {
        $status = $data['PAYMENTSTATUS'] ?? null;

        return ($status == 'Completed') ? TransactionInterface::STATUS_COMPLETED : TransactionInterface::STATUS_ERROR;
    }

    /**
     * Get checkout gateway
     *
     * @return mixed
     */
    public function getGateway()
    {
        return $this->gateway;
    }
    
    /**
     * Create driver config properties array
     *
     * @param Arikaim\Core\Collection\Properties $properties
     * @return Properties
     */
    public function createDriverConfig($properties)
    {
        $properties->property('mode',function($property) {
            $property
                ->title('Mode')
                ->type('list')
                ->items(['sandbox','live'])
                ->required(true)
                ->default('sandbox');             
        });

        // Sandbox Credentials
        $properties->property('sandbox_group',function($property) {
            $property
                ->title('Sandbox API Credentials')
                ->type('group')
                ->required(true)              
                ->default('sandbox');               
        });

        $properties->property('username',function($property) {
            $property
                ->title('User Name')
                ->type('text')
                ->group('sandbox')
                ->default('');              
        });
       
        $properties->property('password',function($property) {
            $property
                ->title('Password')
                ->type('text')
                ->group('sandbox')
                ->default('');               
        });

        $properties->property('signature',function($property) {
            $property
                ->title('Signature')
                ->type('text')
                ->group('sandbox')
                ->default('');             
        });

        // Live Credentials
        $properties->property('live_group',function($property) {
            $property
                ->title('Live API Credentials')
                ->type('group')
                ->required(true)              
                ->default('live');               
        });

        $properties->property('username',function($property) {
            $property
                ->title('User Name')
                ->type('text')
                ->group('live')
                ->default('');              
        });

        $properties->property('password',function($property) {
            $property
                ->title('Password')
                ->type('text')
                ->group('live')
                ->default('');             
        });

        $properties->property('signature',function($property) {
            $property
                ->title('Signature')
                ->type('text')
                ->group('live')
                ->default('');             
        });

        $properties->property('cancel_url',function($property) {
            $property
                ->title('Cancel Url')
                ->type('url')
                ->readonly(true)
                ->default('/checkout/cancel/');
        });

        $properties->property('return_url',function($property) {
            $property
                ->title('Success Url')
                ->type('url')
                ->readonly(true)
                ->default('/checkout/success/');
        });

        $properties->property('notify_url',function($property) {
            $property
                ->title('Notify Url')
                ->type('url')
                ->readonly(true)
                ->default('/api/checkout/notify');
        });
    } 
}
