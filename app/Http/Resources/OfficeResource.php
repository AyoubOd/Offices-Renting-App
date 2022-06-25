<?php

namespace App\Http\Resources;

use Illuminate\Support\Arr;

use Illuminate\Http\Resources\Json\JsonResource;

class OfficeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'user' => UserResource::make($this->user),

            'images' => ImageResource::collection($this->images),

            'reservations' => ReservationResource::collection($this->reservations),

            'tags' => TagResource::collection($this->tags),

            $this->merge(Arr::except(parent::toArray($request), [
                'user_id', 'created_at', 'verified_at', 'deleted_at', 'hidden', 'updated_at'
            ]))

        ];
    }
}
