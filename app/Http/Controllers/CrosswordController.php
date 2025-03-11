<?php

namespace App\Http\Controllers;

use App\Http\Requests\CrosswordCreateRequest;
use App\Http\Requests\CrosswordGeneratePreviewRequest;
use App\Http\Requests\SolutionCreateRequest;
use App\Models\Crossword;
use App\Models\UserSolution;
use App\Services\CrosswordGenerator;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use PDOException;

class CrosswordController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth', except: ['index'])
        ];
    }

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
        if (Gate::denies('create', Crossword::class)) {
            return redirect()->route('crosswords.index')
                ->with('error', 'Sizda krosvord yaratishga ruxsat yo\'q!');
        }

        return view('crosswords.create');
    }

    public function store(CrosswordCreateRequest $request)
    {
        if (Gate::denies('create', Crossword::class)) {
            return redirect()->route('crosswords.index')
                ->with('error', 'Sizda krosvord yaratishga ruxsat yo\'q!');
        }

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
        if (!$crossword->published && Gate::denies('view', $crossword)) {
            return redirect()->route('crosswords.index')
                ->with('error', 'Sizda bu krosvordni ko\'rishga ruxsat yo\'q!');
        }

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
        if (!$crossword->published && Gate::denies('view', $crossword)) {
            return redirect()->route('crosswords.index')
                ->with('error', 'Bu krosvordni o\'ynashga ruxsat yo\'q!');
        }

        $userSolution = UserSolution::where('user_id', Auth::id())
            ->where('crossword_id', $crossword->id)
            ->first();

        return view('crosswords.play', [
            'crossword' => $crossword,
            'userSolution' => $userSolution
        ]);
    }

    public function saveSolution(SolutionCreateRequest $request, Crossword $crossword)
    {
        if (!$crossword->published && Gate::denies('view', $crossword)) {
            return response()->json([
                'success' => false,
                'message' => 'Ushbu krosvord yechimini saqlashga ruxsat yo\'q!'
            ], 403);
        }

        $request->validated();

        try {
            $userSolution = UserSolution::query()->updateOrCreate([
                'user_id' => Auth::user()->id,
                'crossword_id' => $crossword->id
            ], [
                'solution_data' => $request->input('solution_data'),
                'completed' => $request->input('completed'),
                'time_taken' => $request->input('time_taken'),
                'score' => $this->calculateScore($request->input('solution_data'), $crossword, $request->input('time_taken'))
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

    public function generatePreview(CrosswordGeneratePreviewRequest $request)
{
    if (Gate::denies('create', Crossword::class)) {
        return response()->json([
            'success' => false,
            'message' => 'Sizda krosvord generatsiya qilishga ruxsat yo\'q!'
        ], 403);
    }

    $request->validated();

    $generator = new CrosswordGenerator();

    foreach ($request->words as $wordData) {
        $generator->addWord($wordData['word'], $wordData['clue']);
    }

    $generator->optimizeGrid();

    return response()->json([
        'success' => true,
        'grid' => $generator->getGrid(),
        'words' => $generator->getWords(),
        'gridSize' => $generator->getGridSize()
    ]);
}
}
