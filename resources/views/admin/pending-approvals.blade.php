@extends('layouts.app')

@section('title', 'Pending Approvals')

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="page-title">Pending Approvals</h1>
                <p class="page-subtitle">Review and approve user registration requests</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Pending Requests -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-clock"></i> Pending Approval Requests
        </div>
        <div class="card-body">
            @if($requests->count() > 0)
                <div class="space-y-4">
                    @foreach($requests as $request)
                        <div class="card border-l-4 border-l-warning">
                            <div class="card-body">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-full bg-warning text-white flex items-center justify-center">
                                            {{ strtoupper(substr($request->user->full_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-lg">{{ $request->user->full_name }}</h3>
                                            <p class="text-gray-600">@{{ $request->user->username }}</p>
                                            <p class="text-sm text-gray-500">
                                                Requested on {{ $request->created_at->format('M d, Y \a\t g:i A') }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="btn-group">
                                        <form method="POST" action="{{ route('admin.approve', $request->user) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        
                                        <form method="POST" action="{{ route('admin.reject', $request->user) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                
                                @if($request->message)
                                    <div class="mt-4 p-3 bg-gray-50 rounded">
                                        <h4 class="font-semibold text-sm text-gray-700 mb-1">User Message:</h4>
                                        <p class="text-gray-600">{{ $request->message }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $requests->links() }}
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-check-circle text-gray-400 text-4xl mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-600 mb-2">No Pending Approvals</h3>
                    <p class="text-gray-500">All user registration requests have been processed.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection