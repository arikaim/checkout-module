<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Modules\Checkout\Driver;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Driver\Traits\Driver;
use Arikaim\Core\Interfaces\Driver\DriverInterface;
use Arikaim\Modules\Checkout\CheckoutInterface;
use Arikaim\Core\Http\Url;

/**
 * PayPal driver class
 */
class PayPalCheckoutDriver implements DriverInterface, CheckoutInterface
{   
    use Driver;

    /**
     * Checkout data
     *
     * @var array
     */
    protected $data;

    /**
     * Checkout config
     *
     * @var array
     */
    protected $config;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setDriverParams('paypal','checkout','Paypal checkout','Driver for Paypal payments.');
    }

    /**
     * Initialize driver
     *
     * @return void
     */
    public function initDriver($properties)
    {
        $this->instance = new \Srmklive\PayPal\Services\ExpressCheckout();  
        $this->config = $properties->getValues(); 

        if (isset($this->config['notify_url']) == true) {
            $this->config['notify_url'] = Url::BASE_URL . $this->config['notify_url'];
        } 
    
        $this->instance->setApiCredentials($this->config);       
    }

    /**
     * Set currency
     *
     * @param mixed $currency
     * @return void
     */
    public function setCurrency($currency)
    {
        $this->instance->setCurrency($currency);
    }

    /**
     * Set api credentials 
     *
     * @param array $credentials
     * @return void
     */
    public function setCredentials($credentials)
    {
        $this->instance->setApiCredentials($credentials);
    }

    /**
     * Set checkout data
     *
     * @param array $data
     * @return mixed
     */
    public function setCheckoutData($data)
    {
        $this->data = $data;

        $this->data['return_url'] = Url::BASE_URL . $this->config['return_url'];
        $this->data['cancel_url'] = Url::BASE_URL . $this->config['cancel_url'];
        $this->data['currency'] = $this->config['currency'];

        return $this->instance->setExpressCheckout($this->data);
    }

    /**
     * Get checkout link
     *
     * @param mixed $data
     * @return string
     */
    public function getCheckoutLink()
    {
        $response = $this->setCheckoutData($this->data);

        return $response['paypal_link'];
    }

    /**
     * Get checkout config
     *
     * @return mixed
     */
    protected function getCredentials()
    {
        return $this->config;
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

        $properties->property('currency',function($property) {
            $property
                ->title('Currency')
                ->type('text')
                ->required(true)
                ->default('USD');               
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
                ->title('Api Username')
                ->type('text')
                ->group('sandbox')
                ->default('');              
        });
       
        $properties->property('password',function($property) {
            $property
                ->title('Api password')
                ->type('text')
                ->group('sandbox')
                ->default('');               
        });

        $properties->property('secret',function($property) {
            $property
                ->title('Api secret')
                ->type('text')
                ->group('sandbox')
                ->default('');             
        });

        $properties->property('certificate',function($property) {
            $property
                ->title('Api certificate')
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
                ->title('Api Username')
                ->type('text')
                ->group('live')
                ->default('');              
        });

        $properties->property('password',function($property) {
            $property
                ->title('Api password')
                ->type('text')
                ->group('live')
                ->default('');               
        });

        $properties->property('secret',function($property) {
            $property
                ->title('Api secret')
                ->type('text')
                ->group('live')
                ->default('');             
        });

        $properties->property('certificate',function($property) {
            $property
                ->title('Api certificate')
                ->type('text')
                ->group('live')
                ->default('');               
        });

        $properties->property('payment_action',function($property) {
            $property
                ->title('Payment action')
                ->type('list')
                ->items(['Sale','Authorization','Order'])
                ->default('Sale')
                ->required(true);
        });
        
        $properties->property('cancel_url',function($property) {
            $property
                ->title('Cancel Url')
                ->type('url')
                ->readonly(true)
                ->default('/api/checkout/cancel');
        });

        $properties->property('return_url',function($property) {
            $property
                ->title('Success Url')
                ->type('url')
                ->readonly(true)
                ->default('/api/checkout/success');
        });

        $properties->property('notify_url',function($property) {
            $property
                ->title('Notify Url')
                ->type('url')
                ->readonly(true)
                ->default('/api/checkout/notify');
        });

        $properties->property('validate_ssl',function($property) {
            $property
                ->title('Validate SSL')
                ->type('boolean')
                ->readonly(true)
                ->default(true);
        });
    } 
}
