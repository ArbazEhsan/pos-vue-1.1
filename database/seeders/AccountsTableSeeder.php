<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AccountsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('accounts')->truncate();

        $json = File::get(database_path('data/accounts.json'));
        $data = json_decode($json, true);

        // Extract only table data
        foreach ($data as $entry) {
            if (
                isset($entry['type']) &&
                $entry['type'] === 'table' &&
                $entry['name'] === 'accounts'
            ) {
                DB::table('accounts')->insert(
                    collect($entry['data'])->map(function ($item) {
                        return [
                            'id'              => (int) $item['id'],
                            'user_id'         => (int) $item['user_id'],
                            'name'            => $item['name'],
                            'email'           => $item['email'],
                            'city'            => $item['city'],
                            'address'         => $item['address'],
                            'contact_person'  => $item['contact_person'],
                            'mobile'          => $item['mobile'],
                            'tel_1'           => $item['tel_1'],
                            'tel_2'           => $item['tel_2'],
                            'type'            => $item['type'],
                            'status'          => (int) $item['status'],
                            'created_at'      => $item['created_at'],
                            'updated_at'      => $item['updated_at'],
                        ];
                    })->toArray()
                );
            }
        }
    }
}
