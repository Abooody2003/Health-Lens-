@extends('admin.layout')

@section('title', 'Surgery Analysis Details')
@section('page-title', 'Surgery Analysis Details')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Surgery Analysis Details</h1>
    <a href="{{ route('admin.surgery-analyses.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Analyses
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Analysis Information</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th width="200">ID</th>
                        <td>{{ $surgeryAnalysis->id }}</td>
                    </tr>
                    <tr>
                        <th>User</th>
                        <td>
                            <a href="{{ route('admin.users.show', $surgeryAnalysis->user) }}">
                                {{ $surgeryAnalysis->user->full_name ?? 'N/A' }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($surgeryAnalysis->status === 'completed')
                                <span class="badge bg-success">Completed</span>
                            @elseif($surgeryAnalysis->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($surgeryAnalysis->status === 'processing')
                                <span class="badge bg-info">Processing</span>
                            @else
                                <span class="badge bg-danger">Failed</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Age</th>
                        <td>{{ $surgeryAnalysis->age }}</td>
                    </tr>
                    <tr>
                        <th>Gender</th>
                        <td>{{ ucfirst($surgeryAnalysis->gender) }}</td>
                    </tr>
                    <tr>
                        <th>Kmax</th>
                        <td>{{ $surgeryAnalysis->kmax }}</td>
                    </tr>
                    <tr>
                        <th>CCT</th>
                        <td>{{ $surgeryAnalysis->cct }}</td>
                    </tr>
                    <tr>
                        <th>Astigmatism Value</th>
                        <td>{{ $surgeryAnalysis->astig_value }}</td>
                    </tr>
                    @if($surgeryAnalysis->kc_probability)
                        <tr>
                            <th>KC Probability</th>
                            <td>{{ number_format($surgeryAnalysis->kc_probability * 100, 2) }}%</td>
                        </tr>
                    @endif
                    @if($surgeryAnalysis->recommended_surgery)
                        <tr>
                            <th>Recommended Surgery</th>
                            <td>{{ $surgeryAnalysis->recommended_surgery }}</td>
                        </tr>
                    @endif
                    @if($surgeryAnalysis->rsb_um)
                        <tr>
                            <th>RSB (μm)</th>
                            <td>{{ $surgeryAnalysis->rsb_um }}</td>
                        </tr>
                    @endif
                    @if($surgeryAnalysis->ablation_depth_um)
                        <tr>
                            <th>Ablation Depth (μm)</th>
                            <td>{{ $surgeryAnalysis->ablation_depth_um }}</td>
                        </tr>
                    @endif
                    @if($surgeryAnalysis->safety_warnings)
                        <tr>
                            <th>Safety Warnings</th>
                            <td>
                                @if(is_array($surgeryAnalysis->safety_warnings))
                                    <ul class="mb-0">
                                        @foreach($surgeryAnalysis->safety_warnings as $warning)
                                            <li>{{ $warning }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    {{ $surgeryAnalysis->safety_warnings }}
                                @endif
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <th>Created</th>
                        <td>{{ $surgeryAnalysis->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Last Updated</th>
                        <td>{{ $surgeryAnalysis->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

