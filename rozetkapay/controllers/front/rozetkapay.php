<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);
include_once __dir__ . '/../../vendor/RozetkaPay/autoloader.php';
include_once __dir__ . '/log.php';

class RozetkaPayRozetkaPayModuleFrontController extends ModuleFrontController {

    public $ssl = true;
    public $display_column_left = false;
    private $settingList = [
        'login' => '',
        'password' => '',
        'qr_code' => 0,
        'send_info_customer_status' => 1,
        'send_info_product_status' => 1,
        'order_status_init' => 0,
        'order_status_pending' => 0,
        'order_status_success' => 12,
        'order_status_failure' => 8,
        'view_title_default' => 1,
        'view_icon_status' => 1,
        'sandbox_status' => 0,
        'log_status' => 0,
    ];
    private $rpay;
    private $langCode;
    private $languages = [];
    
    private $extlog = false;

    public function __construct() {
        parent::__construct();
        $this->bootstrap = true;
        $this->rpay = new \Payment\RozetkaPay\RozetkaPay();

        $this->langCode = Context::getContext()->language->iso_code;

        if ($this->langCode == "ru") {
            $this->langCode = "uk";
        }
        
        if (Configuration::get('ROZETKAPAY_LOG_STATUS') === "1") {
            
            $this->extlog = new \Log('rozetkapay');
            
        }
        
        $this->loadLanguages();
    }

    public function initContent() {
        parent::initContent();
        

        $this->loadLanguages();

        $action = Tools::getValue('action');
        
        if ($action == "creatPay") {
            $this->creatPay();
            return;
        }
        
        if ($action == "result") {
            $this->result();
            return;
        }
        
        if ($action == "callback") {
            $this->callback();
            return;
        }
        
        $cart = $this->context->cart;

        $this->context->smarty->assign(array(
            'nbProducts' => $cart->nbProducts(),
            'cust_currency' => $cart->id_currency,
            'currencies' => $this->module->getCurrency((int) $cart->id_currency),
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
            'this_path' => $this->module->getPathUri(),
            'this_path_bw' => $this->module->getPathUri(),
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/',
            'urlCreatPay' => Context::getContext()->link->getModuleLink('rozetkapay', 'rozetkapay',
                    ['action' => 'creatPay', 'id_cart' => $cart->id,]),
            'pageType' => 'comfire',
            'text_payment_execution_h3' => $this->languages['text_payment_execution_h3'],
        ));

        $this->context->smarty->assign('showIcon', Configuration::get('ROZETKAPAY_VIEW_ICON_STATUS') == "1");
        $this->context->smarty->assign('text_title', $this->getTitle());

        $this->context->smarty->force_compile = true;
        $this->setTemplate('payment_execution.tpl');
    }

    public function creatPay() {

        $idCart = Tools::getValue('id_cart');

        if (!empty($idCart)) {
            $this->module->validateOrder(intval($this->context->cart->id), _PS_OS_PREPARATION_, $this->context->cart->getOrderTotal(), 'RozetkaPay');
            Tools::redirect(Context::getContext()->link->getModuleLink('rozetkapay', 'rozetkapay',
                            ['action' => 'creatPay', 'id_order' => $this->module->currentOrder]));
            return;
        }
        
        
        $idOrder = Tools::getValue('id_order');
        
        if(empty($idOrder)){
            return 'fatal';
        }
        
        $orderInfo = new OrderCore($idOrder);
        
        if (Configuration::get('ROZETKAPAY_SANDBOX_STATUS') === "1") {
            $this->rpay->setBasicAuthTest();
            $orderInfo->id = $orderInfo->id . "_" . md5($_SERVER['HTTP_HOST']);
        } else {
            $this->rpay->setBasicAuth(Configuration::get('ROZETKAPAY_LOGIN'), Configuration::get('ROZETKAPAY_PASSWORD'));
        }

        $this->rpay->setResultURL(Context::getContext()->link->getModuleLink('rozetkapay', 'rozetkapay', 
                ['action' => 'result', 'id_order' => $idOrder]));
        $this->rpay->setCallbackURL(Context::getContext()->link->getModuleLink('rozetkapay', 'rozetkapay', 
                ['action' => 'callback', 'id_order' => $idOrder]));

        $total = $orderInfo->total_paid;

        $currency = new CurrencyCore($orderInfo->id_currency);
        $currencyCode = $currency->iso_code;

        $dataCheckout = new \Payment\RozetkaPay\Model\PaymentCreateRequest();
        if ($currencyCode != "UAH") {
            $total = Tools::convertPrice($total, $currencyCode, "UAH");
            $currencyCode = "UAH";
        }

        $dataCheckout->amount = $total;
        $dataCheckout->external_id = $orderInfo->id;
        $dataCheckout->currency = $currencyCode;

        $language = Language::getIsoById(intval($orderInfo->id_lang));
        $language = (!in_array($language, array('uk', 'en'))) ? 'uk' : $language;
        $language = strtoupper($language);

        if (Configuration::get('ROZETKAPAY_SEND_INFO_CUSTOMER_STATUS') == "1") {

            $address = new AddressCore($orderInfo->id_address_invoice);

            if ($address) {

                $customerNew = new \Payment\RozetkaPay\Model\Customer();

                $customer = new CustomerCore($address->id_customer);

                $countrys = Country::getCountries($orderInfo->id_lang);

                if (isset($countrys[$address->id_country])) {
                    $customerNew->country = $countrys[$address->id_country]['iso_code'];
                }

                $customerNew->first_name = $address->firstname;
                $customerNew->last_name = $address->lastname;
                $customerNew->phone = $address->phone_mobile;
                $customerNew->email = $customer->email;
                $customerNew->city = $address->city;
                $customerNew->postal_code = $address->postcode;
                $customerNew->address = $address->address1 . ' ' . $address->address2;

                $dataCheckout->customer = $customer;
            }
        }

        if (Configuration::get('ROZETKAPAY_SEND_INFO_PRODUCT_STATUS') == "1") {

            foreach ($orderInfo->getProducts() as $product) {

                $productNew = new \Payment\RozetkaPay\Model\Product();

                $productPrices[] = $product['total_wt'];
                $productQty[] = $product['quantity'];

                $productNew->id = $product['product_id'];
                $productNew->name = $product['product_name'];

                $productNew->quantity = $product['product_quantity'];
                $productNew->net_amount = $product['product_price_wt'];
                $productNew->vat_amount = $product['total_wt'];

                $productNew->url = Context::getContext()->link->getProductLink($product);

                $dataCheckout->products[] = $productNew;
            }
        }

        list($result, $error) = $this->rpay->paymentCreate($dataCheckout);

        $isPay = false;
        $message = "";
        $urlPay = '';
        $payQRcode = "";
        if ($error === false && isset($result->is_success)) {
            if (isset($result->action) && $result->action->type == "url") {
                $urlPay = $result->action->value;
                $isPay = true;
            }
        } else {
            $message = $error->message;
        }
        $isPayQRcode = false;
        if ($isPay) {
            if (Configuration::get('ROZETKAPAY_QR_CODE') === "1") {
                $payQRcode = (new \chillerlan\QRCode\QRCode)->render($urlPay);
                $isPayQRcode = true;
            } else {
                Tools::redirect($urlPay);
            }
        } else {
            Tools::displayError($message);
        }

        if (isset($result->data)) {
            $message = $result->data['message'];
        } elseif (isset($result->message)) {
            $message = $result->message;
        }



        $this->context->smarty->assign('isPay', $isPay);
        $this->context->smarty->assign('isPayQRcode', $isPayQRcode);
        $this->context->smarty->assign('urlPay', $urlPay);
        $this->context->smarty->assign('payQRcode', $payQRcode);
        $this->context->smarty->assign('message', $message);

        $this->context->smarty->assign(array(
            'pageType' => '',
            'text_payment_execution_h3' => $this->languages['text_payment_execution_h3'],
        ));

        $this->context->smarty->assign('urlCancel', Context::getContext()->link->getPageLink('order', true, NULL, "step=3"));
        
        $this->context->smarty->assign('text_title', $this->getTitle());
        $this->context->smarty->force_compile = true;
        $this->setTemplate('payment_execution.tpl');
    }
    
    
    public function callback() {
      
        $this->log('fun: callback');
        $this->log(file_get_contents('php://input'));
        
                //
//        $test = '{"id":"470542479947469672","external_id":"28_","is_success":true,"details":'
//                . '{"payment_id":"52fe2a4a-73d6-4aec-a031-3fed0ff03d83","transaction_id":"52fe2a4a-73d6-4aec-a031-3fed0ff03d83",'
//                . '"billing_order_id":null,"gateway_order_id":"1f02d2fd-44e1-4200-8b99-dddc15c7f56d","rnn":null,'
//                . '"amount":"106","currency":"UAH","status":"success","status_code":"3ds_required",'
//                . '"status_description":"3DS verification is required","created_at":"2023-07-22T02:59:33.796Z",'
//                . '"processed_at":"2023-07-22T02:59:51.258Z","description":"Оплата № 470542479947469672",'
//                . '"payload":null,"properties":null},"receipt_url":null,"action_required":true,'
//                . '"action":{"type":"url","value":"https://payment.evopay.com.ua/sandbox/action/52fe2a4a-73d6-4aec-a031-3fed0ff03d83/start_3ds"}}';
        $test = '';

        $result = $this->rpay->сallbacks($test);

        if (!isset($result->external_id)) {
            $this->log('Failure error return data:');
            return;
        }

        $this->log('    result:');
        $this->log($result);

        if (Configuration::get('ROZETKAPAY_SANDBOX_STATUS') === "1") {
            $ids = explode("_", $result->external_id);
            $id_order = $ids[0];
        } else {
            $id_order = $result->external_id;
        }

        $status = $result->details->status;
        

        $this->log('    id_order: ' . $id_order);
        
        $this->log('    status: ' . $status);
        
        $orderStatus_id = $this->getRozetkaPayStatusToOrderStatus($status);
        
//        if($orderStatus_id != 0){
//            $this->module->validateOrder(intval($orderInfo->id), _PS_OS_PREPARATION_, $orderInfo->getOrderTotal(), 'RozetkaPay');
//        }

        $this->log('    orderStatus_id: ' . $orderStatus_id);
        
        $status_holding = isset($this->request->get['holding']);
        $this->log('    hasHolding: ' . $status_holding);

        $refund = isset($this->request->get['refund']);
        $this->log('    hasRefund: ' . $refund);
        
        if($orderStatus_id != "0"){

            $history = new OrderHistory();
            $history->id_order = $id_order;
            $history->changeIdOrderState((int)$orderStatus_id, $id_order);
            
        }
        $history->addWithemail(true);
       

        exit();
    }
    
    public function result() {
        
        $id_order = Tools::getValue('id_order');
        if (Configuration::get('ROZETKAPAY_SANDBOX_STATUS') === "1") {
            $ids = explode("_", $id_order);
            $id_order = $ids[0];
        }
        
        $this->log('fun: result');
        
        $this->log('    id_order: ' . $id_order);

        $order = new OrderCore(intval($id_order));
        
        $status = $this->getOrderStatus($id_order);
        
        $complete = true;
        
        if($status == (int)Configuration::get('ROZETKAPAY_ORDER_STATUS_SUCCESS')){
            $complete = true;
        }
        
        
        if($complete){
            $customer = new CustomerCore($order->id_customer);
            
            Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$order->id.
                    '&id_module='.(int)$this->module->id.'&id_order='.$order->id.'&key='.$customer->secure_key);
        }else{
            Tools::redirect('index.php?controller=order&step=1');
        }        

    }
    

    public function log($var) {
        if ($this->extlog !== false) {
            $this->extlog->write($var);
        }
    }

    public function loadLanguages() {

        $dir = dirname(dirname(__DIR__)) . '/translations/';

        if (file_exists($dir . $this->langCode . '.php')) {
            include $dir . $this->langCode . '.php';
        } else {
            include $dir . 'en.php';
        }

        foreach ($_MODULE as $key => $value) {
            $this->context->smarty->assign($key, $value);
        }

        return $this->languages = $_MODULE;
    }

    public function getTitle() {

        $title = '';
        $this->loadLanguages();
        if (Configuration::get('ROZETKAPAY_VIEW_TITLE_DEFAULT') == "1") {
            $title = $this->languages['text_title'];
        } else {
            $title = Configuration::get('ROZETKAPAY_VIEW_TITLE_' . strtoupper(Context::getContext()->language->iso_code));
            if ($title === null || empty($title)) {
                $title = $this->languages['text_title'];
            }
        }

        if (Configuration::get('ROZETKAPAY_SANDBOX_STATUS') === "1") {
            $title .= '(Test)';
        }

        return $title;
    }
    
    public function getRozetkaPayStatusToOrderStatus($status) {

        switch ($status) {
            case "init":
                return Configuration::get('ROZETKAPAY_ORDER_STATUS_INIT');
                break;
            case "pending":
                return Configuration::get('ROZETKAPAY_ORDER_STATUS_PENDING');
                break;
            case "success":
                return Configuration::get('ROZETKAPAY_ORDER_STATUS_SUCCESS');
                break;
            case "failure":
                return Configuration::get('ROZETKAPAY_ORDER_STATUS_FAILURE');
                break;

            default:
                return "0";
                break;
        }
    }
    
    public function getOrderStatus($id_order) {
        $order = new Order($id_order);
        if (Validate::isLoadedObject($order)) {
            return $order->getCurrentState();
        } else {
            return false;
        }
    }

}
