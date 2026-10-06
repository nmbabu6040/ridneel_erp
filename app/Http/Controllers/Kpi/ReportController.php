<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\SalesTarget;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * SR-type targets only, ranked by achievement — this is both the
     * "KPI report" and the "leaderboard/reward" view: the ranking
     * itself, with the top 3 visually badged, *is* the reward display
     * (no separate points/badges table needed for a first version).
     * A manager (kpi.manage) sees every SR; an SR with only kpi.view
     * sees just their own row — same permission, scoped by identity.
     */
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $user  = $request->user();

        $query = SalesTarget::where('target_type', 'sr')->where('period_month', $month . '-01');

        if (!$user->hasPermission('kpi.manage')) {
            $query->where('target_ref_id', $user->id);
        }

        $targets = $query->get()->map(function (SalesTarget $t) {
            $achievement = $t->achievement();
            $srUser      = $t->subject();

            return (object) [
                'target'      => $t,
                'sr'          => $srUser,
                'achievement' => $achievement,
                'percent'     => $t->achievementPercent(),
                'commission'  => $srUser && $srUser->commission_rate
                    ? round($achievement * (float) $srUser->commission_rate / 100, 2)
                    : null,
            ];
        })->sortByDesc('achievement')->values();

        $dealerTargets = SalesTarget::where('target_type', 'dealer')
            ->where('period_month', $month . '-01')
            ->get()
            ->map(fn (SalesTarget $t) => (object) [
                'target'      => $t,
                'dealer'      => $t->subject(),
                'achievement' => $t->achievement(),
                'percent'     => $t->achievementPercent(),
            ])
            ->sortByDesc('achievement')
            ->values();

        return view('kpi.report', [
            'month'         => $month,
            'rows'          => $targets,
            'dealerRows'    => $user->hasPermission('kpi.manage') ? $dealerTargets : collect(),
            'isManagerView' => $user->hasPermission('kpi.manage'),
        ]);
    }
}
