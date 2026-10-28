<?php

namespace App\Http\Controllers;

use App\Models\Appeal;
use App\Models\Comment;
use App\Models\ModerationAction;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Services\AntispamService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct(
        protected AntispamService $antispamService
    ) {}

    public function index(Request $request)
    {
        $status = $request->query('status', 'open');

        $reports = Report::with(['reporter.profile', 'resolver.profile', 'evidence'])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $pendingAppeals = Appeal::with('user.profile')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        $recentActions = ModerationAction::with('moderator.profile')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $stats = [
            'open_reports' => Report::where('status', 'open')->count(),
            'pending_appeals' => Appeal::where('status', 'pending')->count(),
            'suspended_users' => User::where('write_suspended_until', '>', Carbon::now('UTC'))->count(),
            'banned_users' => User::where('status', 'banned')->count(),
        ];

        return view('admin.index', [
            'reports' => $reports,
            'pendingAppeals' => $pendingAppeals,
            'recentActions' => $recentActions,
            'stats' => $stats,
            'currentStatus' => $status,
        ]);
    }

    public function showReport(int $id)
    {
        $report = Report::with(['reporter.profile', 'resolver.profile', 'evidence'])->findOrFail($id);
        $targetInstance = $report->getTargetInstance();

        return view('admin.report', [
            'report' => $report,
            'targetInstance' => $targetInstance,
        ]);
    }

    public function resolveReport(Request $request, int $id)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:resolved,dismissed'],
            'resolution_note' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $admin = Auth::user();
        $report = Report::findOrFail($id);

        $report->update([
            'status' => $request->input('status'),
            'resolved_by' => $admin->id,
            'resolved_at' => Carbon::now('UTC'),
            'resolution_note' => $request->input('resolution_note'),
        ]);

        ModerationAction::create([
            'moderator_id' => $admin->id,
            'target_id' => $report->id,
            'target_type' => 'report',
            'action' => $request->input('status') === 'resolved' ? 'resolve_report' : 'dismiss_report',
            'reason' => $request->input('resolution_note'),
        ]);

        return redirect()->route('admin.index')->with('success', 'Signalement mis à jour.');
    }

    public function moderateUser(Request $request, int $userId)
    {
        $request->validate([
            'action' => ['required', 'string', 'in:suspend_24h,lift_suspension,ban,unban'],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $admin = Auth::user();
        $target = User::findOrFail($userId);
        $action = $request->input('action');
        $reason = $request->input('reason');

        DB::transaction(function () use ($admin, $target, $action, $reason) {
            match ($action) {
                'suspend_24h' => $this->antispamService->suspendUserWriting($target, 24, $reason),
                'lift_suspension' => $this->antispamService->liftSuspension($target),
                'ban' => $target->update(['status' => 'banned']),
                'unban' => $target->update(['status' => 'active']),
            };

            ModerationAction::create([
                'moderator_id' => $admin->id,
                'target_user_id' => $target->id,
                'target_id' => $target->id,
                'target_type' => 'user',
                'action' => $action,
                'reason' => $reason,
            ]);
        });

        return redirect()->back()->with('success', 'Mesure appliquée avec succès.');
    }

    public function moderatePost(Request $request, int $postId)
    {
        $request->validate([
            'action' => ['required', 'string', 'in:remove,restore'],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $admin = Auth::user();
        $post = Post::findOrFail($postId);
        $action = $request->input('action');
        $reason = $request->input('reason');

        $newStatus = $action === 'remove' ? 'removed' : 'published';

        DB::transaction(function () use ($admin, $post, $newStatus, $action, $reason) {
            $post->update(['status' => $newStatus]);

            ModerationAction::create([
                'moderator_id' => $admin->id,
                'target_user_id' => $post->user_id,
                'target_id' => $post->id,
                'target_type' => 'post',
                'action' => $action === 'remove' ? 'remove_post' : 'restore_post',
                'reason' => $reason,
            ]);
        });

        return redirect()->back()->with('success', 'Publication modérée.');
    }

    public function handleAppeal(Request $request, int $appealId)
    {
        $request->validate([
            'decision' => ['required', 'string', 'in:accept,reject'],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $admin = Auth::user();
        $appeal = Appeal::with('user')->findOrFail($appealId);
        $decision = $request->input('decision');
        $reason = $request->input('reason');

        DB::transaction(function () use ($admin, $appeal, $decision, $reason) {
            if ($decision === 'accept') {
                $this->antispamService->liftSuspension($appeal->user);
                $appeal->update([
                    'status' => 'accepted',
                    'reviewed_by' => $admin->id,
                    'reviewed_at' => Carbon::now('UTC'),
                ]);
            } else {
                $appeal->update([
                    'status' => 'rejected',
                    'reviewed_by' => $admin->id,
                    'reviewed_at' => Carbon::now('UTC'),
                ]);
            }

            ModerationAction::create([
                'moderator_id' => $admin->id,
                'target_user_id' => $appeal->user_id,
                'target_id' => $appeal->id,
                'target_type' => 'appeal',
                'action' => $decision === 'accept' ? 'accept_appeal' : 'reject_appeal',
                'reason' => $reason,
            ]);
        });

        return redirect()->back()->with('success', 'Décision enregistrée.');
    }
}
