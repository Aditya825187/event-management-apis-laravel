<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Models\Attendee;
use Illuminate\Http\Request;
use App\Http\Traits\CanLoadRelationships;

class AttendeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use CanLoadRelationships;

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index',]);
        $this->middleware('throttle:api')->only(['store', 'destroy']);
        $this->authorizeResource(Attendee::class, 'attendee');
    }
    public function index(Event $event)
    {
        $relations = ['user'];
        $attendees = $this->loadRelationships($event->attendees()->latest()->getQuery(), $relations);

        return  AttendeeResource::collection($attendees->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
        $attendee = $event->attendees()->create([
            'user_id' => 1
        ]);

        return new AttendeeResource($attendee);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event, Attendee $attendee)
    {
        $relations = ['user'];
        return new AttendeeResource($this->loadRelationships($attendee, $relations));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event, Attendee $attendee)
    {

        $this->authorize('delete-event', [$event, $attendee]);
        $attendee->delete();
        return response(status: 204);
    }
}
