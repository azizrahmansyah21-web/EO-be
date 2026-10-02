<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GuestController extends Controller
{
    /**
     * List all guests with search and filtering
     * Endpoint: GET /api/admin/guests or GET /api/sales/guests
     */
    public function index(Request $request): JsonResponse
    {
        $query = Guest::with(['event', 'sales'])->latest();

        // If authenticated user is a Sales Consultant, filter only their prospects
        if ($request->user()?->role === 'sales') {
            $query->where('sales_id', $request->user()->id);
        }

        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('company', 'like', "%{$s}%");
            });
        }

        $guests = $query->paginate($request->integer('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $guests->items(),
            'meta' => [
                'current_page' => $guests->currentPage(),
                'total' => $guests->total(),
                'per_page' => $guests->perPage(),
                'last_page' => $guests->lastPage(),
            ],
        ]);
    }

    /**
     * Create single guest
     * Endpoint: POST /api/sales/guests or POST /api/admin/guests
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'company' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'vip' => 'nullable|boolean',
            'vip_tier' => 'nullable|in:REGULAR,VIP,VVIP',
            'pax' => 'nullable|integer|min:1|max:5',
            'car_model' => 'nullable|string|max:100',
        ]);

        // Clean phone number format
        $cleanPhone = preg_replace('/[^0-9]/', '', $validated['phone']);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }
        $validated['phone'] = $cleanPhone;

        // Anti-Duplicate check per event
        $exists = Guest::where('event_id', $validated['event_id'])
            ->where('phone', $cleanPhone)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor WhatsApp ini sudah terdaftar sebagai tamu pada event ini.',
            ], 422);
        }

        $validated['sales_id'] = $request->user()?->role === 'sales'
            ? $request->user()->id
            : $request->input('sales_id');

        $validated['token'] = 'TKN-' . strtoupper(Str::random(6)) . '-PKU';
        $validated['status'] = $request->user()?->role === 'admin' ? 'invited' : 'pending';

        $guest = Guest::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data tamu berhasil didaftarkan.',
            'guest' => $guest,
        ], 201);
    }

    /**
     * Batch Smart Import (Column Mapping)
     * Endpoint: POST /api/admin/guests/import
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'guests' => 'required|array|min:1',
            'guests.*.name' => 'required|string',
            'guests.*.phone' => 'required|string',
        ]);

        $eventId = (int) $request->event_id;
        $importedCount = 0;
        $skippedCount = 0;

        foreach ($request->guests as $row) {
            $phone = preg_replace('/[^0-9]/', '', $row['phone']);
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            }

            // Check duplicate
            if (Guest::where('event_id', $eventId)->where('phone', $phone)->exists()) {
                $skippedCount++;
                continue;
            }

            // Resolve sales PIC if provided
            $salesId = null;
            if (! empty($row['sales_pic'])) {
                $salesUser = User::where('name', 'like', "%{$row['sales_pic']}%")
                    ->where('role', 'sales')
                    ->first();
                $salesId = $salesUser?->id;
            }

            Guest::create([
                'token' => 'TKN-' . strtoupper(Str::random(6)) . '-PKU',
                'event_id' => $eventId,
                'sales_id' => $salesId,
                'name' => $row['name'],
                'phone' => $phone,
                'company' => $row['company'] ?? null,
                'title' => $row['title'] ?? null,
                'vip' => ! empty($row['vip']),
                'vip_tier' => $row['vip_tier'] ?? (! empty($row['vip']) ? 'VIP' : 'REGULAR'),
                'car_model' => $row['car_model'] ?? null,
                'status' => 'invited',
            ]);

            $importedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Import berhasil: {$importedCount} data diimpor, {$skippedCount} duplikat dilewati.",
            'imported_count' => $importedCount,
            'skipped_count' => $skippedCount,
        ]);
    }
}
