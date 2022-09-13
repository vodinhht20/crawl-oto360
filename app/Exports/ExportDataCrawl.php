<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Excel;

class ExportDataCrawl implements FromArray, WithStrictNullComparison
{
    use Exportable;

    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    private $writerType = \Maatwebsite\Excel\Excel::XLSX;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    public function array(): array
    {
        return [
            $this->data
        ];
    }
}
