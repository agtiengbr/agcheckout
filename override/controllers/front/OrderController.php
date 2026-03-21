<?php
use AGTI\Checkout\Adapter\FieldsGetter;
use AGTI\Checkout\Adapter\PersonTypeGetter;
use AGTI\Checkout\Api;
use AGTI\Checkout\Entity\Configuration;
class OrderController extends OrderControllerCore
{
    /*
    * module: agcheckout
    * date: 2023-11-01 09:44:26
    * version: 0.8.5
    */
    public function initContent()
    {
        require_once _PS_MODULE_DIR_ . 'agcheckout/agcheckout.php';
        
        parent::initContent();
        $config = new Configuration;
        $config->loadConfig();
        $fields = [];
        $person_types = PersonTypeGetter::getPersonTypes();
        foreach ($person_types as $person_type) {
            $fields[$person_type->getId()] = FieldsGetter::getFields($person_type);
        }
        $this->context->smarty->assign([
            'person_types' => $person_types,
            'no_products_message' => $config->getNoProductsText(),
            'fields' => $fields,
            'allow_address_edit' => $config->getAllowAddressEdit()
        ]);
        $this->context->smarty->assign([
            'id_lang' => $this->context->language->id
        ]);
        $parameters =[];
        if(Tools::getValue('email') != ''){
            $parameters = [
                'email' => Tools::getValue('email'),
                'name' => Tools::getValue('firstname').' '.Tools::getValue('lastname'),
            ];
        }
        if (!$this->context->customer->isLogged()) {
            $parameters['newsletter'] = (int) $config->getDefaultNewsletter();
        }
        Media::addJsDef([
            'agcheckout' => [
                'logged' => $this->context->customer->id == NULL ? false:true,
                'google_client_id' => \Configuration::get('AGCHECKOUT_GOOGLE_KEY'),
                'google_prompt' => \Configuration::get('AGCHECKOUT_GOOGLE_PROMPT'),
                'api_url' => $this->context->link->getModuleLink('agcheckout', 'api'),
                'cart' => Api::getCartData($this->context->cart),
                'customer' => Api::getCustomerData($this->context->customer) == [] ? $parameters :Api::getCustomerData($this->context->customer),
                'addresses' => Api::getAddresses($this->context->customer, $this->context->language->id),
                'captcha_public_key' => \Configuration::get('AGCHECKOUT_RECAPTCHA_PUBLIC_KEY'),
                'config' => [
                    'redirect_after_login' => $config->getRedirectAfterLogin(),
                    'ignore_delivery_step' => $config->getIgnoreDeliveryStep() || $this->context->cart->isVirtualCart(),
                    'required_phone' => $config->getRequiredPhone(),
                    'allow_address_edit' => $config->getAllowAddressEdit()
                ],
                'configs_btn' => [
                    'google_type_btn' => \Configuration::get('AGCHECKOUT_GOOGLE_BTN_TYPE'),
                    'google_theme_btn' =>\Configuration::get('AGCHECKOUT_GOOGLE_BTN_THEME'),
                    'google_size_btn' => \Configuration::get('AGCHECKOUT_GOOGLE_BTN_SIZE'),
                    'google_text_btn' => \Configuration::get('AGCHECKOUT_GOOGLE_BTN_TEXT'),
                    'google_shape_btn' => \Configuration::get('AGCHECKOUT_GOOGLE_BTN_SHAPE'),
                    'google_logo_btn' =>  \Configuration::get('AGCHECKOUT_GOOGLE_BTN_LOGO')
                ],
                'urls' => [
                    'facebook' => $this->context->link->getModuleLink('agcheckout', 'facebook'),
                    'google' => $this->context->link->getModuleLink('agcheckout', 'google'),
                    'create_acount' => $this->context->link->getPageLink('order')
                ],
            ]
        ]);
        
        if (file_exists(_PS_THEME_DIR_ . "modules/agcheckout/views/templates/front/checkout.tpl")) {
            $this->setTemplate("../modules/agcheckout/views/templates/front/checkout.tpl");
        } else {
            $this->setTemplate('../../../modules/agcheckout/views/templates/front/checkout');
        }
        $extraFields = Hook::exec('additionalCustomerFormFields', ['fields' => $fields], null, true);
        if ($config->getDefaultNewsletter()) {
            if (isset($extraFields['ps_emailsubscription'])) {  
                foreach ($extraFields['ps_emailsubscription'] as &$field) {
                    if ($field->getName() == 'newsletter') {
                        $field->setValue(true);
                    }
                }
            }
            if (isset($extraFields['psgdpr'])) {  
                foreach ($extraFields['psgdpr'] as &$field) {
                    if ($field->getName() == 'psgdpr') {
                        $field->setValue(true);
                    }
                }
            }
        }
        $this->context->smarty->assign(['extraFields' => $extraFields]);
    }
    
    /*
    * module: agcheckout
    * date: 2023-11-01 09:44:26
    * version: 0.8.5
    */
    public function setMedia()
    {
        parent::setMedia();
        $this->addCss(_PS_MODULE_DIR_ . 'agcheckout/views/css/front.css');
        require_once _PS_MODULE_DIR_  .'agcliente/agcliente.php';
        $obj = new agcliente;
        if (AgClienteConfig::isDebugMode()) {
            $this->registerJavascript(
                "vuejs",
                "https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js", [
                    'server' => 'remote',
                    'position' => 'head',
                    'priority' => 0,
                ]
            );
        } else {
            $this->registerJavascript(
                "vuejs",
                "https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.min.js", [
                    'server' => 'remote',
                    'position' => 'head',
                    'priority' => 0,
                ]
            );
        }
        $this->registerJavascript(
            "vue-the-mask",
            "https://cdn.jsdelivr.net/npm/maska@1.5.1/dist/maska.js", [
                'server' => 'remote'
            ]
        );
        $this->registerJavascript(
            "recaptcha",
            "https://www.google.com/recaptcha/api.js", [
                'server' => 'remote',
                'attributes' => 'async'
            ]
        );
        $this->registerJavascript('agcliente-modal', 'modules/agcliente/views/js/component/modal.js');
        $this->registerJavascript(
            "vuejs-aginput-text",
            "modules/agcliente/views/js/component/input_text.js", [
                'server' => 'local'
            ]
        );
        $this->registerJavascript(
            "vuejs-agcheckout-address-modal",
            "modules/agcheckout/views/js/component/address_modal.js", [
                'server' => 'local'
            ]
        );
        $this->registerJavascript(
            "vuejs-agcheckout-payment-mode",
            "modules/agcheckout/views/js/component/payment_mode.js", [
                'server' => 'local'
            ]
        );
        $this->registerJavascript(
            "vuejs-agcheckout-cart-modal",
            "modules/agcheckout/views/js/component/cart_modal.js", [
                'server' => 'local'
            ]
        );
        $this->registerJavascript(
            "axios",
            "https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js", [
                'server' => 'remote'
            ]
        );
        $this->registerStylesheet(
            "vuejs-agmodal",
            "modules/agcliente/views/css/agmodal.css"
        );
        $this->addJs([
            _PS_MODULE_DIR_ . 'agcheckout/views/js/front.js'
        ]);


        if (Module::isEnabled('mercadopago')) {
            Media::addJsDef([
                'agcheckout_mp' => [
                    'public_key' => \Configuration::get('MERCADOPAGO_PUBLIC_KEY')
                ]
            ]);
            
            $this->registerJavascript(
                "mp",
                "https://sdk.mercadopago.com/js/v2", [
                    'server' => 'remote'
                ]
            );

            $this->addJs([
                _PS_MODULE_DIR_ . 'mercadopago/views/js/custom-card.js',
                _PS_MODULE_DIR_ . 'mercadopago/views/js/front.js'
            ]);
        }
    }
}
