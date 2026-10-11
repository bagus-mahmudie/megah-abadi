<?php

namespace App\Actions;

use App\Models\ProductRatingsSummary;
use App\Models\Review;

class UpdateProductRatingSummary
{
    public static function run(int $productId): void
    {
        // Ambil semua review valid (rating 1..5)
        $q = Review::query()
            ->where('product_id', $productId)
            ->whereBetween('rating', [1, 5]);

        $total = (clone $q)->count();
        if ($total === 0) {
            // Tidak ada review -> set nol semua (atau bisa juga delete row)
            ProductRatingsSummary::updateOrCreate(
                ['product_id' => $productId],
                [
                    'avg_rating'    => 0,
                    'total_reviews' => 0,
                    'count_5' => 0, 'count_4' => 0, 'count_3' => 0, 'count_2' => 0, 'count_1' => 0,
                ]
            );
            return;
        }

        $avg   = (clone $q)->avg('rating');

        // List count per rating
        $byStar = (clone $q)
            ->selectRaw('rating, COUNT(*) as c')
            ->groupBy('rating')
            ->pluck('c', 'rating')
            ->all();

        $c1 = (int)($byStar[1] ?? 0);
        $c2 = (int)($byStar[2] ?? 0);
        $c3 = (int)($byStar[3] ?? 0);
        $c4 = (int)($byStar[4] ?? 0);
        $c5 = (int)($byStar[5] ?? 0);

        ProductRatingsSummary::updateOrCreate(
            ['product_id' => $productId],
            [
                'avg_rating'    => round($avg, 2),
                'total_reviews' => $total,
                'count_5' => $c5, 'count_4' => $c4, 'count_3' => $c3, 'count_2' => $c2, 'count_1' => $c1,
            ]
        );
    }
}
