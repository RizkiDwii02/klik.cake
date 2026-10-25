<?php

namespace Database\Seeders;

use App\Enums\AvailabilityType;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $wholeCake = Collection::where('slug', 'whole-cake')->firstOrFail();
        $sliceCake = Collection::where('slug', 'slice-cup-cake')->firstOrFail();
        $customCake = Collection::where('slug', 'custom-ulang-tahun')->firstOrFail();
        $rotiManis = Collection::where('slug', 'roti-manis')->firstOrFail();
        $rotiGurih = Collection::where('slug', 'roti-tawar-gurih')->firstOrFail();
        $croissant = Collection::where('slug', 'croissant-danish')->firstOrFail();
        $cookies = Collection::where('slug', 'cookies-pie')->firstOrFail();
        $paketAcara = Collection::where('slug', 'paket-acara')->firstOrFail();
        $paketKantor = Collection::where('slug', 'paket-kantor')->firstOrFail();

        // 1. Strawberry Cream Cake — HARUS PERSIS SESUAI SPESIFIKASI
        $scc = Product::updateOrCreate(
            ['slug' => 'strawberry-cream-cake'],
            [
                'collection_id' => $wholeCake->id,
                'name' => 'Strawberry Cream Cake',
                'description' => 'Sponge cake vanila lembut berpadu krim segar ringan dan buah stroberi manis segar pilihan khas Pinky Cake.',
                'portion_info' => 'Porsi 6–8 orang (16 cm) / 10–12 orang (20 cm)',
                'ingredients' => 'Tepung terigu protein rendah, gula kastor, telur ayam segar, mentega wisman, ekstrak vanila murni, whipped cream nabati, buah stroberi segar.',
                'allergens' => 'susu, telur, gluten',
                'shelf_life' => 'Simpan 2 hari di kulkas',
                'lead_time_days' => 1, // minimal H-1
                'availability_type' => AvailabilityType::PREORDER->value,
                'is_featured' => true,
                'is_fresh_bake' => true,
                'rating' => 4.9,
            ]
        );

        Variant::updateOrCreate(
            ['sku' => 'SCC-16CM'],
            [
                'product_id' => $scc->id,
                'name' => '16 cm',
                'price' => 185000,
                'stock' => 20,
            ]
        );

        Variant::updateOrCreate(
            ['sku' => 'SCC-20CM'],
            [
                'product_id' => $scc->id,
                'name' => '20 cm',
                'price' => 245000,
                'stock' => 15,
            ]
        );

        // 2–13: 11+ Contoh Produk Lain (Ditandai CONTOH)
        $sampleProducts = [
            [
                'collection_id' => $wholeCake->id,
                'name' => '[CONTOH] Double Chocolate Fudge Cake',
                'slug' => 'contoh-double-chocolate-fudge-cake',
                'description' => 'Kue cokelat pekat berlapis ganache dark chocolate Belgia premium dengan lelehan lembut di lidah.',
                'portion_info' => 'Porsi 6–8 orang',
                'ingredients' => 'Dark chocolate 70%, cocoa butter, tepung gandum, telur, mentega, susu segar.',
                'allergens' => 'susu, telur, gluten',
                'shelf_life' => 'Simpan 3 hari di kulkas',
                'lead_time_days' => 1,
                'availability_type' => AvailabilityType::PREORDER->value,
                'is_featured' => true,
                'is_fresh_bake' => true,
                'rating' => 4.8,
                'variants' => [
                    ['name' => '16 cm', 'sku' => 'DCFC-16', 'price' => 195000, 'stock' => 15],
                    ['name' => '20 cm', 'sku' => 'DCFC-20', 'price' => 260000, 'stock' => 10],
                ],
            ],
            [
                'collection_id' => $wholeCake->id,
                'name' => '[CONTOH] Basque Burnt Cheesecake',
                'slug' => 'contoh-basque-burnt-cheesecake',
                'description' => 'Cheesecake panggang khas Basque dengan permukaan karamel gelap yang harum dan bagian tengah yang lembut lumer.',
                'portion_info' => 'Porsi 6–8 orang',
                'ingredients' => 'Cream cheese Australia, heavy cream, gula, telur, tepung maizena.',
                'allergens' => 'susu, telur',
                'shelf_life' => 'Simpan 4 hari di kulkas',
                'lead_time_days' => 1,
                'availability_type' => AvailabilityType::PREORDER->value,
                'is_featured' => true,
                'is_fresh_bake' => true,
                'rating' => 4.9,
                'variants' => [
                    ['name' => '16 cm', 'sku' => 'BBC-16', 'price' => 175000, 'stock' => 20],
                    ['name' => '20 cm', 'sku' => 'BBC-20', 'price' => 235000, 'stock' => 12],
                ],
            ],
            [
                'collection_id' => $customCake->id,
                'name' => '[CONTOH] Custom Birthday Cake Tema Floral Pastel',
                'slug' => 'contoh-custom-birthday-cake-floral',
                'description' => 'Kue ulang tahun dekorasi custom bunga buttercream pastel elegan dengan ucapan nama khusus.',
                'portion_info' => 'Porsi 10–12 orang',
                'ingredients' => 'Sponge cake vanilla / cokelat, swiss meringue buttercream, edible flower.',
                'allergens' => 'susu, telur, gluten',
                'shelf_life' => 'Simpan 2 hari di suhu sejuk',
                'lead_time_days' => 3, // Custom cake lead time minimal H-3
                'availability_type' => AvailabilityType::PREORDER->value,
                'is_featured' => false,
                'is_fresh_bake' => false,
                'rating' => 5.0,
                'variants' => [
                    ['name' => 'Diameter 18 cm', 'sku' => 'CUST-FLORAL-18', 'price' => 350000, 'stock' => 5],
                    ['name' => 'Diameter 22 cm', 'sku' => 'CUST-FLORAL-22', 'price' => 475000, 'stock' => 5],
                ],
            ],
            [
                'collection_id' => $sliceCake->id,
                'name' => '[CONTOH] Red Velvet Slice Cake',
                'slug' => 'contoh-red-velvet-slice-cake',
                'description' => 'Satu potong red velvet lembut beraroma buttermilk dengan lapisan cream cheese frosting segar gurih.',
                'portion_info' => 'Porsi 1 orang',
                'ingredients' => 'Tepung terigu, cokelat bubuk, buttermilk, cream cheese frosting.',
                'allergens' => 'susu, telur, gluten',
                'shelf_life' => 'Simpan 2 hari di kulkas',
                'lead_time_days' => 1,
                'availability_type' => AvailabilityType::READY_TODAY->value,
                'is_featured' => false,
                'is_fresh_bake' => true,
                'rating' => 4.7,
                'variants' => [
                    ['name' => '1 Slice', 'sku' => 'RV-SLICE-1', 'price' => 32000, 'stock' => 30],
                    ['name' => 'Kotak Isi 4', 'sku' => 'RV-SLICE-4', 'price' => 120000, 'stock' => 15],
                ],
            ],
            [
                'collection_id' => $rotiManis->id,
                'name' => '[CONTOH] Roti Sisir Mentega Wisman',
                'slug' => 'contoh-roti-sisir-mentega-wisman',
                'description' => 'Roti sisir tradisional yang super lembut dengan polesan mentega Wisman wangi gurih dan gula halus.',
                'portion_info' => 'Porsi 3–4 orang',
                'ingredients' => 'Tepung terigu cakra, mentega Wisman, susu murni, gula, kuning telur.',
                'allergens' => 'susu, telur, gluten',
                'shelf_life' => '3 hari suhu ruang',
                'lead_time_days' => 1,
                'availability_type' => AvailabilityType::READY_TODAY->value,
                'is_featured' => true,
                'is_fresh_bake' => true,
                'rating' => 4.9,
                'variants' => [
                    ['name' => '1 Loyang (8 Sisir)', 'sku' => 'RSM-8', 'price' => 45000, 'stock' => 25],
                ],
            ],
            [
                'collection_id' => $rotiManis->id,
                'name' => '[CONTOH] Signature Pandan Roll Cheese',
                'slug' => 'contoh-signature-pandan-roll-cheese',
                'description' => 'Bolu gulung aroma pandan daun suji asli berisi krim keju dan taburan keju cheddar melimpah.',
                'portion_info' => 'Porsi 6–8 potong',
                'ingredients' => 'Ekstrak pandan suji, keju cheddar parut, tepung terigu, telur, mentega.',
                'allergens' => 'susu, telur, gluten',
                'shelf_life' => '3 hari suhu ruang',
                'lead_time_days' => 1,
                'availability_type' => AvailabilityType::READY_TODAY->value,
                'is_featured' => false,
                'is_fresh_bake' => true,
                'rating' => 4.8,
                'variants' => [
                    ['name' => '1 Gulung (22 cm)', 'sku' => 'PRC-22', 'price' => 75000, 'stock' => 20],
                ],
            ],
            [
                'collection_id' => $rotiGurih->id,
                'name' => '[CONTOH] Roti Daging Bakso Sapi Makassar',
                'slug' => 'contoh-roti-daging-bakso-sapi',
                'description' => 'Roti empuk gurih dengan isian daging sapi cincang bumbu rempah khas Makassar yang manis gurih sedap.',
                'portion_info' => 'Porsi 1 orang',
                'ingredients' => 'Daging sapi segar cincang, bawang merah, rempah, tepung terigu, wijen sangrai.',
                'allergens' => 'gluten, wijen',
                'shelf_life' => '2 hari suhu ruang',
                'lead_time_days' => 1,
                'availability_type' => AvailabilityType::READY_TODAY->value,
                'is_featured' => true,
                'is_fresh_bake' => true,
                'rating' => 4.9,
                'variants' => [
                    ['name' => '1 Pcs', 'sku' => 'RDBS-1', 'price' => 18000, 'stock' => 40],
                    ['name' => 'Paket Isi 5', 'sku' => 'RDBS-5', 'price' => 85000, 'stock' => 15],
                ],
            ],
            [
                'collection_id' => $croissant->id,
                'name' => '[CONTOH] Classic French Butter Croissant',
                'slug' => 'contoh-classic-french-butter-croissant',
                'description' => 'Croissant berlapis renyah di luar, sarang lebah lembut di dalam dengan aroma mentega Prancis yang semerbak.',
                'portion_info' => 'Porsi 1 orang',
                'ingredients' => 'Mentega elle & vire, tepung gandum Prancis, ragi alami, susu cair.',
                'allergens' => 'susu, gluten',
                'shelf_life' => '1 hari renyah maksimal (dapat dipanaskan ulang)',
                'lead_time_days' => 1,
                'availability_type' => AvailabilityType::READY_TODAY->value,
                'is_featured' => true,
                'is_fresh_bake' => true,
                'rating' => 4.9,
                'variants' => [
                    ['name' => '1 Pcs', 'sku' => 'CFBC-1', 'price' => 22000, 'stock' => 35],
                    ['name' => 'Box Isi 4', 'sku' => 'CFBC-4', 'price' => 82000, 'stock' => 15],
                ],
            ],
            [
                'collection_id' => $croissant->id,
                'name' => '[CONTOH] Pain Au Chocolat',
                'slug' => 'contoh-pain-au-chocolat',
                'description' => 'Pastry mentega berlapis renyah dengan isian dua batang cokelat dark batangan Belgia yang lumer saat dihangatkan.',
                'portion_info' => 'Porsi 1 orang',
                'ingredients' => 'Baton dark chocolate, mentega Prancis, tepung gandum, ragi.',
                'allergens' => 'susu, gluten, kedelai',
                'shelf_life' => '1–2 hari',
                'lead_time_days' => 1,
                'availability_type' => AvailabilityType::READY_TODAY->value,
                'is_featured' => false,
                'is_fresh_bake' => true,
                'rating' => 4.8,
                'variants' => [
                    ['name' => '1 Pcs', 'sku' => 'PAC-1', 'price' => 25000, 'stock' => 30],
                ],
            ],
            [
                'collection_id' => $cookies->id,
                'name' => '[CONTOH] Fudgy Brownies Bites Crust',
                'slug' => 'contoh-fudgy-brownies-bites',
                'description' => 'Brownies potong gigitan sekali santap dengan lapisan atas shiny crust renyah dan bagian dalam padat lumer.',
                'portion_info' => 'Isi 16 potong bite size',
                'ingredients' => 'Dark chocolate, cocoa powder, telur, butter, gula tebu, sea salt.',
                'allergens' => 'susu, telur, gluten',
                'shelf_life' => '7 hari suhu ruang / 14 hari di kulkas',
                'lead_time_days' => 1,
                'availability_type' => AvailabilityType::READY_TODAY->value,
                'is_featured' => false,
                'is_fresh_bake' => false,
                'rating' => 4.9,
                'variants' => [
                    ['name' => 'Box 250 gram', 'sku' => 'FBB-250', 'price' => 65000, 'stock' => 25],
                    ['name' => 'Box 500 gram', 'sku' => 'FBB-500', 'price' => 120000, 'stock' => 15],
                ],
            ],
            [
                'collection_id' => $paketAcara->id,
                'name' => '[CONTOH] Snackbox Acara Melati',
                'slug' => 'contoh-snackbox-acara-melati',
                'description' => 'Paket kotak snack ekonomis lengkap: Roti Manis Cokelat, Lemper Bakar Ayam, dan Bolu Gulung Mini plus air mineral.',
                'portion_info' => 'Paket untuk 1 orang',
                'ingredients' => 'Kombinasi 3 varian kue segar manis dan gurih pilihan.',
                'allergens' => 'susu, telur, gluten',
                'shelf_life' => 'Konsumsi di hari yang sama',
                'lead_time_days' => 1,
                'availability_type' => AvailabilityType::PREORDER->value,
                'is_featured' => false,
                'is_fresh_bake' => true,
                'rating' => 4.7,
                'variants' => [
                    ['name' => '1 Box (3 Kue + Air)', 'sku' => 'SNACK-MELATI-1', 'price' => 24000, 'stock' => 100],
                ],
            ],
            [
                'collection_id' => $paketKantor->id,
                'name' => '[CONTOH] Snackbox Eksekutif Pertemuan Kantor',
                'slug' => 'contoh-snackbox-eksekutif-kantor',
                'description' => 'Paket hantaran snack premium box elegan: Mini Croissant Butter, Pastel Ayam Spesial, Red Velvet Slice, dan jus kemasan.',
                'portion_info' => 'Paket untuk 1 orang',
                'ingredients' => 'Kombinasi 3 pastry & cake premium Pinky Cake.',
                'allergens' => 'susu, telur, gluten',
                'shelf_life' => 'Konsumsi di hari yang sama',
                'lead_time_days' => 1,
                'availability_type' => AvailabilityType::PREORDER->value,
                'is_featured' => true,
                'is_fresh_bake' => true,
                'rating' => 5.0,
                'variants' => [
                    ['name' => '1 Box Premium', 'sku' => 'SNACK-EKS-1', 'price' => 38000, 'stock' => 80],
                ],
            ],
        ];

        foreach ($sampleProducts as $data) {
            $variants = $data['variants'];
            unset($data['variants']);

            $product = Product::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            foreach ($variants as $variantData) {
                Variant::updateOrCreate(
                    ['sku' => $variantData['sku']],
                    array_merge($variantData, ['product_id' => $product->id])
                );
            }
        }
    }
}
