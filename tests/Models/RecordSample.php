<?php
declare(strict_types = 1);

namespace MyaZaki\Csvert\Tests\Models;

use MyaZaki\Csvert\Record;

class RecordSample extends Record
{
    public $charset = 'SJIS-win';
    public $delimiter = "\t";

    public $columns = [
        'キー',
        '値'
    ];
}
