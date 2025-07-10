<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run()
    {
        $tags = [
            ['name' => 'Work', 'color' => '#FF5733'],
            ['name' => 'Personal', 'color' => '#33FF57'],
            ['name' => 'Urgent', 'color' => '#FF3333'],
            ['name' => 'Low Priority', 'color' => '#33A1FF'],
            ['name' => 'Home', 'color' => '#A833FF'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
