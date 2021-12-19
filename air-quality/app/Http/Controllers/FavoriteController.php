<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Models\Favorite;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Auth;
use Illuminate\Validation\Rule;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function index()
    {
        $favorites = Favorite::all();
        $api = new ApiController();
        $data = $api->index();
        $array = [$favorites, $data];

        return view('map', ['array' => $array]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function findAjaxFunction(Request $request)
    {
        if ($request->has('name')) {
            $request->validate([
                'name' => 'required|max:255|string',
                'category' => ['required', Rule::in(['park', 'city', 'running_place'])],
                'coordinates' => 'required|numeric'
            ], [
                'name.required' => 'A name is mandatory!',
                'name.max' => 'The name cannot be longer than 255 signs',
                'name.string' => 'A name must contain strings',
                'category.required' => 'You have to choose a category',
            ]);
            return $this->storeFavorites($request);
        } elseif ($request->has('poll')) {
            return $this->requestApiData($request);
        }
    }
    public function requestApiData($request)
    {
        $api = new ApiController();
        $apiData = $api->dataRequest($request->poll);
        return response()->json(['apiData' => $apiData]);
    }
    public function storeFavorites($request)
    {
        $array = explode(',', $request->coordinates);

        $favorite = new Favorite;
        $favorite->name = $request->name;
        $favorite->coordinates_x = $array[0];
        $favorite->coordinates_y  = $array[1];
        $favorite->category = $request->category;
        $favorite->user_id = $request->Auth::user()->id;
        $result = $favorite->save();
        if ($result) {
            back()->with('success', 'Saved the favorite in the DB');
            $last = DB::table('favorites')->latest()->first();
            return  response()->json(['last' => $last]);
        } else
            return back()->with('error', 'Something wrong with the DB.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //Need to use favorite_id as a hidden field to use the edit/show/update method
        //             $favorite = Favorite::find($id);
        //     return view('favorites', ['favorite' => $favorite]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //Need to use favorite_id as a hidden field to use the edit/show/update method
        // $favorite = Favorite::find($id);
        // return view('edit-favorite', ['favorite' => $favorite]);
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
        // Validations
        // $request->validated();
        $array = explode(',', $request->coordinates);


        $favorite = Favorite::find($id);

        $favorite->id = $request->id;
        $favorite->name = $request->name;
        $favorite->coordinates_x = $request->coordinates_x;
        $favorite->coordinates_y  =  $request->coordinates_y;
        $favorite->category = $request->category;
        $favorite->user_id = $request->user_id; // use the auth id
        // $favorite->user_id = $request->Auth::user()->id; // use the auth id

        if ($favorite->save())
            return back()->with('success', 'Updated in the DB');
        else
            return back()->with('error', 'Something wrong with the DB');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        $result = Favorite::destroy($id);



        if ($result)
            return back()->with('success', 'Favorite was deleted from the DB');
        else
            return back()->with('error', 'Something wrong with the DB.');
    }
}
