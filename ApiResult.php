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
 * ApiResult class
 */
class ApiResult
{   
    /**
     * Result data
     *
     * @var array
     */
    protected $result = [];

    /**
     * Constructor
     * 
     * @param string $status
     * @param mixed $result
     * @param string|null $error
     * @param array|null $errorDetails
     */
    public function __construct($status = 'success', $result = [], $error = null, $errorDetails = null)
    {
        $this->result = [
            'status'        => $status,
            'result'        => $result,
            'error'         => $error,
            'error_details' => $errorDetails
        ];
    }

    /**
     * Create success result
     *
     * @param mixed $result
     * @return ApiResult
     */
    public static function success($result)
    {
        return new Self('success',$result);
    } 

    /**
     * Create error result
     *
     * @param string $error
     * @param array|string $errorDetails
     * @return ApiResult
     */
    public static function error($error, $errorDetails)
    {
        if (\is_string($errorDetails) == true) {
            $errorDetails = \json_decode($errorDetails,true);
        }
     
        return new Self('error',null,$error,$errorDetails);            
    }

    /**
     * Return true if result has error
     *
     * @return boolean
     */
    public function hasError()
    {
        return ($this->getStatus() != 'success');
    }

    /**
     * Return true if request status is success
     *
     * @return boolean
     */
    public function hasSuccess()
    {
        return ($this->getStatus() == 'success');
    }

    /**
     * Get api result status
     *
     * @return string|null
     */
    public function getStatus() 
    {
        return $this->result['status'] ?? null;
    }

    /**
     * Get result data
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result['result'] ?? null;
    }

    /**
     * Get resutl item value
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getResultItem($name, $default = null)
    {
        return $this->result['result'][$name] ?? $default;
    }

    /**
     * Get error message
     *
     * @return string
     */
    public function getError()
    {
        return $this->result['error'] ?? null;
    }

    /**
     * Get error details
     *
     * @return mixed
     */
    public function getErrorDetails()
    {
        return $this->result['error_details'] ?? null;
    }

    /**
     * Get resutl as array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->result;
    }
}
