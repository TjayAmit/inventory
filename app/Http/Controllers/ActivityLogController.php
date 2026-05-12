<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = Activity::with('causer:id,name,email')
            ->when($request->search, function ($q, $s) {
                $q->where(function ($qq) use ($s) {
                    $qq->where('description', 'like', "%{$s}%")
                       ->orWhere('log_name', 'like', "%{$s}%")
                       ->orWhere('subject_type', 'like', "%{$s}%")
                       ->orWhereHas('causer', fn ($cq) => $cq->where('name', 'like', "%{$s}%"));
                });
            })
            ->when($request->log_name, fn ($q, $v) => $q->inLog($v))
            ->when($request->event,    fn ($q, $v) => $q->where('event', $v))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to,   fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate($request->per_page ?? 20)
            ->withQueryString();

        $logNames = Activity::distinct()->pluck('log_name')->filter()->sort()->values();
        $events   = Activity::distinct()->pluck('event')->filter()->sort()->values();

        return Inertia::render('activity-log/index', [
            'data'     => $logs,
            'filters'  => $request->only(['search', 'per_page', 'log_name', 'event', 'date_from', 'date_to']),
            'logNames' => $logNames,
            'events'   => $events,
        ]);
    }
}
