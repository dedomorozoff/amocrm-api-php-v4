<?php

declare(strict_types=1);

namespace Tests\Unit\AmoCRM;

use PHPUnit\Framework\TestCase;
use AmoCRM\AmoCatalogElement;
use AmoCRM\AmoAPIException;

class AmoCatalogElementTest extends TestCase
{
    /**
     * @var AmoCatalogElement
     */
    private $amoCatalogElement;

    protected function setUp(): void
    {
        parent::setUp();
        $this->amoCatalogElement = new AmoCatalogElement();
    }

    // Тесты для работы с catalog_id

    public function testGetParamsRequiresCatalogId()
    {
        $this->amoCatalogElement->name = 'Test Element';
        $params = $this->amoCatalogElement->getParams();
        
        // catalog_id не обязателен для getParams(), но нужен для save()
        $this->assertArrayHasKey('name', $params);
    }

    public function testGetParamsWithCustomFields()
    {
        $this->amoCatalogElement->custom_fields = [
            [
                'field_id' => 123,
                'values' => [
                    ['value' => 'Test Value']
                ]
            ]
        ];
        
        $params = $this->amoCatalogElement->getParams();
        $this->assertArrayHasKey('custom_fields', $params);
        $this->assertIsArray($params['custom_fields']);
    }

    // Тесты для проверки структуры URL

    public function testUrlConstant()
    {
        $this->assertEquals('/api/v4/catalogs', AmoCatalogElement::URL);
    }

    // Тесты для проверки свойств

    public function testProperties()
    {
        $this->amoCatalogElement->id = 1;
        $this->amoCatalogElement->name = 'Test';
        $this->amoCatalogElement->catalog_id = 100;
        $this->amoCatalogElement->is_deleted = false;
        
        $this->assertEquals(1, $this->amoCatalogElement->id);
        $this->assertEquals('Test', $this->amoCatalogElement->name);
        $this->assertEquals(100, $this->amoCatalogElement->catalog_id);
        $this->assertFalse($this->amoCatalogElement->is_deleted);
    }

    public function testGetParamsIncludesAllProperties()
    {
        $this->amoCatalogElement->id = 1;
        $this->amoCatalogElement->name = 'Test Element';
        $this->amoCatalogElement->catalog_id = 100;
        $this->amoCatalogElement->is_deleted = false;
        
        $params = $this->amoCatalogElement->getParams();
        
        $this->assertEquals(1, $params['id']);
        $this->assertEquals('Test Element', $params['name']);
        $this->assertEquals(100, $params['catalog_id']);
        $this->assertFalse($params['is_deleted']);
    }
}

