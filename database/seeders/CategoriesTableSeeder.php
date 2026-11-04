<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->truncate();

        $json = File::get(database_path('data/categories.json'));
        $data = json_decode($json, true);

        // Extract only table data
        foreach ($data as $entry) {
            if (
                isset($entry['type']) &&
                $entry['type'] === 'table' &&
                $entry['name'] === 'categories'
            ) {
                DB::table('categories')->insert(
                    collect($entry['data'])->map(function ($item) {
                        return [
                            'id'         => (int) $item['id'],
                            'user_id'    => (int) $item['user_id'],
                            'parent_id'  => $item['parent_id'] ? (int) $item['parent_id'] : null,
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
