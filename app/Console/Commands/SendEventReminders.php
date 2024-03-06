<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Notifications\EventReminderNotification;


class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-event-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $events = \App\Models\Event::with('attendees.user')
            ->whereBetween('start_time', [now(), now()->addDay()])
            ->get();
        $eventCount = $events->count();

        $events->each(fn ($event) => $event->attendees->each(fn ($attendee) => $attendee->user->notify(
            new EventReminderNotification($event)
        )));

        $this->info("Found {$eventCount} events");
        $this->info("Notification reminders has been sent");
    }
}
