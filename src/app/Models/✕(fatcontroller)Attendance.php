<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'date', 'start_time', 'end_time', 'attendance_status_id'];

    /**
     * Attendanceは１対多の関係でEmployeeと関連（従業員の勤怠情報）
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // // Attendanceは多対多の関係でAttendanceStatusと関連（従業員の勤怠ステータス）
    // public function statuses()
    // {
    //     return $this->belongsToMany(AttendanceStatus::class, 'attendance_attendance_status');
    // }

    /**
     * Attendanceは１対多の関係でAttendanceStatusと関連（従業員の勤怠ステータス）
     */
    public function status()
    {
        return $this->belongsTo(AttendanceStatus::class, 'attendance_status_id');
    }

    /**
     * Attendanceは１対多の関係でBreakと関連（従業員は１日に何度でも休憩できる）
     */
    public function breaks()
    {
        return $this->hasMany(BreakModel::class);
    }

    /**
     * Attendanceは１対多の関係でAttendanceRequestと関連（修正申請は何度でもできる）
     */
    public function attendanceRequests()
    {
        return $this->hasMany(BreakModel::class);
    }

    /**
     * リクエストされた従業員の今日の勤怠レコードを取得
     */
    public static function getTodayAttendanceForSpecifiedEmployee($employeeId)
    {
        return self::where('employee_id', $employeeId)
            ->where('date', Carbon::today()->toDateString())
            ->with('status')
            ->first();
    }

    /**
     * リクエストされた従業員の最新の勤怠レコードを取得
     */
    public static function getLatestAttendanceForSpecifiedEmployee($employeeId)
    {
        return self::where('employee_id', $employeeId)
            ->latest()
            ->with('status')
            ->first();
    }

    /**
     * 従業員の出勤登録処理
     */
    public static function clockIn($employeeId, $today, $statusOn)
    {
        return self::create([
            'employee_id' => $employeeId,
            'date' => $today,
            'start_time' => Carbon::now()->toTimeString(),
            'attendance_status_id' => $statusOn->id,
        ]);
    }
}
