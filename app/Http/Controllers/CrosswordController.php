<?php

namespace App\Http\Controllers;

use App\Http\Requests\CrosswordCreateRequest;
use App\Http\Requests\SolutionCreateRequest;
use App\Models\Crossword;
use App\Models\UserSolution;
use App\Services\CrosswordGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use PDOException;

class CrosswordController extends Controller
{
    public function index()
    {
        $crosswords = Crossword::query()->where('published', true)
        ->with(['creator:id,name'])
        ->latest()
        ->paginate(10);

        return view('crosswords.index', [
            'crosswords' => $crosswords
        ]);
    }

    public function create()
    {
        return view('crosswords.create');
    }

    public function store(CrosswordCreateRequest $request)
    {
        $request->validated();

        $generator = new CrosswordGenerator();

        try {
            foreach ($request->input('words') as $wordContainer) {
                $generator->addWord($wordContainer['word'], $wordContainer['clue']);
            }

            $generator->optimizeGrid();

            $crossword = new Crossword([
               'title' => $request->input('title'),
               'grid_data' => $generator->getGrid(),
               'words' => $generator->getWords(),
               'published' => $request->has('publish'),
               'created_by' => Auth::user()->id
            ]);

            $crossword->save();
        } catch (InvalidArgumentException $e) {
            // TODO: send error back with appropriate message later
            return redirect()->back()->with('error', $e->getMessage());
        } catch (PDOException $e) {
            // TODO: send error back with appropriate message later
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('crosswords.show', ['crossword' => $crossword])
            ->with('success', 'Krosvord yaratildi!');
    }

    public function show(Crossword $crossword)
    {
        $userSolution = null;

        if (Auth::check()) {
            $userSolution = UserSolution::query()
            ->where('user_id', Auth::user()->id)
            ->where('crossword_id', $crossword->id)
            ->first();
        }
        return view('crosswords.show', [
            'crossword' => $crossword,
            'userSolution' => $userSolution
        ]);
    }

    public function play(Crossword $crossword)
    {
        return view('crosswords.play', [
            'crossword' => $crossword
        ]);
    }

    public function saveSolution(SolutionCreateRequest $request, Crossword $crossword)
    {
        $request->validated();

        try {
            $userSolution = UserSolution::query()->updateOrCreate([
                'user_id' => Auth::user()->id,
                'crossword_id' => $crossword->id
            ], [
                'solution_data' => $request->input('solution_data'),
                'completed' => $request->input('completed'),
                'time_taken' => $request->input('time_taken'),
                'score' => $request->input('score')
            ]);

            // TODO: create universal response mechanizm later
            return response()->json([
                'success' => true,
                'solution' => $userSolution
            ]);
        } catch (PDOException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function calculateScore($solutionData, Crossword $crossword, $timeTaken)
    {
        $words = collect($crossword->words);
        $correctWords = 0;

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

        $score = $correctWords * 10;

        if ($timeTaken && $correctWords == $words->count()) {
            $timeBonus = max(0, 300 - $timeTaken) * 2;
            $score += $timeBonus;
        }

        return $score;
    }

    public function leaderboard()
    {
        $userSolutions = UserSolution::query()
        ->selectRaw('COUNT(*) as solved_count')
        ->where('completed', true)
        ->groupBy('user_id')
        ->orderByDesc('solved_count')
        ->with('user:id,name')
        ->take(10)
        ->get();

        return view('crosswords.leaderboard', [
            'topUsers' => $userSolutions
        ]);
    }
}
