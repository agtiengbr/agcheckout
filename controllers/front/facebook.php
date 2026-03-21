<?php

class agcheckoutFacebookModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();
        $action = Tools::getValue('action');
        if (method_exists($this, $action)) {
            $this->display_header = false;
            $this->display_footer = false;

            $this->{$action}();
        }

        exit();
    }

    public function getFacebookButton()
    {
        $fb_data = [
            'app_id' => Configuration::get('AGCHECKOUT_FACEBOOK_APP_ID'),
            'app_secret' => Configuration::get('AGCHECKOUT_FACEBOOK_APP_SECRET'),
            'default_graph_version' => 'v'.Configuration::get('AGCHECKOUT_FACEBOOK_VERSION_APP')
        ];

        if ($fb_data['app_id'] && $fb_data['app_secret'] && $fb_data['default_graph_version']) {
            $fb = new Facebook\Facebook([
                'app_id' => Configuration::get('AGCHECKOUT_FACEBOOK_APP_ID'),
                'app_secret' => Configuration::get('AGCHECKOUT_FACEBOOK_APP_SECRET'),
                'default_graph_version' => 'v'.Configuration::get('AGCHECKOUT_FACEBOOK_VERSION_APP')
            ]);

            $helper = $fb->getRedirectLoginHelper();

            $permissions = ['email']; // Optional permissions

            switch (Tools::getValue('form')) {
                case 'login':
                case 'registration':
                    $back = 'authentication';
                    break;
                case 'login_checkout':
                case 'registration_checkout':
                    $back = 'order';
                    break;
                default:
                    $back = 'order';
            }

            $loginUrl = $helper->getLoginUrl($this->context->link->getModuleLink($this->module->name, 'fbRedirect', array('back' => @$back?: 'authentication')), $permissions);


            $this->context->smarty->assign([
                'login_url' => $loginUrl,
                'acustomers_facebook_image' => $this->context->shop->getBaseURL(true) .'modules/' . $this->module->name . '/views/img/facebook_logo.png'
            ]);

            $this->setTemplate('module:agcheckout/views/templates/front/fb_login/' . Tools::getValue('form') . '.ps17.tpl');
            $this->display();

        }
        exit();
    }
}