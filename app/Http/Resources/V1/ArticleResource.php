<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    //default response chai data ma auxa..ie data:{[],[]....}, now artciles:{[],[]..}
    public static $wrap='articles';
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'type'=>'article',
            'id'=>$this->id(),//form article model
            'attributes'=>[
                'title'=>$this->title(),
                'slug'=>$this->slug(),
                'created_at'=>$this->created_at

            ],

            'relationships'=>[
                'author'=>AuthorResource::make($this->author())
            ],
            'links'=>[
                'self'=>route('articles.show',$this->id()),
                'related'=>route('articles.show',$this->slug()),


            ],

        ];
    }

    public function with($request){
        return [
            'status'=>'success'
        ];
    }

    public function withResponse($request, $response)
    {
        //response json format ma ho vanera inform garxa
        $response->header('Accept','application/json');


    }

}
