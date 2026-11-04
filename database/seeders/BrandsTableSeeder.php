<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BrandsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('brands')->truncate();

        $json = File::get(database_path('data/brands.json'));
        $data = json_decode($json, true);

        foreach ($data as $entry) {
            if (
                isset($entry['type']) &&
                $entry['type'] === 'table' &&
                $entry['name'] === 'brands'
            ) {
                DB::table('brands')->insert(
                    collect($entry['data'])->map(function ($item) {
                        return [
                            'id'         => (int) $item['id'],
                            'name'       => $item['name'],
                            'status'     => (int) $item['status'],
                            'created_at'=> $item['created_at'],
                            'updated_at'=> $item['updated_at'],
                        ];
                    })->toArray()
                );
            }
        }
    }
}
