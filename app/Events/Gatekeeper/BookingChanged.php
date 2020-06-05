<?php

namespace App\Events\Gatekeeper;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use HMS\Entities\Gatekeeper\TemporaryAccessBooking;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Http\Resources\Gatekeeper\TemporaryAccessBooking as TemporaryAccessBookingResources;

class BookingChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var TemporaryAccessBooking
     */
    public $orignalBooking;

    /**
     * @var TemporaryAccessBooking
     */
    public $booking;

    /**
     * @var int
     */
    public $buildingId;

    /**
     * Create a new event instance.
     *
     * @param TemporaryAccessBooking $orignalBooking
     * @param TemporaryAccessBooking $booking
     *
     * @return void
     */
    public function __construct(TemporaryAccessBooking $orignalBooking, TemporaryAccessBooking $booking)
    {
        $this->orignalBooking = $orignalBooking;
        $this->booking = $booking;
        $this->buildingId = $booking->getBookableArea()->getBuilding()->getId();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('gatekeeper.temporaryAccessBookings.' . $this->buildingId);
    }

    /**
     * Get the data that should be sent with the broadcasted event.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'orignalBooking' =>(new TemporaryAccessBookingResources($this->orignalBooking))->resolve(),
            'booking' => (new TemporaryAccessBookingResources($this->booking))->resolve(),
        ];
    }
}
