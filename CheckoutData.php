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

use Arikaim\Core\Http\Session;

/**
 * CheckoutData class
 */
class CheckoutData 
{   
    /**
     * Amount paid
     *
     * @var float
     */
    protected $amount = 0.00;

    /**
     * Currency code
     *
     * @var string
     */
    protected $currency;

    /**
     * Order id
     *
     * @var string|null
     */
    protected $orderId = null;

    /**
    * Constructor
    *
    * @param float $amount
    * @param string $currency
    * @param mixed $orderId
    */
    public function __construct($amount, string $currency, ?string $orderId)
    {
       $this->amount = $amount;
       $this->currency = $currency;
       $this->orderId = $orderId;
    }

    /**
     * Get checkout data
     *
     * @param string|null $id
     * @return Self|null
     */
    public static function get(?string $id)
    {
        $data = (empty($id) == true) ? null : Session::get($id,null);

        return new Self($data['amount'],$data['currency'],$data['order_id']);
    }

    /**
     * Create checkout data 
     *
     * @param float $amount
     * @param string $currency
     * @param string|null $orderId
     * @return void
     */
    public static function create($amount, string $currency, ?string $orderId)
    {
        return new Self($amount,$currency,$orderId);
    }

    /**
     * Save to session var
     *
     * @param string $id
     * @return void
     */
    public function save(string $id): void
    {
        Session::set($id,$this->toArray());       
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'amount'   => $this->amount,
            'currency' => $this->currency,
            'order_id' => $this->orderId
        ];
    }

    /**
     * Set order id
     *
     * @param string|null $id
     * @return void
     */
    public function setOrderId(?string $id): void
    {
        $this->orderId = $id;
    }

    /**
     * Get order id
     *
     * @return string|null
     */
    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    /**
     * Get amount
     *
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }
}
