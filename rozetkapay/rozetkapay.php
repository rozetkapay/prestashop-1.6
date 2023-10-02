<?php

include_once __dir__ . '/vendor/RozetkaPay/autoloader.php';
include_once __dir__ . '/controllers/front/log.php';

class RozetkaPay extends PaymentModule {

    private $errors = [];
    private $languages = [];
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
        'view_title' => [],
        'view_icon_status' => 1,
        'sandbox_status' => 0,
        'log_status' => 0,
    ];
    private $langCode;
    public $name = 'rozetkapay';
    public $tab = 'payments_gateways';
    public $version = '1.0.0';
    public $author = 'RozetkaPay';
    public $ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.99.99');
    public $displayName = 'RozetkaPay';
    public $description = 'Модуль оплати для RozetkaPay';
    public $confirmUninstall = 'Ви впевнені, що хочете видалити цей модуль?';
    public $bootstrap = true;
    private $extlog = false;

    public function __construct() {
        parent::__construct();

        $this->langCode = Context::getContext()->language->iso_code;

        if ($this->langCode == "ru") {
            $this->langCode = "uk";
        }

        $this->loadLanguagesA1();

        $this->displayName = $this->l('RozetkaPay');
        $this->description = $this->l('Модуль оплати для RozetkaPay');
        $this->confirmUninstall = $this->l('Ви впевнені, що хочете видалити цей модуль?');

        if (Configuration::get('ROZETKAPAY_LOG_STATUS') === "1") {

            $this->extlog = new \Log('rozetkapay');
        }
    }

    public function install() {
        return parent::install() && $this->registerHook('payment') && $this->registerHook('displayAdminOrderContentOrder');
    }

    public function uninstall() {
        return parent::uninstall();
    }

    public function getContent() {
        $this->loadLanguagesA1();

        $action = Tools::getValue('action');

        if ($action == "payIonfo") {
            $this->payIonfo();
            return;
        }

        if ($action == "payRefund") {
            $this->payRefund();
            return;
        }

        if ($action == "logClear") {
            $this->logClear();
        }

        if ($action == "logDownload") {
            $this->logDownload();
            return;
        }

        if ($action == "logRefresh") {
            $this->logRefresh();
            return;
        }
        
        if (Tools::isSubmit('submitSetting')) {

            foreach ($this->settingList as $key => $default) {
                if ($key == "view_title") {

                    $langs = Tools::getValue('view_title');

                    if (is_array($langs)) {
                        foreach ($langs as $code => $value) {
                            Configuration::updateValue('ROZETKAPAY_' . strtoupper($key . "_" . $code), $value);
                        }
                        continue;
                    }
                }
                if (Tools::getValue($key) === false) {
                    Configuration::updateValue('ROZETKAPAY_' . strtoupper($key), $default);
                } else {
                    Configuration::updateValue('ROZETKAPAY_' . strtoupper($key), Tools::getValue($key));
                }
            }

            Tools::redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        }

        $listLanguages = Language::getLanguages();

        foreach ($this->settingList as $key => $default) {

            if ($key == "view_title") {

                $setting_view_title = [];

                foreach ($listLanguages as $language) {

                    if (Configuration::get('ROZETKAPAY_' . strtoupper($key . "_" . $language['iso_code'])) === false) {
                        $setting_view_title[$language['iso_code']] = '';
                    } else {
                        $setting_view_title[$language['iso_code']] = Configuration::get('ROZETKAPAY_' . strtoupper($key . "_" . $language['iso_code']));
                    }
                }
                $this->context->smarty->assign('setting_' . $key, $setting_view_title);
                continue;
            }

            if (Configuration::get('ROZETKAPAY_' . strtoupper($key)) === false) {
                $this->context->smarty->assign('setting_' . $key, $default);
            } else {
                $this->context->smarty->assign('setting_' . $key, Configuration::get('ROZETKAPAY_' . strtoupper($key)));
            }
        }

        $this->context->smarty->assign('url_submit', $this->getAdminLinkA1());

        $this->context->smarty->assign('urlLogClear', $this->getAdminLinkA1('logClear'));
        $this->context->smarty->assign('urlLogDownload', $this->getAdminLinkA1('logDownload'));
        $this->context->smarty->assign('urlLogRefresh', $this->getAdminLinkA1('logRefresh'));

        $this->context->smarty->assign('order_statuses', OrderState::getOrderStates((int) Configuration::get('PS_LANG_DEFAULT')));
        $this->context->smarty->assign('languages', $listLanguages);

        return $this->display('rozetkapay', 'admin.tpl');
    }

    public function getSetting($name) {
        return Configuration::get('ROZETKAPAY_' . strtoupper($name));
    }

    protected function getAdminLinkA1($action = '', $params = array()) {

        $param_string = '&configure=' . $this->name;
        if (!empty($action)) {
            $param_string .= '&action=' . $action;
        }

        foreach ($params as $pK => $pV) {
            $param_string .= '&' . $pK . '=' . $pV;
        }
        return Context::getContext()->link->getAdminLink('AdminModules', true) . $param_string;
    }

    protected function getAdminLink($controller = 'AdminModules', $withToken = true, $sfRouteParams = array(), $params = array()) {

        $param_string = '&configure=' . $this->name;
        foreach ($params as $pK => $pV) {
            $param_string .= '&' . $pK . '=' . $pV;
        }
        return Context::getContext()->link->getAdminLink($controller, $withToken) . $param_string;
    }

    public function logDownload() {
        $this->extlog->download();
    }

    public function logClear() {
        $this->extlog->clear();
    }

    public function logRefresh() {
        $json = [];

        $json['ok'] = true;

        if ($this->extlog->getSize() > 5242880) {
            $json['ok'] = false;
            $json['warning'] = sprintf($this->languages['text_error_log_warning'], $this->extlog->getSizeFormat());
        } else {
            $json['log'] = $this->extlog->getContent();
        }


        header('Content-Type: application/json');
        die(json_encode($json));
    }

    public function hookPayment($params) {
        
        if (!$this->active)
            return;
        if (!$this->_checkCurrency($params['cart']))
            return;

        if (Configuration::get('ROZETKAPAY_SANDBOX_STATUS') === "1") {

            $live_configurator_token = Tools::getValue('live_configurator_token');

            if (empty($live_configurator_token)) {
                return '';
            }
        }

        $this->context->smarty->force_compile = true;

        $urlPayCreat = Context::getContext()->link->getModuleLink('rozetkapay', 'rozetkapay');

        $this->context->smarty->assign('urlPayCreat', $urlPayCreat);
        $this->context->smarty->assign('urlCancel', $urlPayCreat);

        $this->context->smarty->assign('showIcon', Configuration::get('ROZETKAPAY_VIEW_ICON_STATUS') == "1");

        $title = '';
        $this->loadLanguagesA1();
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
        $this->languages['text_title'] = $title;
        $this->context->smarty->assign('text_title', $title);
        $this->context->smarty->assign($this->languages);

        return $this->display('rozetkapay', 'rozetkapay.tpl');
    }

    private function _checkCurrency($cart) {

        $currency_order = new Currency((int) ($cart->id_currency));
        $currencies_module = $this->getCurrency((int) $cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module AS $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    public function loadLanguagesA1() {

        $dir = __DIR__ . '/translations/';

        if (file_exists($dir . $this->langCode . '.php')) {
            include $dir . $this->langCode . '.php';
        } else {
            include $dir . 'en.php';
        }

        foreach ($_MODULE as $key => $value) {
            $this->context->smarty->assign($key, $value);
        }
        $this->languages = $_MODULE;

        return $this->languages;
    }

    public function hookDisplayAdminOrderContentOrder($params) {

        if ($params['order']->payment !== "RozetkaPay") {
            return '';
        }

        $id_cart = (int) $params['order']->id_cart;

        $id_order = Tools::getValue('id_order');

        if ($id_order != $id_cart) {
            $id_order = $id_cart;
        }

        $this->context->smarty->assign('id_order', $id_order);
        $this->context->smarty->assign('urlRayInfo', $this->getAdminLinkA1('payIonfo'));
        $this->context->smarty->assign('urlPayRefund', $this->getAdminLinkA1('payRefund'));

        $this->loadLanguagesA1();

        $this->context->smarty->force_compile = true;
        return $this->display('rozetkapay', 'rozetkapay_order.tpl');
    }

    public function validation() {

        return false;
    }

    public function payRefund() {

        $json = [];

        $json['ok'] = false;
        $json['error'] = [];

        $id_order = Tools::getValue('id_order');

        if (empty($id_order)) {
            $json['error']['error_order_id'] = $this->languages['text_payRefund_error_order_id'];
        }

        $total = Tools::getValue('total');

        if (empty($total)) {
            $json['error']['total'] = $this->languages['text_payRefund_error_total'];
        }

        if ($total <= 0) {
            $json['error']['error_total'] = $this->languages['text_payRefund_error_total'];
        }

        if (empty($this->error)) {

            $rpay = new \Payment\RozetkaPay\RozetkaPay();

            if (Configuration::get('ROZETKAPAY_SANDBOX_STATUS') === "1") {
                $rpay->setBasicAuthTest();
                $id_order = $id_order . "_" . md5($_SERVER['HTTP_HOST']);
            } else {
                $rpay->setBasicAuth(Configuration::get('ROZETKAPAY_LOGIN'), Configuration::get('ROZETKAPAY_PASSWORD'));
            }

            $order_info = new Order($id_order);
            $currency = new CurrencyCore($order_info->id_currency);
            $currencyCode = $currency->iso_code;

            $dataPay = new \Payment\RozetkaPay\Model\PaymentRequest();

            $dataPay->external_id = (string) $id_order;
            $dataPay->amount = $total;
            $dataPay->currency = $currencyCode;

            list($status, $error) = $rpay->paymentRefund($dataPay);

            if ($error !== false) {
                $json['error'][$error->code] = $error->message;
            }

            $json['ok'] = $status;
        }

        if ($json['ok']) {
            $json['alert'] = $this->languages['text_success'];
        } else {
            $json['alert'] = $this->languages['text_failure'];
        }

        header('Content-Type: application/json');
        die(json_encode($json));
    }

    public function payIonfo() {
        $json = [];

        $json['ok'] = false;
        $json['details'] = [];
        $json['error'] = [];

        $id_order = Tools::getValue('id_order');

        if (empty($id_order)) {
            $json['error']['error_order_id'] = $this->languages['text_pay_error_order_id'];
        }

        if (empty($this->error)) {

            $order_info = new Order($id_order);

            $rpay = new \Payment\RozetkaPay\RozetkaPay();

            if (Configuration::get('ROZETKAPAY_SANDBOX_STATUS') === "1") {
                $rpay->setBasicAuthTest();
                $id_order = $id_order . "_" . md5($_SERVER['HTTP_HOST']);
            } else {
                $rpay->setBasicAuth(Configuration::get('ROZETKAPAY_LOGIN'), Configuration::get('ROZETKAPAY_PASSWORD'));
            }

            list($results, $json['error']) = $rpay->paymentInfo((string) $id_order);

            $details = [];
            if (empty($json['error'])) {
                if (isset($results['purchase_details']) && !empty($results['purchase_details'])) {
                    foreach ($results['purchase_details'] as $detail) {
                        $details[] = [
                            'amount' => $detail->amount,
                            'currency' => $detail->currency,
                            'status' => $detail->status,
                            'created_at' => (new \DateTime($detail->created_at))->getTimestamp(),
                            'type' => 'purchase'
                        ];
                    }
                }

                if (isset($results['confirmation_details']) && !empty($results['purchase_details'])) {
                    foreach ($results['confirmation_details'] as $detail) {
                        $details[] = [
                            'amount' => $detail->amount,
                            'currency' => $detail->currency,
                            'status' => $detail->status,
                            'created_at' => (new \DateTime($detail->created_at))->getTimestamp(),
                            'type' => 'confirmation'
                        ];
                    }
                }

                if (isset($results['cancellation_details']) && !empty($results['purchase_details'])) {
                    foreach ($results['cancellation_details'] as $detail) {
                        $details[] = [
                            'amount' => $detail->amount,
                            'currency' => $detail->currency,
                            'status' => $detail->status,
                            'created_at' => (new \DateTime($detail->created_at))->getTimestamp(),
                            'type' => 'cancellation'
                        ];
                    }
                }

                if (isset($results['refund_details']) && !empty($results['purchase_details'])) {
                    foreach ($results['refund_details'] as $detail) {
                        $details[] = [
                            'amount' => $detail->amount,
                            'currency' => $detail->currency,
                            'status' => $detail->status,
                            'created_at' => (new \DateTime($detail->created_at))->getTimestamp(),
                            'type' => 'refund'
                        ];
                    }
                }
            }

            $sort_order = array();

            foreach ($details as $key => $value) {
                $sort_order[$key] = $value['created_at'];
            }

            array_multisort($sort_order, SORT_DESC, $details);

            $dat = new \DateTime();
            foreach ($details as $key => $detail) {
                $details[$key]['created_at'] = $dat->setTimestamp($detail['created_at'])->format(Context::getContext()->language->date_format_full);
            }

            $json['ok'] = true;
            $json['details'] = $details;
            $json['alert'] = $this->languages['text_success'];
        } else {

            $json['alert'] = $this->language->get('text_error');
        }

        $json['debug'] = $rpay->debug;

        header('Content-Type: application/json');
        die(json_encode($json));
    }

    public function log($var) {
        if ($this->extlog !== false) {
            $this->extlog->write($var);
        }
    }

}
