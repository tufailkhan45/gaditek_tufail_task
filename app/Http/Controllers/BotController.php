<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Sentiment\Analyzer;

class BotController extends Controller
{
    //
    public function indentifyFakeReviews(Request $request)
    {
        try {
            $fakeReviews = [];

            //get reviews user wise
            $groupedReviews = Review::get()->groupBy('user_id');

            foreach ($groupedReviews as $userId => $groupReview) {
                if (count($this->getDuplicateReviews($groupReview)))
                    $fakeReviews['duplicate_reviews'] = $this->getDuplicateReviews($groupReview);

                if (count($this->checkReviewsContainFillerWords($groupReview)))
                    $fakeReviews['filler_words'][] = $this->checkReviewsContainFillerWords($groupReview);
            }
            return success('Success', $fakeReviews);
        } catch (\Exception $e) {
            return error('Something went wrong');
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
                $duplicateReviews[] = $duplicates->map(function ($review) {
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

            //Package to use the sentiment of sentence used davmixcool/php-sentiment-analyzerow
            //Result are some how matching on https://text2data.com/Demo but it depends on ML what is running behind it.
            $analyzer = new Analyzer();
            $fillerWords = config('constant.fillerWords');

            foreach ($reviews as $review) {

                //function of package to get sentiment of sentence it'll return an array of neg. pos, neu values
                $checkSentiment = $analyzer->getSentiment($review->review);
                //Checked if sentence is postive (as we need to focus in postive reviews).
                //Checked rating is 5 starts  
                //last condition if the words contain filler words
                if (round($checkSentiment['pos']) && $review->rating == 5 && Str::contains($review->review, $fillerWords)) {
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
