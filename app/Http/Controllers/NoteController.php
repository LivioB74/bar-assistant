<?php

declare(strict_types=1);

namespace Kami\Cocktail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kami\Cocktail\Models\Note;
use Illuminate\Http\JsonResponse;
use Kami\Cocktail\Models\Cocktail;
use Kami\Cocktail\Http\Requests\NoteRequest;
use Kami\Cocktail\Http\Resources\NoteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class NoteController extends Controller
{
    public function show(Request $request, int $id): JsonResource
    {
        $note = Note::findOrFail($id);

        if ($request->user()->cannot('show', $note)) {
            abort(403);
        }

        return new NoteResource($note);
    }

    public function store(NoteRequest $request): JsonResponse
    {
        $resourceId = $request->post('resource_id');
        $resourceType = $request->post('resource');

        $resourceModel = match ($resourceType) {
            'cocktail' => Cocktail::findOrFail($resourceId),
            default => abort(404)
        };

        if ($request->user()->cannot('addNote', $resourceModel)) {
            abort(403);
        }

        $note = $resourceModel->addNote($request->post('note'), $request->user()->id);

        return (new NoteResource($note))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('notes.show', $note->id));
    }

    public function delete(Request $request, int $id): Response
    {
        $note = Note::findOrFail($id);

        if ($request->user()->cannot('delete', $note)) {
            abort(403);
        }

        $note->delete();

        return response(null, 204);
    }
}
