<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake('id_ID');

        // 1) Pastikan kategori ada (tidak menghapus yang lama)
        $categoryNames = ['Kimia Resin', 'Alat Pertukangan', 'Aksesoris Elektronik', 'Dekorasi Rumah'];
        $categories = collect($categoryNames)->map(function ($name) {
            return Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        });

        // 2) Tambahkan 4 produk BARU (tanpa delete/truncate)
        $toCreate = 4;
        DB::transaction(function () use ($faker, $categories, $toCreate) {
            foreach (range(1, $toCreate) as $i) {
                $category = $categories->random();

                // pastikan SKU unik
                do {
                    $sku = strtoupper(Str::random(10));
                } while (Product::where('sku', $sku)->exists());

                $product = Product::create([
                    'category_id'      => $category->id,
                    'name'             => Str::title($faker->words(rand(2, 5), true)),
                    'brand'            => $faker->company(),
                    'sku'              => $sku,
                    'base_price'       => $faker->randomFloat(2, 20000, 150000),
                    'discount_percent' => $faker->optional(0.7)->randomElement([5,10,15,20,25,30]),
                    'currency'         => 'IDR',
                    'description_html' => '<p>'.$faker->paragraph(3).'</p>',
                    'spec_html'        => '<ul><li>'.implode('</li><li>', $faker->words(4)).'</li></ul>',
                    'favorite_count'   => $faker->numberBetween(100, 5000),
                    'sold_count'       => $faker->numberBetween(100, 5000),
                    'average_rating'   => $faker->randomFloat(1, 3.5, 5.0),
                    'rating_count'     => $faker->numberBetween(10, 200),
                    'weight_gram'      => $faker->numberBetween(100, 1000),
                    'volume_ml'        => $faker->numberBetween(10, 500),
                    'status'           => 'active',
                ]);

                // Gambar (2–4 per produk)
                foreach (range(1, rand(2, 4)) as $pos) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url'  => '/uploads/products/'.$faker->uuid().'.jpg',
                        'alt'        => 'Gambar '.$pos.' '.$product->name,
                        'position'   => $pos - 1,
                    ]);
                }

                // Varian (2–5 per produk)
                $colors = ['Hitam','Putih','Merah','Hijau','Biru','Coklat','Abu-Abu'];
                foreach (range(1, rand(2, 5)) as $v) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'sku'        => strtoupper(Str::random(10)),
                        'options'    => json_encode([
                            'color'   => $faker->randomElement($colors),
                            'size_ml' => $faker->randomElement([30, 50, 100, 200, 500]),
                        ]),
                        'price'      => null,
                        'stock'      => $faker->numberBetween(5, 50),
                        'is_active'  => true,
                    ]);
                }
            }
        });

        $this->command->info("✅ Seeder sukses! {$toCreate} produk acak baru berhasil ditambahkan tanpa menghapus data lama.");
    }
}
