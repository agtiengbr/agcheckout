<?php

use AGTI\Checkout\Adapter\FieldsGetter;
use AGTI\Checkout\Adapter\ModuleLoader;
use AGTI\Checkout\Adapter\PersonTypeGetter as AdapterPersonTypeGetter;
use AGTI\Checkout\Api;
use AGTI\Checkout\Entity\Configuration;
use AGTI\Checkout\Entity\PersonType;
use AGTI\Cliente\Exception\AddressNotFoundException;
use AGTI\Cliente\Service\AddressFinder;
use AGTI\Cliente\Entity\ServiceArgs\AddressFinder as ServiceArgsAddressFinder;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;

class agcheckoutapiModuleFrontController extends ModuleFrontController
{
    /** @var Configuration */
    protected $config;
    public function initContent()
    {
        $method = Tools::getValue('method');

        if (method_exists($this, $method)) {
            $this->config = new Configuration;
            $this->config->loadConfig();

            if ($this->context->cart->isVirtualCart()) {
                $this->context->cart->id_carrier = 0;
                $this->context->cart->update();
            } elseif ($this->config->getIgnoreDeliveryStep()) {
                $this->context->cart->id_carrier = $this->config->getDefaultCarrier();
                $this->context->cart->update();
            }
            
            $this->$method();
            exit();
        }

        header("HTTP/1.1 404 Not Found");
        exit();
    }

    protected function getCustomerData()
    {
        try {
            /** @var Customer */
            $customer = $this->context->customer;
            echo json_encode([
                'customer_data' => Api::getCustomerData($customer)
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        exit();
    }

    protected function saveCustomerData()
    {
        try {
            /** @var Customer */
            $customer = $this->context->customer;

            $fullname = trim(Tools::getValue('name'));
            $names = explode(' ', $fullname);
            $customer->firstname = $names[0];
            $customer->lastname = implode(' ', array_splice($names, 1));
            $customer->email = Tools::getValue('email');
            $customer->birthday = Tools::getValue('birthday');
            $customer->passwd  = Tools::encrypt(Tools::getValue('password'));
            $customer->newsletter = Tools::getValue('newsletter') == 'true' ? 1 : 0;
            $recaptchaResponse = Tools::getValue('g-recaptcha-response');
            $secret = \Configuration::get('AGCHECKOUT_RECAPTCHA_PRIVATE_KEY');
            $public = \Configuration::get('AGCHECKOUT_RECAPTCHA_PUBLIC_KEY');

            if (!$customer->email) {
                throw new Exception("Informe o seu endereço de e-mail");
            }
            $this->id = Tools::getValue('id');

            if (!Validate::isEmail($customer->email)) {
                throw new Exception("Informe um e-mail válido.");
            }

            $existing = (new Customer)->getByEmail($customer->email);


            if (!$this->checkRecaptcha($recaptchaResponse)->success && $secret && $public) {
                throw new Exception("ReCaptcha invalido.");
            }

            if (Validate::isLoadedObject($existing) && $existing->id != $this->id) {
                throw new Exception("Esse endereço de e-mail já está cadastrado.");
            }

            if (!$customer->firstname || !$customer->lastname) {
                throw new Exception("Informe o seu nome completo");
            }

            if (\DateTime::createFromFormat('Y-m-d', $customer->birthday) > new \DateTime()) {
                throw new Exception("A data de Nascimento deve estar no passado.");
            }

            $customer->person_type = Tools::getValue('person_type');

            ModuleLoader::loadModule('agcustomers');
            $personType = new PersonType(new agcustomers);
            $personType->setId($customer->person_type);

            foreach (FieldsGetter::getFields($personType) as $field) {
                if ($field->getId() === 'firstname' || $field->getId() === 'lastname') {
                    continue;
                }

                $customer->{$field->getId()} = Tools::getValue($field->getId());
            }
            
            $customer->validateFields(false, true);
            $customer->save();

            $this->context->updateCustomer($customer);
            echo json_encode([
                'success' => true,
                'customer_data' => Api::getCustomerData($customer)
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        exit();
    }

    protected function login()
    {
        $email = Tools::getValue('email');
        $passwd  = Tools::getValue('password');

        if (!Validate::isEmail($email)) {
            throw new Exception("Informe um e-mail válido.");
        }
        
        $customer = $this->context->customer->getByEmail($email, $passwd);

        try {
            if (!Validate::isLoadedObject($customer)) {
                throw new Exception("E-mail ou senha incorretos.");
            }

            $this->context->updateCustomer($customer);

            echo json_encode([
                'success' => true
            ]);
        }  catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        exit();
    }
    
    protected function saveAddress()
    {
        $obj = new Address(Tools::getValue('id_address'));
        try {
            if (Validate::isLoadedObject($obj) && $obj->id_customer != $this->context->customer->id) {
                throw new Exception("Você não pode editar este endereço.");
            }
            
            $postcode = Tools::getValue('postcode');
            $postcode = preg_replace("/[^0-9]/", "", $postcode);

            $obj->id_customer = $this->context->customer->id;
            $obj->postcode = sprintf("%08d", $postcode);
            $obj->alias = 'Endereço';
            $obj->other = Tools::getValue('other');

            $obj->postcode = substr($obj->postcode, 0, 5) . '-' . substr($obj->postcode, 5, 3);

            $names = explode(' ', Tools::getValue('name'));
            $obj->firstname = $names[0];
            $obj->lastname = implode(' ', array_splice($names, 1));

            if (empty($obj->firstname)) {
                $c = new Customer($obj->id_customer);

                $obj->firstname = $c->firstname;
                $obj->lastname = $c->lastname;
            }
            

            $obj->phone_mobile = Tools::getValue('phone');
            
            $obj->address1 = Tools::getValue('street');
            $obj->address2 = Tools::getValue('district');
            $obj->city     = Tools::getValue('city');

            $obj->id_country = Country::getByIso('BR');
            $obj->id_state = State::getIdByIso(Tools::getValue('uf'), $obj->id_country);
            $obj->number = Tools::getValue('number');

            $required_fields = [
                'firstname',
                'lastname',
                'city',
                'address1',
                'address2',
                'id_state',
                'id_country',
                'number'
            ];

            if ($this->config->getRequiredPhone()) {
                $required_fields[] = 'phone_mobile';
            }

            foreach ($required_fields as $field) {
                if ($obj->{$field} == '') {
                    throw new Exception("{$field} é obrigatório.");
                }
            }
            
            $validation_error = $obj->validateFields(false, true);
            if ($validation_error !== true) {
                throw new Exception($validation_error);
            }

            $obj->save();
            $db_error = Db::getInstance()->getMsgError();
            if ($db_error) {
                throw new Exception($db_error);
            }


            if (Tools::getValue('type') == 'delivery' || $this->config->getIgnoreDeliveryStep() || $this->context->cart->isVirtualCart()) {
                $this->context->cart->id_address_delivery = $obj->id;
            }

            if (Tools::getValue('type') == 'invoice') {
                $this->context->cart->id_address_invoice = $obj->id;
            }

            $this->context->cart->update();

            echo json_encode([
                'success' => true,
                'id_address' => $obj->id
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    protected function deleteAddress()
    {
        try {
            $obj = new Address(Tools::getValue('id'));
            if ($obj->id_customer != $this->context->customer->id) {
                throw new Exception("Você não pode excluir este endereço.");
            }

            $obj->delete();
            echo json_encode([
                'success' => true,
                'id_address' => $obj->id
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        
    }

    protected function findAddresses()
    {
        echo json_encode([
            'success' => true,
            'id_address_delivery' => $this->context->cart->id_address_delivery,
            'id_address_invoice' => $this->context->cart->id_address_invoice,
            'addresses' => Api::getAddresses($this->context->customer, $this->context->language->id)
        ]);
    }

    protected function findAddressByPostcode()
    {
        $postcode = Tools::getValue('postcode');
        try {
            $args = new ServiceArgsAddressFinder;
            $args->setPostcode($postcode);

            $service = new AddressFinder;
            $r = $service->exec($args);
            $address = $r->getAddress();

            echo json_encode([
                'success' => true,
                'address' => $address->toJson()
            ]);
        } catch (AddressNotFoundException $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Endereço não encontrado'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function getCarriers()
    {
        $id_address_delivery = Tools::getValue('id_address_delivery');

        $cart = $this->context->cart;
        $cart->updateAddressId($cart->id_address_delivery, $id_address_delivery);
        $cart->id_address_delivery = $id_address_delivery;
        $cart->save();

        $deliveryOptionsFinder = new DeliveryOptionsFinder(
            $this->context,
            $this->getTranslator(),
            $this->objectPresenter,
            new PriceFormatter()
        );

        $carriers = $deliveryOptionsFinder->getDeliveryOptions();
        $result = [];
        foreach ($carriers as $carrier) {
            $result[] = [
                'id_carrier' => $carrier['id'],
                'img' => $carrier['logo'],
                'name' => $carrier['name'],
                'delay' => $carrier['delay'],
                'shipping_cost' => $carrier['price']
            ];
        }

        echo json_encode([
            'success' => true,
            'carriers' => $result,
            'id_carrier' => $this->context->cart->id_carrier
        ]);

        exit();
    }

    protected function getPaymentOptions()
    {
        try {
            $config = new Configuration;
            $config->loadConfig();

            if ($this->context->cart->isVirtualCart()) {
                $idCarrier = 0;
            } elseif ($config->getIgnoreDeliveryStep()) {
                $idCarrier = $config->getDefaultCarrier();
            } else {
                $idCarrier = Tools::getValue('id_carrier');
            }

            if (isset($idCarrier) && $idCarrier) {
                $deliveryOption = [$this->context->cart->id_address_delivery => $idCarrier . ','];
                $this->context->cart->setDeliveryOption($deliveryOption);

                $this->context->cart->id_carrier = $idCarrier;
                $this->context->cart->update();
            }

            $finder = new PaymentOptionsFinder;
            $options = $finder->present();           

            $r = [];
            foreach ($options as $module_options) {
                foreach ($module_options as $option) {
                    $option['is_open'] = false;
                    $r[] = $option;
                }
            }
            
            echo json_encode([
                'success' => true,
                'options' => $r,
                'cart' => Api::getCartData($this->context->cart),
                'total_to_pay' => Tools::displayPrice($this->context->cart->getOrderTotal()),
                'total_unformatted' => $this->context->cart->getOrderTotal()
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'options' => $e->getMessage()
            ]);
        }

        exit();
    }

    protected function loadCart()
    {
        try {
            echo json_encode([
                'success' => true,
                'cart' => Api::getCartData($this->context->cart),
                'total_unformatted' => $this->context->cart->getOrderTotal()
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        exit();
    }

    protected function addVoucher()
    {
        try {
            $code = Tools::getValue('code');
            $id = CartRule::getIdByCode($code);

            if (!$id) {
                throw new Exception('Cupom inválido.');
            }

            $cr = new CartRule($id);
            $err = $cr->checkValidity($this->context);

            if ($err) {
                throw new Exception($err);
            }

            $this->context->cart->addCartRule($cr->id);

            echo json_encode([
                'success' => true,
                'cart' => Api::getCartData($this->context->cart)
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        exit();
    }

    protected function deleteVoucher()
    {
        try {
            $id = Tools::getValue('id');
            $this->context->cart->removeCartRule($id);
            echo json_encode([
                'success' => true,
                'cart' => Api::getCartData($this->context->cart)
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        exit();
    }

    protected function updateAddressInvoice()
    {
        $id = Tools::getValue('id_address_invoice');

        $this->context->cart->id_address_invoice = $id;
        if ($this->config->getIgnoreDeliveryStep() || $this->context->cart->isVirtualCart()) {
            $this->context->cart->id_address_delivery = $id;
        }

        $this->context->cart->save();

        echo json_encode([
            'success' => true
        ]);
    }

    protected function closeFreeOrder()
    {

        echo json_encode(['success' => true, 'redirect_url' => $this->context->link->getPageLink('order-confirmation') . '?free_order=1']);
    }

    public function checkRecaptcha($response){
        $ch = curl_init();

        $secret = \Configuration::get('AGCHECKOUT_RECAPTCHA_PRIVATE_KEY');
        $bodyReq=
        [
            'secret' => $secret,
            'response' => $response
        ];


        curl_setopt($ch, CURLOPT_URL,"https://www.google.com/recaptcha/api/siteverify");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyReq);

        $result = curl_exec($ch);

        return json_decode($result);
    }

}
