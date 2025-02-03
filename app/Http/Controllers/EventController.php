<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventIndexRequest;
use App\Http\Requests\EventShowRequest;
use App\Http\Requests\EventStoreRequest;
use App\Models\Event;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Exception;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(EventIndexRequest $request)
    {

        try {
            $query = Event::query();

            // Filter by start time (events starting after a given datetime)
            if ($request->has('start_time') && !empty($request->start_time)) {
                $query->where('start_time', '>=', $request->get('start_time'));
            }

            // Filter by end time (events ending before a given datetime)
            if ($request->has('end_time') && !empty($request->end_time)) {
                $query->where('end_time', '<=', $request->get('end_time'));
            }

            // Filter by location
            if ($request->has('location')) {
                $query->where('location', 'like', '%' . $request->get('location') . '%');
            }

            // Search by keyword (in title or description)
            if ($request->has('keyword')) {
                $query->where(function ($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->get('keyword') . '%')
                        ->orWhere('description', 'like', '%' . $request->get('keyword') . '%');
                });
            }

            // Pagination: Allow the user to specify the page (default is 2)
            $perPage = $request->get('perPage', 2);

            // Get paginated results
            $events = $query->paginate($perPage);

            // Return the response with pagination data and event list
            return response()->json([
                'data' => $events->getCollection()->makeHidden(['user_id']),
                'meta' => [
                    'current_page' => $events->currentPage(),
                    'last_page' => $events->lastPage(),
                    'total' => $events->total(),
                ]
            ]);
        }catch (Exception $e) {
            // Handle the error
            return response()->json([
                'message' => 'An error occurred while fetching events.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventStoreRequest $request)
    {
        try {
            // Create a new event with validated data
            $event = Event::create([
                'user_id' => auth()->id(),  // Get the authenticated user's ID and store it
                'title'       => $request->title,
                'description' => $request->description,
                'start_time'  => $request->start_time,
                'end_time'    => $request->end_time,
                'location'    => $request->location,
            ]);

            // Return a success response
            return response()->json([
                'message' => 'Event created successfully',
                'event'   => $event
            ], 201);

        } catch (QueryException $e) {
            // Handle database-related errors (e.g., constraint violations)
            return response()->json([
                'message' => 'Failed to create event due to a database error.',
                'error'   => $e->getMessage()
            ], 500);

        } catch (Exception $e) {
            // Handle any other unexpected errors
            return response()->json([
                'message' => 'An unexpected error occurred while creating the event.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EventShowRequest $request, $id)
    {
        try {
            // Find the event by ID, throw 404 if not found
            $event = Event::findOrFail($id);

            // Return a JSON response with event details
            return response()->json([
                'id'          => $event->id,
                'title'       => $event->title,
                'description' => $event->description,
                'start_time'  => $event->start_time,
                'end_time'    => $event->end_time,
                'location'    => $event->location,
            ], 200);

        } catch (Exception $e) {
            // Return error response if event is not found or another error occurs
            return response()->json([
                'message' => 'Event not found',
                'error'   => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventStoreRequest $request, $id)
    {
        try {
            // Find the event by ID
            $event = Event::findOrFail($id);

            // Ensure the authenticated user is the owner of the event
            if ($event->user_id !== auth()->id()) {
                return response()->json([
                    'message' => 'You are not authorized to update this event.'
                ], 403);
            }

            // Update only provided fields
            $event->update($request->only([
                'title', 'description', 'start_time', 'end_time', 'location'
            ]));

            return response()->json([
                'message' => 'Event updated successfully',
                'event'   => $event
            ], 200);

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
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Find the event by ID
            $event = Event::findOrFail($id);

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
