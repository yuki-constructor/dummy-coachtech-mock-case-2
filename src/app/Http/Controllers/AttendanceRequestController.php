<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequestRequest;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\AttendanceRequestBreak;
use App\Models\AttendanceRequestStatus;
use App\Models\BreakModel;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AttendanceRequestController extends Controller
{
    /**
     * ==============================
     * 従業員ユーザーの勤怠修正申請関連
     * ==============================
     */

    /**
     * 勤怠修正申請を処理
     * @route POST /employee/attendance/{attendanceId}/request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function attendanceRequest(AttendanceRequestRequest $request, $attendanceId)
    {
        $employee = auth('employee')->user();

        // 勤怠データ取得（認証ユーザーに紐づいているかチェック）
        // $attendance = Attendance::where('id', $attendanceId)
        // ->where('employee_id', $employee->id)
        // ->firstOrFail();
        $attendance = Attendance::findOrFail($attendanceId);

        // 修正申請のステータス（「承認待ち」）
        // $pendingStatus = AttendanceRequestStatus::where('request_status', '承認待ち')->firstOrFail();

        // AttendanceRequestStatusモデルでステータスを定数化。attendance_request_statusesテーブルから「承認待ち」のレコードを取得
        $pendingStatus = AttendanceRequestStatus::where('request_status', AttendanceRequestStatus::STATUS_PENDING_APPROVAL)->first();

        // リクエストの日時を `Y-m-d H:i:s` に変換
        $date = $attendance->date; // 勤怠の日付を取得

        $startDateTime = Carbon::parse("{$date} {$request->start_time}");
        $endDateTime = Carbon::parse("{$date} {$request->end_time}");

        // 勤怠修正申請を登録
        $attendanceRequest = AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'attendance_request_status_id' => $pendingStatus->id,
            // 'start_time' => $request->start_time,
            'start_time' => $startDateTime,
            // 'end_time' => $request->end_time,
            'end_time' => $endDateTime,
            'reason' => $request->reason,
        ]);

        // 休憩修正申請の登録
        foreach ($request->breaks as $breakId => $break) {
            $breakStartDateTime = Carbon::parse("{$date} {$break['start']}");
            $breakEndDateTime = Carbon::parse("{$date} {$break['end']}");

            AttendanceRequestBreak::create([
                'attendance_request_id' => $attendanceRequest->id,
                'break_id' => $breakId,
                // 'break_start_time' => $break['start'],
                'break_start_time' => $breakStartDateTime,
                // 'break_end_time' => $break['end'],
                'break_end_time' => $breakEndDateTime,
            ]);
        };

        // 申請一覧画面（承認待ち）へリダイレクト
        return redirect()->route('employee.attendance.request.list.pending');
    }

    /**
     * 勤怠修正申請一覧画面（承認待ち）を表示
     * @route GET /employee/attendance/request/list/pending
     * @return \Illuminate\View\View
     */
    public function attendanceRequestListPending()
    {
        // ログイン中の従業員を取得
        $employee = Auth::guard('employee')->user();

        // "承認待ち" のIDを取得
        // $pendingStatus = AttendanceRequestStatus::where('request_status', '承認待ち')->first()->id;

        // AttendanceRequestStatusモデルでステータスを定数化。attendance_request_statusesテーブルから「承認待ち」のidを取得
        $pendingStatus = AttendanceRequestStatus::where('request_status', AttendanceRequestStatus::STATUS_PENDING_APPROVAL)->first()->id;

        // ログイン中の従業員の承認待ち申請を取得
        $attendanceRequests = AttendanceRequest::where('attendance_request_status_id', $pendingStatus)
            ->whereHas('attendance', function ($query) use ($employee) {
                $query->where('employee_id', $employee->id);
            })
            ->with(['attendance', 'attendance.employee', 'attendanceRequestStatus'])
            ->orderByDesc('created_at')
            ->get();

        return view('attendance.employee.attendance-request-list-pending', compact('attendanceRequests', 'employee'));
    }

    /**
     * 勤怠修正申請一覧画面（承認済み）を表示
     * @route GET /employee/attendance/request/list/approved
     * @return \Illuminate\View\View
     */
    public function attendanceRequestListApproved()
    {
        // ログイン中の従業員を取得
        $employee = Auth::guard('employee')->user();

        // AttendanceRequestStatusモデルでステータスを定数化。attendance_request_statusesテーブルから「承認済み」のidを取得
        $approvedStatus = AttendanceRequestStatus::where('request_status', AttendanceRequestStatus::STATUS_APPROVED)->first()->id;

        //  // ログイン中の従業員の承認済み申請を取得
        $attendanceRequests = AttendanceRequest::where('attendance_request_status_id', $approvedStatus)
            ->whereHas('attendance', function ($query) use ($employee) {
                $query->where('employee_id', $employee->id);
            })
            ->with(['attendance', 'attendance.employee', 'attendanceRequestStatus'])
            ->orderByDesc('created_at')
            ->get();

        return view('attendance.employee.attendance-request-list-approved', compact('attendanceRequests', 'employee'));
    }

    /**
     * 修正申請詳細画面を表示
     *
     * @route GET /employee/attendance/request/{attendanceRequestId}/show
     * @return \Illuminate\View\View
     */
    public function attendanceRequestShow($attendanceRequestId)
    {
        // リクエストされたattendance_request_idのレコードを取得
        $attendanceRequest = AttendanceRequest::with('attendanceRequestBreaks')
            ->findOrFail($attendanceRequestId);

        // AttendanceRequestStatusモデルでステータスを定数化。attendance_request_statusesテーブルから「承認待ち」のidを取得
        $pendingStatusId = AttendanceRequestStatus::where('request_status', AttendanceRequestStatus::STATUS_PENDING_APPROVAL)->value('id');

        return view('attendance.employee.attendance-request-show', compact(['attendanceRequest','pendingStatusId']));
    }

    /**
     * ==============================
     * 管理者ユーザーの勤怠修正承認関連
     * ==============================
     */

    /**
     * 勤怠修正申請一覧画面（承認待ち）を表示
     * @route GET /admin/attendance/request/list/pending
     * @return \Illuminate\View\View
     */
    public function adminAttendanceRequestListPending()
    {

        // AttendanceRequestStatusモデルでステータスを定数化。attendance_request_statusesテーブルから「承認待ち」のidを取得
        $pendingStatus = AttendanceRequestStatus::where('request_status', AttendanceRequestStatus::STATUS_PENDING_APPROVAL)->first()->id;

        // 従業員の承認待ち申請を取得
        $attendanceRequests = AttendanceRequest::where('attendance_request_status_id', $pendingStatus)
            // ->whereHas('attendance', function ($query) use ($employee) {
            //     $query->where('employee_id', $employee->id);
            // })
            ->with(['attendance', 'attendance.employee', 'attendanceRequestStatus'])
            ->orderByDesc('created_at')
            ->get();

        return view('attendance.admin.attendance-request-list-pending', compact('attendanceRequests'));
    }

    /**
     * 勤怠修正申請一覧画面（承認済み）を表示
     * @route GET /admin/attendance/request/list/approved
     * @return \Illuminate\View\View
     */
    public function adminAttendanceRequestListApproved()
    {

        // AttendanceRequestStatusモデルでステータスを定数化。attendance_request_statusesテーブルから「承認済み」のidを取得
        $approvedStatus = AttendanceRequestStatus::where('request_status', AttendanceRequestStatus::STATUS_APPROVED)->first()->id;

        // 従業員の承認済み申請を取得
        $attendanceRequests = AttendanceRequest::where('attendance_request_status_id', $approvedStatus)
            // ->whereHas('attendance', function ($query) use ($employee) {
            //     $query->where('employee_id', $employee->id);
            // })
            ->with(['attendance', 'attendance.employee', 'attendanceRequestStatus'])
            ->orderByDesc('created_at')
            ->get();

        return view('attendance.admin.attendance-request-list-approved', compact('attendanceRequests'));
    }

    /**
     * 修正申請承認画面を表示
     *
     * @route GET /admin/attendance/request/{attendanceRequestId}/show
     * @return \Illuminate\View\View
     */
    public function adminAttendanceRequestShow($attendanceRequestId)
    {
        // リクエストされたattendance_request_idのレコードを取得
        $attendanceRequest = AttendanceRequest::with('attendanceRequestBreaks')
            ->findOrFail($attendanceRequestId);

        // AttendanceRequestStatusモデルでステータスを定数化。attendance_request_statusesテーブルから「承認済み」のidを取得
        $approvedStatusId = AttendanceRequestStatus::where('request_status', AttendanceRequestStatus::STATUS_APPROVED)->value('id');

        return view('attendance.admin.attendance-request-show', compact(['attendanceRequest', 'approvedStatusId']));
    }

    /**
     * 修正申請の承認処理（管理者）
     *
     * @route POST /admin/attendance/{attendanceId}/correct
     * @return \Illuminate\Http\RedirectResponse
     */
    public function attendanceRequestAcknowledge(Request $request, $attendanceRequestId)
    {
        // リクエストされたattendance_request_idのレコードを取得
        // $attendanceRequest = AttendanceRequest::with('attendanceRequestBreaks')->findOrFail($attendanceRequestId);

        // 対象の申請を取得
        $attendanceRequest = AttendanceRequest::with('attendanceRequestBreaks')->findOrFail($attendanceRequestId);
        $attendance = $attendanceRequest->attendance;

        // // attendancesテーブルの出勤時間・退勤時間の更新
        // $attendanceRequest->attendance()->update([
        //                'start_time' => \Carbon\Carbon::parse($attendanceRequest->attendance->date . ' ' . $request->start_time), // 日付と組み合わせる
        //     'end_time' => \Carbon\Carbon::parse($attendanceRequest->attendance->date . ' ' . $request->end_time), // 日付と組み合わせる
        // ]);

        // 勤怠テーブルの更新
        $attendance->update([
            // 'start_time' => Carbon::parse($attendance->date . ' ' . $attendanceRequest->start_time),
            // 'end_time' => Carbon::parse($attendance->date . ' ' . $attendanceRequest->end_time),
            'start_time' => $attendanceRequest->start_time,
            'end_time' => $attendanceRequest->end_time,
        ]);

        // // リクエストされた break データの処理
        // $attendanceRequestBreaks = $request->input('breaks', []);

        // foreach ($attendanceRequestBreaks as $attendanceRequestBreakId => $attendanceRequestBreakData) {
        //     if (!empty($attendanceRequestBreakData['start']) && !empty($attendanceRequestBreakData['end'])) {

        //         $breakId = AttendanceRequestBreak::where('id', $attendanceRequestBreakId)->get("break_id");

        //         // breaksテーブルのレコードを更新
        //         BreakModel::where('id', $breakId)->update([
        //             'break_start_time' => \Carbon\Carbon::parse($attendanceRequest->attendance->date . ' ' . $attendanceRequestBreakData['start']), // 日付と組み合わせる
        //             'break_end_time' => \Carbon\Carbon::parse($attendanceRequest->attendance->date . ' ' . $attendanceRequestBreakData['end']), // 日付と組み合わせる
        //         ]);
        //     } elseif (empty($attendanceRequestBreakData['start']) && empty($attendanceRequestBreakData['end'])) {
        //         // start と end の両方がない場合、NULL に更新
        //         BreakModel::where('id', $attendanceRequestBreakId)->update([
        //             'break_start_time' => null,
        //             'break_end_time' => null,
        //         ]);
        //     }
        // }

        // 休憩時間の更新
        foreach ($attendanceRequest->attendanceRequestBreaks as $attendanceRequestBreak) {
            $break = BreakModel::find($attendanceRequestBreak->break_id);
            if ($break) {
                $break->update([
                    // 'break_start_time' => Carbon::parse($attendance->date . ' ' . $attendanceRequestBreak->break_start_time),
                    // 'break_end_time' => Carbon::parse($attendance->date . ' ' . $attendanceRequestBreak->break_end_time),
                    'break_start_time' => $attendanceRequestBreak->break_start_time,
                    'break_end_time' => $attendanceRequestBreak->break_end_time,
                ]);
            }
        }

        // // 修正申請のステータスを「承認済み」に更新
        // $attendanceRequest->update([
        //     'attendance_request_status_id' => AttendanceRequestStatus::where('request_status', AttendanceRequestStatus::STATUS_APPROVED)->first()->id,
        // ]);

        // AttendanceRequestStatusモデルでステータスを定数化。attendance_request_statusesテーブルから「承認済み」のidを取得
        $approvedStatusId = AttendanceRequestStatus::where('request_status', AttendanceRequestStatus::STATUS_APPROVED)->value('id');

        // 修正申請のステータスを「承認済み」に更新
        $attendanceRequest->update([
            'attendance_request_status_id' => $approvedStatusId,
        ]);

        // // フラッシュセッションに「承認済み」の ID を保存
        // Session::flash('approvedStatusId', $approvedStatusId);

        // return redirect()->route('admin.attendance.request.show', [$attendanceRequestId => $attendanceRequest->id]);

        return view('attendance.admin.attendance-request-show', compact(['attendanceRequest', 'approvedStatusId']));
    }
}
