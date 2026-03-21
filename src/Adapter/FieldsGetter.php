<?php

namespace AGTI\Checkout\Adapter;

use AGTI\Checkout\Adapter\AgCustomers\FieldsGetter as AgCustomersFieldsGetter;
use AGTI\Checkout\Entity\PersonType;
use AGTI\Checkout\Exception\ModuleNotSupported;

class FieldsGetter
{
    public static function getFields(PersonType $personType)
    {
        switch(strtolower(get_class($personType->getModule()))) {
            case 'agcustomers':
                return AgCustomersFieldsGetter::getFields($personType);
                break;
            default:
                throw new ModuleNotSupported("Módulo " . get_class($personType->getModule()) . " não suportado.");
                break;
        }
    }
    
    public static function getDataFromCustomer(\Customer $customer)
    {
        return AgCustomersFieldsGetter::getDataFromCustomer($customer);
    }
}