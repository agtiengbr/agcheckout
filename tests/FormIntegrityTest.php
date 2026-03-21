<?php
namespace AGTI\Checkout\Test;

use AGTI\Checkout\Entity\AddressField;
use AGTI\Checkout\Entity\CustomerField;
use AGTI\Checkout\Entity\Form;
use AGTI\Checkout\Entity\Row;
use AGTI\Checkout\Enum\FormType;
use AGTI\Checkout\Exception\InconsistentFieldType;

class FormIntegrityTest extends \Codeception\Test\Unit
{
    use \Codeception\AssertThrows;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testValidCustomerRow()
    {
        $this->assertDoesNotThrow(InconsistentFieldType::class, function(){
            $field = new CustomerField;
            $row = new Row(FormType::FORM_TYPE_CUSTOMER);
            $row->addField($field);
        });

        $this->assertDoesNotThrow(InconsistentFieldType::class, function(){
            $field = new AddressField;
            $row = new Row(FormType::FORM_TYPE_ADDRESS);
            $row->addField($field);
        });
    }

    public function testInvalidCustomerRow()
    {
        $this->assertThrows(InconsistentFieldType::class, function(){
            $field = new CustomerField;
            $row = new Row(FormType::FORM_TYPE_ADDRESS);
            $row->addField($field);   
        });

        $this->assertThrows(InconsistentFieldType::class, function(){
            $field = new AddressField;
            $row = new Row(FormType::FORM_TYPE_CUSTOMER);
            $row->addField($field);   
        });
    }

    public function testValidForm()
    {
        $this->assertDoesNotThrow(InconsistentFieldType::class, function(){
            $form = new Form(FormType::FORM_TYPE_ADDRESS);
            $row = new Row(FormType::FORM_TYPE_ADDRESS);

            $form->addRow($row);
        });

        $this->assertDoesNotThrow(InconsistentFieldType::class, function(){
            $form = new Form(FormType::FORM_TYPE_CUSTOMER);
            $row = new Row(FormType::FORM_TYPE_CUSTOMER);

            $form->addRow($row);
        });
    }

    public function testInvalidForm()
    {
        $this->assertThrows(InconsistentFieldType::class, function(){
            $form = new Form(FormType::FORM_TYPE_ADDRESS);
            $row = new Row(FormType::FORM_TYPE_CUSTOMER);

            $form->addRow($row);
        });

        $this->assertThrows(InconsistentFieldType::class, function(){
            $form = new Form(FormType::FORM_TYPE_CUSTOMER);
            $row = new Row(FormType::FORM_TYPE_ADDRESS);

            $form->addRow($row);
        });
    }
    
}