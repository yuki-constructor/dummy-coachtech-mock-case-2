<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequestRequest;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\BreakModel;
use Carbon\Carbon;
use Illuminate\Http\Request;


class AttendanceController extends Controller
{
    /**
     * ==============================
     * 従業員ユーザーの勤怠登録関連
     * ==============================
     */

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
            ->with('status')
            ->first();

        // //attendance_statusesテーブルから勤務外のレコードを取得
        // $status = AttendanceStatus::where('status', '勤務外')->first();

        //AttendanceStatusモデルでステータスを定数化。attendance_statusesテーブルから「勤務外」のレコードを取得
        $status = AttendanceStatus::where('status', AttendanceStatus::STATUS_OFF)->first();

        // 今日の勤怠レコードがあり、かつ end_time がセットされている（退勤済み）の場合
        // if (Attendance::where('employee_id', $employee->id)->where('date', $today)->exists())
        // if ($todayAttendance && !is_null($todayAttendance->end_time)) {

        // 「出勤」は1日に1回だけ押下できるため、今日の勤怠レコードがあり、かつ 勤怠ステータスが退勤済みか判定
        if ($todayAttendance && ($todayAttendance->attendance_status_id === $status->id)) {

            // エラーメッセージ表示
            // return view('attendance.employee.attendance-message')->with('error', '本日はすでに出勤登録と退勤登録が完了しています。');
            return to_route('employee.attendance.message')->with(['message' => '本日はすでに出勤登録と退勤登録が完了しています。']);
        }

        // 最新の勤怠レコードを取得（日をまたいで退勤する場合のため、当日制限なし）
        $attendance = Attendance::where('employee_id', $employee->id)->latest()->with('status')->first();

        // 勤怠登録画面を表示
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

        // // attendance_statusesテーブルから勤務中のレコードを取得
        // $status = AttendanceStatus::where('status', '勤務中')->first();

        // AttendanceStatusモデルでステータスを定数化。attendance_statusesテーブルから「勤務中」のレコードを取得
        $status = AttendanceStatus::where('status', AttendanceStatus::STATUS_ON)->first();

        // 出勤テーブルにレコード作成（勤務中ステータスを付与）
        Attendance::create([
            'employee_id' => $employee->id,
            'date' => $today,
            'start_time' => Carbon::now()->toTimeString(),
            'attendance_status_id' => $status->id,
        ]);

        // // 勤務中ステータスを付与
        // $status = AttendanceStatus::where('status', '勤務中')->first();
        // $attendance->statuses()->sync($status->id);

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

        // 休憩テーブルにレコード作成
        $attendance->breaks()->create([

            'break_start_time' => Carbon::now()->toTimeString(),
        ]);

        // $attendance->statuses()->sync();

        // // ステータスを「休憩中」に更新
        // $status = AttendanceStatus::where('status', '休憩中')->first();
        // $attendance->statuses()->sync($status->id);

        // // attendance_statusesテーブルから休憩中のレコードを取得
        // $status = AttendanceStatus::where('status', '休憩中')->first();

        // AttendanceStatusモデルでステータスを定数化。attendance_statusesテーブルから「休憩中」のレコードを取得
        $status = AttendanceStatus::where('status', AttendanceStatus::STATUS_BREAK)->first();

        // 勤怠テーブルのステータスを「休憩中」に更新
        $attendance->update([
            'attendance_status_id' => $status->id
        ]);

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

        // 休憩テーブルから、休憩戻りの登録がされていないレコードを取得
        $lastBreak = $attendance->breaks()->whereNull('break_end_time')->latest()->first();

        // 休憩戻りが登録されていない休憩がない場合はエラー（ビューの制御で、「休憩中」のステータスでない場合、休憩戻りボタンが表示されることはないため、削除）
        // if (!$lastBreak) {
        //     return redirect()->back()->withErrors('未終了の休憩がありません。');
        // }

        // 休憩戻り時間を登録
        $lastBreak->update(['break_end_time' => Carbon::now()->toTimeString()]);

        // ステータスを「勤務中」に戻す
        // $status = AttendanceStatus::where('status', '勤務中')->first();
        // $attendance->statuses()->sync($status->id);

        // // attendance_statusesテーブルから勤務中のレコードを取得
        // $status = AttendanceStatus::where('status', '勤務中')->first();

        // AttendanceStatusモデルでステータスを定数化。attendance_statusesテーブルから「勤務中」のレコードを取得
        $status = AttendanceStatus::where('status', AttendanceStatus::STATUS_ON)->first();

        // 勤怠テーブルのステータスを「勤務中」に更新
        $attendance->update([
            'attendance_status_id' => $status->id
        ]);

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

        // // attendance_statusesテーブルから勤務外のレコードを取得
        // $status = AttendanceStatus::where('status', '勤務外')->first();

        // AttendanceStatusモデルでステータスを定数化。attendance_statusesテーブルから「勤務外」のレコードを取得
        $status = AttendanceStatus::where('status', AttendanceStatus::STATUS_OFF)->first();

        // 勤怠テーブルのステータスを「勤務外」に更新
        $attendance->update([
            'end_time' => Carbon::now()->toTimeString(),
            'attendance_status_id'  => $status->id,
        ]);

        // $status = AttendanceStatus::where('status', '退勤済')->first();
        // $attendance->statuses()->sync($status->id);

        // ステータスを「勤務外」に更新
        // $status = AttendanceStatus::where('status', '勤務外')->first();
        // $attendance->statuses()->sync($status->id);

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


    /**
     * ==============================
     * 管理者ユーザーの勤怠管理関連
     * ==============================
     */

    /**
     * 日次勤怠一覧画面（管理者）を表示
     *
     * @route GET /admin/attendance/daily-list
     * @return \Illuminate\View\View
     */
    public function attendanceDailyList(Request $request, $date = null)
    {
        // 日付を取得 (指定がない場合は今日の日付)
        $date = $date ? Carbon::parse($date) : Carbon::today();

        // 勤怠データを取得
        $attendances = Attendance::where('date', $date->toDateString())
            ->with(['employee', 'breaks'])
            ->get();

        return view('attendance.admin.attendance-daily-list', compact('attendances', 'date'));
    }

    /**
     * 勤怠詳細画面（管理者）を表示
     *
     * @route GET /attendances/{attendanceId}/show
     * @return \Illuminate\View\View
     */
    public function adminAttendanceShow($attendanceId)
    {
        $attendance = Attendance::with(['employee', 'breaks'])->findOrFail($attendanceId);

        return view('attendance.admin.attendance-show', compact('attendance'));
    }

    /**
     * 勤怠情報の更新処理（管理者）
     *
     * @route POST /attendances/{attendanceId}/correct
     */
    // public function adminAttendanceCorrect(AttendanceRequestRequest $request, $attendanceId)
    public function adminAttendanceCorrect(AttendanceRequestRequest $request, $attendanceId)
    {
        // 勤怠データの取得
        $attendance = Attendance::with('breaks')->findOrFail($attendanceId);

        // 出勤時間・退勤時間の更新
        $attendance->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        // breaksテーブルのidを取得
        // $existingBreakIds = $attendance->breaks->pluck('id')->toArray();

        // リクエストされた break データの処理
        $requestBreaks = $request->input('breaks', []);

        // 更新・追加された break ID を保存
        // $updatedBreakIds = [];

        //        foreach ($requestBreaks as $breakId => $breakData) {
        //     if (!empty($breakData['start']) && !empty($breakData['end'])) {
        //         // 既存の break レコードを更新
        //         BreakModel::where('id', $breakId)->update([
        //             'break_start_time' => $breakData['start'],
        //             'break_end_time' => $breakData['end'],
        //         ]);
        //     } elseif (empty($breakData['start']) && empty($breakData['end'])) {
        //         // start と end の両方がない場合、NULL に更新
        //         BreakModel::where('id', $breakId)->update([
        //             'break_start_time' => null,
        //             'break_end_time' => null,
        //         ]);
        //     }
        // }

        foreach ($requestBreaks as $breakId => $breakData) {
            if (!empty($breakData['start']) && !empty($breakData['end'])) {
                // 既存の break レコードを更新
                BreakModel::where('id', $breakId)->update([
                    'break_start_time' => $breakData['start'],
                    'break_end_time' => $breakData['end'],
                ]);
            } elseif (empty($breakData['start']) && empty($breakData['end'])) {
                // start と end の両方がない場合、NULL に更新
                BreakModel::where('id', $breakId)->update([
                    'break_start_time' => null,
                    'break_end_time' => null,
                ]);
            }
        }

        return redirect()->route('admin.attendances.show', $attendanceId)->with('success',$attendance->employee->name. 'さんの勤怠情報を修正しました。');
    }
}
