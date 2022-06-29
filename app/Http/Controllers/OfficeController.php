<?php

namespace App\Http\Controllers;

use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\Validators\OfficeValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Notifications\OfficePendingNotification;
use Illuminate\Validation\ValidationException;

class OfficeController extends Controller
{
    /**
     * index
     *
     * @return JsonResource
     */
    public function index()
    {
        $offices = Office::query()
            ->when(
                request('user_id') && auth()->user() && auth()->id() == request('user_id'),
                fn ($query) => $query,
                fn ($query) => $query
                    ->where('approval_status', Office::APPROVAL_APPROVED)
                    ->where('hidden', false)
            )
            ->when(request('user_id'), fn ($query) => $query->where('user_id', request('user_id'))) // filtering by the one who owns the office
            ->when(request('visitor_id'), fn ($query) => $query->whereRelation('reservations', 'user_id', '=', request('visitor_id'))) // all the offices reserved by this user
            ->with(['images', 'tags', 'user', 'reservations'])
            ->withCount(['reservations' => fn ($query) => $query->where('status', Reservation::STATUS_ACTIVE)])
            ->when(
                request('lat') && request('lng'),
                fn ($query) => $query->nearestTo(request('lat'), request('lng')),
                fn ($query) => $query->orderBy('id', 'ASC')
            )
            ->paginate(20);

        return OfficeResource::collection(
            $offices
        );
    }


    /**
     * show
     *
     * @param  Office $office
     * @return JsonResource
     */
    public function show(Office $office)
    {
        $office->loadCount(['reservations' => fn ($query) => $query->where('status', Reservation::STATUS_ACTIVE)])
            ->load(['images', 'user', 'tags']);

        return OfficeResource::make($office); // same as doing *return new OfficeResource($office)*
    }


    public function create(Request $request)
    {
        // creating an instance of Office to pass it to the 
        // OfficeValidator::validate() method  
        $office = app()->make(Office::class);

        // validating and storing the validated data
        $validated_data = app()->make(
            OfficeValidator::class
        )->validate($office, $request->all());

        // attaching a user to the office (the owner of the office)
        $validated_data['user_id'] = auth()->user()->id;

        // ensuring that all the db queries are executed without errors
        // we need those two queries to run and work both or not work both
        DB::transaction(function () use ($validated_data, $office) {
            $office->fill(
                Arr::except($validated_data, ['tags'])
            )->save();

            $office->tags()->attach($validated_data['tags']);
        });

        Notification::send(User::firstWhere('is_admin', true), new OfficePendingNotification($office));

        // returning the json Api resource response
        return OfficeResource::make($office);
    }

    public function update(Office $office, Request $request)
    {
        $this->authorize('update', [$office]);

        $validated_data = app()->make(
            OfficeValidator::class
        )->validate($office, $request->all());

        if ($requiresReview = Arr::hasAny($validated_data, ['lat', 'lng', 'price_per_day'])) {
            $validated_data = Arr::add($validated_data, 'approval_status', Office::APPROVAL_PENDING);
        }

        // ensuring that all the db queries are executed without errors
        // we need those two queries to run and work both or not work both
        DB::transaction(function () use ($validated_data, $office) {
            $office->update(
                Arr::except($validated_data, ['tags'])
            );

            if (isset($validated_data['tags'])) {
                $office->tags()->sync($validated_data['tags']);
            }
        });

        if ($requiresReview == true) {
            Notification::send(User::firstWhere('is_admin', true), new OfficePendingNotification($office));
        }

        // returning the json Api resource response
        return OfficeResource::make($office);
    }


    public function delete(Office $office)
    {
        $this->authorize('delete', [$office]);

        throw_if(
            $office->reservations()->where('status', Reservation::STATUS_ACTIVE)->exists(),
            ValidationException::withMessages(['office' => 'this Office cannot be deleted'])
        );

        return $office->delete();
    }
}
