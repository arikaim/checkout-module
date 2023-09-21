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
use Arikaim\Modules\Checkout\CheckoutResponse;
use Arikaim\Modules\Checkout\Interfaces\CheckoutDriverInterface;
use Arikaim\Modules\Checkout\Interfaces\TransactionInterface;
use Arikaim\Core\Interfaces\Content\ContentItemInterface;

/**
 * PayPal checkout driver class
 */
class PayPalExpressCheckoutDriver implements DriverInterface, CheckoutDriverInterface
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
     * Set return url
     *
     * @var string|null
     */
    protected $returnUrl;

    /**
     * Set cancel url
     *
     * @var string|null
     */
    protected $cancelUrl;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setDriverParams(
            'paypal-express',
            'checkout',
            'Paypal Express Checkout',
            'Driver for Paypal express checkout.'
        );
    }

    /**
     * Get import customer address content type action name
     *
     * @return string|null
     */
    public function getImportCustomerAddressAction(): ?string
    {
        return 'address.import.paypal';
    }

    /**
     * Get import customer content type action name
     *
     * @return string|null
     */
    public function getImportCustomerAction(): ?string
    {
        return 'entity.import.paypal';
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

        $this->returnUrl = $config['return_url'];
        $this->cancelUrl = $config['cancel_url'];
        $this->gateway->setParameter('returnUrl',Url::BASE_URL . $config['return_url'] . $this->getDriverName() . '/');
        $this->gateway->setParameter('cancelUrl',Url::BASE_URL . $config['cancel_url']);
        $this->gateway->setParameter('notifyUrl',Url::BASE_URL . $config['notify_url']);
    }

    /**
     * Checkout rquest
     *
     * @param ContentItemInterface $data
     * @return object|null
     */
    public function checkout(ContentItemInterface $data)
    {
        $extensionName = $data->getValue('extension','all');
        $this->gateway->setParameter('returnUrl',Url::BASE_URL . $this->returnUrl . $this->getDriverName() . '/' . $extensionName . '/');
        $this->gateway->setParameter('cancelUrl',Url::BASE_URL . $this->cancelUrl . $extensionName . '/');

        $response = $this->gateway->purchase([
            'amount'        => $data->getValue('amount'),  
            'currency'      => $data->getValue('currency'),
            'description'   => $data->getValue('description'),
            'transactionId' => $data->getValue('order_id')         
        ])->send();

        $checkoutResponse = new CheckoutResponse();            
        $data = $response->getData();

        $checkoutResponse->setRedirectUrl($response->getRedirectUrl());
        if ($response->isSuccessful() == false) {
            $checkoutResponse->setError($response->getMessage());
        }
      
        $checkoutResponse->setToken($data['TOKEN'] ?? null);
    
        return $checkoutResponse;
    }

    /**
     * Create transaction obj ref
     *
     * @param ContentItemInterface $params
     * @return TransactionInterface|null
     */
    public function completeCheckout(ContentItemInterface $data)
    {
        $response = $this->gateway->completePurchase([
            'token'         => $data->getValue('token'),
            'amount'        => $data->getValue('amount'),
            'currency'      => $data->getValue('currency'),
            'description'   => $data->getValue('description'),
            'transactionId' => $data->getValue('order_id')  
        ])->send();
       
        $transactionId = $response->getTransactionReference();
        if (empty($transactionId) == true) {
            return null;
        }

        $trResponse = $this->gateway->fetchTransaction(['transactionReference' => $transactionId])->send();
        
        $data = $trResponse->getData();
        $firstName = $data['FIRSTNAME'] ?? null;
        $lastName = $data['LASTNAME'] ?? null;
        $amount =  $data['AMT'] ?? $data['L_AMT0'] ?? null;
        $currency = $data['CURRENCYCODE'] ?? $data['currency_code'] ?? null;
        $type = $data['TRANSACTIONTYPE'] ?? Transaction::CHECKOUT;
        if (empty($amount) == true || $amount == 0) {
            // not valid
            return null;
        }

        $transaction = new Transaction(
            $transactionId,
            $data['EMAIL'] ?? null,
            $firstName . ' ' . $lastName,
            $amount,
            $currency,
            $type,
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
     * @return void
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
                ->type('key')
                ->group('live')
                ->default('');             
        });

        $properties->property('signature',function($property) {
            $property
                ->title('Signature')
                ->type('key')
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
