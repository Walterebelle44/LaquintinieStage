<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

// Réservé à l'admin : journal d'activité complet de l'application
class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::query()->with(['causer', 'subject'])->latest();

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->string('log_name'));
        }
        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->integer('causer_id'));
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date('date_debut'));
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date('date_fin'));
        }

        return response()->json($query->paginate($request->integer('per_page', 25)));
    }
}
