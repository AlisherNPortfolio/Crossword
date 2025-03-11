<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\DashboardCompetitionCreateRequest;
use App\Http\Requests\Dashboard\DashboardCompetitionUpdateRequest;
use App\Models\Competition;
use App\Models\CompetitionResult;
use App\Models\Crossword;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CompetitionController extends Controller implements HasMiddleware
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
        $currentTime = Carbon::now();

        if (Auth::user()->isAdmin()) {
            $activeCompetitions = Competition::where('end_time', '>=', $currentTime)
                ->where('is_active', true)
                ->with('crossword:id,title,created_by')
                ->orderBy('start_time')
                ->get();

            $pastCompetitions = Competition::where('end_time', '<', $currentTime)
                ->orWhere('is_active', false)
                ->with('crossword:id,title,created_by')
                ->latest('end_time')
                ->paginate(15);
        } else {
            $creatorCrosswordIds = Crossword::where('created_by', Auth::id())
                ->pluck('id')
                ->toArray();

            $activeCompetitions = Competition::whereIn('crossword_id', $creatorCrosswordIds)
                ->where('end_time', '>=', $currentTime)
                ->where('is_active', true)
                ->with('crossword:id,title,created_by')
                ->orderBy('start_time')
                ->get();

            $pastCompetitions = Competition::whereIn('crossword_id', $creatorCrosswordIds)
                ->where(function($query) use ($currentTime) {
                    $query->where('end_time', '<', $currentTime)
                          ->orWhere('is_active', false);
                })
                ->with('crossword:id,title,created_by')
                ->latest('end_time')
                ->paginate(15);
        }

        return view('dashboard.competitions.index', [
            'activeCompetitions' => $activeCompetitions,
            'pastCompetitions' => $pastCompetitions
        ]);
    }

    public function create()
    {
        if (Gate::denies('create', Competition::class)) {
            return redirect()->route('dashboard.competitions.index')
                ->with('error', 'Sizda musobaqa yaratishga ruxsat yo\'q!');
        }

        if (Auth::user()->isAdmin()) {
            $crosswords = Crossword::where('published', true)
                ->with('creator:id,name')
                ->orderBy('title')
                ->get(['id', 'title', 'created_by']);
        } else {
            $crosswords = Crossword::where('created_by', Auth::id())
                ->where('published', true)
                ->orderBy('title')
                ->get(['id', 'title', 'created_by']);
        }

        return view('dashboard.competitions.create', [
            'crosswords' => $crosswords
        ]);
    }

    public function store(DashboardCompetitionCreateRequest $request)
    {
        if (Gate::denies('create', Competition::class)) {
            return redirect()->route('dashboard.competitions.index')
                ->with('error', 'Sizda musobaqa yaratishga ruxsat yo\'q!');
        }

        $request->validated();

        $crossword = Crossword::findOrFail($request->crossword_id);
        if (!$crossword->published) {
            return redirect()->route('dashboard.competitions.create')
                ->with('error', 'Siz faqat ko\'rsatilgan krosvordlar uchun musobaqa yaratishingiz mumkin.');
        }

        if (!Auth::user()->isAdmin() && $crossword->created_by !== Auth::id()) {
            return redirect()->route('dashboard.competitions.create')
                ->with('error', 'Siz faqat o\'zingiz yaratgan krosvordlar uchun musobaqa yaratishingiz mumkin.');
        }

        $competition = Competition::create([
            'title' => $request->title,
            'description' => $request->description,
            'crossword_id' => $request->crossword_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_active' => true
        ]);

        return redirect()->route('dashboard.competitions.show', $competition)
            ->with('success', 'Musobaqa yaratildi!');
    }

    public function show(Competition $competition)
    {
        if (Gate::denies('view', $competition)) {
            return redirect()->route('dashboard.competitions.index')
                ->with('error', 'Bu musobaqani ko\'rishga ruxsat yo\'q!');
        }

        $competition->load('crossword');

        $results = CompetitionResult::where('competition_id', $competition->id)
            ->with('user:id,name')
            ->orderByDesc('score')
            ->orderBy('time_taken')
            ->get();

        $totalParticipants = $results->count();
        $completedCount = $results->where('completed', true)->count();
        $completionRate = $totalParticipants > 0 ? ($completedCount / $totalParticipants) * 100 : 0;

        $currentTime = Carbon::now();
        $status = 'upcoming';
        if ($competition->start_time <= $currentTime && $competition->end_time >= $currentTime && $competition->is_active) {
            $status = 'faol';
        } elseif ($competition->end_time < $currentTime || !$competition->is_active) {
            $status = 'tugagan';
        }

        return view('dashboard.competitions.show', [
            'competition' => $competition,
            'results' => $results,
            'totalParticipants' => $totalParticipants,
            'completedCount' => $completedCount,
            'completionRate' => $completionRate,
            'status' => $status
        ]);
    }

    public function edit(Competition $competition)
    {
        if (Gate::denies('update', $competition)) {
            return redirect()->route('dashboard.competitions.index')
                ->with('error', 'Siz bu musobaqani o\'zgartira olmaysiz!');
        }

        $currentTime = Carbon::now();
        if ($competition->start_time <= $currentTime) {
            return redirect()->route('dashboard.competitions.show', $competition)
                ->with('error', 'Bu musobaqa boshlanib bo\'lgan. Uni o\'zgartira olmaysiz!');
        }

        if (Auth::user()->isAdmin()) {
            $crosswords = Crossword::where('published', true)
                ->with('creator:id,name')
                ->orderBy('title')
                ->get(['id', 'title', 'created_by']);
        } else {
            $crosswords = Crossword::where('created_by', auth()->id())
                ->where('published', true)
                ->orderBy('title')
                ->get(['id', 'title', 'created_by']);
        }

        return view('dashboard.competitions.edit', [
            'competition' => $competition,
            'crosswords' => $crosswords
        ]);
    }

    public function update(DashboardCompetitionUpdateRequest $request, Competition $competition)
    {
        if (Gate::denies('update', $competition)) {
            return redirect()->route('dashboard.competitions.index')
                ->with('error', 'Sizda bu musobaqani o\'zgartirishga ruxsat yo\'q!');
        }

        $currentTime = Carbon::now();
        if ($competition->start_time <= $currentTime) {
            return redirect()->route('dashboard.competitions.show', $competition)
                ->with('error', 'Bu musobaqa boshlanib bo\'lgan. Uni o\'zgartira olmaysiz!');
        }

        $request->validated();

        $crossword = Crossword::findOrFail($request->crossword_id);
        if (!$crossword->published) {
            return redirect()->route('dashboard.competitions.edit', $competition)
                ->with('error', 'Faqat e\'lon qilingan krosvordlar uchun musobaqa yaratishingiz mumkin.');
        }

        if (!Auth::user()->isAdmin() && $crossword->created_by !== Auth::id()) {
            return redirect()->route('dashboard.competitions.edit', $competition)
                ->with('error', 'Faqat o\'zingiz yaratgan krosvordlar uchun musobaqa yaratishingiz mumkin.');
        }

        $competition->update([
            'title' => $request->title,
            'description' => $request->description,
            'crossword_id' => $request->crossword_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return redirect()->route('dashboard.competitions.show', $competition)
            ->with('success', 'Musobaqa o\'zgartirildi!');
    }

    public function destroy(Competition $competition)
    {
        if (Gate::denies('delete', $competition)) {
            return redirect()->route('dashboard.competitions.index')
                ->with('error', 'Sizda bu musobaqani o\'chirishga ruxsat yo\'q!');
        }

        $currentTime = Carbon::now();
        if ($competition->start_time <= $currentTime) {
            return redirect()->route('dashboard.competitions.show', $competition)
                ->with('error', 'Bu musobaqa boshlanib bo\'lgan. Uni o\'chirishga ruxsat yo\'q!');
        }

        $competition->delete();

        return redirect()->route('dashboard.competitions.index')
            ->with('success', 'Musobaqa o\'chirildi!');
    }

    public function terminate(Competition $competition)
    {
        if (Gate::denies('terminate', $competition)) {
            return redirect()->route('dashboard.competitions.index')
                ->with('error', 'Sizda musobaqani to\'xtatishga ruxsat yo\'q!');
        }

        $currentTime = Carbon::now();
        if ($competition->start_time > $currentTime || $competition->end_time < $currentTime || !$competition->is_active) {
            return redirect()->route('dashboard.competitions.show', $competition)
                ->with('error', 'Bu musobaqa boshlanib bo\'lgan. Uni to\'xtatishga ruxsat yo\'q!');
        }

        $competition->update([
            'is_active' => false
        ]);

        return redirect()->route('dashboard.competitions.show', $competition)
            ->with('success', 'Musobaqa to\'xtatildi!');
    }
}
