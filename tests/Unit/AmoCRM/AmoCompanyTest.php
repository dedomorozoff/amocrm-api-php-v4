<?php

declare(strict_types=1);

namespace Tests\Unit\AmoCRM;

use PHPUnit\Framework\TestCase;
use AmoCRM\AmoCompany;

class AmoCompanyTest extends TestCase
{
    /**
     * @var AmoCompany
     */
    private $amoCompany;

    protected function setUp(): void
    {
        parent::setUp();
        $this->amoCompany = new AmoCompany();
    }

    // addLeads() tests

    public function testAddLeadsInteger()
    {
        $this->amoCompany->addLeads(12345678);
        $this->assertEquals(['id' => [12345678]], $this->amoCompany->leads);
    }

    public function testAddLeadsArray()
    {
        $this->amoCompany->addLeads([12345678, 12345679, 12345680]);
        $this->assertEquals(['id' => [12345678, 12345679, 12345680]], $this->amoCompany->leads);
    }

    // addContacts() tests

    public function testAddContactsInteger()
    {
        $this->amoCompany->addContacts(12345678);
        $this->assertEquals(['id' => [12345678]], $this->amoCompany->contacts);
    }

    public function testAddContactsArray()
    {
        $this->amoCompany->addContacts([12345678, 12345679, 12345680]);
        $this->assertEquals(['id' => [12345678, 12345679, 12345680]], $this->amoCompany->contacts);
    }

    // getPhone() tests - обновлено для v4 API

    public function testGetPhoneWithFieldCode()
    {
        $this->amoCompany->custom_fields_values = [
            [
                'field_code' => 'PHONE',
                'values' => [
                    ['value' => '+79991234567']
                ]
            ]
        ];
        
        $phone = $this->amoCompany->getPhone();
        $this->assertEquals('+79991234567', $phone);
    }

    public function testGetPhoneWithoutFieldCode()
    {
        $this->amoCompany->custom_fields_values = [
            [
                'field_id' => 123,
                'values' => [
                    ['value' => '+79991234567']
                ]
            ]
        ];
        
        $phone = $this->amoCompany->getPhone();
        $this->assertEquals('+79991234567', $phone);
    }

    public function testGetPhoneWhenNoPhone()
    {
        $this->amoCompany->custom_fields_values = [
            [
                'field_code' => 'EMAIL',
                'values' => [
                    ['value' => 'test@example.com']
                ]
            ]
        ];
        
        $phone = $this->amoCompany->getPhone();
        $this->assertNull($phone);
    }

    // getEmail() tests - обновлено для v4 API

    public function testGetEmailWithFieldCode()
    {
        $this->amoCompany->custom_fields_values = [
            [
                'field_code' => 'EMAIL',
                'values' => [
                    ['value' => 'test@example.com']
                ]
            ]
        ];
        
        $email = $this->amoCompany->getEmail();
        $this->assertEquals('test@example.com', $email);
    }

    public function testGetEmailWithoutFieldCode()
    {
        $this->amoCompany->custom_fields_values = [
            [
                'field_id' => 456,
                'values' => [
                    ['value' => 'test@example.com']
                ]
            ]
        ];
        
        $email = $this->amoCompany->getEmail();
        $this->assertEquals('test@example.com', $email);
    }

    public function testGetEmailWhenNoEmail()
    {
        $this->amoCompany->custom_fields_values = [
            [
                'field_code' => 'PHONE',
                'values' => [
                    ['value' => '+79991234567']
                ]
            ]
        ];
        
        $email = $this->amoCompany->getEmail();
        $this->assertNull($email);
    }

    // getParams() tests

    public function testGetParamsWithLeads()
    {
        $this->amoCompany->addLeads([12345678, 12345679]);
        $params = $this->amoCompany->getParams();
        $this->assertEquals([12345678, 12345679], $params['leads_id']);
    }

    public function testGetParamsWithContacts()
    {
        $this->amoCompany->addContacts([12345678, 12345679]);
        $params = $this->amoCompany->getParams();
        $this->assertEquals([12345678, 12345679], $params['contacts_id']);
    }
}

