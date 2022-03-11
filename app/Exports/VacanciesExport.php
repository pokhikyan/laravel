<?php

namespace App\Exports;

use App\Models\Vacancies;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VacanciesExport implements FromCollection, WithHeadings,WithChunkReading
{
    public $filtered;
    public $headings;
    public function __construct( $filtered, $headings ) {
        $this->filtered = $filtered;
        $this->headings = $headings;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->filtered;
    }

    public function headings() : array
    {
        return $this->headings;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
