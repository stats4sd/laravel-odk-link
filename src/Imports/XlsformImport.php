<?php

namespace Stats4sd\OdkLink\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class XlsformImport implements WithMultipleSheets
{

    public function sheets(): array
    {
        return [
            'survey' => new XlsformSurveyImport(),
            'choices' => new XlsformChoicesImport(),
        ];
    }
}
