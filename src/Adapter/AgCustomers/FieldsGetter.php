<?php

namespace AGTI\Checkout\Adapter\AgCustomers;

use AGTI\Checkout\Entity\CustomerField;
use AGTI\Checkout\Entity\PersonType;
use AGTI\Checkout\Exception\ModuleNotSupported;

class FieldsGetter
{
    /**
     * Busca todos os campos do formulário de cadastro para um tipo de pessoa.
     * 
     * @return CustomerField[]
     * @throws ModuleNotSupported O tipo de pessoa pertence a um módulo de cadastro incompatível.
     */
    public static function getFields(PersonType $personType)
    {
        /** @var agcustomers */
        $module = $personType->getModule();

        if (strtolower(get_class($module)) !== 'agcustomers') {
            throw new ModuleNotSupported("Esse método só deve ser chamado para o módulo agcustomers, e não suporta o módulo " . get_class($module));
        }

        $return = [];

        $options = $module->getOptions();
        foreach ($options['fields']['customer'] as $field) {
            if (isset($field['insert'][$personType->getId()]) && $field['insert'][$personType->getId()]) {
                $required = isset($field['required'][$personType->getId()]) && $field['required'][$personType->getId()];

                
                $return[] = (new CustomerField)
                    ->setName(is_array($field['label']) ? $field['label'][\Context::getContext()->language->id] : $field['label'])
                    ->setId($field['name'])
                    ->setRequired($required);
            }
        }

        return $return;
    }

    public static function getDataFromCustomer(\Customer $customer)
    {
        $return = [];
        
        $module = new \agcustomers;
        $options = $module->getOptions();
        foreach ($options['fields']['customer'] as $field) {
            $return[$field['name']] = $customer->{$field['name']} ?? '';
        }
        return $return;
    }
}