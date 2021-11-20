<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return CategoryResource::collection(Category::all());
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
            'name' => 'required|string',
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
            $cat = Category::create($ins);
            $cat->save();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success'=>false,
                'message'=>$ex->getMessage()
            ], 409);
        }

        return (new CategoryResource($cat))
            ->additional([
                'success' => true,
                'message' => "Category created"
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
        return (new CategoryResource(Category::findOrFail($id)))->additional([
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
            'name' => 'required|string',
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
            $cat = Category::findOrFail($id);
            $ins['updated_by'] = Auth::user()->id;
            $cat->update($ins);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success'=>false,
                'message'=>$ex->getMessage()
            ], 409);
        }

        return (new CategoryResource($cat))
            ->additional([
                'success' => true,
                'message' => "Category updated"
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
            $cat = Category::findOrFail($id);
            $ins['updated_by'] = Auth::user()->id;
            $cat->delete();
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
                'message' => "Category deleted"
            ]);
    }
}
