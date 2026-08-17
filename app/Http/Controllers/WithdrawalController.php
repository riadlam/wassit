<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWithdrawalRequest;
use App\Models\Seller;
use App\Models\Withdrawal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WithdrawalController extends Controller
{
    public function store(StoreWithdrawalRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $seller = Seller::query()
                ->whereKey($request->user()->id)
                ->lockForUpdate()
                ->firstOrFail();

            $amount = round((float) $request->validated('amount'), 2);
            $pendingAmount = (float) $seller->withdrawals()
                ->where('status', 'pending')
                ->sum('amount');
            $availableToWithdraw = max(0, (float) $seller->wallet - $pendingAmount);

            if ($amount > $availableToWithdraw) {
                throw ValidationException::withMessages([
                    'amount' => 'This request exceeds your available balance after pending withdrawals.',
                ]);
            }

            Withdrawal::create([
                'seller_id' => $seller->id,
                'amount' => $amount,
                'status' => 'pending',
                'payment_method' => $request->validated('payment_method'),
                'payment_details' => [
                    'account_holder' => $request->validated('account_holder'),
                    'account_number' => $request->validated('account_number'),
                ],
            ]);
        }, 3);

        return redirect()
            ->route('account.wallet')
            ->with('success', 'Your withdrawal request was submitted for review.');
    }
}
