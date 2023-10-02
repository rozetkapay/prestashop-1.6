<?php

namespace Payment\RozetkaPay\Model;
class PaymentsResponsesError extends \Payment\RozetkaPay\Model\Model {
    
    /**
     * 
     * @var string
     */
    public $code;
    
    /**
     * 
     * @var string
     */
    public $message;
    
    /**
     * 
     * @var string
     */
    public $param;
    
    /**
     * 
     * @var string
     */
    public $payment_id;
        
    /**
     * 
     * @var Payment\RozetkaPay\Model\ErrorType
     */
    public $type;
    
    public function __construct($data = []) {
        parent::__construct($data);
        
//        if(isset($data['type'])){            
//            $this->type = \Payment\RozetkaPay\Model\ErrorType::api_error;      
//        }
    }
    
}
