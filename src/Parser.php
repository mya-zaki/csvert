<?php

declare(strict_types=1);

namespace MyaZaki\Csvert;

use Illuminate\Support\Collection;
use MultibyteStringStream;
use MyaZaki\Csvert\Record;

class Parser
{
    /** @var \MyaZaki\Csvert\Record */
    private $record;

    /** @var string */
    private $filepath;

    /** @var resource */
    private $fp;

    /**
     * Create a new parser instance.
     */
    public function __construct(Record $record)
    {
        MultibyteStringStream::registerStreamFilter();

        $this->record = $record;
    }

    public function parse(string $filepath): Parser
    {
        $this->filepath = $filepath;
        return $this;
    }

    public function parseString($content): Parser
    {
        $this->filepath = 'data://text/plain;base64,' . base64_encode($content);
        return $this;
    }

    public function get(): Collection
    {
        $records = new Collection();
        $this->walk(function (Record $record) use (&$records) {
            $records->push($record);
        });
        return $records;
    }

    /**
     * Get original text
     */
    public function getContents(): string
    {
        $fp = $this->open();
        return stream_get_contents($fp);
    }

    public function getRawHeader(): ?array
    {
        if ($this->record->header) {
            $fp = $this->open();
            return ($header = fgetcsv($fp, 0, $this->record->delimiter, $this->record->enclosure, $this->record->escape)) ?: null;
        }
        return null;
    }

    /**
     * Applies the callback to the records of the read lines
     */
    public function walk(\Closure $callback): ?bool
    {
        $records = $this->generate();
        foreach ($records as $record) {
            call_user_func($callback, $record);
        }
        return true;
    }

    private function generate(): \Generator
    {
        // fgetcsvで1行(ヘッダ行)を読み込んだあとにrewind($fp)で
        // ファイルポインタを先頭に戻すと、１文字目のマルチバイト文字が文字化けすることがある
        // 回避策として、毎回openする
        $fp = $this->open();

        $lineNo = 0;
        if ($this->record->header) {
            fgetcsv($fp, 0, $this->record->delimiter, $this->record->enclosure, $this->record->escape);
            ++$lineNo;
        }

        while (false !== $row = $this->fetch($fp)) {
            yield $this->record->newInstance($row, ++$lineNo);
        }
    }

    private function open()
    {
        $fp = fopen($this->filepath, 'r');
        if ($fp === false) {
            throw new Exception('failed to open file.');
        }
        if ($this->record->charset !== 'UTF-8') {
            stream_filter_append($fp, "convert.mbstring.UTF-8/{$this->record->charset}", STREAM_FILTER_READ);
        }

        return $fp;
    }

    private function fetch(&$fp)
    {
        $keys = $this->record->columns;
        $data = fgetcsv($fp, 0, $this->record->delimiter, $this->record->enclosure, $this->record->escape);
        if (!is_array($data)) {
            // EOF
            return false;
        }

        if (empty($keys)) {
            return array_map(function ($value) {
                return strlen($value = trim($value)) > 0 ? $value : null;
            }, $data);
        }
        $row = [];
        foreach ($keys as $i => $name) {
            if (!is_null($name)) {
                $row[$name] = null;
                if (isset($data[$i])) {
                    $row[$name] = strlen($value = trim($data[$i])) > 0 ? $value : null;
                }
            }
        }
        return $row;
    }
}
