<?php
namespace AGTI\Checkout\Test;

require_once '../../config/config.inc.php';

class AgCustomersAdapterTest extends \Codeception\Test\Unit
{

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testSomeFeature()
    {
        $this->assertFalse(\Configuration::get('teste'));
    }
}