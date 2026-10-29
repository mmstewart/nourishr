<?php

namespace App\Http\Controllers;

use App\Exceptions\CoachRefusedException;
use App\Exceptions\CoachUnavailableException;
use App\Http\Requests\AskCoachRequest;
use App\Services\CoachService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CoachController extends Controller
{
    public function __construct(private CoachService $coach) {}

    public function index(Request $request)
    {
        abort_unless($this->coach->isEnabled(), 404);

        return Inertia::render('Coach', [
            'question' => $request->session()->get('coach_question'),
            'answer' => $request->session()->get('coach_answer'),
            'remainingToday' => $this->coach->remainingToday($request->user()),
            'dailyLimit' => $this->coach->dailyLimit(),
        ]);
    }

    public function ask(AskCoachRequest $request)
    {
        abort_unless($this->coach->isEnabled(), 404);

        $user = $request->user();
        $question = $request->validated('question');

        if ($this->coach->remainingToday($user) === 0) {
            return to_route('coach')
                ->with('coach_question', $question)
                ->with('error', "You've used all {$this->coach->dailyLimit()} coach questions for today. Your questions reset at midnight.");
        }

        try {
            $answer = $this->coach->ask($user, $question);
        } catch (CoachRefusedException) {
            return to_route('coach')
                ->with('coach_question', $question)
                ->with('error', "I can't help with that question. Try asking about food, nutrition, or fitness.");
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
