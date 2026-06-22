<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\FixedAsset;
use App\Services\DepreciationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FixedAssetController extends Controller
{
    public function index()
    {
        $assets = FixedAsset::with(['account', 'accumulatedDepreciationAccount', 'depreciationExpenseAccount'])->latest()->paginate(20);
        return view('fixed-assets.index', compact('assets'));
    }

    public function create()
    {
        $assetAccounts = Account::where('category', 'aktiva')->where('is_header', false)->orderBy('code')->get();
        return view('fixed-assets.form', compact('assetAccounts'));
    }

    public function store(Request $request, DepreciationService $depreciationService)
    {
        $companyId = auth()->user()->current_company_id;

        $validated = $request->validate([
            'asset_code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where('company_id', $companyId),
            ],
            'accumulated_depreciation_account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where('company_id', $companyId),
            ],
            'depreciation_expense_account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where('company_id', $companyId),
            ],
            'acquisition_date' => 'required|date',
            'acquisition_cost' => 'required|numeric|min:0',
            'useful_life_months' => 'required|integer|min:1',
            'depreciation_method' => 'nullable|in:straight_line,declining_balance',
            'salvage_value' => 'nullable|numeric|min:0',
        ]);

        $validated['salvage_value'] ??= 0;
        $validated['depreciation_method'] ??= 'straight_line';

        $asset = FixedAsset::create($validated);
        $depreciationService->generateSchedule($asset);

        return redirect()->route('fixed-assets.index')->with('success', 'Aset tetap berhasil ditambahkan, jadwal depresiasi digenerate.');
    }

    public function show(FixedAsset $fixedAsset)
    {
        $schedules = $fixedAsset->schedules()->orderBy('period_no')->get();
        return view('fixed-assets.show', compact('fixedAsset', 'schedules'));
    }

    public function postDepreciation(Request $request, DepreciationService $service)
    {
        $result = $service->postMonthlyDepreciation(
            auth()->user()->current_company_id,
            $request->year ?? now()->year,
            $request->month ?? now()->month
        );

        return redirect()->route('fixed-assets.index')->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }
}
