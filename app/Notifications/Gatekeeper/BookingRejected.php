<?php

namespace App\Notifications\Gatekeeper;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use HMS\Entities\Gatekeeper\TemporaryAccessBooking;

class BookingRejected extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var TemporaryAccessBooking
     */
    protected $booking;

    /**
     * @var string
     */
    protected $reason;

    /**
     * Create a new notification instance.
     *
     * @param TemporaryAccessBooking  $temporaryAccessBooking
     * @param string $reason
     *
     * @return void
     */
    public function __construct(TemporaryAccessBooking $booking, string $reason)
    {
        $this->booking = $booking;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $bookableArea = $this->booking->getBookableArea();

        return (new MailMessage)
            ->subject('Nottingham Hackspace: Access Booking Request Rejected')
            ->markdown(
                'emails.gatekeeper.booking_rejected',
                [
                    'buildingName' => $bookableArea->getBuilding()->getName(),
                    'name' => $this->booking->getUser()->getFirstname(),
                    'start' => $this->booking->getStart(),
                    'end' => $this->booking->getEnd(),
                    'bookableAreaName' => $bookableArea->getName(),
                    'guests' => $this->booking->getGuests(),
                    'reason' => $this->reason,
                ]
            );
    }
}
