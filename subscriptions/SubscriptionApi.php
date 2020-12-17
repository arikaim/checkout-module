<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Modules\Checkout\Subscriptions;

/**
 * Subscription api base class
 */
class SubscriptionApi
{   
    /**
     * Api client
     *
     * @var mixed
     */
    protected $apiClient;

    /**
     * Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Constructor
     * 
     * @param mixed $apiClient 
     */
    public function __construct($apiClient, array $options = [])
    {
        $this->apiClient = $apiClient;
        $this->options = $options;      
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get api client
     *
     * @return mixed
     */
    public function getApiClient()
    {
        return $this->apiClient;
    }

    /**
     * Get option value
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }
}
