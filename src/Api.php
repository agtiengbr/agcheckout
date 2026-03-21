<?php

namespace AGTI\Checkout;

use AGTI\Checkout\Adapter\AgCustomers\PersonTypeGetter;
use AGTI\Checkout\Adapter\FieldsGetter;
use Cart;
use Tools;

class Api
{
    public static function getCartData(\Cart $cart)
    {
        $products = $cart->getProducts();
        $products_to_return = [];

        foreach ($products as $product) {
            $cover = \Product::getcover($product['id_product']);
            if ($cover) {
                $image = \Context::getContext()->link->getImageLink($product['link_rewrite'], $product['id_product'] . '-' . $cover['id_image'], 'large_default');
            } else {
                $image = \Context::getContext()->link->getImageLink($product['link_rewrite'], \Context::getContext()->language->iso_code . "-default",  'small_default');
            }
            $products_to_return[] = [
                'reference' =>  $product['reference'],
                'combination' =>  $product['attributes'],
                'name' =>  $product['name'],
                'price' =>  Tools::displayPrice($product['total']),
                'cart_quantity' => $product['cart_quantity'],
                'image' => $image
            ];
        }

        $rules_to_return = [];
        $rules = $cart->getCartRules();
        foreach ($rules as $rule) {
            $rules_to_return[] = [
                'id' => $rule['id_cart_rule'],
                'name' => $rule['name'],
                'discountValue' => $rule['value_real'],
                'discountValueFormatted' => Tools::displayPrice($rule['value_real'])
            ];
        }

        return [
            'id_address_delivery' => $cart->id_address_delivery,
            'id_address_invoice' => $cart->id_address_invoice,
            'products' => $products_to_return,
            'subtotals' => [
                'products' => Tools::displayPrice($cart->getOrderTotal(true, Cart::ONLY_PRODUCTS)),
                'shipping' => Tools::displayPrice($cart->getOrderTotal(true, Cart::ONLY_SHIPPING)),
                'discount' => Tools::displayPrice($cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS)),
                'total' => Tools::displayPrice($cart->getOrderTotal(true)),
            ],
            'vouchers' => $rules_to_return
        ];
    }

    public static function getCustomerData(\Customer $customer)
    {
        return $customer->isLogged() ? self::getCustomerDataArray($customer) : [];
    }

    public static function getAddresses(\Customer $customer, $idLang)
    {
        $addresses = [];

        if ($customer->isLogged()) {
            $addresses = $customer->getAddresses($idLang);
            usort($addresses, function($a1, $a2) {
                return strtotime($a2['date_add']) - strtotime($a1['date_add']);
            });

            foreach ($addresses as &$address) {
                $address['street'] = $address['address1'];
                $address['district'] = $address['address2'];

                unset($address['address1']); 
                unset($address['address2']); 
            }
        }

        return $addresses;
    }

    private static function getCustomerDataArray(\Customer $customer)
    {
        $customer_data = [
            'id' => $customer->id,
            'name' => ($customer->id || $customer->id_customer) ? "$customer->firstname $customer->lastname" : "",
            'email' => $customer->email,
            'birthday' => $customer->birthday,
            'person_type' => PersonTypeGetter::getPersonTypeFromCustomer($customer),
            'newsletter' => (bool) $customer->newsletter
        ];

        $customer_extra_data = FieldsGetter::getDataFromCustomer($customer);
        $customer_data = array_merge($customer_data, $customer_extra_data);
   
        return $customer_data;
    }
}