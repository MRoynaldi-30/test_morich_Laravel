<?php

namespace App\Http\Controllers;

use App\Models\DailyProduction;
use App\Models\ProductionOrder;
use App\Models\ProductionSchedule;
use App\Models\SewingLine;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'lineCount' => SewingLine::where('is_active', true)->count(),
            'orderCount' => ProductionOrder::where('is_active', true)->count(),
            'activeSchedules' => ProductionSchedule::whereIn(
                'status',
                ['planned', 'in_progress', 'delayed']
            )->count(),
            'delayedSchedules' => ProductionSchedule::where(
                'status',
                'delayed'
            )->count(),
            'todayTarget' => DailyProduction::whereDate(
                'production_date',
                today()
            )->sum('target_qty'),
            'todayActual' => DailyProduction::whereDate(
                'production_date',
                today()
            )->sum('actual_qty'),
        ]);
    }
}