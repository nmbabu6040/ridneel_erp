<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\SalesTarget;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TargetController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));

        $targets = SalesTarget::where('period_month', $month . '-01')
            ->orderBy('target_type')
            ->get();

        return view('kpi.targets.index', compact('targets', 'month'));
    }

    public function create()
    {
        return view('kpi.targets.form', [
            'target'   => new SalesTarget(),
            'srUsers'  => User::whereHas('role', fn ($q) => $q->where('slug', 'sr'))->orderBy('name')->get(),
            'dealers'  => Partner::type('dealer')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'target_type'   => ['required', Rule::in(['sr', 'dealer'])],
            'target_ref_id' => ['required', 'integer'],
            'period_month'  => ['required', 'date_format:Y-m'],
            'target_amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $periodDate = $data['period_month'] . '-01';

        $exists = SalesTarget::where('target_type', $data['target_type'])
            ->where('target_ref_id', $data['target_ref_id'])
            ->where('period_month', $periodDate)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['target_amount' => __('app.target_already_set')]);
        }

        SalesTarget::create([
            'target_type'   => $data['target_type'],
            'target_ref_id' => $data['target_ref_id'],
            'period_month'  => $periodDate,
            'target_amount' => $data['target_amount'],
            'created_by'    => $request->user()->id,
        ]);

        return redirect()->route('kpi.targets.index', ['month' => $data['period_month']])
            ->with('success', __('app.target_saved'));
    }

    public function edit(SalesTarget $target)
    {
        return view('kpi.targets.edit', compact('target'));
    }

    public function update(Request $request, SalesTarget $target): RedirectResponse
    {
        $data = $request->validate([
            'target_amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $target->update($data);

        return redirect()->route('kpi.targets.index', ['month' => $target->period_month->format('Y-m')])
            ->with('success', __('app.target_saved'));
    }

    public function destroy(SalesTarget $target): RedirectResponse
    {
        $month = $target->period_month->format('Y-m');
        $target->delete();

        return redirect()->route('kpi.targets.index', ['month' => $month])->with('success', __('app.target_deleted'));
    }
}
