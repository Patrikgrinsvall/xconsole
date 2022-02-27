<?php

namespace PatrikGrinsvall\XConsole\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PatrikGrinsvall\XConsole\Traits\HasTheme;

class XConsoleEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels, HasTheme;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public string $message = "")
    {
        if ($this->message != "") {
            Log::channel('stderr')->info("XConsole Event::" . $message);
        }

    }


    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('console.log');
    }
}
