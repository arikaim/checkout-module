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

use Arikaim\Core\Extension\Module;

/**
 * Checkout module class
 */
class Checkout extends Module
{   
    /**
     * Install module
     *
     * @return void
     */
    public function install()
    {    
        $this->installDriver('Arikaim\\Modules\\Checkout\\Drivers\\PayPalSubscriptionsDriver'); 
        $this->installDriver('Arikaim\\Modules\\Checkout\\Drivers\\PayPalExpressCheckoutDriver'); 
        $this->installDriver('Arikaim\\Modules\\Checkout\\Drivers\\StripeCheckoutDriver'); 
    }
}
