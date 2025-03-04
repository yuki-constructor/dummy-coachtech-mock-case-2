@extends('layouts.admin-app')

@section('title', '従業員一覧画面（管理者）')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/attendance/admin/employee-list.css') }}">
@endpush

@section('content')
    <div class="container-wrap">
        <div class="container">
            <h1>スタッフ一覧</h1>
            <div class="attendance-table">
                <div class="table-header">
                    <span class="table-header-name">名前</span>
                    <span class="table-header-mail">メールアドレス</span>
                    <span class="table-header-detail">月次退勤</span>
                </div>

                {{-- employeeデータを繰り返し --}}
                @foreach ($employees as $employee)
                    <div class="table-row">

                        {{-- 名前 --}}
                        <span class="name">{{ $employee->name }}</span>

                        {{-- メールアドレス --}}
                        <span class="mail">{{ $employee->email }}</span>

                        {{-- 休憩時間の合計を計算 --}}
                        {{-- @php
                        $totalBreakMinutes = $attendance->breaks->sum(function($break) {
                            return \Carbon\Carbon::parse($break->break_start_time)->diffInMinutes($break->break_end_time);
                        });
                        $breakTime = floor($totalBreakMinutes / 60) . ':' . str_pad($totalBreakMinutes % 60, 2, '0', STR_PAD_LEFT);
                    @endphp --}}

                        {{-- @php
                            $totalBreakMinutes = 0;
                            foreach ($attendance->breaks as $break) {
                                if ($break->break_start_time && $break->break_end_time) {
                                    $totalBreakMinutes += \Carbon\Carbon::parse(
                                        $break->break_start_time,
                                    )->diffInMinutes($break->break_end_time);
                                }
                            }
                            $breakTime =
                                $totalBreakMinutes > 0
                                    ? floor($totalBreakMinutes / 60) .
                                        ':' .
                                        str_pad($totalBreakMinutes % 60, 2, '0', STR_PAD_LEFT)
                                    : '-';
                        @endphp

                        <span>{{ $breakTime }}</span> --}}

                        {{-- 勤務時間の合計を計算 --}}
                        {{-- @php
                        $workMinutes = \Carbon\Carbon::parse($attendance->start_time)->diffInMinutes($attendance->end_time) - $totalBreakMinutes;
                        $totalWorkTime = floor($workMinutes / 60) . ':' . str_pad($workMinutes % 60, 2, '0', STR_PAD_LEFT);
                    @endphp --}}

                        {{-- @php
                            $totalWorkTime = '-';
                            if ($attendance->start_time && $attendance->end_time) {
                                $workMinutes =
                                    \Carbon\Carbon::parse($attendance->start_time)->diffInMinutes(
                                        $attendance->end_time,
                                    ) - $totalBreakMinutes;
                                $totalWorkTime =
                                    floor($workMinutes / 60) . ':' . str_pad($workMinutes % 60, 2, '0', STR_PAD_LEFT);
                            }
                        @endphp
                        <span>{{ $totalWorkTime }}</span> --}}

                        {{-- 詳細リンク --}}
                        <a href="{{ route('admin.attendance.monthly-list', ['employeeId' => $employee->id]) }}"
                            class="detail">詳細</a>
                    </div>
                @endforeach

            </div>
        </div>
    </div>
@endsection
