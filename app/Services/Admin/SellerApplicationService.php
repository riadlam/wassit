<?php

namespace App\Services\Admin;

use App\Models\Seller;
use App\Models\SellerApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SellerApplicationService
{
    public function approve(SellerApplication $application, ?string $adminNotes = null): SellerApplication
    {
        return DB::transaction(function () use ($application, $adminNotes) {
            $lockedApplication = SellerApplication::query()
                ->whereKey($application->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedApplication->status === 'approved') {
                return $lockedApplication;
            }

            $this->assertPending($lockedApplication);

            $user = User::query()
                ->whereKey($lockedApplication->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $user->update(['role' => 'seller']);

            Seller::firstOrCreate(
                ['id' => $user->id],
                [
                    'pfp' => null,
                    'rating' => 5.0,
                    'total_sales' => 0,
                    'bio' => null,
                    'verified' => false,
                    'wallet' => 0,
                ]
            );

            $lockedApplication->update([
                'status' => 'approved',
                'admin_notes' => $adminNotes ?? $lockedApplication->admin_notes,
            ]);

            return $lockedApplication->fresh();
        }, 3);
    }

    public function reject(SellerApplication $application, ?string $adminNotes = null): SellerApplication
    {
        return DB::transaction(function () use ($application, $adminNotes) {
            $lockedApplication = SellerApplication::query()
                ->whereKey($application->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedApplication->status === 'rejected') {
                return $lockedApplication;
            }

            $this->assertPending($lockedApplication);

            $lockedApplication->update([
                'status' => 'rejected',
                'admin_notes' => $adminNotes ?? $lockedApplication->admin_notes,
            ]);

            return $lockedApplication->fresh();
        }, 3);
    }

    public function assertPending(SellerApplication $application): void
    {
        if ($application->status !== 'pending') {
            throw new InvalidArgumentException('Application is no longer pending.');
        }
    }
}
