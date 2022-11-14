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
use Stripe\Stripe;

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
 * Stripe checkout driver class
 */
class StripeCheckoutDriver implements DriverInterface, CheckoutDriverInterface
{   
    use Driver;

    /**
     * Options
     *
     * @var array
     */
    protected $options;

    /**
     * Stripe client 
     *
     * @var object
     */
    protected $client;

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
            'stripe-checkout',
            'checkout',
            'Stripe Checkout',
            'Driver for Stripe checkout.'
        );
    }

    /**
     * Get import customer address content type action name
     *
     * @return string|null
     */
    public function getImportCustomerAddressAction(): ?string
    {
        return 'address.import.stripe';
    }

    /**
     * Get import customer content type action name
     *
     * @return string|null
     */
    public function getImportCustomerAction(): ?string
    {
        return 'entity.import.stripe';
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
        $this->returnUrl = $config['return_url'];
        $this->cancelUrl = $config['cancel_url'];

        $publicKey = $properties->getValueAsText('public_key',$mode);
        $privateKey = $properties->getValueAsText('private_key',$mode);
        
        Stripe::setApiKey($privateKey);

        $this->client = new StripeClient($privateKey);
    }

    /**
     * Checkout rquest
     *
     * @param ContentItemInterface $data
     * @return object|null
     */
    public function checkout(ContentItemInterface $data)
    {
        $unitPrice = \round((float)$data->getValue('amount',0) * 100,0);
        $description = $data->getValue('description');
        $extensionName = $data->getValue('extension','all');
        $successUrl = Url::BASE_URL . $this->returnUrl . $this->getDriverName() . '/' . $extensionName . '/?token={CHECKOUT_SESSION_ID}';
        $cancelUrl = Url::BASE_URL . $this->cancelUrl . $extensionName . '/';
    
        $sessionData = [
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $data->getValue('currency'),
                        'product_data' => [
                            'name' => (empty($description) == true) ? 'Sales Order' : $description,
                        ],
                        'unit_amount' => $unitPrice,
                    ],                   
                    'quantity'  => 1,
                ]
            ],
            'mode'        => 'payment',
            'metadata'    => [
                'order_id' => $data->getValue('order_id',null)
            ],
            'success_url' => $successUrl,
            'cancel_url'  => $cancelUrl
        ];

        $vendorFee = (float)$data->getValue('vendor_fee');
        $vendorFee = \round(($vendorFee * 100),0);

        $vendorAccount = $data->getValue('vendor_account');
        if (empty($vendorFee) == false && empty($vendorAccount) == false) {
            // add vendor fee checkout data
            $sessionData['payment_intent_data'] = [
                'application_fee_amount' => $vendorFee,
                'transfer_data' => [
                    'destination' => $vendorAccount
                ]
            ];
        }

        $response = \Stripe\Checkout\Session::create($sessionData);
        $checkoutResponse = new CheckoutResponse();            
        $checkoutResponse->setRedirectUrl($response->url);
       
        $checkoutResponse->setToken($response->id ?? null);
    
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
        $session = $this->client->checkout->sessions->retrieve($data->getValue('token'),[]);
        $amount = \round((int)$session->amount_total / 100,2);
      
        $transaction = new Transaction(
            $session->id,
            $session->customer_details->email,
            $session->customer_details->name,         
            $amount,
            $session->currency,
            Transaction::CHECKOUT,
            $this->getDriverName(),
            $session->toArray()           
        );
      
        $status = ($session->payment_status == 'paid') ? TransactionInterface::STATUS_COMPLETED : TransactionInterface::STATUS_ERROR;
        $transaction->setStatus($status);

        $orderId = $session->metadata->order_id ?? null;
        if (empty($orderId) == false) {
            $transaction->setOrderId($orderId);
        }
       
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
        return $this->client;
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
        // Test mode public key
        $properties->property('public_key',function($property) {
            $property
                ->title('Public Key')
                ->type('text')
                ->group('test')
                ->default('');              
        });
        // Test mode private key
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
        // Live mode private key
        $properties->property('private_key',function($property) {
            $property
                ->title('Private Key')
                ->type('text')
                ->group('live')
                ->default('');              
        });
        // Live mode public key
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
