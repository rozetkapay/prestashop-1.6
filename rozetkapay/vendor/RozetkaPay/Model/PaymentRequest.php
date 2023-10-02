<?php

namespace Payment\RozetkaPay\Model;

use Payment\RozetkaPay\Model\Model;
use Payment\RozetkaPay\Model\RequestUserDetails;

class PaymentRequest extends Model{
    
    /**
     * 
     * @var string
     */
    public $external_id = '';
    
    /**
     * 
     * @var float
     */
    public $amount = 0;
    
    /**
     * 
     * @var string
     */
    public $callback_url = '';
    
    /**
     * 
     * @var string
     */
    public $currency = 'UAH';
        
    /**
     * 
     * @var string
     */
    public $payload = '';
    
    /**
     * 
     * @var Payment\RozetkaPay\Model\RequestUserDetails
     */
    public $recipient;
    
    public function __construct($data = []) {
        
        parent::__construct($data);
        
        
        if(isset($data['recipient']) && !empty($data['recipient'])){            
            $this->recipient = new RequestUserDetails($data['recipient']);            
        }
        
    }
    
    
    
}
