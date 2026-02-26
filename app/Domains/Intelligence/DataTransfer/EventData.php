<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\DataTransfer;

use Carbon\Carbon;
use Illuminate\Http\Request;

class EventData
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly string $type,
        public readonly Carbon $occurredAt,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly string $status,
        public readonly string $confidence,
        public readonly ?string $locationName = null,
        public readonly ?int $actorId = null,
        public readonly ?array $customFields = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            title: $request->string('title')->toString(),
            description: $request->string('description')->toString(),
            type: $request->string('type')->toString(),
            occurredAt: Carbon::parse($request->input('occurred_at')),
            latitude: $request->float('latitude'),
            longitude: $request->float('longitude'),
            status: $request->string('status')->toString(),
            confidence: $request->string('confidence')->toString(),
            locationName: $request->input('location_name'),
            actorId: $request->integer('actor_id') ?: null,
            customFields: $request->input('custom_fields'),
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'occurred_at' => $this->occurredAt,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'location_name' => $this->locationName,
            'actor_id' => $this->actorId,
            'status' => $this->status,
            'confidence' => $this->confidence,
            'custom_fields' => $this->customFields,
        ];
    }
}
