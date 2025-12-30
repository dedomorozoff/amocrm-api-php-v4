<?php

declare(strict_types=1);

namespace Tests\Unit\AmoCRM;

use PHPUnit\Framework\TestCase;
use AmoCRM\AmoAPI;
use AmoCRM\AmoLead;
use AmoCRM\AmoContact;
use AmoCRM\AmoCompany;

class AmoAPITest extends TestCase
{
    /**
     * Тест метода getItems() для парсинга ответов v4 API
     */
    public function testGetItemsWithEmbeddedLeads()
    {
        $response = [
            '_embedded' => [
                'leads' => [
                    ['id' => 1, 'name' => 'Lead 1'],
                    ['id' => 2, 'name' => 'Lead 2']
                ]
            ]
        ];
        
        $result = AmoAPI::getItems($response);
        $this->assertCount(2, $result);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals(2, $result[1]['id']);
    }

    public function testGetItemsWithEmbeddedContacts()
    {
        $response = [
            '_embedded' => [
                'contacts' => [
                    ['id' => 1, 'name' => 'Contact 1']
                ]
            ]
        ];
        
        $result = AmoAPI::getItems($response);
        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['id']);
    }

    public function testGetItemsWithEmbeddedUnsorted()
    {
        $response = [
            '_embedded' => [
                'unsorted' => [
                    ['uid' => 'uid1', 'category' => 'form']
                ]
            ]
        ];
        
        $result = AmoAPI::getItems($response);
        $this->assertCount(1, $result);
        $this->assertEquals('uid1', $result[0]['uid']);
    }

    public function testGetItemsWithEmbeddedElements()
    {
        $response = [
            '_embedded' => [
                'elements' => [
                    ['id' => 1, 'name' => 'Element 1']
                ]
            ]
        ];
        
        $result = AmoAPI::getItems($response);
        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['id']);
    }

    public function testGetItemsWithoutEmbedded()
    {
        $response = [
            'id' => 1,
            'name' => 'Test'
        ];
        
        $result = AmoAPI::getItems($response);
        $this->assertEquals($response, $result);
    }

    public function testGetItemsWithNull()
    {
        $result = AmoAPI::getItems(null);
        $this->assertNull($result);
    }

    /**
     * Тест структуры запросов для saveObjects() - проверка разделения на POST и PATCH
     */
    public function testSaveObjectsStructureForV4()
    {
        // Создаем лид без ID (для POST)
        $lead1 = new AmoLead(['name' => 'New Lead']);
        
        // Создаем лид с ID (для PATCH)
        $lead2 = new AmoLead(['id' => 123, 'name' => 'Updated Lead']);
        
        // Проверяем структуру getParams()
        $params1 = $lead1->getParams();
        $this->assertArrayNotHasKey('id', $params1);
        
        $params2 = $lead2->getParams();
        $this->assertEquals(123, $params2['id']);
    }

    /**
     * Тест структуры запросов для deleteObjects() - проверка формирования массива ID
     */
    public function testDeleteObjectsStructureForV4()
    {
        $lead1 = new AmoLead(['id' => 1]);
        $lead2 = new AmoLead(['id' => 2]);
        
        $params1 = $lead1->getParams();
        $params2 = $lead2->getParams();
        
        $this->assertEquals(1, $params1['id']);
        $this->assertEquals(2, $params2['id']);
    }
}

