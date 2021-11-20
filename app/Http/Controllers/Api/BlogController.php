<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::user()->is_admin == 1){
            return BlogResource::collection(Blog::all());
        }else{
            return BlogResource::collection(Blog::where('created_by',Auth::user()->id)->get());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'category_id' => 'required|numeric|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success'=>false,
                'message'=>'invalid',
                'errors'=>$validator->errors()->toArray(),
            ], 422);
        }
        DB::beginTransaction();
        try {
            $ins = $validator->validate();
            $ins['created_by'] = Auth::user()->id;
            $blog = Blog::create($ins);
            $blog->save();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success'=>false,
                'message'=>$ex->getMessage()
            ], 409);
        }

        return (new BlogResource($blog))
            ->additional([
                'success' => true,
                'message' => "Blog created"
            ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(empty(Blog::where(['id'=>$id, 'created_by'=>Auth::user()->id])->first())){
            return response()->json([
                'success'=>false,
                'message'=>"invalid"
            ], 422);
        }
        return (new BlogResource(Blog::findOrFail($id)))->additional([
            'success' => true,
            'message' => ""
        ]);
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
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'category_id' => 'required|numeric|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success'=>false,
                'message'=>'invalid',
                'errors'=>$validator->errors()->toArray(),
            ], 422);
        }
        if(empty(Blog::where(['id'=>$id, 'created_by'=>Auth::user()->id])->first())){
            return response()->json([
                'success'=>false,
                'message'=>"invalid"
            ], 422);
        }

        DB::beginTransaction();
        try {
            $ins = $validator->validate();
            $blog = Blog::where(['id'=>$id, 'created_by'=>Auth::user()->id])->first();
            $blog->update($ins);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success'=>false,
                'message'=>$ex->getMessage()
            ], 409);
        }

        return (new BlogResource($blog))
            ->additional([
                'success' => true,
                'message' => "Blog updated"
            ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(empty($id)){
            return response()->json([
                'success'=>false,
                'message'=>"invalid"
            ], 422);
        }
        DB::beginTransaction();
        try {
            $blog = Blog::where(['id'=>$id, 'created_by'=>Auth::user()->id])->first();
            $ins['updated_by'] = Auth::user()->id;
            if(empty($blog)){
                return response()->json([
                    'success'=>false,
                    'message'=>"invalid"
                ], 422);
            }
            $blog->delete();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success'=>false,
                'message'=>$ex->getMessage()
            ], 409);
        }

        return response()->json([
                'success' => true,
                'message' => "Blog deleted"
            ]);
    }
}
