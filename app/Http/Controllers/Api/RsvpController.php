<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RsvpController extends Controller
{
    /**
     * Get Public Invitation details for Landing RSVP Screen
     * Endpoint: GET /api/rsvp/{token}
     */
    public function getInvitation(string $token): JsonResponse
    {
        $guest = Guest::with(['event', 'sales'])->where('token', $token)->first();

        if (! $guest) {
            return response()->json([
                'success' => false,
                'message' => 'Tautan undangan tidak valid atau kedaluwarsa.',
            ], 404);
        }

        $event = $guest->event;
        $sales = $guest->sales;

        return response()->json([
            'success' => true,
            'guest' => [
                'name' => $guest->name,
                'token' => $guest->token,
                'vip' => $guest->vip,
                'vip_tier' => $guest->vip_tier,
                'status' => $guest->status,
                'pax' => $guest->pax,
            ],
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'subtitle' => $event->subtitle ?? 'ANNUAL GATHERING',
                'date' => $event->event_date->translatedFormat('l, d F Y'),
                'time' => $event->event_time,
                'venue' => $event->venue,
                'address' => $event->address,
                'image_url' => $event->image_url ?? '/toyota-event.jpg',
                'deadline' => 'Kamis, 13 Maret 2025 pukul 18:00 WIB',
            ],
            'sales' => [
                'name' => $sales?->name ?? 'Tim Sales Agung Toyota',
                'branch' => $sales?->branch ?? 'Cabang Sutomo',
                'phone' => $sales?->phone ?? '0812-3456-7890',
            ],
        ]);
    }

    /**
     * Submit RSVP Confirmation (Hadir / Tidak Hadir + Pax)
     * Endpoint: POST /api/rsvp/{token}/confirm
     */
    public function confirm(string $token, Request $request): JsonResponse
    {
        $request->validate([
            'attendance' => 'required|in:hadir,tidak',
            'pax' => 'required|integer|min:1|max:5',
        ]);

        $guest = Guest::where('token', $token)->firstOrFail();

        $newStatus = $request->attendance === 'hadir' ? 'confirmed' : 'declined';
        $guest->update([
            'status' => $newStatus,
            'pax' => (int) $request->pax,
        ]);

        $redirectUrl = $request->attendance === 'hadir' ? "/ticket/{$token}" : "/rsvp/{$token}";

        return response()->json([
            'success' => true,
            'message' => 'Konfirmasi kehadiran berhasil dicatat.',
            'redirect_url' => $redirectUrl,
            'ticket_token' => $token,
            'status' => $newStatus,
        ]);
    }

    /**
     * Get Confirmed E-Ticket data for QR Screen
     * Endpoint: GET /api/ticket/{token}
     */
    public function getTicket(string $token): JsonResponse
    {
        $guest = Guest::with(['event', 'sales'])->where('token', $token)->firstOrFail();
        $event = $guest->event;
        $sales = $guest->sales;

        return response()->json([
            'success' => true,
            'ticket' => [
                'name' => $guest->name,
                'token' => $guest->token,
                'pax' => $guest->pax,
                'phone' => $guest->phone,
                'vip' => $guest->vip,
                'status' => $guest->status,
                'claimed_at' => $guest->claimed_at?->toIso8601String(),
                'event' => [
                    'name' => $event->name,
                    'date' => $event->event_date->translatedFormat('l, d F Y'),
                    'time' => $event->event_time,
                    'venue' => $event->venue,
                    'address' => $event->address,
                ],
                'sales' => [
                    'name' => $sales?->name ?? 'Doni Saputra',
                    'phone' => $sales?->phone ?? '0812-3456-7890',
                ],
            ],
        ]);
    }
}
