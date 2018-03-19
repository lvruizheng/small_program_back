<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class TaskResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $format = [
            'id' => $this->id,
            'title' => $this->title,
            'intro' => $this->introduce,
            'location' => $this->location,
            'start' => $this->start,
            'end' => $this->end,
        ];
        return $format;
    }
}
