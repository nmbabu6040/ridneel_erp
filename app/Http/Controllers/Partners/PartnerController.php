<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'dealer');
        abort_unless(in_array($type, ['dealer', 'retailer', 'mechanic', 'supplier'], true), 404);

        $query = Partner::type($type)->with('linkedDealer')->latest();

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $partners = $query->paginate(15)->withQueryString();

        return view('partners.index', compact('partners', 'type'));
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'dealer');
        abort_unless(in_array($type, ['dealer', 'retailer', 'mechanic', 'supplier'], true), 404);

        return view('partners.form', [
            'partner' => new Partner(['type' => $type]),
            'type'    => $type,
            'dealers' => $type === 'retailer' ? Partner::type('dealer')->where('is_active', true)->orderBy('name')->get() : collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Partner::create($data);

        return redirect()->route('partners.index', ['type' => $data['type']])
            ->with('success', __('app.partner_created'));
    }

    public function edit(Partner $partner)
    {
        return view('partners.form', [
            'partner' => $partner,
            'type'    => $partner->type,
            'dealers' => $partner->type === 'retailer' ? Partner::type('dealer')->where('is_active', true)->orderBy('name')->get() : collect(),
        ]);
    }

    public function update(Request $request, Partner $partner): RedirectResponse
    {
        $data = $this->validated($request, $partner->id);

        $partner->update($data);

        return redirect()->route('partners.index', ['type' => $partner->type])
            ->with('success', __('app.partner_updated'));
    }

    public function destroy(Partner $partner): RedirectResponse
    {
        if ($partner->retailers()->exists()) {
            return back()->with('error', __('app.partner_in_use'));
        }

        $type = $partner->type;
        $partner->delete();

        return redirect()->route('partners.index', ['type' => $type])
            ->with('success', __('app.partner_deleted'));
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $type = $request->input('type', 'dealer');

        $data = $request->validate([
            'type'             => ['required', Rule::in(['dealer', 'retailer', 'mechanic', 'supplier'])],
            'name'             => ['required', 'string', 'max:150'],
            'business_name'    => ['nullable', 'string', 'max:150'],
            'phone'            => ['required', 'string', 'max:20', Rule::unique('partners', 'phone')->where('type', $type)->ignore($ignoreId)],
            'email'            => ['nullable', 'email', 'max:150'],
            'address'          => ['nullable', 'string', 'max:255'],
            'territory'        => ['nullable', 'string', 'max:150'],
            'linked_dealer_id' => ['nullable', 'exists:partners,id', 'required_if:type,retailer'],
            'opening_balance'  => ['nullable', 'numeric'],
            'credit_limit'     => ['nullable', 'numeric', 'min:0'],
            'is_active'        => ['nullable', 'boolean'],
        ]);

        $data['is_active']       = $request->boolean('is_active');
        $data['opening_balance'] = $data['opening_balance'] ?? 0;
        $data['credit_limit']    = $data['credit_limit'] ?? 0;

        if ($type !== 'retailer') {
            $data['linked_dealer_id'] = null;
        }

        return $data;
    }
}
