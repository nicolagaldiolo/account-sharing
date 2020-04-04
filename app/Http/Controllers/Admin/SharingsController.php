<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SharingApprovationStatus;
use App\Events\SharingStatusUpdated;
use App\Http\Resources\Sharing as SharingResource;
use App\Sharing;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SharingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return SharingResource::collection(Sharing::with(['renewalFrequency'])->paginate(config('custom.paginate')));
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
    public function show($id)
    {
        //
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
    public function update(Request $request, Sharing $sharing, $action)
    {
        $action = intval($action);

        $this->authorize('change-sharing-status', [$sharing, $action]);

        // Update the sharing
        $sharing->status = $action;
        $sharing->save();

        event(New SharingStatusUpdated($sharing));

        if($sharing->status === SharingApprovationStatus::Refused){
            $sharing->delete();
        }

        return new SharingResource($sharing);
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
