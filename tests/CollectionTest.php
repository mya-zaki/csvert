<?php
namespace MyaZaki\Csvert\Tests;

use MyaZaki\Csvert\Tests\Models\RecordSampleHeadless;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testCollection()
    {
        $records = RecordSampleHeadless::parse('tests/files/sample_headless.csv')->get();

        $this->assertSame(250, $records->avg('value'));
        $this->assertTrue($records->contains('value', 100));
        $this->assertFalse($records->containsStrict('value', 100));

        $records2 = collect($records->all());
        unset($records2[0]);
        $diff = $records->diff($records2);
        $this->assertSame(1, $diff->count());

        $this->assertCount(2, $records->groupBy('flag'));
        $this->assertSame('key1,key2,key3,キー１,キー２', $records->implode('key', ','));
        $this->assertSame('key2', $records->keyBy('key')->keys()[1]);

        $grouped = $records->mapToGroups(function ($item, $key) {
            return [$item['flag'] => $item['key']];
        });
        $this->assertSame(['key2', 'キー２'], $grouped->toArray()[0]);

        $keyed = $records->mapWithKeys(function ($item, $key) {
            return [$item['key'] => $item['value']];
        });
        $this->assertNull($keyed->all()['key3']);

        $this->assertSame('450', $records->max('value'));
        $this->assertSame('200', $records->median('value'));
        $this->assertSame('100', $records->min('value'));
        $this->assertSame(750, $records->sum('value'));
        $this->assertSame([1], $records->mode('flag'));

        $this->assertSame('450', $records->pluck('value', 'key')->all()['キー１']);
        $this->assertSame('キー１', $records->sortByDesc('value')->first()['key']);

        $this->assertSame('key1', $records->unique('flag')[0]['key']);

        $this->assertSame('key3', $records->where('flag', 1)->values()[1]['key']);
        $this->assertSame('キー１', $records->whereIn('value', [200, 450])->values()[1]['key']);
    }
}
