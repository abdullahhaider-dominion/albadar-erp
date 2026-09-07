@extends('layouts.app')

@section('title', 'Settings')
@section('page_title', 'Settings')
@section('page_subtitle', 'Basic company preferences')

@section('content')
<div class="row g-3">
    <div class="col-lg-6">
        <div class="panel">
            <div class="panel-header"><strong>General Settings</strong></div>
            <div class="panel-body">
                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control" required value="{{ old('company_name', $companyName) }}">
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="allow_editor_edit" value="1" id="allow_editor_edit" @checked(old('allow_editor_edit', $allowEditorEdit))>
                        <label class="form-check-label" for="allow_editor_edit">
                            Allow Editors to edit existing entries
                        </label>
                        <div class="form-text">By default editors can only add new income/expense entries.</div>
                    </div>
                    <button class="btn btn-accent">Save Settings</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="panel">
            <div class="panel-header"><strong>Database Backup</strong></div>
            <div class="panel-body">
                <p class="text-muted">Download a full backup of the Roznamcha database.</p>
                <a href="{{ route('settings.backup') }}" class="btn btn-outline-primary">
                    <i class="bi bi-download"></i> Download Backup
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
