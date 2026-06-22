<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index() { $items = Item::orderBy('name')->paginate(20); return view('inventory.index', compact('items')); }
    public function create() { $accounts = Account::where('is_header', false)->orderBy('code')->get(); return view('inventory.form', compact('accounts')); }

    public function store(Request $r)
    {
        $validated = $r->validate([
            'sku' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:20',
            'category' => 'nullable|string|max:100',
            'costing_method' => 'nullable|in:fifo,average',
            'inventory_account_id' => 'nullable|exists:accounts,id',
            'cogs_account_id' => 'nullable|exists:accounts,id',
            'sales_account_id' => 'nullable|exists:accounts,id',
            'min_stock' => 'nullable|integer|min:0',
        ]);

        Item::create($validated);
        return redirect()->route('items.index')->with('success', 'Item created.');
    }

    public function edit(Item $item)
    {
        $accounts = Account::where('is_header', false)->orderBy('code')->get();
        return view('inventory.form', compact('item', 'accounts'));
    }

    public function update(Request $r, Item $item)
    {
        $validated = $r->validate([
            'sku' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:20',
            'category' => 'nullable|string|max:100',
            'costing_method' => 'nullable|in:fifo,average',
            'inventory_account_id' => 'nullable|exists:accounts,id',
            'cogs_account_id' => 'nullable|exists:accounts,id',
            'sales_account_id' => 'nullable|exists:accounts,id',
            'min_stock' => 'nullable|integer|min:0',
        ]);

        $item->update($validated);
        return redirect()->route('items.index')->with('success', 'Item updated.');
    }
}
