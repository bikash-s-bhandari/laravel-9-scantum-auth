<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use App\Http\Controllers\CommonApiController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;

class BlogController extends CommonApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $blog_query=Blog::with(['user:id,name','category:id,name,slug']);
        if($request->has('search')){
            //{{APP_URL}}/blogs?search=population
            $blog_query->where('title','LIKE','%'.$request->search.'%');
        }

        if($request->has('category')){
            //{{APP_URL}}/blogs?category=population
            $blog_query->whereHas('category',function($query) use ($request){
                $query->where('slug',$request->category);

            });
        }

        if($request->has('user_id')){
            //{{APP_URL}}/blogs?user_id=2
            $blog_query->where('user_id',$request->user_id);


        }

        //for sorting purpose
         //{{APP_URL}}/blogs?user_id=2&sortBy=id&sortOrder=asc
        if($request->sortBy && in_array($request->sortBy,['id','created_at'])){
            $sortBy=$request->sortBy;

        }else{
            $sortBy='id';
        }

        if($request->sortOrder && in_array($request->sortOrder,['asc','desc'])){
            $sortOrder=$request->sortOrder;

        }else{
            $sortOrder='desc';
        }

        //for pagination purpose

        $perPage=5;
        if($request->has('perPage')){
            $perPage=$request->perPage;
        }

        //yo paginate chai boolean value ho..paginate apply vako xa ki nai vanera
        //http://127.0.0.1:8000/api/v1/blogs?paginate=1&perPage=1&page=1
        if($request->has('paginate')){
            $blogs=$blog_query->orderBy($sortBy,$sortOrder)->paginate($perPage);

        }else{
            $blogs=$blog_query->orderBy($sortBy,$sortOrder)->get();

        }



        return response()->json([
            'success'=>true,
            'data'=>$blogs,
            'message'=>''
        ],Response::HTTP_OK);
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
            'title'=>'required|string|max:50',
            'short_description'=>'required|max:200',
            'long_description'=>'required|max:10000',
            'category_id'=>'required',
            'image'=>'nullable|image|mimes:jpg,png,bmp'

        ]);
        if(!$validation['success']) {
            return $this->sendError('Validation Error.', $validation['message'],Response::HTTP_UNPROCESSABLE_ENTITY);

        }
        $image_name=null;

        if($request->image){
            // $image_name=time().'.'.$request->image->extension();
            // $request->image->move(public_path('/uploads/blog_images'),$image_name);

            $path = Storage::disk('s3')->put('images', $request->image);
            $path = Storage::disk('s3')->url($path);
            $image_name=$path;

        }



       $blog=Blog::create([
            'title'=>$request->input('title'),
            'short_description'=>$request->input('short_description'),
            'long_description'=>$request->input('long_description'),
            'category_id'=>$request->input('category_id'),
            'user_id'=>$request->user()->id??1,
            'image'=>$image_name

        ]);



        return response()->json([
            'success'=>true,
            'data'=>$blog,
            'message'=>'Blog successfully created'
        ],Response::HTTP_CREATED);


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function show(Blog $blog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Blog $blog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function destroy(Blog $blog)
    {
        //
    }
}
