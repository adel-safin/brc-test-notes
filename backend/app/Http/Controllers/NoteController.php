<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListNotesRequest;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class NoteController extends Controller
{
    public function index(ListNotesRequest $request): AnonymousResourceCollection
    {
        $request->validated();

        $notes = Note::query()->latest('updated_at')->get();

        return NoteResource::collection($notes);
    }

    public function store(StoreNoteRequest $request): JsonResponse
    {
        $note = Note::query()->create($request->validated());

        return (new NoteResource($note))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Note $note): NoteResource
    {
        return new NoteResource($note);
    }

    public function update(UpdateNoteRequest $request, Note $note): NoteResource
    {
        $note->update($request->validated());

        return new NoteResource($note);
    }

    public function destroy(Note $note): Response
    {
        $note->delete();

        return response()->noContent();
    }
}
