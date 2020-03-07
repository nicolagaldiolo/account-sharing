<?php

namespace App\Imports;

use App\Category;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ServiceDataImport implements ToCollection, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {

        Storage::deleteDirectory('archive');

        foreach ($rows as $row)
        {
            collect(config('custom.countries'))->keys()->each(function ($key) use ($row){
                File::copyDirectory(
                    storage_path('import_data/images' . $row->get('id')),
                    storage_path('app/public/archive' . $row->get('id'))
                );

                Category::create([
                    'str_id'        => $row->get('id'),
                    'name'          => $row->get('service'),
                    'description'   => $row->get('desc'),
                    'capacity'      => $row->get('account'),
                    'price'         => $row->get($key),
                    'multiaccount'  => $row->get('multiaccount'),
                    'custom'        => $row->get('custom'),
                    'country'       => $key
                ]);
            });
        }
    }
}
