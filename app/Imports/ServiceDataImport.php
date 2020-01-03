<?php

namespace App\Imports;

use App\Category;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ServiceDataImport implements ToCollection, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row)
        {

            collect(config('custom.countries'))->keys()->each(function ($key) use ($row){
                Category::create([
                    'name' => $row->get('servizio'),
                    'description' => $row->get('desc'),
                    'capacity' => $row->get('account'),
                    'price' => $row->get($key),
                    'customizable' => $row->get('custom'),
                    'country' => $key
                ]);
            });
        }
    }
}
