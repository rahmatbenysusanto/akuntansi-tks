<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Vendor;
use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use App\Models\SalesPayment;
use App\Models\PurchasePayment;
use App\Models\SalesPaymentAllocation;
use App\Models\PurchasePaymentAllocation;
use Illuminate\Support\Facades\DB;

class ARAPService
{
    public function kartuPiutang(int $customerId): array
    {
        $customer = Customer::findOrFail($customerId);
        $invoices = SalesInvoice::where('customer_id', $customerId)
            ->whereIn('status', ['posted', 'partial', 'paid'])
            ->orderBy('invoice_date')
            ->get();

        // BATCH: get all payment allocations for these invoices in ONE query
        $invoiceIds = $invoices->pluck('id')->toArray();
        $allocations = [];
        if (!empty($invoiceIds)) {
            $allocations = SalesPaymentAllocation::whereIn('sales_invoice_id', $invoiceIds)
                ->selectRaw('sales_invoice_id, COALESCE(SUM(amount), 0) as total_paid')
                ->groupBy('sales_invoice_id')
                ->pluck('total_paid', 'sales_invoice_id')
                ->toArray();
        }

        $invoiceData = $invoices->map(function ($inv) use ($allocations) {
            $totalPaid = (float) ($allocations[$inv->id] ?? 0);
            return [
                'invoice' => $inv,
                'total_paid' => $totalPaid,
                'outstanding' => $inv->total - $totalPaid,
            ];
        });

        $payments = SalesPayment::where('customer_id', $customerId)->orderBy('payment_date')->get();

        $saldo = $invoiceData->sum(fn($i) => $i['outstanding']);

        return [
            'customer' => $customer,
            'invoices' => $invoiceData,
            'payments' => $payments,
            'saldo' => $saldo,
        ];
    }

    public function kartuHutang(int $vendorId): array
    {
        $vendor = Vendor::findOrFail($vendorId);
        $invoices = PurchaseInvoice::where('vendor_id', $vendorId)
            ->whereIn('status', ['posted', 'partial', 'paid'])
            ->orderBy('invoice_date')
            ->get();

        // BATCH: get all payment allocations in ONE query
        $invoiceIds = $invoices->pluck('id')->toArray();
        $allocations = [];
        if (!empty($invoiceIds)) {
            $allocations = PurchasePaymentAllocation::whereIn('purchase_invoice_id', $invoiceIds)
                ->selectRaw('purchase_invoice_id, COALESCE(SUM(amount), 0) as total_paid')
                ->groupBy('purchase_invoice_id')
                ->pluck('total_paid', 'purchase_invoice_id')
                ->toArray();
        }

        $invoiceData = $invoices->map(function ($inv) use ($allocations) {
            $totalPaid = (float) ($allocations[$inv->id] ?? 0);
            return [
                'invoice' => $inv,
                'total_paid' => $totalPaid,
                'outstanding' => $inv->total - $totalPaid,
            ];
        });

        $payments = PurchasePayment::where('vendor_id', $vendorId)->orderBy('payment_date')->get();
        $saldo = $invoiceData->sum(fn($i) => $i['outstanding']);

        return [
            'vendor' => $vendor,
            'invoices' => $invoiceData,
            'payments' => $payments,
            'saldo' => $saldo,
        ];
    }

    public function agingPiutang(): array
    {
        $customers = Customer::where('is_active', true)->get();

        // BATCH: get all invoices for all customers in ONE query
        $allInvoices = SalesInvoice::whereIn('status', ['posted', 'partial'])
            ->whereIn('customer_id', $customers->pluck('id')->toArray())
            ->orderBy('invoice_date')
            ->get()
            ->groupBy('customer_id');

        // BATCH: get all payment allocations in ONE query
        $allInvoiceIds = $allInvoices->flatten(1)->pluck('id')->toArray();
        $allocations = [];
        if (!empty($allInvoiceIds)) {
            $allocations = SalesPaymentAllocation::whereIn('sales_invoice_id', $allInvoiceIds)
                ->selectRaw('sales_invoice_id, COALESCE(SUM(amount), 0) as total_paid')
                ->groupBy('sales_invoice_id')
                ->pluck('total_paid', 'sales_invoice_id')
                ->toArray();
        }

        $result = [];
        foreach ($customers as $customer) {
            $invoices = $allInvoices->get($customer->id, collect());

            $aging = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '>90' => 0];
            $totalOutstanding = 0;

            foreach ($invoices as $inv) {
                $paid = (float) ($allocations[$inv->id] ?? 0);
                $outstanding = $inv->total - $paid;
                if ($outstanding <= 0) continue;

                $totalOutstanding += $outstanding;

                // Days overdue = positive if past due, negative/zero if not yet due
                $daysOverdue = (int) now()->startOfDay()->diffInDays($inv->due_date->startOfDay(), false);
                // Only age invoices that are actually past due (daysOverdue < 0)
                $daysPastDue = -$daysOverdue;

                if ($daysPastDue <= 0) continue; // not yet past due — skip aging
                if ($daysPastDue <= 30) $aging['0-30'] += $outstanding;
                elseif ($daysPastDue <= 60) $aging['31-60'] += $outstanding;
                elseif ($daysPastDue <= 90) $aging['61-90'] += $outstanding;
                else $aging['>90'] += $outstanding;
            }

            if ($totalOutstanding > 0) {
                $result[] = [
                    'customer' => $customer,
                    'aging' => $aging,
                    'total' => $totalOutstanding,
                ];
            }
        }

        return $result;
    }

    public function agingHutang(): array
    {
        $vendors = Vendor::where('is_active', true)->get();

        // BATCH: get all invoices for all vendors in ONE query
        $allInvoices = PurchaseInvoice::whereIn('status', ['posted', 'partial'])
            ->whereIn('vendor_id', $vendors->pluck('id')->toArray())
            ->orderBy('invoice_date')
            ->get()
            ->groupBy('vendor_id');

        // BATCH: get all payment allocations in ONE query
        $allInvoiceIds = $allInvoices->flatten(1)->pluck('id')->toArray();
        $allocations = [];
        if (!empty($allInvoiceIds)) {
            $allocations = PurchasePaymentAllocation::whereIn('purchase_invoice_id', $allInvoiceIds)
                ->selectRaw('purchase_invoice_id, COALESCE(SUM(amount), 0) as total_paid')
                ->groupBy('purchase_invoice_id')
                ->pluck('total_paid', 'purchase_invoice_id')
                ->toArray();
        }

        $result = [];
        foreach ($vendors as $vendor) {
            $invoices = $allInvoices->get($vendor->id, collect());

            $aging = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '>90' => 0];
            $totalOutstanding = 0;

            foreach ($invoices as $inv) {
                $paid = (float) ($allocations[$inv->id] ?? 0);
                $outstanding = $inv->total - $paid;
                if ($outstanding <= 0) continue;

                $totalOutstanding += $outstanding;

                // Days overdue = positive if past due, negative/zero if not yet due
                $daysOverdue = (int) now()->startOfDay()->diffInDays($inv->due_date->startOfDay(), false);
                // Only age invoices that are actually past due (daysOverdue < 0)
                $daysPastDue = -$daysOverdue;

                if ($daysPastDue <= 0) continue; // not yet past due — skip aging
                if ($daysPastDue <= 30) $aging['0-30'] += $outstanding;
                elseif ($daysPastDue <= 60) $aging['31-60'] += $outstanding;
                elseif ($daysPastDue <= 90) $aging['61-90'] += $outstanding;
                else $aging['>90'] += $outstanding;
            }

            if ($totalOutstanding > 0) {
                $result[] = [
                    'vendor' => $vendor,
                    'aging' => $aging,
                    'total' => $totalOutstanding,
                ];
            }
        }

        return $result;
    }
}
