<?php
namespace MyaZaki\Csvert\Tests;

use MyaZaki\Csvert\Tests\Models\RecordSample;
use MyaZaki\Csvert\Tests\Models\RecordSampleHeadless;

class WriteTest extends \PHPUnit\Framework\TestCase
{
    protected $output;

    protected function setUp(): void
    {
        $this->output = __DIR__ . DIRECTORY_SEPARATOR . 'output' . DIRECTORY_SEPARATOR . 'test.csv';
        @unlink($this->output);
    }

    public function testSave()
    {
        $records = RecordSample::parse('tests/files/sample.csv')->get();

        $writer = RecordSample::getWriter();
        $writer->setRecords($records);

        $this->assertSame(file_get_contents('tests/files/sample.csv'), trim($writer->getContents()));

        $writer->setRecords($records);
        $writer->save($this->output);

        $this->assertSame(file_get_contents('tests/files/sample.csv'), trim(file_get_contents($this->output)));
    }

    public function testWrite()
    {
        $writer = RecordSample::getWriter();

        $collection = collect();

        $collection->push(new RecordSample(['キー' => 'キー１', '値' => 10]));
        $collection->push(new RecordSample(['キー' => 'キー２', '値' => 1]));
        $collection->push(new RecordSample(['キー' => 'キー３', '値' => true]));
        $collection->push(new RecordSample(['値' => 0.5, 'キー' => 'キー４']));
        $collection->push(new RecordSample(['値' => false, 'キー' => 'キー５']));

        $writer->setRecords($collection);

        $expect = <<<STR
キー	値
キー１	10
キー２	1
キー３	1
キー４	0.5
キー５	

STR;
        $this->assertSame($expect, mb_convert_encoding($writer->getContents(), 'UTF-8', 'SJIS-win'));
    }

    public function testTypeError()
    {
        $this->expectException(\MyaZaki\Csvert\Exception::class);
        $this->expectExceptionMessage('must be of the type MyaZaki\\Csvert\\Tests\\Models\\RecordSample');

        $records = RecordSampleHeadless::parse('tests/files/sample_headless.csv')->get();

        $writer = RecordSample::getWriter();
        $writer->setRecords($records);
    }
}
