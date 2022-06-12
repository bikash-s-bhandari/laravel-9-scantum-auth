<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ArticleCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data'=>$this->collection,
            'links'=>'some links here..'

        ];
    }

    public function with($request){
        return [
            'status'=>'success'
        ];
    }

    public function withResponse($request, $response)
    {
        //api response header ma add gareko
        $response->header('Accept','application/json');
        $response->header('Version','1.0.0');

    }
}
