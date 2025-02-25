@extends('layouts.employee-app')

@section('title', '勤怠詳細画面（従業員）')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/attendance/employee/attendance-show.css') }}">
@endpush

@section('content')
    <div class="container-wrap">
        <div class="container">
            <h1>勤怠詳細</h1>
            {{-- <form
            action="{{ route('attendance.request', $attendance->id) }}"
            method="POST"
          > --}}
            <form action="" method="POST">
                @csrf
                <div class="attendance-table">
                    <div class="table-row">
                        <span class="label">名前</span>
                        <span class="value">{{ auth('employee')->user()->name }}</span>
                        <span class="space"></span>
                    </div>
                    <div class="table-row">
                        <span class="label">日付</span>
                        <div class="date">
                            <span class="date-year">{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
                            <span class="date-day">{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</span>
                        </div>
                        <span class="space"></span>
                    </div>
                    <div class="table-row">
                        <span class="label">出勤・退勤</span>
                        <div class="time">
                            <input class="time-box" type="time" name="start_time"
                                value="{{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }}" />〜
                            <input class="time-box" type="time" name="end_time" value="{{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i') }}" />
                            <!-- <span class="time-box">09:00</span> 〜
                    <span class="time-box">18:00</span> -->
                        </div>
                        <span class="space"></span>
                    </div>
                    @foreach ($attendance->breaks as $break)
                        <div class="table-row">
                            <span class="label">休憩</span>
                            <div class="time">
                                <input class="time-box" type="time" name="breaks[{{ $break->id }}][start]"
                                    value="{{ \Carbon\Carbon::parse($break->break_start_time)->format('H:i') }}" />〜
                                <input class="time-box" type="time" name="breaks[{{ $break->id }}][end]"
                                    value="{{ \Carbon\Carbon::parse($break->break_end_time)->format('H:i') }}" />
                                <!-- <span class="time-box">12:00</span> 〜
                    <span class="time-box">13:00</span> -->
                            </div>
                            <span class="space"></span>
                        </div>
                    @endforeach
                    <div class="table-row">
                        <span class="label">備考</span>
                        <div class="reason">
                            <textarea class="reason__input" name="reason" placeholder="修正理由"></textarea>
                        </div>
                        <!-- <input
                      type="text"
                      class="note"
                      value="電車遅延のため"
                      readonly
                    /> -->
                        <span class="space"></span>
                    </div>
                </div>
                <button class="edit-button">修正</button>
            </form>
        </div>
    </div>
@endsection
