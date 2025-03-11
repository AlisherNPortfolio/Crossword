<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\DashboardCrosswordCreateRequest;
use App\Http\Requests\Dashboard\DashboardCrosswordUpdateRequest;
use App\Models\Crossword;
use App\Services\CrosswordGenerator;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CrosswordController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'checkRole:administrator,creator',
            'checkPermission:dashboard.access'
        ];
    }

    public function index()
    {
        // Admin sees all crosswords
        if (Auth::user()->isAdmin()) {
            $crosswords = Crossword::with('creator:id,name')
                ->latest()
                ->paginate(15);
        } else {
            $crosswords = Crossword::where('created_by', Auth::id())
                ->with('creator:id,name')
                ->latest()
                ->paginate(15);
        }

        return view('dashboard.crosswords.index', [
            'crosswords' => $crosswords
        ]);
    }

    public function create()
    {
        if (Gate::denies('create', Crossword::class)) {
            return redirect()->route('dashboard.crosswords.index')
                ->with('error', 'Sizda krosvord yaratish uchun ruxsat yo\'q!');
        }

        return view('dashboard.crosswords.create');
    }

    public function store(DashboardCrosswordCreateRequest $request)
    {
        if (Gate::denies('create', Crossword::class)) {
            return redirect()->route('dashboard.crosswords.index')
                ->with('error', 'You do not have permission to create crosswords.');
        }

        $request->validated();

        $generator = new CrosswordGenerator();

        foreach ($request->words as $wordData) {
            $generator->addWord($wordData['word'], $wordData['clue']);
        }

        $generator->optimizeGrid();

        $crossword = new Crossword([
            'title' => $request->title,
            'grid_data' => $generator->getGrid(),
            'words' => $generator->getWords(),
            'published' => $request->has('publish'),
            'created_by' => Auth::id()
        ]);

        $crossword->save();

        return redirect()->route('dashboard.crosswords.show', $crossword)
            ->with('success', 'Krosvord yaratildi!');
    }

    public function show(Crossword $crossword)
    {
        if (Gate::denies('view', $crossword)) {
            return redirect()->route('dashboard.crosswords.index')
                ->with('error', 'You do not have permission to view this crossword.');
        }

        $completions = $crossword->solutions()
            ->where('completed', true)
            ->with('user:id,name')
            ->orderBy('time_taken')
            ->take(10)
            ->get();

        $totalAttempts = $crossword->solutions()->count();
        $completionRate = $totalAttempts > 0
            ? ($crossword->solutions()->where('completed', true)->count() / $totalAttempts) * 100
            : 0;

        return view('dashboard.crosswords.show', [
            'crossword' => $crossword,
            'completions' => $completions,
            'completionRate' => $completionRate,
            'totalAttempts' => $totalAttempts
        ]);
    }

    public function edit(Crossword $crossword)
    {
        if (Gate::denies('update', $crossword)) {
            return redirect()->route('dashboard.crosswords.index')
                ->with('error', 'Sizda krosvordni o\'zgartirishga ruxsat yo\'q!');
        }

        if ($crossword->solutions()->count() > 0) {
            return redirect()->route('dashboard.crosswords.show', $crossword)
                ->with('error', 'Bu krosvord yechilgani uchun uni o\'zgartirish mumkin emas!');
        }

        return view('dashboard.crosswords.edit', [
            'crossword' => $crossword
        ]);
    }

    public function update(DashboardCrosswordUpdateRequest $request, Crossword $crossword)
    {
        if (Gate::denies('update', $crossword)) {
            return redirect()->route('dashboard.crosswords.index')
                ->with('error', 'Sizda krosvorni yangilashga ruxsat yo\'q!');
        }

        if ($crossword->solutions()->count() > 0) {
            return redirect()->route('dashboard.crosswords.show', $crossword)
                ->with('error', 'Bu krosvord yechilgani uchun uni o\'zgartirish mumkin emas!');
        }

        $request->validated();

        $crossword->update([
            'title' => $request->title,
            'published' => $request->has('publish'),
        ]);

        return redirect()->route('dashboard.crosswords.show', $crossword)
            ->with('success', 'Krosvord yangilandi!');
    }

    public function destroy(Crossword $crossword)
    {
        if (Gate::denies('delete', $crossword)) {
            return redirect()->route('dashboard.crosswords.index')
                ->with('error', 'Krosvordni o\'chirishga ruxsat yo\'q!');
        }

        if ($crossword->solutions()->count() > 0 || $crossword->competitions()->count() > 0) {
            return redirect()->route('dashboard.crosswords.show', $crossword)
                ->with('error', 'Bu krosvord foydalanilayotgani sababli o\'chirib bo\'lmaydi!');
        }

        $crossword->delete();

        return redirect()->route('dashboard.crosswords.index')
            ->with('success', 'Krosvord o\'chirildi!');
    }

    public function togglePublish(Crossword $crossword)
    {
        if (Gate::denies('publish', $crossword)) {
            return redirect()->route('dashboard.crosswords.index')
                ->with('error', 'Bu krosvordni ko\'rsatish/yopishga sizda ruxsat yo\'q!');
        }

        $crossword->update([
            'published' => !$crossword->published
        ]);

        $action = $crossword->published ? 'ko\'rsatildi' : 'yopildi';
        return redirect()->route('dashboard.crosswords.show', $crossword)
            ->with('success', "Krosvord {$action}!");
    }
}
