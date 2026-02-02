@extends('layouts.app')


@push('styles')
    <link href="{{ url('css/reportlist.css') }}" rel="stylesheet">
@endpush

@section('content')


<script>
    function confirmDelete(button) {
        if (confirm("Are you sure you want to delete this auction?")) {
            button.closest('form').submit();
        }
    }
</script>

<!-- Tabs for Report Types -->
<div class="nav nav-tabs" id="reportTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request('type') == 'auction' || !request('type') ? 'active' : '' }}" id="auction-tab" href="{{ route('reports.index', ['type' => 'auction']) }}" role="tab" aria-controls="auction" aria-selected="{{ request('type') == 'auction' ? 'true' : 'false' }}">Auction Reports</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request('type') == 'user' ? 'active' : '' }}" id="user-tab" href="{{ route('reports.index', ['type' => 'user']) }}" role="tab" aria-controls="user" aria-selected="{{ request('type') == 'user' ? 'true' : 'false' }}">User Reports</a>
    </li>
</div>


<!-- Tab Content -->
<div class="reports-container">


    <nav class="report-filters">
        <ul>
            <li>
                <a href="{{ route('reports.index', ['type' => request('type'), 'solved' => 'true', 'order' => request('order')]) }}" 
                class="{{ request('solved') === 'true' ? 'active' : '' }}">
                    Solved Reports
                </a>
            </li>
            <li>
                <a href="{{ route('reports.index', ['type' => request('type'), 'solved' => 'false', 'order' => request('order')]) }}" 
                class="{{ request('solved') === 'false' ? 'active' : '' }}">
                    Not Solved Reports
                </a>
            </li>
            <li>
                <a href="{{ route('reports.index', ['type' => request('type'), 'order' => request('order')]) }}" 
                class="{{ is_null(request('solved')) ? 'active' : '' }}">
                    All Reports
                </a>
            </li>
        </ul>
    </nav>

    
    <!-- Auction Reports Tab -->
    <div class="tab-content">
        <div class="tab-pane fade {{ request('type') == 'auction' || !request('type') ? 'show active' : '' }}" id="auction" role="tabpanel" aria-labelledby="auction-tab">
        <!-- Order Toggle -->
        <div class="order-toggle">
            <a href="{{ route('reports.index', ['solved' => request('solved'), 'order' => request('order') === 'asc' ? 'desc' : 'asc']) }}">
                Order by Date: {{ request('order') === 'asc' ? 'Descending' : 'Ascending' }}
            </a>
        </div>

        <form method="GET" action="{{ route('reports.index') }}">
            <div class="search-report">
                <input type="text" name="x" placeholder="Search for report..." value="{{ request('x') }}">
            </div>
        </form>
        <table class="simple-table">
            <thead>
                <tr>
                    <th>Auction ID</th>
                    <th>Auction Name</th>
                    <th>Reason</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Is Solved?</th>
                    <th>Reported By</th>
                    <th>Reviewed By</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($auctionReports as $report)
                    <tr>
                        <td>{{ $report->auction->itemname ?? 'N/A' }}</td>
                        <td>{{ $report->reported_id ?? 'N/A' }}</td>
                        <td>{{ $report->reason }}</td>
                        <td>{{ $report->type }}</td>
                        <td>{{ $report->date }}</td>
                        <td>{{ $report->issolved ? 'Yes' : 'No' }}</td>
                        <td>User ID {{ $report->reporter }}</td>
                        <td>{{ $report->reviewer ?? 'N/A' }}</td>
                        <td>{{ $report->description }}</td>
                        <td>
                            <form action="{{ route('reports.delete', $report->id) }}" method="POST" class="inline-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn delete-btn" onclick="confirmDelete(this)">Delete</button>
                            </form>
                            <form action="{{ route('reports.toggleResolved', $report->id) }}" method="POST" class="inline-form">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn resolve-btn">
                                    {{ $report->issolved ? 'Set as not solved' : 'Set as solved' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="no-reports">No reports found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination-links">
            {{ $auctionReports->links() }}
        </div>
        </div>
    </div>

    <!-- User Reports Tab -->
    <div class="tab-pane fade {{ request('type') == 'user' ? 'show active' : '' }}" id="user" role="tabpanel" aria-labelledby="user-tab">
    <!-- Order Toggle -->
    <div class="order-toggle">
            <a href="{{ route('reports.index', ['solved' => request('solved'), 'order' => request('order') === 'asc' ? 'desc' : 'asc']) }}">
                Order by Date: {{ request('order') === 'asc' ? 'Descending' : 'Ascending' }}
            </a>
        </div>
    
    <form method="GET" action="{{ route('reports.index') }}">
        <div class="search-report">
            <input type="text" name="x" placeholder="Search for report..." value="{{ request('x') }}">
        </div>
    </form>
    <table class="simple-table">
            <thead>
                <tr>
                    <th>Auction</th>
                    <th>Reason</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Is Solved?</th>
                    <th>Reported By</th>
                    <th>Reviewed By</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($userReports as $report)
                    <tr>
                        <td>{{ $report->reported_id ?? 'N/A' }}</td>
                        <td>{{ $report->reason }}</td>
                        <td>{{ $report->type }}</td>
                        <td>{{ $report->date }}</td>
                        <td>{{ $report->issolved ? 'Yes' : 'No' }}</td>
                        <td>User ID {{ $report->reporter }}</td>
                        <td>{{ $report->reviewer ?? 'N/A' }}</td>
                        <td>{{ $report->description }}</td>
                        <td>
                            <form action="{{ route('reports.delete', $report->id) }}" method="POST" class="inline-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn delete-btn" onclick="confirmDelete(this)">Delete</button>
                            </form>
                            <form action="{{ route('reports.toggleResolved', $report->id) }}" method="POST" class="inline-form">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn resolve-btn">
                                    {{ $report->issolved ? 'Set as not solved' : 'Set as solved' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="no-reports">No reports found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection


