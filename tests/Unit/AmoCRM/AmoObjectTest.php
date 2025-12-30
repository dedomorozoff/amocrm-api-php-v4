<?php

declare(strict_types=1);

namespace Tests\Unit\AmoCRM;

use PHPUnit\Framework\TestCase;
use AmoCRM\AmoLead;
use AmoCRM\AmoContact;

class AmoObjectTest extends TestCase
{
    /**
     * Тесты для метода getParams() - проверка структуры для v4
     */
    public function testGetParamsIncludesCustomFieldsValues()
    {
        $lead = new AmoLead();
        $lead->custom_fields_values = [
            [
                'field_id' => 123,
                'values' => [
                    ['value' => 'Test Value']
                ]
            ]
        ];
        
        $params = $lead->getParams();
        $this->assertArrayHasKey('custom_fields_values', $params);
        $this->assertIsArray($params['custom_fields_values']);
    }

    public function testGetParamsIncludesTags()
    {
        $lead = new AmoLead();
        $lead->tags = [
            ['name' => 'Tag1'],
            ['name' => 'Tag2']
        ];
        
        $params = $lead->getParams();
        $this->assertArrayHasKey('tags', $params);
        $this->assertEquals(['Tag1', 'Tag2'], $params['tags']);
    }

    public function testGetParamsIncludesUpdatedAtForExistingEntity()
    {
        $lead = new AmoLead(['id' => 123]);
        $params = $lead->getParams();
        
        $this->assertArrayHasKey('updated_at', $params);
        $this->assertIsInt($params['updated_at']);
    }

    public function testGetParamsDoesNotIncludeUpdatedAtForNewEntity()
    {
        $lead = new AmoLead();
        $params = $lead->getParams();
        
        $this->assertArrayNotHasKey('updated_at', $params);
    }

    /**
     * Тесты для работы с custom_fields_values
     */
    public function testSetCustomFields()
    {
        $lead = new AmoLead();
        $lead->setCustomFields([
            123 => 'Value1',
            456 => ['Value2', 'Value3']
        ]);
        
        $this->assertCount(2, $lead->custom_fields_values);
    }

    public function testGetCustomFieldValueById()
    {
        $lead = new AmoLead();
        $lead->custom_fields_values = [
            [
                'field_id' => 123,
                'values' => [
                    ['value' => 'Value1'],
                    ['value' => 'Value2']
                ]
            ]
        ];
        
        $value = $lead->getCustomFieldValueById(123);
        $this->assertEquals('Value1', $value);
        
        $values = $lead->getCustomFieldValueById(123, false);
        $this->assertCount(2, $values);
    }

    public function testGetCustomFieldValueByIdReturnsNull()
    {
        $lead = new AmoLead();
        $value = $lead->getCustomFieldValueById(999);
        $this->assertNull($value);
    }

    /**
     * Тесты для работы с тегами
     */
    public function testAddTags()
    {
        $lead = new AmoLead();
        $lead->addTags('Tag1');
        $lead->addTags(['Tag2', 'Tag3']);
        
        $this->assertCount(3, $lead->tags);
    }

    public function testAddTagsPreventsDuplicates()
    {
        $lead = new AmoLead();
        $lead->addTags('Tag1');
        $lead->addTags('Tag1');
        
        $this->assertCount(1, $lead->tags);
    }

    public function testDelTags()
    {
        $lead = new AmoLead();
        $lead->tags = [
            ['name' => 'Tag1'],
            ['name' => 'Tag2'],
            ['name' => 'Tag3']
        ];
        
        $lead->delTags('Tag2');
        $this->assertCount(2, $lead->tags);
    }
}

