# csvert

## Configure Record Object

```
<?php
namespace App;

use MyaZaki\Csvert\Record;

class PostalCode extends Record
{
    public $delimiter = ',';
    public $enclosure = '"';
    public $escape = '\\';

    public $charset = 'SJIS-win';

    public $header = true;

    public $columns = [
        'Code',
        'Street',
        'City',
        'State',
    ];

    public function getAddress()
    {
        return $this->attributes['Street'] . ', ' . $this->attributes['City'] . ', ' . $this->attributes['State']；
    }
}
```

`columns`
Header fields of CSV.
This fields are keys of Record object.

`header`
External source has header fields.
default=true

`charset`  
Encoding of the external source.  
default=UTF-8

`delimiter`: The optional delimiter parameter sets the field delimiter (one character only).  
default=','

`enclosure`: The optional enclosure parameter sets the field enclosure character (one character only).  
default='"'

`escape`: The optional escape parameter sets the escape character (at most one character). An empty string ("") disables the proprietary escape mechanism.  
default='\\'

Please refer to https://www.php.net/manual/en/function.fgetcsv.php

## Parse CSV

postal.csv

```
Code,Street,City,State
640941,"旭ケ丘","札幌市中央区","北海道"
600041,"大通東","札幌市中央区","北海道"
・・・
```

### parse file

```
$parser = PostalCode::parse($filepath);
$address_list = [];
$parser->walk(function ($record) use (&$address_list) { // Call user function each line
    // Given Record instance
    $address_list[] = $record['Code'] . ' ' . $record->getAddress();
});
```

### parse string

```
$parser = PostalCode::parseString($csv_content);
$records = $parser->get(); // Get Collection
$address = $records->where('State', '北海道')->first();
var_dump($address['Code']);
```

## Save CSV

```
$writer = RecordSample::getWriter();

$collection = collect();
$collection->push(new PostalCode(['Code' => '600042', 'State' => '北海道', 'City' => '札幌市中央区', 'Street' => '大通西（１～１９丁目）']));
$collection->push(new PostalCode(['Code' => '640820', 'State' => '北海道', 'City' => '札幌市中央区', 'Street' => '大通西（２０～２８丁目）']));

$writer->setRecords($collection);

$writer->save($filepath);
```
