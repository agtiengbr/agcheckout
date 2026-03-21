<?php

namespace AGTI\Checkout\Adapter\AgCustomers;

use AGTI\Checkout\Adapter\PersonTypeGetter as AdapterPersonTypeGetter;
use AGTI\Checkout\Entity\PersonType;
use AGTI\Checkout\Exception\ModuleNotFound;

class PersonTypeGetter extends AdapterPersonTypeGetter
{
    /**
     * Retorna todos os tipos de pessoa cadastrados no módulo agcustomers
     * 
     * @return PersonType[]
     * @throws ModuleNotFound Classe agcustomers não localizada. Algum include/require não foi realizado,
     * ou o método de carregamento do módulo não foi invocado.
     */
    public static function getPersonTypes()
    {
        if (!class_exists('agcustomers')) {
            throw new ModuleNotFound("A classe agcustomers não foi localizada.");
        }
        $module = new \agcustomers;
        $module_person_types = $module->getOptions()['type_person'];
        
        $return = [];
        foreach ($module_person_types as $module_person_type) {
            if (!$module_person_type['active']) {
                continue;
            }

            if (is_array($module_person_type['label'])) {
                $name = $module_person_type['label'][\Context::getContext()->language->id];
            } else {
                $name = $module_person_type['label'];
            }
            
            $return[] = (new PersonType($module))
                ->setName($name)
                ->setId($module_person_type['name'])
                ->setActive($module_person_type['active']);
        }

        return $return;
    }

    /**
     * @return string
     */
    public static function getPersonTypeFromCustomer(\Customer $customer)
    {
        return $customer->person_type;
    }
}