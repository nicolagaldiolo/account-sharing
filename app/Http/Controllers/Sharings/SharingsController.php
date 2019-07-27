<?php

namespace App\Http\Controllers\Sharings;

use App\Enums\SharingStatus;
use App\Http\Requests\SharingRequest;
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
    public function index(Request $request)
    {

        $param = $request->input('type', '');

        switch ($param){
            case 'pending':
                $sharings = Auth::user()->sharings()->pending()->get();
                break;
            case 'approved':
                $sharings = Auth::user()->sharings()->approved()->get();
                break;
            case 'owner':
                // manipolo i dati tornati raggruppando gli utenti per stato della relazione con sharing(es: pendind: utenti..., joined: utenti...)
                $sharings = Auth::user()->sharingOwners()->with('users')->get()->each(function($sharing){
                    $sharing['sharing_status'] = collect(SharingStatus::getInstances())->each(function($sharingStatus) use($sharing){
                        $sharingStatus->users = $sharing->users->where('pivot.status', $sharingStatus->value)->values();
                    });
                });
                break;
            case 'joined':
                $sharings = Auth::user()->sharings()->joined()->get();
                break;
            default:
                $sharings = Sharing::public()->get();
                break;
        }

        return $sharings;
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
    public function store(SharingRequest $request)
    {
        $user = $request->user();
        return $user->sharingOwners()->create($request->validated());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Sharing $sharing)
    {
        return $sharing->load('category', 'activeUsers');
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

    public function transition(Request $request, Sharing $sharing, $transition)
    {
        $user = $request->user();
        $user->sharings()->syncWithoutDetaching([$sharing->id => ['status' => $transition]]);
    }

    public function requestToManage()
    {
        $status = 1;
        return Auth::user()->sharingOwners()->byStatus($status)->get();
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
