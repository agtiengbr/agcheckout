<?php
namespace AGTI\Checkout\Test;

use AGTI\Checkout\Adapter\AgCustomers\PersonTypeGetter;
use AGTI\Checkout\Entity\PersonType;
use AGTI\Checkout\Exception\ModuleNotFound;

require_once '../../config/config.inc.php';

class PersonTypeGetterTest extends \Codeception\Test\Unit
{
    use \Codeception\AssertThrows;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testSomeFeature()
    {
    }
}