<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use PGDatabase\Models\DataType;
use PGDatabase\Models\Model;

class TestModel extends Model
{
    protected string $TABLE_NAME = 'test_table';

    public function setEditAbleFlag(bool $flag): void
    {
        $this->EDITABLE = $flag;
    }

    public function setSoftDeleteFlag(bool $flag): void
    {
        $this->SOFT_DELETE = $flag;
    }

    public function setOrderByColumns(string $columns): void
    {
        $this->ORDER_BY_COLUMNS = $columns;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setRequest(array $request): void
    {
        $this->id = (int)($request['id'] ?? 0);
        $this->setValues(self::extractDataValues([
            'name' => ['datatype' => DataType::STRING],
            'age'  => ['datatype' => DataType::INT],
        ], $request));
    }

    public function getData(): array
    {
        return $this->getValues();
    }
}

class ModelTest extends TestCase
{
    protected function setUp(): void
    {
        putenv('DB_SECURITY_ENVIROMENT=true');
        putenv('DB_HOST=127.0.0.1');
        putenv('DB_PORT=5432');
    }

    protected function tearDown(): void
    {
        putenv('DB_SECURITY_ENVIROMENT');
        putenv('DB_HOST');
        putenv('DB_PORT');
    }

    // --- extractDataValues ---

    public function testExtractDataValuesString(): void
    {
        $result = Model::extractDataValues(
            ['name' => ['datatype' => DataType::STRING]],
            ['name' => '  Hello World  ']
        );
        $this->assertSame('Hello World', $result['name']);
    }

    public function testExtractDataValuesStringWithNonStringRawValue(): void
    {
        $result = Model::extractDataValues(
            ['name' => ['datatype' => DataType::STRING, 'default' => 'fallback']],
            ['name' => 123]
        );
        $this->assertSame('fallback', $result['name']);
    }

    public function testExtractDataValuesInt(): void
    {
        $result = Model::extractDataValues(
            ['age' => ['datatype' => DataType::INT]],
            ['age' => '42']
        );
        $this->assertSame(42, $result['age']);
    }

    public function testExtractDataValuesIntWithDefaultWhenMissing(): void
    {
        $result = Model::extractDataValues(
            ['age' => ['datatype' => DataType::INT, 'default' => 18]],
            []
        );
        $this->assertSame(18, $result['age']);
    }

    public function testExtractDataValuesIntWithNonNumericFallsBackToDefault(): void
    {
        $result = Model::extractDataValues(
            ['age' => ['datatype' => DataType::INT, 'default' => 0]],
            ['age' => 'abc']
        );
        $this->assertSame(0, $result['age']);
    }

    public function testExtractDataValuesFloat(): void
    {
        $result = Model::extractDataValues(
            ['price' => ['datatype' => DataType::FLOAT]],
            ['price' => '3.14']
        );
        $this->assertSame(3.14, $result['price']);
    }

    public function testExtractDataValuesBoolTrue(): void
    {
        $result = Model::extractDataValues(
            ['active' => ['datatype' => DataType::BOOL]],
            ['active' => 1]
        );
        $this->assertTrue($result['active']);
    }

    public function testExtractDataValuesBoolFalse(): void
    {
        $result = Model::extractDataValues(
            ['active' => ['datatype' => DataType::BOOL]],
            ['active' => 'false']
        );
        $this->assertFalse($result['active']);
    }

    public function testExtractDataValuesBoolWithDefault(): void
    {
        $result = Model::extractDataValues(
            ['active' => ['datatype' => DataType::BOOL, 'default' => true]],
            ['active' => 'invalid']
        );
        $this->assertTrue($result['active']);
    }

    public function testExtractDataValuesStringUpper(): void
    {
        $result = Model::extractDataValues(
            ['code' => ['datatype' => DataType::STRING_UPPER]],
            ['code' => '  hello  ']
        );
        $this->assertSame('HELLO', $result['code']);
    }

    public function testExtractDataValuesUtf8(): void
    {
        $result = Model::extractDataValues(
            ['text' => ['datatype' => DataType::UTF8]],
            ['text' => 'café']
        );
        $this->assertSame('café', $result['text']);
    }

    public function testExtractDataValuesUtf8Upper(): void
    {
        $result = Model::extractDataValues(
            ['text' => ['datatype' => DataType::UTF8_UPPER]],
            ['text' => 'café']
        );
        $this->assertSame('CAFÉ', $result['text']);
    }

    public function testExtractDataValuesDefaultDataTypeIsString(): void
    {
        $result = Model::extractDataValues(
            ['name' => []],
            ['name' => '  text  ']
        );
        $this->assertSame('text', $result['name']);
    }

    public function testExtractDataValuesDateFallsThroughToString(): void
    {
        $result = Model::extractDataValues(
            ['created' => ['datatype' => DataType::DATE]],
            ['created' => '2024-01-15']
        );
        $this->assertSame('2024-01-15', $result['created']);
    }

    public function testExtractDataValuesDatetimeFallsThroughToString(): void
    {
        $result = Model::extractDataValues(
            ['ts' => ['datatype' => DataType::DATETIME]],
            ['ts' => '2024-01-15 10:30:00']
        );
        $this->assertSame('2024-01-15 10:30:00', $result['ts']);
    }

    public function testExtractDataValuesArrayFallsThroughToString(): void
    {
        $result = Model::extractDataValues(
            ['tags' => ['datatype' => DataType::ARRAY]],
            ['tags' => 'a,b']
        );
        $this->assertSame('a,b', $result['tags']);
    }

    public function testExtractDataValuesJsonFallsThroughToString(): void
    {
        $result = Model::extractDataValues(
            ['meta' => ['datatype' => DataType::JSON]],
            ['meta' => '{"key":"value"}']
        );
        $this->assertSame('{"key":"value"}', $result['meta']);
    }

    public function testExtractDataValuesWithDefaultWhenMissing(): void
    {
        $result = Model::extractDataValues(
            ['name' => ['datatype' => DataType::STRING, 'default' => 'Guest']],
            []
        );
        $this->assertSame('Guest', $result['name']);
    }

    // --- getValues / setValues ---

    public function testGetValuesReturnsSetValues(): void
    {
        $model = new TestModel();
        $model->setValues(['name' => 'John']);
        $this->assertSame(['name' => 'John'], $model->getValues());
    }

    public function testGetValuesInitialState(): void
    {
        $model = new TestModel();
        $this->assertSame([], $model->getValues());
    }

    // --- setRequest ---

    public function testSetRequestWithData(): void
    {
        $model = new TestModel();
        $model->setRequest(['name' => '  Alice  ', 'age' => '30']);
        $this->assertSame('Alice', $model->getValues()['name']);
        $this->assertSame(30, $model->getValues()['age']);
    }

    // --- find / findAll offline ---

    public function testFindReturnsEmptyArrayWhenNotConnected(): void
    {
        $model = new TestModel();
        $this->assertSame([], $model->find(1));
    }

    public function testFindReturnsEmptyArrayWhenNotConnectedWithCustomColumn(): void
    {
        $model = new TestModel();
        $this->assertSame([], $model->find('admin', 'role'));
    }

    public function testFindAllReturnsEmptyArrayWhenNotConnected(): void
    {
        $model = new TestModel();
        $this->assertSame([], $model->findAll());
    }

    // --- update offline ---

    public function testUpdateReturnsFalseWhenNotConnected(): void
    {
        $model = new TestModel();
        $this->assertFalse($model->update(['name' => 'John'], ['id' => 1]));
    }

    // --- delete offline ---

    public function testDeleteReturnsFalseWhenNotConnected(): void
    {
        $model = new TestModel();
        $this->assertFalse($model->delete(1));
    }

    // --- restore ---

    public function testRestoreReturnsFalseWhenSoftDeleteDisabled(): void
    {
        $model = new TestModel();
        $model->setSoftDeleteFlag(false);
        $this->assertFalse($model->restore(1));
    }

    public function testRestoreReturnsFalseWhenNotConnected(): void
    {
        $model = new TestModel();
        $model->setSoftDeleteFlag(true);
        $this->assertFalse($model->restore(1));
    }

    // --- onEditable / offEditable ---

    public function testOnEditableReturnsFalseWhenEditableDisabled(): void
    {
        $model = new TestModel();
        $model->setEditAbleFlag(false);
        $this->assertFalse($model->onEditable(1));
    }

    public function testOffEditableReturnsFalseWhenEditableDisabled(): void
    {
        $model = new TestModel();
        $model->setEditAbleFlag(false);
        $this->assertFalse($model->offEditable(1));
    }
}
