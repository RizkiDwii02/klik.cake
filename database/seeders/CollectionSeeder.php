<?php

namespace Database\Seeders;

use App\Models\Collection;
use Illuminate\Database\Seeder;

class CollectionSeeder extends Seeder
{
    public function run(): void
    {
        $structure = [
            [
                'name' => 'Roti',
                'slug' => 'roti',
                'description' => 'Pilihan roti hangat, lembut, dan harum dipanggang setiap hari.',
                'sort_order' => 1,
                'children' => [
                    ['name' => 'Roti Manis', 'slug' => 'roti-manis', 'description' => 'Roti manis dengan aneka isian lembut dan lezat.'],
                    ['name' => 'Roti Tawar & Gurih', 'slug' => 'roti-tawar-gurih', 'description' => 'Roti tawar bergizi dan roti gurih dengan topping lezat.'],
                ],
            ],
            [
                'name' => 'Cake',
                'slug' => 'cake',
                'description' => 'Kue tart lembut untuk momen spesial dan perayaan istimewa.',
                'sort_order' => 2,
                'children' => [
                    ['name' => 'Whole Cake', 'slug' => 'whole-cake', 'description' => 'Kue utuh aneka ukuran untuk ulang tahun dan pesta.'],
                    ['name' => 'Slice / Cup Cake', 'slug' => 'slice-cup-cake', 'description' => 'Potongan cake satuan atau cup cake cantik nikmat.'],
                    ['name' => 'Custom & Ulang Tahun', 'slug' => 'custom-ulang-tahun', 'description' => 'Pesanan kue custom tema khusus dengan lead time H-3.'],
                ],
            ],
            [
                'name' => 'Pastry',
                'slug' => 'pastry',
                'description' => 'Pastry renyah berlapis dengan mentega premium pilihan.',
                'sort_order' => 3,
                'children' => [
                    ['name' => 'Croissant & Danish', 'slug' => 'croissant-danish', 'description' => 'Croissant renyah berlapis danish harum mentega.'],
                    ['name' => 'Cookies & Pie', 'slug' => 'cookies-pie', 'description' => 'Kue kering renyah dan pie lezat aneka isian manis.'],
                ],
            ],
            [
                'name' => 'Snackbox',
                'slug' => 'snackbox',
                'description' => 'Paket hantaran dan snackbox lengkap untuk segala macam kegiatan.',
                'sort_order' => 4,
                'children' => [
                    ['name' => 'Paket Acara', 'slug' => 'paket-acara', 'description' => 'Snackbox lengkap untuk arisan, tasyakuran, dan reuni.'],
                    ['name' => 'Paket Kantor', 'slug' => 'paket-kantor', 'description' => 'Snackbox praktis dan elegan untuk rapat dan seminar kantor.'],
                ],
            ],
        ];

        foreach ($structure as $parentData) {
            $children = $parentData['children'];
            unset($parentData['children']);

            $parent = Collection::updateOrCreate(
                ['slug' => $parentData['slug']],
                $parentData
            );

            foreach ($children as $index => $childData) {
                Collection::updateOrCreate(
                    ['slug' => $childData['slug']],
                    array_merge($childData, [
                        'parent_id' => $parent->id,
                        'sort_order' => $index + 1,
                    ])
                );
            }
        }
    }
}
