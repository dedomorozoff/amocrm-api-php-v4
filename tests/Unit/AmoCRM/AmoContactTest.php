<?php

declare(strict_types=1);

namespace Tests\Unit\AmoCRM;

use PHPUnit\Framework\TestCase;
use AmoCRM\AmoContact;

class AmoContactTest extends TestCase
{
    /**
     * @var AmoContact
     */
    private $amoContact;

    protected function setUp(): void
    {
        parent::setUp();
        $this->amoContact = new AmoContact();
    }

    // addLeads() tests

    public function testAddLeadsInteger()
    {
        $this->amoContact->addLeads(12345678);
        $this->assertEquals(['id' => [12345678]], $this->amoContact->leads);
    }

    public function testAddLeadsArray()
    {
        $this->amoContact->addLeads([12345678, 12345679, 12345680]);
        $this->assertEquals(['id' => [12345678, 12345679, 12345680]], $this->amoContact->leads);
    }

    public function testAddLeadsIntegerDuplicate()
    {
        $this->amoContact->addLeads(12345678);
        $this->amoContact->addLeads(12345678);
        $this->assertEquals(['id' => [12345678]], $this->amoContact->leads);
    }

    public function testAddLeadsArrayDuplicate()
    {
        $this->amoContact->addLeads([12345678, 12345679]);
        $this->amoContact->addLeads([12345679, 12345680]);
        $this->assertEquals(['id' => [12345678, 12345679, 12345680]], $this->amoContact->leads);
    }

    // addCompany() tests

    public function testAddCompany()
    {
        $this->amoContact->addCompany(12345678);
        $this->assertEquals(['id' => 12345678], $this->amoContact->company);
    }

    public function testAddCompanyTwice()
    {
        $this->amoContact->addCompany(12345678);
        $this->amoContact->addCompany(12345672);
        $this->assertEquals(['id' => 12345672], $this->amoContact->company);
    }

    // getPhone() tests - обновлено для v4 API

    public function testGetPhoneWithFieldCode()
    {
        $this->amoContact->custom_fields_values = [
            [
                'field_code' => 'PHONE',
                'values' => [
                    ['value' => '+79991234567']
                ]
            ]
        ];
        
        $phone = $this->amoContact->getPhone();
        $this->assertEquals('+79991234567', $phone);
    }

    public function testGetPhoneWithoutFieldCode()
    {
        $this->amoContact->custom_fields_values = [
            [
                'field_id' => 123,
                'values' => [
                    ['value' => '+79991234567']
                ]
            ]
        ];
        
        $phone = $this->amoContact->getPhone();
        $this->assertEquals('+79991234567', $phone);
    }

    public function testGetPhoneWithMultipleValues()
    {
        $this->amoContact->custom_fields_values = [
            [
                'field_id' => 123,
                'values' => [
                    ['value' => '+79991234567'],
                    ['value' => '+79991234568']
                ]
            ]
        ];
        
        $phone = $this->amoContact->getPhone();
        $this->assertEquals('+79991234567', $phone);
    }

    public function testGetPhoneWhenNoPhone()
    {
        $this->amoContact->custom_fields_values = [
            [
                'field_code' => 'EMAIL',
                'values' => [
                    ['value' => 'test@example.com']
                ]
            ]
        ];
        
        $phone = $this->amoContact->getPhone();
        $this->assertNull($phone);
    }

    public function testGetPhoneWhenEmptyFields()
    {
        $this->amoContact->custom_fields_values = [];
        $phone = $this->amoContact->getPhone();
        $this->assertNull($phone);
    }

    // getEmail() tests - обновлено для v4 API

    public function testGetEmailWithFieldCode()
    {
        $this->amoContact->custom_fields_values = [
            [
                'field_code' => 'EMAIL',
                'values' => [
                    ['value' => 'test@example.com']
                ]
            ]
        ];
        
        $email = $this->amoContact->getEmail();
        $this->assertEquals('test@example.com', $email);
    }

    public function testGetEmailWithoutFieldCode()
    {
        $this->amoContact->custom_fields_values = [
            [
                'field_id' => 456,
                'values' => [
                    ['value' => 'test@example.com']
                ]
            ]
        ];
        
        $email = $this->amoContact->getEmail();
        $this->assertEquals('test@example.com', $email);
    }

    public function testGetEmailWithMultipleValues()
    {
        $this->amoContact->custom_fields_values = [
            [
                'field_id' => 456,
                'values' => [
                    ['value' => 'test1@example.com'],
                    ['value' => 'test2@example.com']
                ]
            ]
        ];
        
        $email = $this->amoContact->getEmail();
        $this->assertEquals('test1@example.com', $email);
    }

    public function testGetEmailWhenNoEmail()
    {
        $this->amoContact->custom_fields_values = [
            [
                'field_code' => 'PHONE',
                'values' => [
                    ['value' => '+79991234567']
                ]
            ]
        ];
        
        $email = $this->amoContact->getEmail();
        $this->assertNull($email);
    }

    public function testGetEmailWhenEmptyFields()
    {
        $this->amoContact->custom_fields_values = [];
        $email = $this->amoContact->getEmail();
        $this->assertNull($email);
    }

    // getParams() tests

    public function testGetParamsWithCompany()
    {
        $this->amoContact->addCompany(12345678);
        $params = $this->amoContact->getParams();
        $this->assertEquals(12345678, $params['company_id']);
    }

    public function testGetParamsWithLeads()
    {
        $this->amoContact->addLeads([12345678, 12345679]);
        $params = $this->amoContact->getParams();
        $this->assertEquals([12345678, 12345679], $params['leads_id']);
    }
}

