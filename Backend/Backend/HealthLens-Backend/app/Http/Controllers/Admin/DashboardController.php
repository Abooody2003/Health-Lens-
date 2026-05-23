<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Specialization;
use App\Models\SurgeryAnalysis;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_doctors' => Doctor::count(),
            'total_specializations' => Specialization::count(),
            'total_surgery_analyses' => SurgeryAnalysis::count(),
            'pending_analyses' => SurgeryAnalysis::where('status', 'pending')->count(),
            'completed_analyses' => SurgeryAnalysis::where('status', 'completed')->count(),
            'processing_analyses' => SurgeryAnalysis::where('status', 'processing')->count(),
            'failed_analyses' => SurgeryAnalysis::where('status', 'failed')->count(),
        ];

        $recentUsers = User::where('role', 'user')->latest()->take(5)->get();
        $recentAnalyses = SurgeryAnalysis::with('user')->latest()->take(5)->get();

        // ----- Chart data -----

        $usersPerMonth = User::where('role', 'user')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, count(*) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $usersPerMonthLabels = [];
        $usersPerMonthData = [];
        for ($i = 11; $i >= 0; $i--) {
            $d = Carbon::now()->subMonths($i);
            $key = $d->format('Y-m');
            $usersPerMonthLabels[] = $d->format('M Y');
            $usersPerMonthData[] = $usersPerMonth[$key] ?? 0;
        }

        $analysesPerMonth = SurgeryAnalysis::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, count(*) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $analysesPerMonthLabels = [];
        $analysesPerMonthData = [];
        for ($i = 11; $i >= 0; $i--) {
            $d = Carbon::now()->subMonths($i);
            $key = $d->format('Y-m');
            $analysesPerMonthLabels[] = $d->format('M Y');
            $analysesPerMonthData[] = $analysesPerMonth[$key] ?? 0;
        }

        $analysesByStatus = [
            'labels' => ['Completed', 'Pending', 'Processing', 'Failed'],
            'data' => [
                $stats['completed_analyses'],
                $stats['pending_analyses'],
                $stats['processing_analyses'],
                $stats['failed_analyses'],
            ],
            'colors' => ['#10b981', '#f59e0b', '#3b82f6', '#ef4444'],
        ];

        $usersByPlan = User::where('role', 'user')
            ->select('plan')
            ->selectRaw('count(*) as total')
            ->groupBy('plan')
            ->orderByRaw("FIELD(plan, 'free', 'premium', 'pro')")
            ->get();

        $usersByPlanLabels = $usersByPlan->pluck('plan')->map(fn ($p) => ucfirst($p))->values()->toArray();
        $usersByPlanData = $usersByPlan->pluck('total')->values()->toArray();

        $doctorsPerSpecialization = Doctor::join('specializations', 'doctors.specialization_id', '=', 'specializations.id')
            ->select('specializations.name')
            ->selectRaw('count(*) as total')
            ->groupBy('specializations.name')
            ->orderByDesc('total')
            ->get();

        $doctorsPerSpecLabels = $doctorsPerSpecialization->pluck('name')->values()->toArray();
        $doctorsPerSpecData = $doctorsPerSpecialization->pluck('total')->values()->toArray();

        $analysesBySurgery = SurgeryAnalysis::whereNotNull('recommended_surgery')
            ->select('recommended_surgery')
            ->selectRaw('count(*) as total')
            ->groupBy('recommended_surgery')
            ->orderByDesc('total')
            ->get();

        $surgeryLabels = $analysesBySurgery->pluck('recommended_surgery')->values()->toArray();
        $surgeryData = $analysesBySurgery->pluck('total')->values()->toArray();

        $analysesByGender = SurgeryAnalysis::select('gender')
            ->selectRaw('count(*) as total')
            ->groupBy('gender')
            ->get();

        $genderLabels = $analysesByGender->pluck('gender')->map(fn ($g) => ucfirst($g))->values()->toArray();
        $genderData = $analysesByGender->pluck('total')->values()->toArray();

        $ageBuckets = ['18-25', '26-35', '36-45', '46-55', '56-65'];
        $ageData = [];
        foreach ([[18, 25], [26, 35], [36, 45], [46, 55], [56, 65]] as $r) {
            $ageData[] = SurgeryAnalysis::whereBetween('age', $r)->count();
        }

        $dailyTrend = [];
        $dailyLabels = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = Carbon::now()->subDays($i);
            $dailyLabels[] = $d->format('M d');
            $dailyTrend[] = SurgeryAnalysis::whereDate('created_at', $d)->count();
        }

        $completedPerMonth = SurgeryAnalysis::where('status', 'completed')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, count(*) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $completedPerMonthData = [];
        for ($i = 11; $i >= 0; $i--) {
            $d = Carbon::now()->subMonths($i);
            $key = $d->format('Y-m');
            $completedPerMonthData[] = $completedPerMonth[$key] ?? 0;
        }

        $cctBuckets = ['<500', '500-530', '531-560', '561-590', '>590'];
        $cctData = [
            SurgeryAnalysis::where('cct', '<', 500)->count(),
            SurgeryAnalysis::whereBetween('cct', [500, 530])->count(),
            SurgeryAnalysis::whereBetween('cct', [531, 560])->count(),
            SurgeryAnalysis::whereBetween('cct', [561, 590])->count(),
            SurgeryAnalysis::where('cct', '>', 590)->count(),
        ];

        $kmaxBuckets = ['<44', '44-46', '46-48', '48-50', '50-52', '>52'];
        $kmaxData = [
            SurgeryAnalysis::where('kmax', '<', 44)->count(),
            SurgeryAnalysis::whereBetween('kmax', [44, 46])->count(),
            SurgeryAnalysis::whereBetween('kmax', [46, 48])->count(),
            SurgeryAnalysis::whereBetween('kmax', [48, 50])->count(),
            SurgeryAnalysis::whereBetween('kmax', [50, 52])->count(),
            SurgeryAnalysis::where('kmax', '>', 52)->count(),
        ];

        $weeklyComparison = [];
        $weeklyLabels = [];
        for ($i = 11; $i >= 0; $i--) {
            $start = Carbon::now()->subWeeks($i)->startOfWeek();
            $end = $start->copy()->endOfWeek();
            $weeklyLabels[] = $start->format('M d');
            $weeklyComparison[] = SurgeryAnalysis::whereBetween('created_at', [$start, $end])->count();
        }

        $processingRate = $stats['total_surgery_analyses'] > 0
            ? round(($stats['completed_analyses'] / $stats['total_surgery_analyses']) * 100, 1)
            : 0;

        return view('admin.dashboard', compact(
            'stats',
            'recentUsers',
            'recentAnalyses',
            'usersPerMonthLabels',
            'usersPerMonthData',
            'analysesPerMonthLabels',
            'analysesPerMonthData',
            'analysesByStatus',
            'usersByPlanLabels',
            'usersByPlanData',
            'doctorsPerSpecLabels',
            'doctorsPerSpecData',
            'surgeryLabels',
            'surgeryData',
            'genderLabels',
            'genderData',
            'ageBuckets',
            'ageData',
            'dailyLabels',
            'dailyTrend',
            'completedPerMonthData',
            'cctBuckets',
            'cctData',
            'kmaxBuckets',
            'kmaxData',
            'weeklyLabels',
            'weeklyComparison',
            'processingRate'
        ));
    }
}

