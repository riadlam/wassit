<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Seller;

class ReviewController extends Controller
{
    public function create(Request $request, $seller_id)
    {
        // TODO: Create review for seller
    }

    public function getSellerReviews($seller_id)
    {
        // TODO: Return all reviews for a specific seller
    }
}
