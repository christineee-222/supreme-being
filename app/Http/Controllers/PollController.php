<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollOption;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PollController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create', Poll::class);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'description' => ['nullable', 'string', 'max:2000'],
            'options' => ['required', 'array', 'min:2', 'max:6'],
            'options.*' => ['required', 'string', 'max:80'],
            'ends_at' => ['nullable', 'date'],
        ]);

        $poll = Poll::create([
            'user_id' => request()->user()->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => 'open', // MVP: open immediately
            'ends_at' => $data['ends_at'] ?? null,
        ]);

        foreach ($data['options'] as $i => $text) {
            PollOption::create([
                'poll_id' => $poll->id,
                'text' => $text,
                'sort_order' => $i,
            ]);
        }

        return response()->json($poll->load('options'), 201);
    }

    public function update(Request $request, Poll $poll)
    {
        $this->authorize('update', $poll);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:140'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'status' => ['sometimes', Rule::in(['draft', 'open', 'closed'])],
            'ends_at' => ['sometimes', 'nullable', 'date'],
        ]);

        $poll->update($data);

        return response()->json($poll->fresh());
    }

    public function vote(Request $request, Poll $poll)
    {
        $this->authorize('vote', $poll);

        abort_if($poll->status !== 'open', 422, 'Poll is not open.');
        abort_if($poll->ends_at && now()->greaterThan($poll->ends_at), 422, 'Poll has ended.');

        $data = $request->validate([
            'option_id' => ['required', 'string', Rule::exists('poll_options', 'id')->where('poll_id', $poll->id)],
        ]);

        // MVP: allow changing vote (update-or-create)
        $poll->votes()->updateOrCreate(
            ['user_id' => request()->user()->id],
            ['poll_option_id' => $data['option_id']]
        );

        return response()->json([
            'status' => 'voted',
            'poll' => $poll->loadCount('votes')->load(['options' => function ($q) {
                $q->withCount(['votes as votes_count']);
            }]),
        ]);
    }
}
