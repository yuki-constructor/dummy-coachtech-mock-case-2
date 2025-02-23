<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class AttendanceStatus extends Model
{
    use HasFactory;

    protected $fillable = ['status'];

    // AttendanceStatusは多対多の関係でAttendanceと関連（従業員の勤怠ステータス）
    public function attendances()
    {
        return $this->belongsToMany(Attendance::class, 'attendance_attendance_status');
    }
}
