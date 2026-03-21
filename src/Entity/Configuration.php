<?php

namespace AGTI\Checkout\Entity;

use AGTI\Checkout\Exception\ValidationException;

class Configuration
{
    protected $redirectAfterLogin;
    protected $noProductsText;
    protected $ignoreDeliveryStep;
    protected $defaultCarrier;
    protected $defaultNewsletter;
    protected $allowAddressEdit;
    protected $requiredPhone;
    protected $facebookAppId;
    protected $facebookAppSecret;
    protected $facebookVersionApp;

    protected $googleBtnKey;
    protected $googleBtnPrompt;

    protected $googleBtnLogo;
    protected $googleBtnShape;
    protected $googleBtnText;
    protected $googleBtnSize;
    protected $googleBtnTheme;
    protected $googleBtnType;

    protected $recaptchaPublicKey;
    protected $recaptchaPrivateKey;

    /**
     * Get the value of facebookAppId
     */ 
    public function getFacebookAppId()
    {
        return $this->facebookAppId;
    }

    /**
     * Set the value of redirectAfterLogin
     *
     * @return  self
     */ 
    public function setFacebookAppId($facebookAppId)
    {
        $this->facebookAppId = $facebookAppId;

        return $this;
    }

    /**
     * Get the value of facebookAppSecret
     */ 
    public function getFacebookAppSecret()
    {
        return $this->facebookAppSecret;
    }

    /**
     * Set the value of redirectAfterLogin
     *
     * @return  self
     */ 
    public function setFacebookAppSecret($facebookAppSecret)
    {
        $this->facebookAppSecret = $facebookAppSecret;

        return $this;
    }

    /**
     * Get the value of facebookVersionApp
     */ 
    public function getFacebookVersionApp()
    {
        return $this->facebookVersionApp;
    }

     /**
     * Set the value of redirectAfterLogin
     *
     * @return  self
     */ 
    public function setFacebookVersionApp($facebookVersionApp)
    {
        $this->facebookVersionApp = $facebookVersionApp;

        return $this;
    }

    /**
     * Get the value of redirectAfterLogin
     */ 
    public function getRedirectAfterLogin()
    {
        return $this->redirectAfterLogin;
    }

    /**
     * Set the value of redirectAfterLogin
     *
     * @return  self
     */ 
    public function setRedirectAfterLogin($redirectAfterLogin)
    {
        $this->redirectAfterLogin = $redirectAfterLogin;

        return $this;
    }

    /**
     * Set the value of requiredPhone
     *
     * @return  self
     */
    public function setRequiredPhone($requiredPhone)
    {
        $this->requiredPhone = $requiredPhone;

        return $this;
    }

    /**
     * Get the value of requiredPhone
     *
     * @return  self
     */
    public function getRequiredPhone()
    {
        return $this->requiredPhone;
    }

    public function loadConfig()
    {
        $config = \Configuration::getMultiple([
            'AGCHECKOUT_REFRESH_AFTER_LOGIN',
            'AGCHECKOUT_NO_PRODUCTS_MESSAGE',
            'AGCHECKOUT_IGNORE_DELIVERY_STEP',
            'AGCHECKOUT_DEFAULT_CARRIER',
            'AGCHECKOUT_REQUIRED_PHONE',
            'AGCHECKOUT_NEWSLETTER_DEFAULT',
            'AGCHECKOUT_ALLOW_ADDRESS_EDIT',

            'AGCHECKOUT_FACEBOOK_APP_ID',
            'AGCHECKOUT_FACEBOOK_APP_SECRET',
            'AGCHECKOUT_FACEBOOK_VERSION_APP',

            'AGCHECKOUT_GOOGLE_BTN_TYPE',
            'AGCHECKOUT_GOOGLE_BTN_THEME',
            'AGCHECKOUT_GOOGLE_BTN_SIZE',
            'AGCHECKOUT_GOOGLE_BTN_TEXT',
            'AGCHECKOUT_GOOGLE_BTN_SHAPE',
            'AGCHECKOUT_GOOGLE_BTN_LOGO',

            'AGCHECKOUT_GOOGLE_KEY',
            'AGCHECKOUT_GOOGLE_PROMPT',

            'AGCHECKOUT_RECAPTCHA_PUBLIC_KEY',
            'AGCHECKOUT_RECAPTCHA_PRIVATE_KEY',


        ]);

        $this->setRedirectAfterLogin($config['AGCHECKOUT_REFRESH_AFTER_LOGIN']);
        $this->setNoProductsText($config['AGCHECKOUT_NO_PRODUCTS_MESSAGE']);
        $this->setIgnoreDeliveryStep($config['AGCHECKOUT_IGNORE_DELIVERY_STEP']);
        $this->setDefaultCarrier($config['AGCHECKOUT_DEFAULT_CARRIER']);
        $this->setRequiredPhone($config['AGCHECKOUT_REQUIRED_PHONE']);
        $this->setDefaultNewsletter($config['AGCHECKOUT_NEWSLETTER_DEFAULT']);
        $this->setAllowAddressEdit($config['AGCHECKOUT_ALLOW_ADDRESS_EDIT']);

        $this->setFacebookAppId($config['AGCHECKOUT_FACEBOOK_APP_ID']);
        $this->setFacebookAppSecret($config['AGCHECKOUT_FACEBOOK_APP_SECRET']);
        $this->setFacebookVersionApp($config['AGCHECKOUT_FACEBOOK_VERSION_APP']);

    
        
        $this->setGoogleBtnType($config['AGCHECKOUT_GOOGLE_BTN_TYPE']);
        $this->setGoogleBtnTheme($config['AGCHECKOUT_GOOGLE_BTN_THEME']);
        $this->setGoogleBtnSize($config['AGCHECKOUT_GOOGLE_BTN_SIZE']);
        $this->setGoogleBtnText($config['AGCHECKOUT_GOOGLE_BTN_TEXT']);
        $this->setGoogleBtnShape($config['AGCHECKOUT_GOOGLE_BTN_SHAPE']);
        $this->setGoogleBtnLogo($config['AGCHECKOUT_GOOGLE_BTN_LOGO']);

        $this->setGoogleBtnKey($config['AGCHECKOUT_GOOGLE_KEY']);
        $this->setGoogleBtnPrompt($config['AGCHECKOUT_GOOGLE_PROMPT']);

        $this->setRecaptchaPublicKey($config['AGCHECKOUT_RECAPTCHA_PUBLIC_KEY']);
        $this->setRecaptchaPrivateKey($config['AGCHECKOUT_RECAPTCHA_PRIVATE_KEY']);
        
    }

    public function persist()
    {
        if ($this->getIgnoreDeliveryStep() && !$this->getDefaultCarrier()) {
            throw new ValidationException("Você deve escolher a transportadora padrão se ativar a opção de ignorar a etapa de entrega.");
        }

        \Configuration::updateValue('AGCHECKOUT_REFRESH_AFTER_LOGIN', $this->getRedirectAfterLogin());
        \Configuration::updateValue('AGCHECKOUT_REQUIRED_PHONE', $this->getRequiredPhone());
        \Configuration::updateValue('AGCHECKOUT_NO_PRODUCTS_MESSAGE', $this->getNoProductsText(), true);
        \Configuration::updateValue('AGCHECKOUT_IGNORE_DELIVERY_STEP', $this->getIgnoreDeliveryStep());
        \Configuration::updateValue('AGCHECKOUT_DEFAULT_CARRIER', $this->getDefaultCarrier());
        \Configuration::updateValue('AGCHECKOUT_NEWSLETTER_DEFAULT', $this->getDefaultNewsletter());
        \Configuration::updateValue('AGCHECKOUT_ALLOW_ADDRESS_EDIT', $this->getAllowAddressEdit());
    }

    public function persistLogin()
    {
        \Configuration::updateValue('AGCHECKOUT_FACEBOOK_APP_ID', $this->getFacebookAppId());
        \Configuration::updateValue('AGCHECKOUT_FACEBOOK_APP_SECRET', $this->getFacebookAppSecret());
        \Configuration::updateValue('AGCHECKOUT_FACEBOOK_VERSION_APP', $this->getFacebookVersionApp());

    }

    public function persistGoogle()
    {

        \Configuration::updateValue('AGCHECKOUT_GOOGLE_PROMPT', $this->getGoogleBtnPrompt());
        \Configuration::updateValue('AGCHECKOUT_GOOGLE_KEY', $this->getGoogleBtnKey());
        \Configuration::updateValue('AGCHECKOUT_GOOGLE_BTN_TYPE', $this->getGoogleBtnType());
        \Configuration::updateValue('AGCHECKOUT_GOOGLE_BTN_THEME', $this->getGoogleBtnTheme());
        \Configuration::updateValue('AGCHECKOUT_GOOGLE_BTN_SIZE', $this->getGoogleBtnSize());
        \Configuration::updateValue('AGCHECKOUT_GOOGLE_BTN_TEXT', $this->getGoogleBtnText());
        \Configuration::updateValue('AGCHECKOUT_GOOGLE_BTN_SHAPE', $this->getGoogleBtnShape());
        \Configuration::updateValue('AGCHECKOUT_GOOGLE_BTN_LOGO', $this->getGoogleBtnLogo());
    }

    public function persistRecaptcha()
    {

        \Configuration::updateValue('AGCHECKOUT_RECAPTCHA_PUBLIC_KEY', $this->getRecaptchaPublicKey());
        \Configuration::updateValue('AGCHECKOUT_RECAPTCHA_PRIVATE_KEY', $this->getRecaptchaPrivateKey());
    }

    /**
     * Get the value of noProductsText
     */ 
    public function getNoProductsText()
    {
        return $this->noProductsText;
    }

    /**
     * Set the value of noProductsText
     *
     * @return  self
     */ 
    public function setNoProductsText($noProductsText)
    {
        $this->noProductsText = $noProductsText;

        return $this;
    }

    /**
     * Get the value of ignoreDeliveryStep
     */ 
    public function getIgnoreDeliveryStep()
    {
        return $this->ignoreDeliveryStep;
    }

    /**
     * Set the value of ignoreDeliveryStep
     *
     * @return  self
     */ 
    public function setIgnoreDeliveryStep($ignoreDeliveryStep)
    {
        $this->ignoreDeliveryStep = $ignoreDeliveryStep;

        return $this;
    }

    /**
     * Get the value of defaultCarrier
     */ 
    public function getDefaultCarrier()
    {
        return $this->defaultCarrier;
    }

    /**
     * Set the value of defaultCarrier
     *
     * @return  self
     */ 
    public function setDefaultCarrier($defaultCarrier)
    {
        $this->defaultCarrier = $defaultCarrier;

        return $this;
    }

    /**
     * Get the value of googleBtnLogo
     */ 
    public function getGoogleBtnLogo()
    {
        return $this->googleBtnLogo;
    }

    /**
     * Set the value of googleBtnLogo
     *
     * @return  self
     */ 
    public function setGoogleBtnLogo($googleBtnLogo)
    {
        $this->googleBtnLogo = $googleBtnLogo;

        return $this;
    }

    /**
     * Get the value of googleBtnKey
     */ 
    public function getGoogleBtnKey()
    {
        return $this->googleBtnKey;
    }

    /**
     * Set the value of googleBtnKey
     *
     * @return  self
     */ 
    public function setGoogleBtnKey($googleBtnKey)
    {
        $this->googleBtnKey = $googleBtnKey;

        return $this;
    }

    /**
     * Get the value of googleBtnShape
     */ 
    public function getGoogleBtnShape()
    {
        return $this->googleBtnShape;
    }

    /**
     * Set the value of googleBtnShape
     *
     * @return  self
     */ 
    public function setGoogleBtnShape($googleBtnShape)
    {
        $this->googleBtnShape = $googleBtnShape;

        return $this;
    }

    /**
     * Get the value of googleBtnText
     */ 
    public function getGoogleBtnText()
    {
        return $this->googleBtnText;
    }

    /**
     * Set the value of googleBtnText
     *
     * @return  self
     */ 
    public function setGoogleBtnText($googleBtnText)
    {
        $this->googleBtnText = $googleBtnText;

        return $this;
    }

    /**
     * Get the value of googleBtnSize
     */ 
    public function getGoogleBtnSize()
    {
        return $this->googleBtnSize;
    }

    /**
     * Set the value of googleBtnSize
     *
     * @return  self
     */ 
    public function setGoogleBtnSize($googleBtnSize)
    {
        $this->googleBtnSize = $googleBtnSize;

        return $this;
    }

    /**
     * Get the value of googleBtnTheme
     */ 
    public function getGoogleBtnTheme()
    {
        return $this->googleBtnTheme;
    }

    /**
     * Set the value of googleBtnTheme
     *
     * @return  self
     */ 
    public function setGoogleBtnTheme($googleBtnTheme)
    {
        $this->googleBtnTheme = $googleBtnTheme;

        return $this;
    }

    /**
     * Get the value of googleBtnType
     */ 
    public function getGoogleBtnType()
    {
        return $this->googleBtnType;
    }

    /**
     * Set the value of googleBtnType
     *
     * @return  self
     */ 
    public function setGoogleBtnType($googleBtnType)
    {
        $this->googleBtnType = $googleBtnType;

        return $this;
    }


    /**
     * Get the value of googleBtnPrompt
     */ 
    public function getGoogleBtnPrompt()
    {
        return $this->googleBtnPrompt;
    }

    /**
     * Set the value of googleBtnPrompt
     *
     * @return  self
     */ 
    public function setGoogleBtnPrompt($googleBtnPrompt)
    {
        $this->googleBtnPrompt = $googleBtnPrompt;

        return $this;
    }

    /**
     * Get the value of allowAddressEdit
     */ 
    public function getAllowAddressEdit()
    {
        return $this->allowAddressEdit;
    }

    /**
     * Set the value of allowAddressEdit
     *
     * @return  self
     */ 
    public function setAllowAddressEdit($allowAddressEdit)
    {
        $this->allowAddressEdit = $allowAddressEdit;

        return $this;
    }

    /**
     * Get the value of defaultNewsletter
     */ 
    public function getDefaultNewsletter()
    {
        return $this->defaultNewsletter;
    }

    /**
     * Set the value of defaultNewsletter
     *
     * @return  self
     */ 
    public function setDefaultNewsletter($defaultNewsletter)
    {
        $this->defaultNewsletter = $defaultNewsletter;

        return $this;
    }

    /**
     * Get the value of recaptchaPublicKey
     */ 
    public function getRecaptchaPublicKey()
    {
        return $this->recaptchaPublicKey;
    }

    /**
     * Set the value of recaptchaPublicKey
     *
     * @return  self
     */ 
    public function setRecaptchaPublicKey($recaptchaPublicKey)
    {
        $this->recaptchaPublicKey = $recaptchaPublicKey;

        return $this;
    }

    /**
     * Get the value of recaptchaPrivateKey
     */ 
    public function getRecaptchaPrivateKey()
    {
        return $this->recaptchaPrivateKey;
    }

    /**
     * Set the value of recaptchaPrivateKey
     *
     * @return  self
     */ 
    public function setRecaptchaPrivateKey($recaptchaPrivateKey)
    {
        $this->recaptchaPrivateKey = $recaptchaPrivateKey;

        return $this;
    }
}
