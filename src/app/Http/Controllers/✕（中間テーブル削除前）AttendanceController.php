<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;


/**
 * ==============================
 * 従業員ユーザーの勤怠登録関連
 * ==============================
 */
class AttendanceController extends Controller
{
    /**
     * 従業員の勤怠登録画面を表示
     *
     * @route GET /employee/attendance-create
     * @return \Illuminate\View\View
     */
    public function attendanceCreate()
    {

        $employee = auth('employee')->user();

        $today = Carbon::today()->toDateString();

        // 今日の勤怠レコードを取得
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        // 今日の勤怠レコードがあり、かつ end_time がセットされている（退勤済み）の場合
        // if (Attendance::where('employee_id', $employee->id)->where('date', $today)->exists())
        // if ($todayAttendance && !is_null($todayAttendance->end_time)) {
        if ($todayAttendance && ($todayAttendance->statuses->contains('status', '勤務外'))) {

            // return view('attendance.employee.attendance-message')->with('error', '本日はすでに出勤登録と退勤登録が完了しています。');
            return to_route('employee.attendance.message')->with(['message' => '本日はすでに出勤登録と退勤登録が完了しています。']);
        }

        // 最新の勤怠レコードを取得（日をまたいで退勤する場合のため、当日制限なし）
        $attendance = Attendance::where('employee_id', $employee->id)->latest()->first();

        return view('attendance.employee.attendance-create', compact('attendance'));
    }

    /**
     * 従業員の勤怠登録（メッセージ）画面を表示
     *
     * @route GET /employee/attendance-message
     * @return \Illuminate\View\View
     */
    public function attendanceMessage()
    {
        return view('attendance.employee.attendance-message');
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


        //         // すでに出勤記録があるかチェック（勤怠登録画面を表示するアクションattendanceCreateで、すでにチェック済みで、ビューの制御で出勤ボタンが表示されることはないため、削除）
        //     $alreadyClockedIn = Attendance::where('user_id', $userId)
        //     ->whereDate('created_at', $today)
        //     ->exists();

        // if ($alreadyClockedIn) {
        //     return redirect()->route('attendance.create')->with('error', '本日はすでに出勤しています。');
        // }

        // if (!Attendance::where('employee_id', $employee->id)->where('date', $today)->exists()) {

        //  // すでに出勤記録があるかチェック（勤怠登録画面を表示するアクションattendanceCreateで、すでにチェック済みで、ビューの制御で出勤ボタンが表示されることはないため、削除）
        // if (Attendance::where('employee_id', $employee->id)->where('date', $today)->exists()) {

        //     return redirect()->route('attendance.create')->with('error', '本日はすでに出勤しています。');
        // }

        // // 最新の勤怠ステータスが「勤務外」か確認（ビューの制御で、「勤務中」の場合、出勤ボタンが表示されることはないため、削除）
        // $latestAttendance = Attendance::where('employee_id', $employee->id)->latest()->first();

        // if ($latestAttendance && $latestAttendance->statuses->contains('status', '勤務中')) {
        //     return redirect()->back()->withErrors('既に勤務中のため、出勤登録できません。');
        // }

        // 新しい出勤レコード作成
        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => $today,
            'start_time' => Carbon::now()->toTimeString(),
        ]);

        // 勤務中ステータスを付与
        $status = AttendanceStatus::where('status', '勤務中')->first();
        $attendance->statuses()->sync($status->id);

        // }
        return redirect()->route('employee.attendance.create');
    }


    // return redirect()->route('attendance.create')->with('error', '本日はすでに出勤しています。');
    // }

    /**
     * 従業員の休憩開始登録処理
     *
     * @route POST /employee//attendance/break-start
     * @return \Illuminate\Http\RedirectResponse
     */
    public function breakStart()
    {

        $employee = auth('employee')->user();

        // $today = Carbon::today()->toDateString();
        // $attendance = Attendance::where('employee_id', $employee->id)->where('date', $today)->first();

        $attendance = Attendance::where('employee_id', $employee->id)->latest()->first();

        // // 最新の勤怠ステータスが「勤務中」か確認（ビューの制御で、「勤務中」でない場合、休憩ボタンが表示されることはないため、削除）
        // // if ($attendance) {
        // if (!$attendance || !$attendance->statuses->contains('status', '勤務中')) {
        //     return redirect()->back()->withErrors('勤務中でないため、休憩開始できません。');
        // }

        // // 未終了の休憩がある場合はエラー（ビューの制御で、「休憩中」のステータスの場合、休憩ボタンが表示されることはないため、削除）
        // if ($attendance->breaks()->whereNull('break_end_time')->exists()) {
        //     return redirect()->back()->withErrors('未終了の休憩があります。');
        // }

        $attendance->breaks()->create([

            'break_start_time' => Carbon::now()->toTimeString(),
        ]);

        // $attendance->statuses()->sync();

        // ステータスを「休憩中」に更新
        $status = AttendanceStatus::where('status', '休憩中')->first();
        $attendance->statuses()->sync($status->id);


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

        // $today = Carbon::today()->toDateString();
        // $attendance = Attendance::where('employee_id', $employee->id)->where('date', $today)->first();

        $attendance = Attendance::where('employee_id', $employee->id)->latest()->first();

        // if ($attendance) {

        // // 休憩中でない場合はエラー（ビューの制御で、「休憩中」のステータスでない場合、休憩戻りボタンが表示されることはないため、削除）
        // if (!$attendance || !$attendance->statuses->contains('status', '休憩中')) {
        //     return redirect()->back()->withErrors('休憩中でないため、休憩終了できません。');
        // }

        // $lastBreak = $attendance->breaks()->latest()->first();

        // if ($lastBreak && !$lastBreak->break_end_time) {

        //     $lastBreak->update(['break_end_time' => Carbon::now()->toTimeString()]);
        // }

        $lastBreak = $attendance->breaks()->whereNull('break_end_time')->latest()->first();

        // 休憩戻りが登録されていない休憩がない場合はエラー（ビューの制御で、「休憩中」のステータスでない場合、休憩戻りボタンが表示されることはないため、削除）
        // if (!$lastBreak) {
        //     return redirect()->back()->withErrors('未終了の休憩がありません。');
        // }

        $lastBreak->update(['break_end_time' => Carbon::now()->toTimeString()]);

        // ステータスを「勤務中」に戻す
        $status = AttendanceStatus::where('status', '勤務中')->first();
        $attendance->statuses()->sync($status->id);

        return redirect()->route('employee.attendance.create');

        // }
    }

    /**
     * 従業員の退勤登録処理
     * @route POST /employee//attendance/break-out
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clockOut()
    {

        $employee = auth('employee')->user();

        // $today = Carbon::today()->toDateString();
        // $attendance = Attendance::where('employee_id', $employee->id)->where('date', $today)->first();

        $attendance = Attendance::where('employee_id', $employee->id)->latest()->first();

        // if ($attendance) {

        // // 勤務中でない場合はエラー（ビューの制御で、「勤務中」のステータスでない場合、退勤ボタンが表示されることはないため、削除）
        // if (!$attendance || !$attendance->statuses->contains('status', '勤務中')) {
        //     return redirect()->back()->withErrors('勤務中でないため、退勤登録できません。');
        // }

        $attendance->update(['end_time' => Carbon::now()->toTimeString()]);

        // $status = AttendanceStatus::where('status', '退勤済')->first();
        // $attendance->statuses()->sync($status->id);

        // ステータスを「勤務外」に更新
        $status = AttendanceStatus::where('status', '勤務外')->first();
        $attendance->statuses()->sync($status->id);

        // }
        // return view('attendance.employee.attendance-clock-out');
        return to_route('employee.attendance.message')->with(['message' => 'お疲れ様でした。']);
    }

    /**
     * 従業員の勤怠一覧画面を表示
     *
     * @route GET /employee/attendance-list
     * @return \Illuminate\View\View
     */
    public function attendanceList(Request $request)
    {
        // ログイン中の従業員情報を取得
        $employee = auth('employee')->user();

        // 指定された月を取得（デフォルトは現在の月）
        $month = $request->query('month', now()->format('Y-m'));

        // 指定月の勤怠データを取得
        $attendances = Attendance::where('employee_id', $employee->id)
            ->where('date', 'like', $month . '%')
            ->orderBy('date', 'asc')
            ->with(['breaks']) // 休憩データも取得
            ->get();

        return view('attendance.employee.attendance-list', compact('attendances', 'month'));
    }

    /**
     * 従業員の勤怠詳細画面を表示
     *
     * @route GET /employee/attendance-show
     * @return \Illuminate\View\View
     */
    public function attendanceShow($attendanceId)
    {
        // ログイン中の従業員情報を取得
        $employee = auth('employee')->user();

        // 該当の勤怠データを取得
        $attendance = Attendance::where('id', $attendanceId)
            ->where('employee_id', $employee->id)
            ->with('breaks')
            ->firstOrFail();

        return view('attendance.employee.attendance-show', compact('attendance'));
    }
}
