<?php

namespace AGTI\Checkout\Adapter;

use AGTI\Checkout\Adapter\AgCustomers\PersonTypeGetter as AgCustomersPersonTypeGetter;

class PersonTypeGetter
{
    /**
     * Retorna todos os tipos de pessoa cadastrados. Por hora esse método é compatível, apenas, com o módulo agcustomers.
     * Mas ele existe para que seja possível compatibilizarmos o checkout com outros módulos de cadastro,
     * como o ngstandard da NeoGest.
     * 
     * @return PersonType[]
     * @throws ModuleNotFound Classe agcustomers não localizada. Algum include/require não foi realizado,
     * ou o método de carregamento do módulo não foi invocado.
     */
    public static function getPersonTypes()
    {
        return AgCustomersPersonTypeGetter::getPersonTypes();
    }

    public static function getPersonTypeFromCustomer(\Customer $customer)
    {
        return AgCustomersPersonTypeGetter::getPersonTypeFromCustomer($customer);
    }
}