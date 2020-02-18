<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Modules\Checkout;

/**
 * Checkout interface
 */
interface CheckoutInterface 
{  
    /**
     * Set currency
     *
     * @param mixed $currency
     * @return void
     */
    public function setCurrency($currency);

    /**
     * Set api credentials 
     *
     * @param array $credentials
     * @return void
     */
    public function setCredentials($credentials);

    /**
     * Set checkout data
     *
     * @param array $data
     * @return mixed
     */
    public function setCheckoutData($data);

    /**
     * Get checkout link
     *
     * @param mixed $data
     * @return string
     */
    public function getCheckoutLink();
}
