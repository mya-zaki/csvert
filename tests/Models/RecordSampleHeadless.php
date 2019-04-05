<?php
namespace MyaZaki\Csvert\Tests\Models;

use MyaZaki\Csvert\Record;

class RecordSampleHeadless extends Record
{
    public $header = false;

    public $columns = [
        'key',
        'value',
        'rate',
        'flag',
        null,
        'date',
        'null'
    ];

    public function getRate()
    {
        return (float)$this['rate'];
    }

    public function getDate()
    {
        return new \DateTime($this['date']);
    }
}
