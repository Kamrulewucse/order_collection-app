<?php

namespace App\Events;

use App\Models\Location; // Assuming you have a Location model
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LocationUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $location;
    public function __construct(array $location)
    {
        $this->location = $location; // Store the passed location data
        \Log::info('LocationUpdated event fired:', ['location' => $this->location]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('location-channel'),
        ];
    }
    public function broadcastAs(): string
    {
        return 'location-updated'; // The event name to listen for
    }
    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->location['user_id'],
            'latitude' => $this->location['latitude'],
            'longitude' => $this->location['longitude'],
        ];
    }
}
