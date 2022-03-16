<?php

namespace whikloj\archivematicaPhp\Tests;

use PHPUnit\Framework\TestCase;
use whikloj\archivematicaPhp\DjangoFilter;

/**
 * Tests of the DjangoFilter
 * @author Jared Whiklo
 * @since 0.0.1
 */
class FilterTests extends TestCase
{
    /**
     * @covers \whikloj\archivematicaPhp\DjangoFilter::create
     */
    public function testCreateFailed(): void
    {
        $obj = new \stdClass();
        $obj->value = "some-value";
        $field = "some-field";
        $this->expectException(\InvalidArgumentException::class);
        DjangoFilter::create($field, $obj)->build();
    }

    /**
     * @covers \whikloj\archivematicaPhp\DjangoFilter::__construct
     * @covers \whikloj\archivematicaPhp\DjangoFilter::create
     * @covers \whikloj\archivematicaPhp\DjangoFilter::build
     */
    public function testString(): void
    {
        $field = "some-field";
        $value = "some-value";
        $this->assertEquals("some-field=some-value", DjangoFilter::create($field, $value)->build());
    }

    /**
     * @covers \whikloj\archivematicaPhp\DjangoFilter::__construct
     * @covers \whikloj\archivematicaPhp\DjangoFilter::create
     * @covers \whikloj\archivematicaPhp\DjangoFilter::build
     */
    public function testInteger(): void
    {
        $field = "some-field";
        $value = 5;
        $this->assertEquals("some-field=5", DjangoFilter::create($field, $value)->build());
    }

    /**
     * @covers \whikloj\archivematicaPhp\DjangoFilter::__construct
     * @covers \whikloj\archivematicaPhp\DjangoFilter::create
     * @covers \whikloj\archivematicaPhp\DjangoFilter::build
     */
    public function testFloat(): void
    {
        $field = "some-field";
        $value = 5.5;
        $this->assertEquals("some-field=5.5", DjangoFilter::create($field, $value)->build());
    }

    /**
     * @covers \whikloj\archivematicaPhp\DjangoFilter::__construct
     * @covers \whikloj\archivematicaPhp\DjangoFilter::create
     * @covers \whikloj\archivematicaPhp\DjangoFilter::build
     */
    public function testBoolean(): void
    {
        $field = "some-field";
        $value = true;
        $this->assertEquals("some-field=true", DjangoFilter::create($field, $value)->build());
        $value = false;
        $this->assertEquals("some-field=false", DjangoFilter::create($field, $value)->build());
    }

    /**
     * @covers \whikloj\archivematicaPhp\DjangoFilter::__construct
     * @covers \whikloj\archivematicaPhp\DjangoFilter::create
     * @covers \whikloj\archivematicaPhp\DjangoFilter::build
     * @covers \whikloj\archivematicaPhp\DjangoFilter::startsWith
     * @covers \whikloj\archivematicaPhp\DjangoFilter::lessThan
     * @covers \whikloj\archivematicaPhp\DjangoFilter::greaterThan
     */
    public function testAdjustments(): void
    {
        $field = "some-field";
        $value = "some-value";
        $this->assertEquals("some-field=some-value", DjangoFilter::create($field, $value)->build());
        $this->assertEquals(
            "some-field__startswith=some-value",
            DjangoFilter::create($field, $value)->startsWith()->build()
        );
        // Currently we don't limit adjustments by value type
        $this->assertEquals("some-field__lt=some-value", DjangoFilter::create($field, $value)->lessThan()->build());
        $this->assertEquals("some-field__gt=some-value", DjangoFilter::create($field, $value)->greaterThan()->build());
    }

    /**
     * @covers \whikloj\archivematicaPhp\DjangoFilter::lessThan
     * @covers \whikloj\archivematicaPhp\DjangoFilter::greaterThan
     * @covers \whikloj\archivematicaPhp\DjangoFilter::startsWith
     */
    public function testAdjustmentOverwrite(): void
    {
        $field = "some-field";
        $value = 5.5;
        $this->assertEquals(
            "some-field__gt=5.5",
            DjangoFilter::create($field, $value)->startsWith()->lessThan()->greaterThan()->build()
        );
    }

    /**
     * @covers \whikloj\archivematicaPhp\DjangoFilter::getValue
     */
    public function testGetValueNotUrlEncode(): void
    {
        $field = "some-field";
        $value = "this is some data";
        $this->assertEquals("this is some data", DjangoFilter::create($field, $value)->getValue());
        $value = "value : with";
        $this->assertEquals("value : with", DjangoFilter::create($field, $value)->getValue());
    }

    /**
     * @covers \whikloj\archivematicaPhp\DjangoFilter::getValue
     */
    public function testGetValueTypes(): void
    {
        $field = "some-field";
        $this->assertEquals("data", DjangoFilter::create($field, "data")->getValue());
        $this->assertIsString(DjangoFilter::create($field, "data")->getValue());
        $this->assertEquals("true", DjangoFilter::create($field, true)->getValue());
        $this->assertIsString(DjangoFilter::create($field, true)->getValue());
        $this->assertEquals("123", DjangoFilter::create($field, 123)->getValue());
        $this->assertIsString(DjangoFilter::create($field, 123)->getValue());
        $this->assertEquals("987.654", DjangoFilter::create($field, 987.654)->getValue());
        $this->assertIsString(DjangoFilter::create($field, 987.654)->getValue());
    }

    /**
     * @covers \whikloj\archivematicaPhp\DjangoFilter::getField
     * @covers \whikloj\archivematicaPhp\DjangoFilter::startsWith
     * @covers \whikloj\archivematicaPhp\DjangoFilter::lessThan
     * @covers \whikloj\archivematicaPhp\DjangoFilter::greaterThan
     */
    public function testGetField(): void
    {
        $field = "some-field";
        $value = "data";
        $this->assertEquals("some-field", DjangoFilter::create($field, $value)->getField());
        $this->assertEquals("some-field__lt", DjangoFilter::create($field, $value)->lessThan()->getField());
        $this->assertEquals("some-field__startswith", DjangoFilter::create($field, $value)->startsWith()->getField());
        $this->assertEquals("some-field__gt", DjangoFilter::create($field, $value)->greaterThan()->getField());
    }
}
