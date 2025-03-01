<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\BreakModel;
use Carbon\CarbonPeriod;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //AttendanceStatusモデルでステータスを定数化。attendance_statusesテーブルから「勤務外」のステータス ID を取得
        $statusOffId = AttendanceStatus::where('status', AttendanceStatus::STATUS_OFF)->first()->id;

        // 全従業員を取得
        $employees = Employee::all();

        // 2023-05-01 から 2023-07-31 までの期間を作成
        $period = CarbonPeriod::create('2025-02-01', '2025-04-30');

        foreach ($employees as $employee) {
            foreach ($period as $date) {
                // 勤怠情報を登録
                $attendance = Attendance::create([
                    'employee_id' => $employee->id,
                    'attendance_status_id' => $statusOffId,
                    'date' => $date->toDateString(),
                    'start_time' => '09:00:00',
                    'end_time' => '18:00:00',
                    'created_at' => $date->copy()->setTime(9, 0, 0)->toDateTimeString(),
                    'updated_at' => $date->copy()->setTime(18, 0, 0)->toDateTimeString(),
                ]);

                // 休憩情報を登録
                BreakModel::create([
                    'attendance_id' => $attendance->id,
                    'break_start_time' => '12:00:00',
                    'break_end_time' => '13:00:00',
                    'created_at' => $date->copy()->setTime(12, 0, 0)->toDateTimeString(),
                    'updated_at' => $date->copy()->setTime(13, 0, 0)->toDateTimeString(),
                ]);
            }
        }
    }
}
