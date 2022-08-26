<?php

declare(strict_types=1);

namespace MyaZaki\Csvert;

use Illuminate\Support\Collection;
use MultibyteStringStream;
use MyaZaki\Csvert\Record;

class Writer
{
    private $record;
    private $tmp;
    private $offset = 0;

    public function __construct(Record $record)
    {
        MultibyteStringStream::registerStreamFilter();

        $this->record = $record;

        $this->tmp = tmpfile();

        if ($this->record->charset !== 'UTF-8') {
            stream_filter_append($this->tmp, "convert.mbstring.{$this->record->charset}/UTF-8", STREAM_FILTER_WRITE);
        }

        if ($this->record->header && !empty($this->record->columns)) {
            if (false === fputcsv($this->tmp, $this->record->columns, $this->record->delimiter, $this->record->enclosure, $this->record->escape)) {
                throw new Exception('failed to write header to file.');
            }
            $this->offset = ftell($this->tmp);
        }
    }

    public function setRecords(Collection $records): Writer
    {
        fseek($this->tmp, $this->offset);
        ftruncate($this->tmp, $this->offset);

        $recordClass = get_class($this->record);

        foreach ($records as $record) {
            if (!($record instanceof $recordClass)) {
                throw new Exception('must be of the type ' . $recordClass);
            }

            if (empty($record->columns)) {
                $fields = $record->toArray();
            } else {
                $fields = array_fill_keys($record->columns, null);
                foreach ($fields as $key => $null) {
                    $fields[$key] = $record[$key] ?? null;
                }
            }

            if (false === fputcsv($this->tmp, $fields, $this->record->delimiter, $this->record->enclosure, $this->record->escape)) {
                throw new Exception('failed to write record to the resource.');
            }
        }

        return $this;
    }

    public function getHandle()
    {
        rewind($this->tmp);
        return $this->tmp;
    }

    public function tmpPath(): string
    {
        return stream_get_meta_data($this->getHandle())['uri'];
    }

    public function save($filepath)
    {
        $dest = fopen($filepath, 'w');
        return stream_copy_to_stream($this->getHandle(), $dest);
    }

    public function getContents(): string
    {
        return stream_get_contents($this->getHandle());
    }

    public function __destruct()
    {
        fclose($this->tmp);
    }
}
