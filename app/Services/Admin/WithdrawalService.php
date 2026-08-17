<?php

namespace App\Services\Admin;

use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WithdrawalService
{
    public function approve(Withdrawal $withdrawal, ?string $adminNotes = null): Withdrawal
    {
        return DB::transaction(function () use ($withdrawal, $adminNotes) {
            $lockedWithdrawal = Withdrawal::query()
                ->whereKey($withdrawal->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedWithdrawal->status === 'approved') {
                return $lockedWithdrawal;
            }

            if ($lockedWithdrawal->status !== 'pending') {
                throw new InvalidArgumentException('Only pending withdrawals can be approved.');
            }

            $seller = $lockedWithdrawal->seller()->lockForUpdate()->firstOrFail();
            $amount = (float) $lockedWithdrawal->amount;

            if ((float) $seller->wallet < $amount) {
                throw new InvalidArgumentException('Seller wallet balance is insufficient for this withdrawal.');
            }

            $seller->decrement('wallet', $amount);

            $lockedWithdrawal->update([
                'status' => 'approved',
                'admin_notes' => $adminNotes ?? $lockedWithdrawal->admin_notes,
                'processed_at' => now(),
            ]);

            return $lockedWithdrawal->fresh();
        }, 3);
    }

    public function reject(Withdrawal $withdrawal, ?string $adminNotes = null): Withdrawal
    {
        return DB::transaction(function () use ($withdrawal, $adminNotes) {
            $lockedWithdrawal = Withdrawal::query()
                ->whereKey($withdrawal->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedWithdrawal->status === 'rejected') {
                return $lockedWithdrawal;
            }

            if ($lockedWithdrawal->status !== 'pending') {
                throw new InvalidArgumentException('Only pending withdrawals can be rejected.');
            }

            $lockedWithdrawal->update([
                'status' => 'rejected',
                'admin_notes' => $adminNotes ?? $lockedWithdrawal->admin_notes,
                'processed_at' => now(),
            ]);

            return $lockedWithdrawal->fresh();
        }, 3);
    }
}
