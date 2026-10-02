<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Guest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScannerAndRsvpTest extends TestCase
{
    use RefreshDatabase;

    public function test_scanner_verify_pos1_success_and_reject_double_scan(): void
    {
        $event = Event::create([
            'name' => 'Toyota Gathering Test',
            'event_date' => '2025-03-15',
            'event_time' => '09:00 WIB',
            'venue' => 'Grand Mercure',
            'address' => 'Pekanbaru',
            'status' => 'active',
            'quota_target' => 100,
        ]);

        $guest = Guest::create([
            'token' => 'TKN-TEST-001',
            'event_id' => $event->id,
            'name' => 'Pak Hendra',
            'phone' => '6281200000001',
            'status' => 'confirmed',
        ]);

        // 1. Pos 1: First scan should succeed
        $response1 = $this->postJson('/api/scanner/verify', [
            'token' => 'TKN-TEST-001',
            'pos' => 1,
        ]);

        $response1->assertStatus(200);
        $response1->assertJsonPath('success', true);
        $response1->assertJsonPath('guest.status', 'attended');

        // 2. Pos 1: Second scan immediately rejected (ALREADY_CLAIMED)
        $response2 = $this->postJson('/api/scanner/verify', [
            'token' => 'TKN-TEST-001',
            'pos' => 1,
        ]);

        $response2->assertStatus(422);
        $response2->assertJsonPath('success', false);
        $response2->assertJsonPath('error_code', 'ALREADY_CLAIMED');
    }

    public function test_scanner_pos2_rejects_if_not_checked_in_at_gate(): void
    {
        $event = Event::create([
            'name' => 'Toyota Gathering Test',
            'event_date' => '2025-03-15',
            'event_time' => '09:00 WIB',
            'venue' => 'Grand Mercure',
            'address' => 'Pekanbaru',
            'status' => 'active',
            'quota_target' => 100,
        ]);

        $guest = Guest::create([
            'token' => 'TKN-TEST-002',
            'event_id' => $event->id,
            'name' => 'Ibu Siti',
            'phone' => '6281200000002',
            'status' => 'confirmed', // NOT attended yet
        ]);

        // Pos 2: Should reject because Gate 1 is not attended
        $response = $this->postJson('/api/scanner/verify', [
            'token' => 'TKN-TEST-002',
            'pos' => 2,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('error_code', 'GATE_NOT_CHECKED_IN');
    }

    public function test_public_rsvp_invitation_and_ticket_flow(): void
    {
        $event = Event::create([
            'name' => 'Toyota Expo 2025',
            'event_date' => '2025-03-15',
            'event_time' => '09:00 WIB',
            'venue' => 'Grand Mercure',
            'address' => 'Pekanbaru',
            'status' => 'active',
            'quota_target' => 100,
        ]);

        $guest = Guest::create([
            'token' => 'TKN-RSVP-999',
            'event_id' => $event->id,
            'name' => 'Bambang Soeharto',
            'phone' => '6281200000003',
            'status' => 'invited',
        ]);

        // 1. GET /api/rsvp/{token}
        $resRsvp = $this->getJson('/api/rsvp/TKN-RSVP-999');
        $resRsvp->assertStatus(200);
        $resRsvp->assertJsonPath('guest.name', 'Bambang Soeharto');

        // 2. POST /api/rsvp/{token}/confirm
        $resConfirm = $this->postJson('/api/rsvp/TKN-RSVP-999/confirm', [
            'attendance' => 'hadir',
            'pax' => 2,
        ]);
        $resConfirm->assertStatus(200);
        $resConfirm->assertJsonPath('redirect_url', '/ticket/TKN-RSVP-999');

        // 3. GET /api/ticket/{token}
        $resTicket = $this->getJson('/api/ticket/TKN-RSVP-999');
        $resTicket->assertStatus(200);
        $resTicket->assertJsonPath('ticket.pax', 2);
    }
}
