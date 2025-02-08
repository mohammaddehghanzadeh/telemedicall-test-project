<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class EventResource
 *
 * This resource formats the response for an event.
 * It transforms event data into a structured JSON format.
 *
 * @package App\Http\Resources
 *
 * @property  int $id Event ID.
 * @property string $title Event title.
 * @property string $description Event description.
 * @property string $start_time Event start date and time.
 * @property string $end_time Event end date and time.
 * @property string $location Event location.
 */

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * This method takes the event data and structures it into a formatted array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed> The formatted event data.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,             // Event ID
            'title'       => $this->title,          // Event title
            'description' => $this->description,    // Event description
            'start_time'  => $this->start_time,     // Event start date and time
            'end_time'    => $this->end_time,       // Event end date and time
            'location'    => $this->location,       // Event location
        ];
    }
}
