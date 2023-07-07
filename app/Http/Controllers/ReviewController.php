<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    //
    public function createReview(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id',
                'rating' => 'required|integer|between:1,5',
                'review' => 'required',
            ]);

            if ($validator->fails()) {
                //return validation errror
            }

            $review = new Review();
            $review->user_id = auth()->user()->id;
            $review->product_id = $request->product_id;
            $review->rating = $request->rating;
            $review->review = $request->review;
            $review->save();

            return ['success' => 'Successfully'];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
