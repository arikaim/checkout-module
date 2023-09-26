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
 * Square checkout (quick pay) driver class
 */
class SquareCheckoutDriver implements DriverInterface, CheckoutDriverInterface
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
            'square-checkout',
            'checkout',
            'Square Checkout',
            'Driver for Square checkout.'
        );
    }

    /**
     * Get import customer address content type action name
     *
     * @return string|null
     */
    public function getImportCustomerAddressAction(): ?string
    {
        return 'address.import.square';
    }

    /**
     * Get import customer content type action name
     *
     * @return string|null
     */
    public function getImportCustomerAction(): ?string
    {
        return 'entity.import.square';
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

        $accessToken = $properties->getValueAsText('access_token',$mode);
       
       // $this->client = new StripeClient($privateKey);
    }

    /**
     * Checkout rquest
     *
     * @param ContentItemInterface $data
     * @return object|null
     */
    public function checkout(ContentItemInterface $data)
    {
        $checkoutResponse = new CheckoutResponse();            
        //$checkoutResponse->setRedirectUrl($response->url);
       
       // $checkoutResponse->setToken($response->id ?? null);
    
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
        // Test mode access key
        $properties->property('access_token',function($property) {
            $property
                ->title('Access Token')
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
        // Live mode access key
        $properties->property('access_token',function($property) {
            $property
                ->title('Access Token')
                ->type('key')
                ->group('live')
                ->default('');              
        });
    } 
}
