<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThemesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('themes')->insert([
            "theme_id" => "7ccc432a06caa",
            "theme_name" => "vCard Theme (White)",
            "theme_description" => "vCard",
            "theme_thumbnail" => "vCard-white.png",
            "theme_price" => "Free"
        ]);

        DB::table('themes')->insert([
            "theme_id" => "7ccc432a06hty",
            "theme_name" => "WhatsApp Store Theme (White)",
            "theme_description" => "WhatsApp Store",
            "theme_thumbnail" => "store.png",
            "theme_price" => "Free"
        ]);
    }
}
