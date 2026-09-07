@extends('layouts.app')

@section('title', 'Users')
@section('page_title', 'Users')
@section('page_subtitle', 'Create and manage Admin / Editor accounts')

@section('content')
<div class="panel">
    <div class="panel-header">
        <strong>All Users</strong>
        <a href="{{ route('users.create') }}" class="btn btn-sm btn-accent">+ Create User</a>
    </div>
    <div class="panel-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Username / Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge text-bg-{{ $user->isAdmin() ? 'primary' : 'secondary' }}">{{ ucfirst($user->role) }}</span></td>
                        <td>
                            @if($user->active)
                                <span class="badge text-bg-success">Active</span>
                            @else
                                <span class="badge text-bg-danger">Disabled</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('d M Y') }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            @if($user->id !== auth()->id())
                                <form action="{{ route('users.toggle', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-{{ $user->active ? 'warning' : 'success' }}">
                                        {{ $user->active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
        <div class="panel-body border-top">{{ $users->links() }}</div>
    @endif
</div>
@endsection
