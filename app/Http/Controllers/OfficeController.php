<?php

namespace App\Http\Controllers;

use App\Http\Resources\OfficeResource;
use Illuminate\Database\Schema\Builder;
use App\Models\Office;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    public function index()
    {
        $offices = Office::query()
            ->where('approval_status', Office::APPROVAL_APPROVED)
            ->where('hidden', false)
            ->when(request('host_id'), fn ($query) => $query->where('user_id', request('host_id'))) // the one who owns the office
            ->when(request('user_id'), fn ($query) => $query->whereHas(
                'reservations',
                fn ($query) =>
                $query->where("user_id", request('user_id'))
            )) // all the offices reserved by this user
            ->latest('id')
            ->paginate(20);

        return OfficeResource::collection(
            $offices
        );
    }
}
