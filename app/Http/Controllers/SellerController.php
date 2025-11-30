<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seller;
use App\Models\AccountForSale;

class SellerController extends Controller
{
    public function getProfile(Request $request)
    {
        // TODO: Return authenticated seller's profile
    }

    public function updateProfile(Request $request)
    {
        // TODO: Update seller profile
    }

    public function getAccounts(Request $request)
    {
        // TODO: Return all accounts for authenticated seller
    }

    public function createAccount(Request $request)
    {
        // TODO: Create new account listing
    }

    public function updateAccount(Request $request, $id)
    {
        // TODO: Update account listing
    }

    public function deleteAccount(Request $request, $id)
    {
        // TODO: Delete account listing
    }
}
