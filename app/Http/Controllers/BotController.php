<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BotController extends Controller
{
    //
    public function indentifyFakeReviews(Request $request)
    {
        try {
            $fakeReviews = [];
            $groupedReviews = Review::get()->groupBy('user_id');

            foreach ($groupedReviews as $userId => $groupReview) {
                if (count($this->getDuplicateReviews($groupReview)))
                    $fakeReviews['duplicate_reviews'] = $this->getDuplicateReviews($groupReview);

                if (count($this->checkReviewsContainFillerWords($groupReview)))
                $fakeReviews['filler_words'][] = $this->checkReviewsContainFillerWords($groupReview);
            }
            return $fakeReviews;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getDuplicateReviews($reviews)
    {
        try {
            $duplicateReviews = [];
            //logic to identify fake review if the user has entered the same review to many products
            //Find unique reviews
            $uniqueReviews = $reviews->unique(['review']);
            //find difference with the original array
            $duplicates = $reviews->diff($uniqueReviews);
            //Check if the duplication occur more then 2 (we can use any number here)
            if ($duplicates->count() > 2)
                $duplicateReviews[] = $reviews->map(function ($review) {
                    $review['is_fake'] = 1;
                    $review['reason'] = 'User reviewed with same review';
                    return $review;
                });
            return $duplicateReviews;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function checkReviewsContainFillerWords($reviews)
    {
        try {
            $containFillerWords = [];
            $fillerWords = config('constant.fillerWords');
            foreach ($reviews as $review) {
                if (Str::contains($review->review, $fillerWords)) {
                    $review['is_fake'] = 1;
                    $review['reason'] = 'Review contains filler words';
                    $containFillerWords[] = $review;
                }
            }
            return $containFillerWords;
        } catch (\Exception $e) {
            return [];
        }
    }
}
