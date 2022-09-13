<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class ExportDataCrawl implements FromArray
{

    public function array(): array
    {
        return [
            ["a" => "b",
            "a1" => "b",
            "a2" => "b",
            "a3" => "b"]
        ];
    }
}
