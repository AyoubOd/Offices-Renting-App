<?php

namespace App\Http\Resources;

use App\Models\Image;
use Illuminate\Support\Arr;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'image' => ImageResource::make($this->image),

            $this->merge(Arr::except(parent::toArray($request), [
                'email', 'email_verified_at', 'created_at', 'updated_at',
            ]))
        ];
    }
}
