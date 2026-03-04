<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Actions;

use App\Domains\Intelligence\DataTransfer\EventData;
use App\Domains\Intelligence\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateEventAction
{
    public function execute(EventData $data, User $user): Event
    {
        // Create point geometry for location
        $location = DB::raw(
            sprintf('POINT(%f, %f)', $data->longitude, $data->latitude)
        );

        return Event::create([
            ...($data->toArray()),
            'location' => $location,
            'created_by' => $user->id,
            'uuid' => Str::uuid()->toString(),
        ]);
    }
}
