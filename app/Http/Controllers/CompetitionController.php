<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompetitionCreateRequest;
use App\Http\Requests\CrosswordSolutionCreateRequest;
use App\Models\Competition;
use App\Models\CompetitionResult;
use App\Models\Crossword;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PDOException;

// TODO: use repository-service pattern for all controllers
class CompetitionController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        $upcomingCompetitions = Competition::query()
        ->with('crossword:id,title')
        ->orderBy('start_time')
        ->take(5)
        ->get();

        $activeCompetitions = Competition::query()
        ->where('end_time', '>=', $now)
        ->where('is_active', true)
        ->with('crossword:id,title')
        ->get();

        $pastCompetitions = Competition::query()
        ->orWhere('is_active', false)
        ->with('crossword:id,title')
        ->orderByDesc('end_time')
        ->paginate(10);

        return view('competition.index', [
            'upcomingCompetitions' => $upcomingCompetitions,
            'activeCompetitions' => $activeCompetitions,
            'pastCompetitions' => $pastCompetitions
        ]);
    }

    public function create()
    {
        $crosswords = Crossword::query()
        ->where('published', true)
        ->orderBy('title')
        ->get(['id', 'title']);

        return view('competition.create', [
            'crosswords' => $crosswords
        ]);
    }

    public function store(CompetitionCreateRequest $request)
    {
        $request->validated();

        try {
            $competition = Competition::query()->create([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'crossword_id' => $request->input('crossword_id'),
                'start_time' => $request->input('start_time'),
                'end_time' => $request->input('end_time'),
                'is_active' => true
            ]);

            return redirect()
                ->route('competition.index', $competition)
                ->with('success', 'Musobaqa muvaffaqiyatli yaratildi');
        } catch (PDOException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function show(Competition $competition)
    {
        $now = Carbon::now();
        $competition->load('crossword');

        $userResult = null;
        $results = null;

        if ($competition->end_time < $now || $competition->is_active == false) {
            $results = CompetitionResult::query()
            ->where('competition_id', $competition->id)
            ->orderByDesc('score')
            ->orderBy('time_taken')
            ->get();

            $rank = 1;
            foreach ($results as $index => $result) {
                if ($index > 0 &&
                ($result->score == $results[$index - 1]->score || ($result->score == $results[$index - 1]->score &&
                $result->time_taken > $results[$index - 1]->time_taken))) {
                    $rank++;
                }

                $result->ranking = $rank;
                $results->save();
            }
        }

        if (Auth::check()) {
            $userResult = CompetitionResult::query()
            ->where('competition_id', $competition->id)
            ->where('user_id', Auth::user()->id)
            ->first();
        }

        $canPlay = $now >= $competition->start_time && $now <= $competition->end_time && $competition->is_active;

        return view('competition.show', [
            'competition' => $competition,
            'userResult' => $userResult,
            'results' => $results,
            'canPlay' => $canPlay
        ]);
    }

    public function play(Competition $competition) {
        $now = Carbon::now();

        if ($now < $competition->start_time || $now > $competition->end_time || !$competition->is_active) {
            return redirect()->route('competition.index')->with('error', 'Musobaqa mavjud emas!');
        }

        $competition->load('crossword');

        return view('competition.play', [
            'competition' => $competition
        ]);
    }

    public function saveSolution(CrosswordSolutionCreateRequest $request, Competition $competition)
    {
        $now = Carbon::now();

        if ($now > $competition->end_time || !$competition->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Musobaqa tugagan'
            ], 403);
        }

        $request->validated();

        $competition->load('crossword');

        $score = $this->calculateScore($request->input('solution_data'), $competition->crossword, $request->input('time_taken'));

        $result = CompetitionResult::query()->updateOrCreate([
            'user_id' => Auth::user()->id,
            'competition_id' => $competition->id
        ], [
            'solution_data' => $request->input('solution_data'),
            'time_taken' => $request->input('time_taken'),
            'score' => $score,
            'completed' => $request->input('completed')
        ]);

        return response()->json([
            'success' => true,
            'result' => $result
        ]);
    }

    public function calculateScore($solutionData, Crossword $crossword, $timeTaken) {
        $words = collect($crossword->words);
        $correctWords = 0;
        $totalLetters = 0;

        foreach ($crossword->grid_data as $row) {
            foreach ($row as $cell) {
                if (!empty($cell['letter'])) {
                    $totalLetters++;
                }
            }
        }

        foreach ($solutionData as $row => $cols) {
            foreach ($cols as $col => $cell) {
                if (!empty($cell['letter'])) {
                    $gridLetter = $crossword->grid_data[$row][$col]['letter'] ?? null;
                    if ($gridLetter && strtoupper($cell['letter']) === strtoupper($gridLetter)) {
                        $correctWords++;
                    }
                }
            }
        }

        $percentageCorrectness = $totalLetters > 0 ? ($correctWords / $totalLetters) : 0;
        $score = round($percentageCorrectness * 1000);

        if ($timeTaken && $correctWords == $totalLetters) {
            $maxTime = 600;
            $timeBonus = max(0, $maxTime - $timeTaken) * 0.5;
            $score += round($timeBonus);
        }

        return $score;
    }

    public function terminate(Competition $competition) {
        $this->authorize('update', $competition);
        $competition->update(['is_active' => false]);

        return redirect()->route('competition.index')->with('success', 'Musobaqa tugatildi');
    }
}
