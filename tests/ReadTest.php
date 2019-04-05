<?php
namespace MyaZaki\Csvert\Tests;

use MyaZaki\Csvert\Tests\Models\RecordSample;
use MyaZaki\Csvert\Tests\Models\RecordSampleNoColumns;
use MyaZaki\Csvert\Tests\Models\RecordSampleHeadless;

class ReadTest extends \PHPUnit\Framework\TestCase
{
    public function testParse()
    {
        $parser = RecordSampleHeadless::parse('tests/files/sample_headless.csv');

        $this->assertNull($parser->getRawHeader());

        $result = $parser->walk(function ($record) {
            return [
                'row'     => $record->getRowNo(),
                'key'     => $record['key'],
                'value'   => $record['value'],
                'rate'    => $record->getRate(),
                'flag'    => $record['flag'],
                'comment' => $record['comment'],
                'date'    => $record->getDate(),
                'null'    => $record['null'],
            ];
        });
        $this->assertSame(1, $result[0]['row']);
        $this->assertSame('key1', $result[0]['key']);
        $this->assertSame('100', $result[0]['value']);
        $this->assertSame(0.2, $result[0]['rate']);
        $this->assertSame('1', $result[0]['flag']);
        $this->assertNull($result[0]['comment']);
        $this->assertSame(1554076800, $result[0]['date']->getTimestamp());
        $this->assertNull($result[0]['null']);
        $this->assertSame(4, $result[3]['row']);
        $this->assertSame('キー１', $result[3]['key']);
        $this->assertSame('450', $result[3]['value']);

        $parser = RecordSampleNoColumns::parse('tests/files/sample_headless.csv');
        $records = $parser->get();
        $this->assertSame('key2', $records[1][0]);
        $this->assertSame('200', $records[1][1]);
    }

    public function testParseString()
    {
        $original = file_get_contents('tests/files/sample.csv');
        $parser = RecordSample::parseString($original);
        $records = $parser->get();

        $header = $parser->getRawHeader();
        $this->assertSame('キー', $header[0]);
        $this->assertSame('値', $header[1]);

        $this->assertSame(3, $records[1]->getRowNo());
        $this->assertSame('key2', $records[1]['キー']);
        $this->assertSame('value2', $records[1]['値']);
        $this->assertTrue($records->contains('キー', 'キー２'));

        $content = $parser->getContents();
        $this->assertSame(mb_convert_encoding($original, 'UTF-8', 'SJIS-win'), $content);
    }

    public function testEmpty()
    {
        $parser = RecordSample::parse('tests/files/sample_headonly.csv');
        $header = $parser->getRawHeader();
        $this->assertSame('キー', $header[0]);
        $this->assertSame('値', $header[1]);

        $records = $parser->get();
        $this->assertTrue($records->isEmpty());


        $parser = RecordSample::parse('tests/files/sample_empty.csv');
        $header = $parser->getRawHeader();
        $this->assertNull($parser->getRawHeader());

        $records = $parser->get();
        $this->assertTrue($records->isEmpty());
    }
}
