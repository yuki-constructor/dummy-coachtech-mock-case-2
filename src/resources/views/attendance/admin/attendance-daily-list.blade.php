@extends('layouts.admin-app')

@section('title', '日次勤怠一覧画面（管理者）')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/attendance/admin/attendance-daily-list.css') }}">
@endpush

@section('content')
    <div class="container-wrap">
        <div class="container">
            <h1>勤怠一覧</h1>
            <div class="day-navigation">
                <a href="{{ route('admin.attendance.daily-list', ['date' => $date->copy()->subDay()->toDateString()]) }}">&larr;
                    前日</a>
                <div class="day-navigation-center">
                    <img class="day-navigation-calendar__image" src="{{ asset('storage/photos/logo_images/calendar.png') }}" alt="カレンダー" />
                    <span class="day">{{ $date->format('Y/m/d') }}</span>
                </div>
                <a href="{{ route('admin.attendance.daily-list', ['date' => $date->copy()->addDay()->toDateString()]) }}">翌日
                    &rarr;</a>
            </div>

            <div class="attendance-table">
                <div class="table-header">
                    <span>名前</span>
                    <span>出勤</span>
                    <span>退勤</span>
                    <span>休憩</span>
                    <span>合計</span>
                    <span>詳細</span>
                </div>

                  {{-- 勤怠データを繰り返し --}}
                @foreach ($attendances as $attendance)
                <div class="table-row">
                    <span>{{ $attendance->employee->name }}</span>

                     {{-- 出勤時刻 --}}
                    <span>{{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }}</span>

                     {{-- 退勤時刻 --}}
                    <span>{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '-' }}</span>

                    {{-- 休憩時間の合計を計算--}}
                    {{-- @php
                        $totalBreakMinutes = $attendance->breaks->sum(function($break) {
                            return \Carbon\Carbon::parse($break->break_start_time)->diffInMinutes($break->break_end_time);
                        });
                        $breakTime = floor($totalBreakMinutes / 60) . ':' . str_pad($totalBreakMinutes % 60, 2, '0', STR_PAD_LEFT);
                    @endphp --}}

                    @php
                    $totalBreakMinutes = 0;
                    foreach ($attendance->breaks as $break) {
                        if ($break->break_start_time && $break->break_end_time) {
                            $totalBreakMinutes += \Carbon\Carbon::parse($break->break_start_time)->diffInMinutes($break->break_end_time);
                        }
                    }
                    $breakTime = $totalBreakMinutes > 0
                        ? floor($totalBreakMinutes / 60) . ':' . str_pad($totalBreakMinutes % 60, 2, '0', STR_PAD_LEFT)
                        : '-';
                @endphp

                    <span>{{ $breakTime }}</span>

                        {{-- 勤務時間の合計を計算--}}
                    {{-- @php
                        $workMinutes = \Carbon\Carbon::parse($attendance->start_time)->diffInMinutes($attendance->end_time) - $totalBreakMinutes;
                        $totalWorkTime = floor($workMinutes / 60) . ':' . str_pad($workMinutes % 60, 2, '0', STR_PAD_LEFT);
                    @endphp --}}

                    @php
                    $totalWorkTime = '-';
                    if ($attendance->start_time && $attendance->end_time) {
                        $workMinutes = \Carbon\Carbon::parse($attendance->start_time)->diffInMinutes($attendance->end_time) - $totalBreakMinutes;
                        $totalWorkTime = floor($workMinutes / 60) . ':' . str_pad($workMinutes % 60, 2, '0', STR_PAD_LEFT);
                    }
                @endphp
                    <span>{{ $totalWorkTime }}</span>

                    {{-- 詳細リンク --}}
                    <a href="{{ route('admin.attendances.show', ['attendanceId' => $attendance->id]) }}">詳細</a>
                </div>
                @endforeach

            </div>
        </div>
    </div>
@endsection
