<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;



class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use CanLoadRelationships;

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show',]);
        $this->middleware('throttle:api')->only(['store', 'update', 'destroy']);
        $this->authorizeResource(Event::class, 'event');
    }
    public function index()
    {
        $relations = ['user', 'attendees', 'attendees.user'];
        $query = $this->loadRelationships(Event::query(), $relations);




        return EventResource::collection($query->latest()->paginate());
    }


    public function store(Request $request)
    {

        $event = Event::create([
            ...$request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time'
            ]),
            'user_id' => 1
        ]);

        return new EventResource($event->load(['user']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $relations = ['user', 'attendees', 'attendees.user'];
        return new EventResource($this->loadRelationships($event, $relations));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {

        $this->authorize('update-event', $event);

        $event->update(
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'sometimes|date',
                'end_time' => 'sometimes|date|after:start_time'
            ])
        );

        return new EventResource($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        // return response()->json([
        //     'massage' => 'Event deleted Successfully'
        // ]);

        return response(status: 204);
    }
}
