<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventIndexRequest;
use App\Http\Requests\EventShowRequest;
use App\Http\Requests\EventStoreRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\services\EventService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Exception;

class EventController extends Controller
{
    protected $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(EventIndexRequest $request)
    {
        // Retrieve a paginated list of events with optional filters
        return $this->eventService->getEvents($request->validated());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventStoreRequest $request)
    {
        // Create a new event (Authenticated users only)
        return $this->eventService->createEvent($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        // Retrieve details of a specific event
        return $this->eventService->getEvent($event);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventStoreRequest $request, Event $event)
    {
        // Update an event (Only the event creator can update).
        return $this->eventService->updateEvent($event, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        // Soft delete an event (Only the event creator can delete)
        return $this->eventService->deleteEvent($event);
    }
}
