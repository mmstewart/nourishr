<?php

namespace App\Http\Controllers;

use App\Exceptions\CoachUnavailableException;
use App\Http\Requests\AskCoachRequest;
use App\Services\CoachService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CoachController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Coach', [
            'question' => $request->session()->get('coach_question'),
            'answer' => $request->session()->get('coach_answer'),
        ]);
    }

    public function ask(AskCoachRequest $request, CoachService $coach)
    {
        $question = $request->validated('question');

        try {
            $answer = $coach->ask($question);
        } catch (CoachUnavailableException) {
            return to_route('coach')
                ->with('coach_question', $question)
                ->with('error', 'The coach is unavailable right now. Please try again in a minute.');
        }

        return to_route('coach')
            ->with('coach_question', $question)
            ->with('coach_answer', $answer);
    }
}
