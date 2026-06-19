<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index() { $items = Item::orderBy('name')->paginate(20); return view('inventory.index', compact('items')); }
    public function create() { $accounts = Account::where('is_header', false)->orderBy('code')->get(); return view('inventory.form', compact('accounts')); }
    public function store(Request $r) { Item::create($r->all()); return redirect()->route('items.index')->with('success', 'Item created.'); }
    public function edit(Item $item) { $accounts = Account::where('is_header', false)->orderBy('code')->get(); return view('inventory.form', compact('item', 'accounts')); }
    public function update(Request $r, Item $item) { $item->update($r->all()); return redirect()->route('items.index')->with('success', 'Item updated.'); }
}
