@extends('layouts.app')

@section('title', 'Expense Categories')
@section('page_title', 'Expense Categories')
@section('page_subtitle', 'Manage Kharcha categories')

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="panel">
            <div class="panel-header"><strong>Add Category</strong></div>
            <div class="panel-body">
                <form method="POST" action="{{ route('categories.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Generator Fuel">
                    </div>
                    <button class="btn btn-accent">Add Category</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="panel">
            <div class="panel-header"><strong>All Categories</strong></div>
            <div class="panel-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($categories as $category)
                            <tr>
                                <td>
                                    <form method="POST" action="{{ route('categories.update', $category) }}" class="d-flex gap-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="active" value="{{ $category->active ? 1 : 0 }}">
                                        <input type="text" name="name" value="{{ $category->name }}" class="form-control form-control-sm" required>
                                        <button class="btn btn-sm btn-outline-primary">Save</button>
                                    </form>
                                </td>
                                <td>
                                    @if($category->active)
                                        <span class="badge text-bg-success">Active</span>
                                    @else
                                        <span class="badge text-bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('categories.toggle', $category) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-secondary">
                                            {{ $category->active ? 'Disable' : 'Enable' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if($categories->hasPages())
                <div class="panel-body border-top">{{ $categories->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
