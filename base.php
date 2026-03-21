<?php
//Adicionar opção para atualizar o checkout após o login

use AGTI\Checkout\Exception\ValidationException;
use AGTI\Checkout\Form\Configuration;
use AGTI\Cliente\Presenter\Tab;
use AGTI\Cliente\Presenter\Tabs;

require_once _PS_MODULE_DIR_ . 'agcheckout/vendor/autoload.php';
require_once _PS_MODULE_DIR_ . 'agcliente/lib/AgModule.php';
require_once _PS_MODULE_DIR_ . 'agcheckout/vendor/facebook/graph-sdk/src/Facebook/autoload.php';

class baseagcheckout extends AgModule
{
    public $hooks = [
        'displayHeader',
        'registerGDPRConsent',
        'displayHeader',
        'displayBeforeBodyClosingTag'
    ];

    public function __construct()
    {
        $this->name                   = 'agcheckout';
        $this->version                = '0.8.7';
        $this->bootstrap              = true;
        $this->author                 = 'AGTI';
        $this->need_instance          = 1;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '8.99');

        parent::__construct();

        $this->displayName = 'Checkout AGTI';
        $this->description = 'Checkout personalizado.';
    }

    public function getContent()
    {
        require_once _PS_MODULE_DIR_ . 'agcliente/agcliente.php';
        $tabs = new Tabs;
        try {
            $form = new Configuration($this);
            $form->postProcess();
        } catch (ValidationException $e) {
            $this->context->controller->errors[] = "Erro de validação: " . $e->getMessage();
        }

        $tab = new Tab;
        $tab->setTitle('Configurações')
            ->setIcon('cogs')
            ->setId('config')
            ->setBody($form->renderHtml())
            ->setActive(true);

        $tabs->addTab($tab);
        $tabs->addTab(
            (new Tab())
                ->setTitle('Manutenção')
                ->setIcon('icon-cog')
                ->setId('maintenance')
                ->setBody(agcliente::renderMaintanceTab($this))
        );

        $tabs->addTab(
            (new Tab)
                ->setTitle('Suporte')
                ->setIcon('icon-help')
                ->setId('help')
                ->setBody(agcliente::renderHelpTab($this))
        );
        
        
        return $tabs->render();
    }



    public function hookDisplayHeader(){

        $this->context->controller->addCss([
            $this->_path . 'views/css/loadingOverlay.css',
            $this->_path . 'views/css/front.css'
        ]);
        
    }

    public function hookDisplayBeforeBodyClosingTag()
    {
        return "<script src='https://accounts.google.com/gsi/client' async defer></script>";
    }
}