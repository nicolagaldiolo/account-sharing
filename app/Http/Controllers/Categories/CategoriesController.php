<?php

namespace App\Http\Controllers\Categories;

use App\Category;
use App\Enums\RenewalFrequencies;
use App\Enums\SharingVisibility;
use App\RenewalFrequency;
use App\Sharing;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $embed = ($request->has('embed') ? explode(',', $request->input('embed')) : []);

        $appends = [];

        if(in_array('renewal_frequencies', $embed)){
            $appends['renewal_frequencies'] = RenewalFrequency::all();
        };

        if(in_array('sharings', $embed)){
            $appends['sharings'] = Sharing::all();
        };

        if(in_array('sharings_visibility', $embed)){
            $appends['sharings_visibility'] = SharingVisibility::toSelectArray();
        };

        $data = [
            'categories' => Category::all(),
        ];

        return array_merge($data, $appends);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        return $category->load('sharings.owner');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
