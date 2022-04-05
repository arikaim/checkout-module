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

    /**
     * Return true if has error
     *
     * @return boolean
     */
    public function hasError(): bool
    {
        return !empty($this->error);
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array 
    {
        return [
            'redirect'      => $this->isRedirect(),
            'redirect_url'  => $this->redirectUrl,
            'token'         => $this->token
        ];
    }

    /**
     * Return true if has redirect url
     *
     * @return boolean
     */
    public function isRedirect(): bool
    {
        return (empty($this->getRedirectUrl()) == false);
    }

    /**
     * Get redirect url
     *
     * @return string|null
     */
    public function getRedirectUrl(): ?string 
    {
        return $this->redirectUrl;
    }

    /**
     * Get checkout token
     *
     * @return string|null
     */
    public function getToken(): ?string 
    {
        return $this->token;
    }

    /**
     * Set redirect url
     *
     * @param string $url
     * @return void
     */
    public function setRedirectUrl(string $url): void
    {
        $this->redirectUrl = $url;
    }

    /**
     * Set token
     *
     * @param string|null $token
     * @return void
     */
    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    /**
     * Set error
     *
     * @param string|null $error
     * @return void
     */
    public function setError(?string $error): void
    {
        $this->error = $error;
    }

    /**
     * Get error message
     *
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }
}
