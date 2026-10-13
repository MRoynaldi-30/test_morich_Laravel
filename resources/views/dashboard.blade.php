@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">Active Sewing Lines</p>
        <p class="mt-3 text-3xl font-bold">{{ $lineCount }}</p>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">Active Orders</p>
        <p class="mt-3 text-3xl font-bold">{{ $orderCount }}</p>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">Active Schedules</p>
        <p class="mt-3 text-3xl font-bold">{{ $activeSchedules }}</p>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">Delayed Schedules</p>
        <p class="mt-3 text-3xl font-bold text-red-600">
            {{ $delayedSchedules }}
        </p>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">Today's Target</p>
        <p class="mt-3 text-3xl font-bold">
            {{ number_format($todayTarget) }}
            <span class="text-sm font-normal text-slate-500">pcs</span>
        </p>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">Today's Actual</p>
        <p class="mt-3 text-3xl font-bold text-blue-700">
            {{ number_format($todayActual) }}
            <span class="text-sm font-normal text-slate-500">pcs</span>
        </p>
    </div>
</div>

<div class="mt-8 rounded-xl border border-slate-200 bg-white p-6">
    <h3 class="font-semibold">Production Overview</h3>
    <p class="mt-2 text-sm leading-6 text-slate-500">
        Gunakan menu Production Schedules untuk membuat jadwal,
        memantau target harian, mencatat aktual, dan melihat hasil balancing.
    </p>
    <a href="{{ route('schedules.page') }}"
       class="mt-4 inline-block rounded-lg bg-blue-700 px-4 py-2.5
              text-sm font-semibold text-white hover:bg-blue-800">
        Manage Schedules
    </a>
</div>
@endsection