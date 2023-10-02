<?php

namespace Payment\RozetkaPay\Model;

class Customer {
    
    /**
     * 
     * @var string
     */
    public $color_mode = "light";
    
    /**
     * 
     * @var string
     */
    public $locale = "";
    
    /**
     * 
     * @var string
     */
    public $account_number = "";
    
    /**
     * 
     * @var string
     */
    public $address = "";
    
    /**
     * 
     * @var string
     */
    public $city = "";
    
    /**
     * 
     * @var string
     */
    public $country = "";
    
    /**
     * 
     * @var string
     */
    public $email = "";
    
    /**
     * 
     * @var string
     */
    public $external_id = "";
    
    /**
     * 
     * @var string
     */
    public $first_name = "";
    
    /**
     * 
     * @var string
     */
    public $last_name = "";
    
    /**
     * 
     * @var string
     */
    public $patronym = "";
    
    /**
     * 
     * @var 
     */
    public $payment_method;
    
    /**
     * 
     * @var string
     */
    public $phone = "";
    
    /**
     * 
     * @var string
     */
    public $postal_code = "";
    
}
