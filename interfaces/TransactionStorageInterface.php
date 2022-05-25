<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Modules\Checkout\Interfaces;

use Arikaim\Modules\Checkout\Interfaces\TransactionInterface;

/**
 * Transaction storage interface
 */
interface TransactionStorageInterface 
{  
    /**
     * Save transaction
     *
     * @param TransactionInterface $transaction
     * @param int|null $userId
     * @return boolean
    */
    public function saveTransaction(TransactionInterface $transaction, ?int $userId = null): bool;

    /**
     * Get transaction
     *
     * @param string $id
     * @return mixed
    */
    public function getTransaction($id);
}
