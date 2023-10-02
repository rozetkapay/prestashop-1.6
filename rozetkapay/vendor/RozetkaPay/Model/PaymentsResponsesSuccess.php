<?php

namespace Payment\RozetkaPay\Model;

use Payment\RozetkaPay\Model\Model;
use Payment\RozetkaPay\Model\UserAction;

class PaymentsResponsesSuccess extends Model {
    
    /**
     * 
     * @var Payment\RozetkaPay\Model\UserAction
     */
    public $action;
    
    /**
     * 
     * @var bool
     */
    public $action_required;
    
    /**
     * 
     * @var Payment\RozetkaPay\Model\TransactionDetails
     */
    public $details;
    
    /**
     * 
     * @var string
     */
    public $external_id;
    
    /**
     * 
     * @var string
     */
    public $id;
    
    /**
     * 
     * @var bool
     */
    public $is_success;
    
    
    /**
     * 
     * @var bool
     */
    public $receipt_url;
    
    public function __construct($data = []) {
        parent::__construct($data);
        
        if(isset($data['action']) && !empty($data['action'])){            
            $this->action = new UserAction($data['action']);            
        }
        
        if(isset($data['details']) && !empty($data['details'])){            
            $this->details = new \Payment\RozetkaPay\Model\TransactionDetails($data['details']);            
        }
    }
    
    
}
