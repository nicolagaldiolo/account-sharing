<?php

namespace App\Http\Controllers\Sharings;

use App\Chat;
use App\Events\ChatMessageSent;
use App\Http\Requests\ChatRequest;
use App\Sharing;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\Chat as ChatResource;

class ChatsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Sharing $sharing)
    {
        $this->authorize('manage-sharing', $sharing);

        $collection = $sharing->chats()->with('user')->latest()->paginate(15);
        return ChatResource::collection($collection);
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
    public function store(ChatRequest $request, Sharing $sharing)
    {
        $this->authorize('manage-sharing', $sharing);

        $this->validate($request, [
            'message' => 'required'
        ]);

        $user = Auth::user();
        $chat = new Chat;

        $chat->message = $request->input('message');
        $chat->sharing()->associate($sharing);
        $chat->user()->associate($user);
        $chat->save();

        broadcast(new ChatMessageSent($user, $chat))->toOthers();

        return new ChatResource($chat);
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
