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
 * Checkout response class
 */
class CheckoutResponse
{   
    /**
     * Error
     *
     * @var string|null
     */
    protected $error = null;

    /**
     * Redirect url
     *
     * @var string|null
     */
    protected $redirectUrl = null;

    /**
     * Get token
     *
     * @var string|null
     */
    protected $token = null;

    public function hasError(): bool
    {
        return !empty($error);
    }

    public function toArray(): array 
    {
        return [
            'redirect'      => $this->isRedirect(),
            'redirect_url'  => $this->redirectUrl,
            'token'         => $this->token
        ];
    }

    public function isRedirect(): bool
    {
        return (empty($this->getRedirectUrl()) == false);
    }

    public function getRedirectUrl(): ?string 
    {
        return $this->redirectUrl;
    }

    public function getToken(): ?string 
    {
        return $this->token;
    }

    public function setRedirectUrl(string $url): void
    {
        $this->redirectUrl = $url;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    public function setError(?string $error): void
    {
        $this->error = $error;
    }
}
