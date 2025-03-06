@extends('layouts.admin-app')

@section('title', '従業員別月次勤怠一覧画面（管理者）')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/attendance/admin/attendance-monthly-list.css') }}">
@endpush

@section('content')
    <div class="container-wrap">
        <div class="container">
            <h1 class="title">{{ $employee->name }}さんの勤怠</h1>
            <div class="month-navigation">
                <a
                    href="{{ route('admin.attendance.monthly-list', ['employeeId' => $employee->id, 'month' => \Carbon\Carbon::parse($month)->subMonth()->format('Y-m')]) }}">&larr;
                    前月</a>
                <div class="month-navigation-center">
                    <img class="month-navigation-calendar__image" src="{{ asset('storage/photos/logo_images/calendar.png') }}"
                        alt="カレンダー" />
                    <span class="month">{{ \Carbon\Carbon::parse($month)->format('Y/m') }}</span>
                </div>
                <a
                    href="{{ route('admin.attendance.monthly-list', ['employeeId' => $employee->id, 'month' => \Carbon\Carbon::parse($month)->addMonth()->format('Y-m')]) }}">翌月
                    &rarr;</a>
            </div>

            <div class="attendance-table">
                <div class="table-header">
                    <span>日付</span>
                    <span>出勤</span>
                    <span>退勤</span>
                    <span>休憩</span>
                    <span>合計</span>
                    <span>詳細</span>
                </div>

                {{-- 勤怠データを繰り返し --}}
                {{-- <div class="table-row">
                    <span>06/01(木)</span>
                    <span>09:00</span>
                    <span>18:00</span>
                    <span>1:00</span>
                    <span>8:00</span>
                    <a href="#">詳細</a>
                </div> --}}

                @foreach ($attendances as $attendance)
                    <div class="table-row">
                        {{-- <span>{{ \Carbon\Carbon::parse($attendance->date)->format('m/d (D)') }}</span> --}}
                        <span>{{ \Carbon\Carbon::parse($attendance->date)->locale('ja')->isoFormat('MM/DD (ddd)') }}</span>

                        {{-- 出勤時刻 --}}
                        <span>{{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '-' }}</span>

                        {{-- 退勤時刻 --}}
                        <span>{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '-' }}</span>

                        {{-- 休憩時間の合計 --}}
                        {{-- <span>
                            @php
                                $totalBreakMinutes = $attendance->breaks->sum(function ($break) {
                                    if ($break->break_end_time) {
                                        return \Carbon\Carbon::parse($break->break_start_time)->diffInMinutes(
                                            \Carbon\Carbon::parse($break->break_end_time),
                                        );
                                    }
                                    return 0;
                                });
                                echo floor($totalBreakMinutes / 60) .
                                    ':' .
                                    str_pad($totalBreakMinutes % 60, 2, '0', STR_PAD_LEFT);
                            @endphp
                        </span> --}}
                        <span>{{ $attendance->total_break_time }}</span>

                        {{-- 勤務時間の合計 --}}
                        {{-- <span>
                            @if ($attendance->start_time && $attendance->end_time)
                                @php
                                    $workMinutes =
                                        \Carbon\Carbon::parse($attendance->start_time)->diffInMinutes(
                                            $attendance->end_time,
                                        ) - $totalBreakMinutes;
                                    echo floor($workMinutes / 60) .
                                        ':' .
                                        str_pad($workMinutes % 60, 2, '0', STR_PAD_LEFT);
                                @endphp
                            @else
                                -
                            @endif
                        </span> --}}
                        <span>{{ $attendance->total_work_time }}</span>

                        {{-- 詳細リンク --}}
                        <a href="{{ route('admin.attendance.show', ['attendanceId' => $attendance->id]) }}">詳細</a>
                    </div>
                @endforeach

            </div>
        </div>
    </div>
@endsection
