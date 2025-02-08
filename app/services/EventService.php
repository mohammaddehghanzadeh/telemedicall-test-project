<?php

namespace App\services;

use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Http\JsonResponse;

class EventService
{
    /**
     * Create a new event.
     *
     * This method accepts event data, creates a new event in the database,
     * and returns the created event.
     *
     * @param array $data Event data including title, description, start time, end time, and location.
     * @return Event The created event.
     * @throws Exception If there is an issue creating the event.
     */
    public function createEvent(array $data)
    {
        try {
            // Create the event in the database
            $event = Event::create([
                'user_id'     => auth()->id(),  // Get the authenticated user's ID
                'title'       => $data['title'],
                'description' => $data['description'],
                'start_time'  => $data['start_time'],
                'end_time'    => $data['end_time'],
                'location'    => $data['location'],
            ]);

            // Return a success response
            return response()->json([
                'message' => 'Event created successfully',
                'event'   => new EventResource($event)
            ], 201);// HTTP status code 201 indicates successful resource creation

        } catch (QueryException $e) {
            throw new Exception('Failed to create event due to a database error: ' . $e->getMessage());

        } catch (Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'message' => 'An unexpected error occurred while creating the event.',
                'error'   => $e->getMessage()
            ], 500);// HTTP status code 500 indicates server error
        }
    }

    /**
     * Fetch a paginated list of events with optional filtering.
     *
     * This method retrieves a paginated list of events, allowing filtering by
     * start time, end time, location, and keyword search (title or description).
     *
     * @param array $filters Filters such as 'start_time', 'end_time', 'location', and 'keyword'.
     * @param int $perPage The number of results per page (default: 2).
     * @return LengthAwarePaginator|JsonResponse Returns paginated event data or an error response.
     */
    public function getEvents(array $filters): JsonResponse
    {
        try {
            $query = Event::query();

            // Filter by start time (events starting after a given datetime)
            if (!empty($filters['start_time'])) {
                $query->where('start_time', '>=', $filters['start_time']);
            }

            // Filter by end time (events ending before a given datetime)
            if (!empty($filters['end_time'])) {
                $query->where('end_time', '<=', $filters['end_time']);
            }

            // Filter by location
            if (!empty($filters['location'])) {
                $query->where('location', 'like', '%' . $filters['location'] . '%');
            }

            // Search by keyword (in title or description)
            if (!empty($filters['keyword'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('title', 'like', '%' . $filters['keyword'] . '%')
                        ->orWhere('description', 'like', '%' . $filters['keyword'] . '%');
                });
            }

            // Extract pagination settings from request
            $perPage = $filters['perPage'] ?? 2;

            // Paginate query
            $events = $query->paginate($perPage);

            // Return JSON response
            return response()->json([
                'data' => EventResource::collection($events),
                'meta' => [
                    'current_page' => $events->currentPage(),
                    'last_page' => $events->lastPage(),
                    'total' => $events->total(),
                ]
            ], 200);// HTTP status code 201 indicates successful resource creation
        } catch (Exception $e) {
            // Return error response if something goes wrong
            return response()->json([
                'message' => 'An error occurred while fetching events.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retrieve a single event by ID.
     *
     * This method fetches the event, checks if it has been soft deleted,
     * and ensures the authenticated user is the owner.
     *
     * @param Event $event The event instance retrieved via route model binding.
     * @return JsonResponse JSON response containing the event details or an error message.
     */
    public function getEvent(Event $event): JsonResponse
    {
        try {
            // Check if the event has been soft deleted
            if ($event->trashed()) {
                return response()->json([
                    'message' => 'This event has been deleted and cannot be updated.'
                ], 410); // HTTP 410: Gone (used for deleted resources)
            }

            // Ensure the authenticated user is the owner of the event
            if ($event->user_id !== auth()->id()) {
                return response()->json([
                    'message' => 'You are not authorized to update this event.'
                ], 403); // HTTP 403: Forbidden (unauthorized action)
            }

            // Return a JSON response with event details
            return response()->json(
                new EventResource($event),
                200 // HTTP status code 200 indicates a successful request and the resource is returned in the response.
            );

        } catch (Exception $e) {
            // Return error response if event is not found or another error occurs
            return response()->json([
                'message' => 'Event not found',
                'error'   => $e->getMessage()
            ], 404);// HTTP status code 404 indicates that the requested resource was not found.
        }
    }

    /**
     * Update an existing event.
     *
     * This method ensures that the authenticated user is the owner of the event,
     * updates only the provided fields, and returns the updated event data.
     *
     * @param Event $event The event to be updated.
     * @param array $data The validated update data.
     * @return JsonResponse JSON response with success message and updated event details.
     */
    public function updateEvent(Event $event, array $data): JsonResponse
    {
        try {

            // Check if the event has been soft deleted
            if ($event->trashed()) {
                return response()->json([
                    'message' => 'This event has been deleted and cannot be updated.'
                ], 410); // HTTP 410: Gone (used for deleted resources)
            }

            // Ensure the authenticated user is the owner of the event
            if ($event->user_id !== auth()->id()) {
                return response()->json([
                    'message' => 'You are not authorized to update this event.'
                ], 403); // HTTP 403: Forbidden (unauthorized action)
            }

            // Update only provided fields
            $event->update($data);

            return response()->json([
                'message' => 'Event updated successfully',
                'event'   => new EventResource($event)
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Event not found'], 404);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Failed to update event due to a database error.',
                'error'   => $e->getMessage()
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'An unexpected error occurred while updating the event.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an event (Soft Delete).
     *
     * Ensures that the authenticated user is the owner before deleting the event.
     * If successful, returns a success message. Otherwise, an error response.
     *
     * @param Event $event The event to be deleted.
     * @return JsonResponse JSON response indicating success or failure.
     */
    public function deleteEvent(Event $event): JsonResponse
    {
        try {
            // Check if the event has been soft deleted
            if ($event->trashed()) {
                return response()->json([
                    'message' => 'This event has been deleted and cannot be deleted.'
                ], 410); // HTTP 410: Gone (used for deleted resources)
            }

            // Check if the authenticated user is the event owner
            if ($event->user_id !== auth()->id()) {
                return response()->json([
                    'message' => 'You are not authorized to delete this event.'
                ], 403);
            }

            // Perform soft delete
            $event->delete();

            return response()->json([
                'message' => 'Event deleted successfully.'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the event.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
