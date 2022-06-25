<?php

namespace App\Http\Controllers;

use App\Http\Resources\OfficeResource;
use Illuminate\Database\Schema\Builder;
use App\Models\Office;
use App\Models\Reservation;
use Illuminate\Http\Request;

use function GuzzleHttp\Promise\each;

class OfficeController extends Controller
{
    public function index()
    {
        $offices = Office::query()
            ->where('approval_status', Office::APPROVAL_APPROVED)
            ->where('hidden', false)
            ->when(request('host_id'), fn ($query) => $query->where('user_id', request('host_id'))) // the one who owns the office
            ->when(request('user_id'), fn ($query) => $query->whereRelation('reservations', 'user_id', '=', request('user_id'))) // all the offices reserved by this user
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
}
