<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * 従業員の勤怠登録画面を表示
     *
     * @route GET /employee/attendance-create
     * @param int $employeeId
     * @return \Illuminate\View\View
     */
    public function attendanceCreate()
    {

        $employee = auth('employee')->user();

        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)->where('date', $today)->first();

        return view('attendance.employee.attendance-create', compact('attendance'));
        // return view('attendance.employee.attendance-create');
    }

    /**
     * 従業員の出勤登録処理
     *
     * @route POST /employee//attendance/clock-in
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clockIn()
    {

        $employee = auth('employee')->user();

        $today = Carbon::today()->toDateString();

        if (!Attendance::where('employee_id', $employee->id)->where('date', $today)->exists()) {

            $attendance = Attendance::create([

                'employee_id' => $employee->id,

                'date' => $today,

                'start_time' => Carbon::now()->toTimeString(),

            ]);

            $status = AttendanceStatus::where('status', '出勤中')->first();

            // if ($status) {
            // AttendanceAttendanceStatus::create
            // $attendance->status()->attach()([
            //     'attendance_id' => $attendance->id,
            //     'attendance_status_id' => $status->id,
            // ]);
            $attendance->statuses()->sync($status->id);

            // }
            return redirect()->route('employee.attendance.create');
        }
        return redirect()->route('employee.attendance.create');
    }

    /**
     * 従業員の休憩開始登録処理
     *
     * @route POST /employee//attendance/break-start
     * @return \Illuminate\Http\RedirectResponse
     */
    public function breakStart()
    {

        $employee = auth('employee')->user();

        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)->where('date', $today)->first();

        // if ($attendance) {

            $attendance->breaks()->create([

                'break_start_time' => Carbon::now()->toTimeString(),
            ]);

$attendance->statuses()->sync();

// ●もし、breaksテーブルの 'break_start_time'カラムに値があり、 'break_end_timeが空の場合、'break_start_time'カラムに新たに値を入れることができない。
// ●attendance_attendance_statusテーブル（中間テーブル）にもデータが入るようにする。
// ●if ($attendance) で確認する意味は何？

        // }

        return redirect()->route('employee.attendance.create');
    }

    /**
     * 従業員の休憩終了登録処理
     * @route POST /employee//attendance/break-end
     * @return \Illuminate\Http\RedirectResponse
     */
    public function breakEnd()
    {

        $employee = auth('employee')->user();

        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)->where('date', $today)->first();

        if ($attendance) {

            $lastBreak = $attendance->breaks()->latest()->first();

            if ($lastBreak && !$lastBreak->break_end_time) {

                $lastBreak->update(['break_end_time' => Carbon::now()->toTimeString()]);
            }
        }
        return redirect()->route('employee.attendance.create');
    }

    /**
     * 従業員の退勤登録処理
     * @route POST /employee//attendance/break-out
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clockOut()
    {

        $employee = auth('employee')->user();

        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)->where('date', $today)->first();

        if ($attendance) {

            $attendance->update(['end_time' => Carbon::now()->toTimeString()]);

            $status = AttendanceStatus::where('status', '退勤済')->first();

            $attendance->statuses()->sync($status->id);
        }
        return redirect()->route('employee.attendance.create');
    }
}
