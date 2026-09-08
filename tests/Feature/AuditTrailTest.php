<?php

namespace Tests\Feature;

use App\Models\Entry;
use App\Models\EntryAuditLog;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function editor(): User
    {
        return User::factory()->create(['role' => User::ROLE_EDITOR]);
    }

    private function income(User $user, array $overrides = []): Entry
    {
        return Entry::query()->create(array_merge([
            'type' => Entry::TYPE_INCOME,
            'entry_date' => now()->toDateString(),
            'amount' => 10000,
            'payment_method' => 'Cash',
            'party_name' => 'Customer',
            'details' => 'Original note',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ], $overrides));
    }

    public function test_login_page_has_branding_and_no_credentials(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('Al Badar');
        $response->assertSee('Roznamcha / ERP System');
        $response->assertDontSee('Roznamcha ERP');
        $response->assertDontSee('admin@roznamcha.local');
        $response->assertDontSee('Default admin');
        $response->assertSee('noindex, nofollow', false);
        $response->assertSee('Powered by Innovant Zone');
        $response->assertSee('https://www.innovantzone.com/');
    }

    public function test_editor_cannot_access_audit_log(): void
    {
        $this->actingAs($this->editor())
            ->get(route('audit.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_audit_log(): void
    {
        $this->actingAs($this->admin())
            ->get(route('audit.index'))
            ->assertOk()
            ->assertSee('Audit Log');
    }

    public function test_edit_preserves_previous_data_and_marks_edited(): void
    {
        $admin = $this->admin();
        $entry = $this->income($admin);

        $this->actingAs($admin)
            ->put(route('entries.update', $entry), [
                'type' => Entry::TYPE_INCOME,
                'entry_date' => now()->toDateString(),
                'amount' => 25000,
                'payment_method' => 'Online',
                'party_name' => 'New Customer',
                'details' => 'Updated note',
                'reason' => 'Correction',
            ])
            ->assertRedirect(route('roznamcha.index'));

        $entry->refresh();
        $this->assertTrue($entry->is_edited);
        $this->assertEquals('25000.00', $entry->amount);

        $log = EntryAuditLog::query()->where('action', 'edited')->first();
        $this->assertNotNull($log);
        $this->assertEquals('10000.00', $log->old_data_json['amount']);
        $this->assertEquals('25000.00', $log->new_data_json['amount']);
        $this->assertEquals('Original note', $log->old_data_json['details']);
        $this->assertEquals('Correction', $log->reason);
        $this->assertEquals($admin->id, $log->performed_by);
    }

    public function test_delete_is_soft_and_restore_works(): void
    {
        $admin = $this->admin();
        $entry = $this->income($admin, ['amount' => 45000]);

        $this->actingAs($admin)
            ->delete(route('entries.destroy', $entry), ['reason' => 'Posted in error'])
            ->assertRedirect(route('roznamcha.index'));

        $this->assertSoftDeleted('entries', ['id' => $entry->id]);
        $this->assertDatabaseHas('entry_audit_logs', [
            'entry_id' => $entry->id,
            'action' => 'deleted',
        ]);

        $this->actingAs($admin)
            ->post(route('entries.restore', $entry))
            ->assertRedirect();

        $this->assertDatabaseHas('entries', [
            'id' => $entry->id,
            'deleted_at' => null,
        ]);
        $this->assertDatabaseHas('entry_audit_logs', [
            'entry_id' => $entry->id,
            'action' => 'restored',
        ]);
    }

    public function test_delete_requires_reason_and_editor_cannot_delete(): void
    {
        $admin = $this->admin();
        $editor = $this->editor();
        $entry = $this->income($admin);

        $this->actingAs($editor)
            ->delete(route('entries.destroy', $entry), ['reason' => 'not allowed'])
            ->assertForbidden();

        $this->actingAs($admin)
            ->delete(route('entries.destroy', $entry))
            ->assertSessionHasErrors('reason');
    }

    public function test_expense_create_logs_audit(): void
    {
        $admin = $this->admin();
        $category = ExpenseCategory::query()->create(['name' => 'Fuel', 'active' => true]);

        $this->actingAs($admin)
            ->post(route('entries.store'), [
                'type' => Entry::TYPE_EXPENSE,
                'entry_date' => now()->toDateString(),
                'amount' => 1200,
                'expense_category_id' => $category->id,
                'payment_method' => 'Cash',
                'party_name' => 'Pump',
                'details' => 'Diesel',
            ])
            ->assertRedirect(route('roznamcha.index'));

        $this->assertDatabaseHas('entry_audit_logs', ['action' => 'created', 'entry_type' => 'expense']);
    }
}
