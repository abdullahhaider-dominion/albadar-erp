@extends('layouts.app')

@php $title = $user ? 'Edit User' : 'Create User'; @endphp
@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', 'Admin & Editor accounts')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="panel">
            <div class="panel-header">
                <strong>{{ $title }}</strong>
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
            </div>
            <div class="panel-body">
                <form method="POST" action="{{ $user ? route('users.update', $user) : route('users.store') }}">
                    @csrf
                    @if($user) @method('PUT') @endif

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required value="{{ old('name', optional($user)->name) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username / Email</label>
                        <input type="text" name="email" class="form-control" required value="{{ old('email', optional($user)->email) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="admin" @selected(old('role', optional($user)->role) === 'admin')>Admin</option>
                            <option value="editor" @selected(old('role', optional($user)->role ?? 'editor') === 'editor')>Editor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password {{ $user ? '(leave blank to keep)' : '' }}</label>
                        <input type="password" name="password" class="form-control" {{ $user ? '' : 'required' }} minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" {{ $user ? '' : 'required' }} minlength="6">
                    </div>
                    @if($user)
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="active" value="1" id="active" @checked(old('active', $user->active))>
                            <label class="form-check-label" for="active">Active</label>
                        </div>
                    @endif
                    <button class="btn btn-accent">{{ $user ? 'Update User' : 'Create User' }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
