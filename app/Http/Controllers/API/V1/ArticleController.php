<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ArticleCollection;
use App\Http\Resources\V1\ArticleResource;
use App\Models\Article;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\CommonApiController;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends CommonApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $articles=Article::all();
        return new ArticleCollection($articles);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = $this->my_validation([
            'title'=>'required|string|max:50|unique:articles,title',
            'body'=>'required|min:5'

        ]);
        if(!$validation['success']) {
            return $this->sendError('Validation Error.', $validation['message'],Response::HTTP_BAD_REQUEST);

        }
       $article=Article::create([
            'title'=>$request->input('title'),
            'slug'=>Str::slug($request->input('title')),
            'body'=>$request->input('body'),
            'author_id'=>auth()->id()??1,

        ]);

        return $this->sendResponse(new ArticleResource($article), 'Article created successfully.',Response::HTTP_CREATED);


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function show(Article $article)
    {
        return $this->sendResponse(new ArticleResource($article), 'Article retrieved successfully.',Response::HTTP_OK);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Article $article)
    {

        $validation = $this->my_validation([
            'title'=>['sometimes','string','max:50',Rule::unique('articles')->ignore($article->title,'title')],
            'body'=>'required|min:5'

        ]);
        if(!$validation['success']) {
            return $this->sendError('Validation Error.', $validation['message'],Response::HTTP_BAD_REQUEST);

        }



        $article=$article->update([
            'title'=>$request->input('title'),
            'slug'=>Str::slug($request->input('title')),
            'body'=>$request->input('body'),
            'author_id'=>auth()->id()??1,

        ]);

        return $this->sendResponse(new ArticleResource($article), 'Article updated successfully.',Response::HTTP_OK);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function destroy(Article $article)
    {
        if($article->isAuthoredBy(auth()->user())){
            $article->delete();
            return $this->sendResponse([], 'Article deleted successfully.',Response::HTTP_NO_CONTENT);

        }else{
            return $this->sendError('Unauthorized',[],Response::HTTP_UNAUTHORIZED);
        }

    }
}
