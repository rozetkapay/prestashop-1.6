<?php

namespace Payment\RozetkaPay\Model;

class TransactionDetails extends \Payment\RozetkaPay\Model\Model  {
    
    /**
     * 
     * @var float
     */
    public $amount;
    
    /**
     * 
     * @var string
     */
    public $billing_order_id;
    
    /**
     * 
     * @var string <date-time>
     */
    public $created_at;
    
    /**
     * 
     * @var string
     */
    public $currency;
    
    /**
     * 
     * @var string
     */
    public $description;
    
    /**
     * 
     * @var string
     */
    public $gateway_order_id;
    
    /**
     * 
     * @var string
     */
    public $payload;
    
    /**
     * 
     * @var string
     */
    public $action_required;
    
    /**
     * 
     * @var string <date-time>
     */
    public $payment_id;
    
    /**
     * 
     * @var object
     */
    public $properties;
    
    /**
     * 
     * @var string
     */
    public $rnn;
    
    /**
     * 
     * @var Enum:Payment\RozetkaPay\Model\OperationStatus
     */
    public $status;
    
    /**
     * 
     * @var string
     */
    public $status_code;    
    
    /**
     * 
     * @var string
     */
    public $status_description;    
    
    /**
     * 
     * @var string
     */
    public $transaction_id;
    
}
