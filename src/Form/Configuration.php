<?php

namespace AGTI\Checkout\Form;

use AGTI\Checkout\Entity\Configuration as EntityConfiguration;
use AGTI\Cliente\Form\Form;

class Configuration extends Form
{
    protected $submitButton = 'agcheckout-configuration-form';
    protected $submitButtonLogin = 'agcheckout-configuration-login-form';
    protected $submitButtonGoogle = 'agcheckout-configuration-login-google-form';
    protected $submitRecaptcha = 'agcheckout-configuration-recaptcha';
    public function renderHtml()
    {
        $carriers = \Carrier::getCarriers(\Context::getContext()->language->id);
        $carriers_select = [[
            'id' => 0,
            'name' => 'Selecione a transportadora a ser usada nos pedidos.'
        ]];

        foreach ($carriers as $carrier) {
            $carriers_select[] = [
                'id' => $carrier['id_carrier'],
                'name' => $carrier['name']
            ];
        }

        $inputsGoogleBtn = [
            [
                'name' => 'AGCHECKOUT_GOOGLE_PROMPT',
                'label' => 'Exibir janela flutuante para login com o google',
                'type' => 'switch',
                'values' => array(
                    array(
                        'id'    => 'AGCHECKOUT_GOOGLE_PROMPT_ON',
                        'value' => 1,
                        'label' => 'Sim',
                    ),
                    array(
                        'id'    => 'AGCHECKOUT_GOOGLE_PROMPT_OFF',
                        'value' => 0,
                        'label' => 'Não',
                    ),
                ),
            ],
            [
                'type' => 'text',
                'label' => 'ID do cliente',
                'name' => 'AGCHECKOUT_GOOGLE_KEY',
            ],
            [
                'type' => 'select',
                'label' => 'Tipo',
                'name' => 'AGCHECKOUT_GOOGLE_BTN_TYPE',
                'options' => [
                    'id' => 'id',
                    'name' => 'Field',
                    'query' => [
                        [
                            "id" => "default",
                            "Field" => "Padrão"
                        ],
                        [
                            "id" => "icon",
                            "Field" => "Ícone"
                        ]
                    ]
                ]
            ],
            [
                'type' => 'select',
                'label' => 'Tema',
                'name' => 'AGCHECKOUT_GOOGLE_BTN_THEME',
                'options' => [
                    'id' => 'id',
                    'name' => 'Field',
                    'query' => [
                        [
                            "id" => "outline",
                            "Field" => "Branco"
                        ],
                        [
                            "id" => "filled_blue",
                            "Field" => "Azul"
                        ],
                        [
                            "id" => "filled_black",
                            "Field" => "Preto"
                        ]
                    ]
                ]
            ],
            [
                'type' => 'select',
                'label' => 'Tamanho',
                'name' => 'AGCHECKOUT_GOOGLE_BTN_SIZE',
                'options' => [
                    'id' => 'id',
                    'name' => 'Field',
                    'query' => [
                        [
                            "id" => "large",
                            "Field" => "Grande"
                        ],
                        [
                            "id" => "medium",
                            "Field" => "Medio"
                        ],
                        [
                            "id" => "small",
                            "Field" => "Pequeno"
                        ]
                    ]
                ]
            ],
            [
                'type' => 'select',
                'label' => 'texto a ser exibido',
                'name' => 'AGCHECKOUT_GOOGLE_BTN_TEXT',
                'options' => [
                    'id' => 'id',
                    'name' => 'Field',
                    'query' => [
                        [
                            "id" => "signin_with",
                            "Field" => "Fazer login com o Google"
                        ],
                        [
                            "id" => "signup_with",
                            "Field" => "Inscreva-se no Google"
                        ],
                        [
                            "id" => "continue_with",
                            "Field" => "Continue with Google"
                        ],
                        [
                            "id" => "signin",
                            "Field" => "Fazer login"
                        ]
                    ]
                ]
            ],
            [
                'type' => 'select',
                'label' => 'Formato',
                'name' => 'AGCHECKOUT_GOOGLE_BTN_SHAPE',
                'options' => [
                    'id' => 'id',
                    'name' => 'Field',
                    'query' => [
                        [
                            "id" => "rectangular",
                            "Field" => "Retangular"
                        ],
                        [
                            "id" => "pill",
                            "Field" => "Pilula"
                        ],
                        [
                            "id" => "circle",
                            "Field" => "Circulo"
                        ],
                        [
                            "id" => "square",
                            "Field" => "Quadrado"
                        ]
                    ]
                ]
            ],
            [
                'type' => 'select',
                'label' => 'Alinhamento do logo',
                'name' => 'AGCHECKOUT_GOOGLE_BTN_LOGO',
                'options' => [
                    'id' => 'id',
                    'name' => 'Field',
                    'query' => [
                        [
                            "id" => "left",
                            "Field" => "Esquerda"
                        ],
                        [
                            "id" => "center",
                            "Field" => "Centralizado"
                        ]
                    ]
                ]
            ],
        ];

        $inputsLogin = [
            [
                'type' => 'text',
                'label' => 'Facebook app id',
                'name' => 'AGCHECKOUT_FACEBOOK_APP_ID',
            ],
            [
                'type' => 'text',
                'label' => 'Facebook app Secret',
                'name' => 'AGCHECKOUT_FACEBOOK_APP_SECRET',
            ],
            [
                'type' => 'text',
                'label' => 'Facebook Version App',
                'name' => 'AGCHECKOUT_FACEBOOK_VERSION_APP',
                'prefix' => 'V'
            ],
        ];

        $inputsRecaptcha = [
            [
                'type' => 'text',
                'label' => 'Chave Publica',
                'name' => 'AGCHECKOUT_PUBLIC_KEY',
            ],
            [
                'type' => 'text',
                'label' => 'Chave Privada',
                'name' => 'AGCHECKOUT_PRIVATE_KEY'
            ],
           
        ];

        $inputs = [
            [
                'type' => 'switch',
                'label' => 'Atualizar tela após o login',
                'name' => 'AGCHECKOUT_REFRESH_AFTER_LOGIN',
                'desc' => 'Utilize essa opção se o seu tema possuir algum identificador do cliente no checkout, como por exemplo o nome no cabeçalho da loja.',
                'values' => array(
                    array(
                        'id'    => 'AGCHECKOUT_REFRESH_AFTER_LOGIN_on',
                        'value' => 1,
                        'label' => 'Sim',
                    ),
                    array(
                        'id'    => 'AGCHECKOUT_REFRESH_AFTER_LOGIN_off',
                        'value' => 0,
                        'label' => 'Não',
                    ),
                ),
            ],
            [
                'type' => 'switch',
                'label' => 'Número de telefone obrigatório',
                'name' => 'AGCHECKOUT_REQUIRED_PHONE',
                'values' => array(
                    array(
                        'id'    => 'AGCHECKOUT_REQUIRED_PHONE_on',
                        'value' => 1,
                        'label' => 'Sim',
                    ),
                    array(
                        'id'    => 'AGCHECKOUT_REQUIRED_PHONE_off',
                        'value' => 0,
                        'label' => 'Não',
                    ),
                ),
            ],
            [
                'type' => 'switch',
                'label' => 'Ignorar etapa de Entrega',
                'name' => 'AGCHECKOUT_IGNORE_DELIVERY_STEP',
                'desc' => 'Se você precisa utilizar produtos virtuais com combinações, ative essa opção. Assim você poderá utilizar produtos com combinações, sem a necessidade de configurá-los como produtos virtuais.',
                'values' => array(
                    array(
                        'id'    => 'AGCHECKOUT_IGNORE_DELIVERY_STEP_on',
                        'value' => 1,
                        'label' => 'Sim',
                    ),
                    array(
                        'id'    => 'AGCHECKOUT_IGNORE_DELIVERY_STEP_off',
                        'value' => 0,
                        'label' => 'Não',
                    ),
                ),
            ],
            [
                'label' => 'Transportadora a ser utilizada',
                'type' => 'select',
                'name' => 'AGCHECKOUT_DEFAULT_CARRIER',
                'options' => [
                    'id' => 'id',
                    'name' => 'name',
                    'query' => $carriers_select
                ]
            ],
            [
                'type' => 'textarea',
                'label' => 'Texto para ser exibido caso o carrinho de compras esteja vazio',
                'autoload_rte' => true,
                'tinymce' => true,
                'name' => 'AGCHECKOUT_NO_PRODUCTS_MESSAGE'
            ],
            [
                'name' => 'AGCHECKOUT_NEWSLETTER_DEFAULT',
                'type' => 'switch',
                'label' => 'Ativar aceite das newsletters por padrão',
                'values' => array(
                    array(
                        'id'    => 'AGCHECKOUT_NEWSLETTER_DEFAULT_on',
                        'value' => 1,
                        'label' => 'Sim',
                    ),
                    array(
                        'id'    => 'AGCHECKOUT_NEWSLETTER_DEFAULT_off',
                        'value' => 0,
                        'label' => 'Não',
                    ),
                ),
            ],
            [
                'name' => 'AGCHECKOUT_ALLOW_ADDRESS_EDIT',
                'type' => 'switch',
                'label' => 'Permitir edição de endereço ao CEP e dados complementares',
                'values' => array(
                    array(
                        'id'    => 'AGCHECKOUT_ALLOW_ADDRESS_EDIT_on',
                        'value' => true,
                        'label' => 'Sim',
                    ),
                    array(
                        'id'    => 'AGCHECKOUT_ALLOW_ADDRESS_EDIT_off',
                        'value' => false,
                        'label' => 'Não',
                    ),
                ),
            ]
        ];

        $forms = [
            'form' => [
                'legend' => ['title' => 'Configuração'],
                'input' => $inputs,
                'submit' => ['title' => 'Salvar', 'name' => $this->submitButton]
            ]
        ];

        $formsLogin = [
            'form' => [
                'legend' => ['title' => 'Login Social com o Facebook'],
                'input' => $inputsLogin,
                'submit' => ['title' => 'Salvar', 'name' => $this->submitButtonLogin]
            ]
        ];

        $formsBtn = [
            'form' => [
                'legend' => ['title' => ' Login Social com o Google'],
                'input' => $inputsGoogleBtn,
                'submit' => ['title' => 'Salvar', 'name' => $this->submitButtonGoogle]
            ]
        ];

        $formsRecaptcha = [
            'form' => [
                'legend' => ['title' => ' ReCaptcha'],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => 'Chave Publica',
                        'name' => 'AGCHECKOUT_RECAPTCHA_PUBLIC_KEY',
                    ],
                    [
                        'type' => 'text',
                        'label' => 'Chave Privada',
                        'name' => 'AGCHECKOUT_RECAPTCHA_PRIVATE_KEY'
                    ],
                   
                ],
                'submit' => ['title' => 'Salvar', 'name' => $this->submitRecaptcha]
            ]
        ];

        
        $form = $this->getHelperForm();
        $this->fillForm($form);

        // return $form->generateForm([$forms]);
        return $form->generateForm([$forms,$formsLogin,$formsBtn,$formsRecaptcha]);
    }

    public function postProcess()
    {

        if (\Tools::isSubmit($this->submitButton)) {
            $this->persistData();

        }
        if (\Tools::isSubmit($this->submitButtonLogin)) {
            $this->persistDataLogin();

        }
        if (\Tools::isSubmit($this->submitButtonGoogle)) {
            
            $this->persistDataGoogleLogin();

        }

        if (\Tools::isSubmit($this->submitRecaptcha)) {
            
            $this->persistDataRecaptcha();

        }

    }

    public function fillForm($form)
    {
        $config = new EntityConfiguration;
        $config->loadConfig();

        $form->fields_value['AGCHECKOUT_REFRESH_AFTER_LOGIN'] = $config->getRedirectAfterLogin();
        $form->fields_value['AGCHECKOUT_NO_PRODUCTS_MESSAGE'] = $config->getNoProductsText();
        $form->fields_value['AGCHECKOUT_IGNORE_DELIVERY_STEP'] = $config->getIgnoreDeliveryStep();
        $form->fields_value['AGCHECKOUT_DEFAULT_CARRIER'] = $config->getDefaultCarrier();
        $form->fields_value['AGCHECKOUT_REQUIRED_PHONE'] = $config->getRequiredPhone();
        $form->fields_value['AGCHECKOUT_NEWSLETTER_DEFAULT'] = $config->getDefaultNewsletter();
        $form->fields_value['AGCHECKOUT_ALLOW_ADDRESS_EDIT'] = $config->getAllowAddressEdit();


        $form->fields_value['AGCHECKOUT_FACEBOOK_APP_ID'] = $config->getFacebookAppId();
        $form->fields_value['AGCHECKOUT_FACEBOOK_APP_SECRET'] = $config->getFacebookAppSecret();
        $form->fields_value['AGCHECKOUT_FACEBOOK_VERSION_APP'] = $config->getFacebookVersionApp();


        $form->fields_value['AGCHECKOUT_GOOGLE_PROMPT'] = $config->getGoogleBtnPrompt();
        $form->fields_value['AGCHECKOUT_GOOGLE_KEY'] = $config->getGoogleBtnKey();
        $form->fields_value['AGCHECKOUT_GOOGLE_BTN_TYPE'] = $config->getGoogleBtnType();
        $form->fields_value['AGCHECKOUT_GOOGLE_BTN_THEME'] = $config->getGoogleBtnTheme();
        $form->fields_value['AGCHECKOUT_GOOGLE_BTN_SIZE'] = $config->getGoogleBtnSize();
        $form->fields_value['AGCHECKOUT_GOOGLE_BTN_TEXT'] = $config->getGoogleBtnText();
        $form->fields_value['AGCHECKOUT_GOOGLE_BTN_SHAPE'] = $config->getGoogleBtnShape();
        $form->fields_value['AGCHECKOUT_GOOGLE_BTN_LOGO'] = $config->getGoogleBtnLogo();

        $form->fields_value['AGCHECKOUT_RECAPTCHA_PUBLIC_KEY'] = $config->getRecaptchaPublicKey();
        $form->fields_value['AGCHECKOUT_RECAPTCHA_PRIVATE_KEY'] = $config->getRecaptchaPrivateKey();

    }

    protected function persistData()
    {
        $config = new EntityConfiguration;
        $config->setRedirectAfterLogin(\Tools::getValue('AGCHECKOUT_REFRESH_AFTER_LOGIN'));
        $config->setNoProductsText(\Tools::getValue('AGCHECKOUT_NO_PRODUCTS_MESSAGE'));
        $config->setIgnoreDeliveryStep(\Tools::getValue('AGCHECKOUT_IGNORE_DELIVERY_STEP'));
        $config->setDefaultCarrier(\Tools::getValue('AGCHECKOUT_DEFAULT_CARRIER'));
        $config->setRequiredPhone(\Tools::getValue('AGCHECKOUT_REQUIRED_PHONE'));
        $config->setDefaultNewsletter(\Tools::getValue('AGCHECKOUT_NEWSLETTER_DEFAULT'));
        $config->setAllowAddressEdit(\Tools::getValue('AGCHECKOUT_ALLOW_ADDRESS_EDIT'));
        
        $config->persist();
    }

    protected function persistDataLogin()
    {
        $config = new EntityConfiguration;
        $config->setFacebookAppId(\Tools::getValue('AGCHECKOUT_FACEBOOK_APP_ID'));
        $config->setFacebookAppSecret(\Tools::getValue('AGCHECKOUT_FACEBOOK_APP_SECRET'));
        $config->setFacebookVersionApp(\Tools::getValue('AGCHECKOUT_FACEBOOK_VERSION_APP'));

        $config->persistLogin();
    }

    protected function persistDataGoogleLogin()
    {
        $config = new EntityConfiguration;
       
        $config->setGoogleBtnPrompt(\Tools::getValue('AGCHECKOUT_GOOGLE_PROMPT'));
        $config->setGoogleBtnKey(\Tools::getValue('AGCHECKOUT_GOOGLE_KEY'));
        $config->setGoogleBtnType(\Tools::getValue('AGCHECKOUT_GOOGLE_BTN_TYPE'));
        $config->setGoogleBtnTheme(\Tools::getValue('AGCHECKOUT_GOOGLE_BTN_THEME'));
        $config->setGoogleBtnSize(\Tools::getValue('AGCHECKOUT_GOOGLE_BTN_SIZE'));
        $config->setGoogleBtnText(\Tools::getValue('AGCHECKOUT_GOOGLE_BTN_TEXT'));
        $config->setGoogleBtnShape(\Tools::getValue('AGCHECKOUT_GOOGLE_BTN_SHAPE'));
        $config->setGoogleBtnLogo(\Tools::getValue('AGCHECKOUT_GOOGLE_BTN_LOGO'));

        $config->persistGoogle();
        
    }

    protected function persistDataRecaptcha()
    {
        $config = new EntityConfiguration;
       
        $config->setRecaptchaPublicKey(\Tools::getValue('AGCHECKOUT_RECAPTCHA_PUBLIC_KEY'));
        $config->setRecaptchaPrivateKey(\Tools::getValue('AGCHECKOUT_RECAPTCHA_PRIVATE_KEY'));

        $config->persistRecaptcha();
        
    }
}
