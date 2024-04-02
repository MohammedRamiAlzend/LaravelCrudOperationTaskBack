<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    use ApiResponseTrait;
    public function index()
    {
        $posts = PostResource::collection(Post::get());
        return $this->apiResponse($posts, 'ok', 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            [
                'title' => $request->title,
                'body' => $request->body,
            ],
            [
                'title' => 'required|max:255',
                'body' => 'required',
            ]);
        if ($validator->fails()) {
            return $this->apiResponse(null, $validator->errors(), 400);
        }
        $post = Post::create(
            [
                'title' => $request->title,
                'body' => $request->body,
            ]
        );

        if ($post) {
            return $this->apiResponse(new PostResource($post), 'the post saved successfully in data base', 200);
        }
        return $this->apiResponse(null, 'the post didn\'t saved successfully in data base', 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::find($id);
        if ($post) {
            return $this->apiResponse(new PostResource($post), 'ok', 200);
        }
        return $this->apiResponse(null, 'The Post Not Found', 401);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make(
            [
                'title' => $request->title,
                'body' => $request->body,
            ],
            [
                'title' => 'required|max:255',
                'body' => 'required',
            ]);
        if ($validator->fails()) {
            return $this->apiResponse(null, $validator->errors(), 400);
        }
        $post = Post::find($id);
        if (!$post) {
            return $this->apiResponse(null, 'The Post Not Found', 404);
        }
        try {
            DB::beginTransaction();
            $post->update(
                [
                    'title' => $request->title,
                    'body' => $request->body,
                ]
            );
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->apiResponse(null, $e->getMessage(), 400);
        }
        if ($post) {
            return $this->apiResponse(new PostResource($post), 'the post Updated successfully in data base', 200);
        }
        return $this->apiResponse(null, 'the post didn\'t Updated successfully in data base', 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $post = Post::find($id);
        try {
            DB::beginTransaction();
            if ($post) {
                $post->delete();
                DB::commit();
                return $this->apiResponse(null, "The Post Deleted Successfully from DataBase ", 404);
            } else {
                return $this->apiResponse(null, "The Post didn't founded in database ", 404);
            }
        } catch (\Throwable $e) {
            return $this->apiResponse(null, $e->getMessage() , 400);
        }

    }
}
