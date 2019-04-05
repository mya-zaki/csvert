<?php
declare(strict_types = 1);

namespace MyaZaki\Csvert;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use MultibyteStringStream;

abstract class Record implements \ArrayAccess, \JsonSerializable, Arrayable, Jsonable
{
    public $delimiter = ',';
    public $enclosure = '"';
    public $escape = '\\';

    public $charset = 'UTF-8';

    public $header = true;

    public $columns = null;
    
    protected $attributes = [];
    protected $rowNo = null;

    public function __construct($attributes = [], $rowNo = null)
    {
        $this->attributes = $attributes;
        $this->rowNo = $rowNo;
    }

    public function getRowNo(): int
    {
        return $this->rowNo;
    }

    public static function getWriter(): Writer
    {
        return (new Writer(new static()));
    }

    public static function parse($filepath): Parser
    {
        return (new Parser(new static()))->parse($filepath);
    }

    public static function parseString($content): Parser
    {
        return (new Parser(new static()))->parseString($content);
    }
    
    public function newInstance($attributes = [], $rowNo = null): Record
    {
        return new static($attributes, $rowNo);
    }

    public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]);
    }
    public function offsetGet($offset)
    {
        return $this->attributes[$offset] ?? null;
    }
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->attributes[] = $value;
        } else {
            $this->attributes[$offset] = $value;
        }
    }
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    public function jsonSerialize()
    {
        return $this->attributes;
    }

    public function toArray()
    {
        return $this->attributes;
    }

    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
