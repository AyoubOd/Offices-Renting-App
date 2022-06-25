<?php

namespace App\Http\Controllers;

use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;


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
            ->where('approval_status', Office::APPROVAL_APPROVED)
            ->where('hidden', false)
            ->when(request('user_id'), fn ($query) => $query->where('user_id', request('user_id'))) // the one who owns the office
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
        $validated_data = validator(
            $request->all(),
            [
                'title' => ['required', 'string'],
                'description' => ['required', 'string'],
                'lat' => ['required', 'numeric'],
                'lng' => ['required', 'numeric'],
                'address_line1' => ['required', 'string'],
                'address_line2' => ['string'],
                'hidden' => ['required', 'boolean'],
                'price_per_day' => ['required', 'numeric', 'min:100'],
                'monthly_discount' => 'min:0',

                'tags' => 'array',
                'tags.*' => ['integer', Rule::exists('tags', 'id')]
            ]
        )->validate();

        $validated_data['user_id'] = auth()->user()->id;

        $office = Office::create(
            Arr::except($validated_data, ['tags'])
        );

        $office->tags()->sync($validated_data['tags']);
        return OfficeResource::make($office);
    }
}
