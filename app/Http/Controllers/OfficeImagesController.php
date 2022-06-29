<?php

namespace App\Http\Controllers;

use App\Http\Resources\ImageResource;
use Illuminate\Http\Request;
use App\Models\Office;
use Illuminate\Support\Facades\Storage;


class OfficeImagesController extends Controller
{
    public function store(Office $office, Request $request) {

        $this->authorize('update', [$office]);

        $validated_data = validator($request->all(), [
            'image' => 'required|file|max:5000|mimes:jpg,png'
        ])->validate();

        $path = Storage::disk('public')->put('/',$request->file('image'));

        $image = $office->images()->create(['path' => $path]);

        return ImageResource::make($image);
    }
}
