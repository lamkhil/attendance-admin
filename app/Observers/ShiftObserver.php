<?php

namespace App\Observers;

use App\Models\Shift;

class ShiftObserver
{
    /**
     * Handle the Shift "created" event.
     */
    public function created(Shift $shift): void
    {
        //
    }

    /**
     * Handle the Shift "updated" event.
     */
    public function updated(Shift $shift): void
    {
        if ($shift->default) {
            // Unset default from other shifts
            Shift::where('id', '!=', $shift->id)
                ->where('default', true)
                ->update(['default' => false]);
        }
    }

    /**
     * Handle the Shift "deleted" event.
     */
    public function deleted(Shift $shift): void
    {
        //
    }

    /**
     * Handle the Shift "restored" event.
     */
    public function restored(Shift $shift): void
    {
        //
    }

    /**
     * Handle the Shift "force deleted" event.
     */
    public function forceDeleted(Shift $shift): void
    {
        //
    }
}
