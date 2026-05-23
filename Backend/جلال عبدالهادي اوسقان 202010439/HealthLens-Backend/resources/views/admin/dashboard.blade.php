@extends('admin.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    .card-body canvas { max-width: 100%; display: block; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>Dashboard Overview</h1>
</div>

<!-- Stats Cards -->
<div class="row">
    <div class="col-md-3">
        <div class="stats-card blue">
            <div class="stats-label">Total Users</div>
            <div class="stats-value">{{ $stats['total_users'] }}</div>
            <div class="d-flex align-items-center mt-2">
                <i class="bi bi-people me-2"></i>
                <small>Registered users</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card green">
            <div class="stats-label">Total Doctors</div>
            <div class="stats-value">{{ $stats['total_doctors'] }}</div>
            <div class="d-flex align-items-center mt-2">
                <i class="bi bi-person-badge me-2"></i>
                <small>Active doctors</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card orange">
            <div class="stats-label">Specializations</div>
            <div class="stats-value">{{ $stats['total_specializations'] }}</div>
            <div class="d-flex align-items-center mt-2">
                <i class="bi bi-tags me-2"></i>
                <small>Available specializations</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card purple">
            <div class="stats-label">Surgery Analyses</div>
            <div class="stats-value">{{ $stats['total_surgery_analyses'] }}</div>
            <div class="d-flex align-items-center mt-2">
                <i class="bi bi-clipboard-data me-2"></i>
                <small>{{ $processingRate }}% completion rate</small>
            </div>
        </div>
    </div>
</div>

<!-- Analysis Status -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Analysis Status</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Pending</span>
                    <span class="badge bg-warning">{{ $stats['pending_analyses'] }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Processing</span>
                    <span class="badge bg-info">{{ $stats['processing_analyses'] }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Completed</span>
                    <span class="badge bg-success">{{ $stats['completed_analyses'] }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Failed</span>
                    <span class="badge bg-danger">{{ $stats['failed_analyses'] }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<h2 class="h5 text-muted mb-3 mt-4"><i class="bi bi-bar-chart-line me-2"></i>Analytics &amp; Statistics</h2>

<!-- Charts Row 1: Users & Analyses Over Time -->
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Users Registered (Last 12 Months)</h5>
            </div>
            <div class="card-body">
                <canvas id="chartUsersPerMonth" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Surgery Analyses (Last 12 Months)</h5>
            </div>
            <div class="card-body">
                <canvas id="chartAnalysesPerMonth" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2: Status & Plans -->
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Analyses by Status</h5>
            </div>
            <div class="card-body d-flex justify-content-center">
                <canvas id="chartAnalysesByStatus" height="240"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart-fill me-2"></i>Users by Plan</h5>
            </div>
            <div class="card-body d-flex justify-content-center">
                <canvas id="chartUsersByPlan" height="240"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-gender-ambiguous me-2"></i>Analyses by Gender</h5>
            </div>
            <div class="card-body d-flex justify-content-center">
                <canvas id="chartAnalysesByGender" height="240"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 3: Doctors & Surgery Types -->
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Doctors per Specialization</h5>
            </div>
            <div class="card-body">
                <canvas id="chartDoctorsPerSpec" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clipboard-pulse me-2"></i>Recommended Surgery Types</h5>
            </div>
            <div class="card-body">
                <canvas id="chartSurgeryTypes" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 4: Age & Daily Trend -->
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-range me-2"></i>Analyses by Age Group</h5>
            </div>
            <div class="card-body">
                <canvas id="chartAgeDistribution" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Daily Analyses (Last 30 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="chartDailyTrend" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 5: Completed Over Time & Weekly -->
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-check2-circle me-2"></i>Completed Analyses per Month</h5>
            </div>
            <div class="card-body">
                <canvas id="chartCompletedPerMonth" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-week me-2"></i>Analyses per Week (Last 12 Weeks)</h5>
            </div>
            <div class="card-body">
                <canvas id="chartWeeklyComparison" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 6: CCT & Kmax -->
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-droplet me-2"></i>CCT Distribution (μm)</h5>
            </div>
            <div class="card-body">
                <canvas id="chartCctDistribution" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-eye me-2"></i>Kmax Distribution (D)</h5>
            </div>
            <div class="card-body">
                <canvas id="chartKmaxDistribution" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Stacked: Analyses vs Completed -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-stack me-2"></i>Total vs Completed Analyses (Monthly)</h5>
            </div>
            <div class="card-body">
                <canvas id="chartStackedAnalyses" height="180"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Users & Analyses -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Users</h5>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentUsers as $user)
                                <tr>
                                    <td>{{ $user->full_name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <div class="text-center text-muted py-3">
                                            <i class="bi bi-people d-block mb-2" style="font-size: 24px; opacity: 0.5;"></i>
                                            <small>No users yet</small>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Analyses</h5>
                <a href="{{ route('admin.surgery-analyses.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAnalyses as $analysis)
                                <tr>
                                    <td>{{ $analysis->user->full_name ?? 'N/A' }}</td>
                                    <td>
                                        @if($analysis->status === 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($analysis->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($analysis->status === 'processing')
                                            <span class="badge bg-info">Processing</span>
                                        @else
                                            <span class="badge bg-danger">Failed</span>
                                        @endif
                                    </td>
                                    <td>{{ $analysis->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <div class="text-center text-muted py-3">
                                            <i class="bi bi-clipboard-data d-block mb-2" style="font-size: 24px; opacity: 0.5;"></i>
                                            <small>No analyses yet</small>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const grid = { color: 'rgba(0,0,0,0.06)' };
    const font = { family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif", size: 12 };

    const usersPerMonth = {
        type: 'line',
        data: {
            labels: @json($usersPerMonthLabels),
            datasets: [{
                label: 'Users',
                data: @json($usersPerMonthData),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: grid, ticks: { font } },
                y: { grid: grid, ticks: { font }, beginAtZero: true }
            }
        }
    };

    const analysesPerMonth = {
        type: 'bar',
        data: {
            labels: @json($analysesPerMonthLabels),
            datasets: [{
                label: 'Analyses',
                data: @json($analysesPerMonthData),
                backgroundColor: 'rgba(139, 92, 246, 0.7)',
                borderColor: '#7c3aed',
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: grid, ticks: { font } },
                y: { grid: grid, ticks: { font }, beginAtZero: true }
            }
        }
    };

    const analysesByStatus = {
        type: 'doughnut',
        data: {
            labels: @json($analysesByStatus['labels']),
            datasets: [{
                data: @json($analysesByStatus['data']),
                backgroundColor: @json($analysesByStatus['colors']),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { font } } }
        }
    };

    const usersByPlan = {
        type: 'pie',
        data: {
            labels: @json($usersByPlanLabels),
            datasets: [{
                data: @json($usersByPlanData),
                backgroundColor: ['#94a3b8', '#f59e0b', '#8b5cf6'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { font } } }
        }
    };

    const analysesByGender = {
        type: 'doughnut',
        data: {
            labels: @json($genderLabels),
            datasets: [{
                data: @json($genderData),
                backgroundColor: ['#3b82f6', '#ec4899'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { font } } }
        }
    };

    const doctorsPerSpec = {
        type: 'bar',
        data: {
            labels: @json($doctorsPerSpecLabels),
            datasets: [{
                label: 'Doctors',
                data: @json($doctorsPerSpecData),
                backgroundColor: 'rgba(16, 185, 129, 0.7)',
                borderColor: '#059669',
                borderWidth: 1,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: grid, ticks: { font }, beginAtZero: true },
                y: { grid: grid, ticks: { font } }
            }
        }
    };

    const surgeryTypes = {
        type: 'bar',
        data: {
            labels: @json($surgeryLabels),
            datasets: [{
                label: 'Count',
                data: @json($surgeryData),
                backgroundColor: 'rgba(245, 158, 11, 0.7)',
                borderColor: '#d97706',
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: grid, ticks: { font } },
                y: { grid: grid, ticks: { font }, beginAtZero: true }
            }
        }
    };

    const ageDistribution = {
        type: 'bar',
        data: {
            labels: @json($ageBuckets),
            datasets: [{
                label: 'Analyses',
                data: @json($ageData),
                backgroundColor: 'rgba(99, 102, 241, 0.7)',
                borderColor: '#4f46e5',
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: grid, ticks: { font } },
                y: { grid: grid, ticks: { font }, beginAtZero: true }
            }
        }
    };

    const dailyTrend = {
        type: 'line',
        data: {
            labels: @json($dailyLabels),
            datasets: [{
                label: 'Analyses',
                data: @json($dailyTrend),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: grid, ticks: { font, maxTicksLimit: 15 } },
                y: { grid: grid, ticks: { font }, beginAtZero: true }
            }
        }
    };

    const completedPerMonth = {
        type: 'line',
        data: {
            labels: @json($analysesPerMonthLabels),
            datasets: [{
                label: 'Completed',
                data: @json($completedPerMonthData),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: grid, ticks: { font } },
                y: { grid: grid, ticks: { font }, beginAtZero: true }
            }
        }
    };

    const weeklyComparison = {
        type: 'bar',
        data: {
            labels: @json($weeklyLabels),
            datasets: [{
                label: 'Analyses',
                data: @json($weeklyComparison),
                backgroundColor: 'rgba(6, 182, 212, 0.7)',
                borderColor: '#0891b2',
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: grid, ticks: { font, maxTicksLimit: 12 } },
                y: { grid: grid, ticks: { font }, beginAtZero: true }
            }
        }
    };

    const cctDistribution = {
        type: 'bar',
        data: {
            labels: @json($cctBuckets),
            datasets: [{
                label: 'Count',
                data: @json($cctData),
                backgroundColor: 'rgba(14, 165, 233, 0.7)',
                borderColor: '#0284c7',
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: grid, ticks: { font } },
                y: { grid: grid, ticks: { font }, beginAtZero: true }
            }
        }
    };

    const kmaxDistribution = {
        type: 'bar',
        data: {
            labels: @json($kmaxBuckets),
            datasets: [{
                label: 'Count',
                data: @json($kmaxData),
                backgroundColor: 'rgba(168, 85, 247, 0.7)',
                borderColor: '#7c3aed',
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: grid, ticks: { font } },
                y: { grid: grid, ticks: { font }, beginAtZero: true }
            }
        }
    };

    const stackedAnalyses = {
        type: 'bar',
        data: {
            labels: @json($analysesPerMonthLabels),
            datasets: [
                {
                    label: 'Total Analyses',
                    data: @json($analysesPerMonthData),
                    backgroundColor: 'rgba(139, 92, 246, 0.6)',
                    borderColor: '#7c3aed',
                    borderWidth: 1,
                },
                {
                    label: 'Completed',
                    data: @json($completedPerMonthData),
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: '#059669',
                    borderWidth: 1,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { font } } },
            scales: {
                x: { grid: grid, ticks: { font } },
                y: { grid: grid, ticks: { font }, beginAtZero: true }
            }
        }
    };

    new Chart(document.getElementById('chartUsersPerMonth'), usersPerMonth);
    new Chart(document.getElementById('chartAnalysesPerMonth'), analysesPerMonth);
    new Chart(document.getElementById('chartAnalysesByStatus'), analysesByStatus);
    new Chart(document.getElementById('chartUsersByPlan'), usersByPlan);
    new Chart(document.getElementById('chartAnalysesByGender'), analysesByGender);
    new Chart(document.getElementById('chartDoctorsPerSpec'), doctorsPerSpec);
    new Chart(document.getElementById('chartSurgeryTypes'), surgeryTypes);
    new Chart(document.getElementById('chartAgeDistribution'), ageDistribution);
    new Chart(document.getElementById('chartDailyTrend'), dailyTrend);
    new Chart(document.getElementById('chartCompletedPerMonth'), completedPerMonth);
    new Chart(document.getElementById('chartWeeklyComparison'), weeklyComparison);
    new Chart(document.getElementById('chartCctDistribution'), cctDistribution);
    new Chart(document.getElementById('chartKmaxDistribution'), kmaxDistribution);
    new Chart(document.getElementById('chartStackedAnalyses'), stackedAnalyses);
})();
</script>
@endpush
