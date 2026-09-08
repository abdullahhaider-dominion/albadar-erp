@if(!empty($entry) && $entry->is_edited)
    @if(auth()->user()->isAdmin())
        <button type="button"
                class="badge badge-edited border-0"
                data-entry-history="{{ route('entries.history', $entry) }}"
                title="View previous vs current data">
            Edited
        </button>
    @else
        <span class="badge badge-edited" title="This entry was edited">Edited</span>
    @endif
@endif
