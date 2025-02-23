<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Attendance extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'date', 'start_time', 'end_time'];

    // Attendanceは１対多の関係でEmployeeと関連（従業員の勤怠情報）
    public function attendance()
    {
        return $this->belongsTo(Employee::class);
    }

    // Attendanceは多対多の関係でAttendanceStatusと関連（従業員の勤怠ステータス）
    public function statuses()
    {
        return $this->belongsToMany(AttendanceStatus::class, 'attendance_attendance_status');
    }

    // Attendanceは１対多の関係でBreakと関連（従業員は１日に何度でも休憩できる）
    public function breaks()
    {
        return $this->hasMany(BreakModel::class);
    }
}
