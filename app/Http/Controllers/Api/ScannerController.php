<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Guest;
use App\Models\ScanLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScannerController extends Controller
{
    /**
     * Verify and Claim Ticket across Pos 1 (Gate), Pos 2 (Souvenir), Pos 3 (Snack)
     * High-Performance Pessimistic Locking to guarantee zero double-scan race conditions.
     * Endpoint: POST /api/scanner/verify
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'pos' => 'required|integer|in:1,2,3',
        ]);

        $token = trim($request->token);
        $pos = (int) $request->pos;
        $operatorId = $request->user()?->id;

        return DB::transaction(function () use ($token, $pos, $operatorId, $request) {
            // Pessimistic Locking: Lock the specific row until transaction completes
            $guest = Guest::where('token', $token)->lockForUpdate()->first();

            if (! $guest) {
                return response()->json([
                    'success' => false,
                    'pos' => $pos,
                    'error_code' => 'INVALID_TOKEN',
                    'message' => 'Token QR tidak valid atau tidak terdaftar di sistem acara.',
                ], 422);
            }

            // --- POS 1: GATE CHECK-IN ---
            if ($pos === 1) {
                if ($guest->status === 'attended') {
                    return response()->json([
                        'success' => false,
                        'pos' => 1,
                        'error_code' => 'ALREADY_CLAIMED',
                        'claimed_at' => $guest->claimed_at?->toIso8601String(),
                        'guest' => $guest,
                        'message' => 'Tamu ini SUDAH CHECK-IN sebelumnya pada ' . ($guest->claimed_at ? $guest->claimed_at->format('H:i') . ' WIB' : 'sesi tadi') . '.',
                    ], 422);
                }

                $guest->update([
                    'status' => 'attended',
                    'claimed_at' => now(),
                ]);

                ScanLog::create([
                    'guest_id' => $guest->id,
                    'operator_id' => $operatorId,
                    'pos' => 1,
                    'action' => 'checkin',
                    'device_info' => $request->userAgent(),
                    'scanned_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'pos' => 1,
                    'message' => 'Verifikasi Gate Berhasil. Selamat datang ' . $guest->name . '.',
                    'guest' => $guest,
                    'timestamp' => now()->toIso8601String(),
                ]);
            }

            // --- POS 2: SOUVENIR HANDOVER ---
            if ($pos === 2) {
                // Precondition: Guest MUST check in at Gate 1 first
                if ($guest->status !== 'attended') {
                    return response()->json([
                        'success' => false,
                        'pos' => 2,
                        'error_code' => 'GATE_NOT_CHECKED_IN',
                        'guest' => $guest,
                        'message' => 'Tamu BELUM CHECK-IN di Gate 1. Arahkan tamu ke Meja Registrasi Utama terlebih dahulu.',
                    ], 422);
                }

                if ($guest->souvenir_claimed) {
                    return response()->json([
                        'success' => false,
                        'pos' => 2,
                        'error_code' => 'ALREADY_CLAIMED',
                        'claimed_at' => $guest->souvenir_claimed_at?->toIso8601String(),
                        'guest' => $guest,
                        'message' => 'Souvenir resmi SUDAH DIAMBIL pada ' . ($guest->souvenir_claimed_at ? $guest->souvenir_claimed_at->format('H:i') . ' WIB' : 'sesi tadi') . '.',
                    ], 422);
                }

                $guest->update([
                    'souvenir_claimed' => true,
                    'souvenir_claimed_at' => now(),
                ]);

                ScanLog::create([
                    'guest_id' => $guest->id,
                    'operator_id' => $operatorId,
                    'pos' => 2,
                    'action' => 'souvenir_handover',
                    'device_info' => $request->userAgent(),
                    'scanned_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'pos' => 2,
                    'message' => 'Souvenir Agung Toyota berhasil diserahkan kepada ' . $guest->name . '.',
                    'guest' => $guest,
                    'timestamp' => now()->toIso8601String(),
                ]);
            }

            // --- POS 3: ARTISAN SNACK BOX HANDOVER ---
            if ($pos === 3) {
                // Precondition: Guest MUST check in at Gate 1 first
                if ($guest->status !== 'attended') {
                    return response()->json([
                        'success' => false,
                        'pos' => 3,
                        'error_code' => 'GATE_NOT_CHECKED_IN',
                        'guest' => $guest,
                        'message' => 'Tamu BELUM CHECK-IN di Gate 1. Arahkan tamu ke Meja Registrasi Utama terlebih dahulu.',
                    ], 422);
                }

                if ($guest->snack_claimed) {
                    return response()->json([
                        'success' => false,
                        'pos' => 3,
                        'error_code' => 'ALREADY_CLAIMED',
                        'claimed_at' => $guest->snack_claimed_at?->toIso8601String(),
                        'guest' => $guest,
                        'message' => 'Snack Box & Voucher SUDAH DIAMBIL pada ' . ($guest->snack_claimed_at ? $guest->snack_claimed_at->format('H:i') . ' WIB' : 'sesi tadi') . '.',
                    ], 422);
                }

                $guest->update([
                    'snack_claimed' => true,
                    'snack_claimed_at' => now(),
                ]);

                ScanLog::create([
                    'guest_id' => $guest->id,
                    'operator_id' => $operatorId,
                    'pos' => 3,
                    'action' => 'snack_handover',
                    'device_info' => $request->userAgent(),
                    'scanned_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'pos' => 3,
                    'message' => 'Snack Box & Voucher berhasil diserahkan kepada ' . $guest->name . '.',
                    'guest' => $guest,
                    'timestamp' => now()->toIso8601String(),
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Pos scanner tidak valid.'], 400);
        });
    }

    /**
     * Get Live Quota and Feed for Scanner Dashboard
     * Endpoint: GET /api/scanner/quotas
     */
    public function getQuotaStats(Request $request): JsonResponse
    {
        $event = Event::where('status', 'active')->first() ?? Event::first();
        $eventId = $event?->id ?? 1;

        $pos1Count = Guest::where('event_id', $eventId)->where('status', 'attended')->count();
        $pos2Count = Guest::where('event_id', $eventId)->where('souvenir_claimed', true)->count();
        $pos3Count = Guest::where('event_id', $eventId)->where('snack_claimed', true)->count();
        $totalGuests = Guest::where('event_id', $eventId)->count();

        return response()->json([
            'success' => true,
            'event' => $event,
            'quotas' => [
                'pos1' => ['current' => $pos1Count, 'total' => $event?->quota_target ?? 300],
                'pos2' => ['current' => $pos2Count, 'total' => 250],
                'pos3' => ['current' => $pos3Count, 'total' => $event?->quota_target ?? 300],
            ],
            'recent_scans' => ScanLog::with('guest')->latest('scanned_at')->take(10)->get(),
        ]);
    }
}
