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
            ->get()
            ->map(function ($inv) {
                $totalPaid = SalesPaymentAllocation::where('sales_invoice_id', $inv->id)->sum('amount');
                return [
                    'invoice' => $inv,
                    'total_paid' => $totalPaid,
                    'outstanding' => $inv->total - $totalPaid,
                ];
            });

        $payments = SalesPayment::where('customer_id', $customerId)->orderBy('payment_date')->get();

        $saldo = $invoices->sum(fn($i) => $i['outstanding']);

        return [
            'customer' => $customer,
            'invoices' => $invoices,
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
            ->get()
            ->map(function ($inv) {
                $totalPaid = PurchasePaymentAllocation::where('purchase_invoice_id', $inv->id)->sum('amount');
                return [
                    'invoice' => $inv,
                    'total_paid' => $totalPaid,
                    'outstanding' => $inv->total - $totalPaid,
                ];
            });

        $payments = PurchasePayment::where('vendor_id', $vendorId)->orderBy('payment_date')->get();
        $saldo = $invoices->sum(fn($i) => $i['outstanding']);

        return [
            'vendor' => $vendor,
            'invoices' => $invoices,
            'payments' => $payments,
            'saldo' => $saldo,
        ];
    }

    public function agingPiutang(): array
    {
        $customers = Customer::where('is_active', true)->get();
        $result = [];

        foreach ($customers as $customer) {
            $invoices = SalesInvoice::where('customer_id', $customer->id)
                ->whereIn('status', ['posted', 'partial'])
                ->get();

            $aging = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '>90' => 0];
            $totalOutstanding = 0;

            foreach ($invoices as $inv) {
                $paid = SalesPaymentAllocation::where('sales_invoice_id', $inv->id)->sum('amount');
                $outstanding = $inv->total - $paid;
                if ($outstanding <= 0) continue;

                $totalOutstanding += $outstanding;
                $daysOverdue = now()->diffInDays($inv->due_date);

                if ($daysOverdue <= 30) $aging['0-30'] += $outstanding;
                elseif ($daysOverdue <= 60) $aging['31-60'] += $outstanding;
                elseif ($daysOverdue <= 90) $aging['61-90'] += $outstanding;
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
        $result = [];

        foreach ($vendors as $vendor) {
            $invoices = PurchaseInvoice::where('vendor_id', $vendor->id)
                ->whereIn('status', ['posted', 'partial'])
                ->get();

            $aging = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '>90' => 0];
            $totalOutstanding = 0;

            foreach ($invoices as $inv) {
                $paid = PurchasePaymentAllocation::where('purchase_invoice_id', $inv->id)->sum('amount');
                $outstanding = $inv->total - $paid;
                if ($outstanding <= 0) continue;

                $totalOutstanding += $outstanding;
                $daysOverdue = now()->diffInDays($inv->due_date);

                if ($daysOverdue <= 30) $aging['0-30'] += $outstanding;
                elseif ($daysOverdue <= 60) $aging['31-60'] += $outstanding;
                elseif ($daysOverdue <= 90) $aging['61-90'] += $outstanding;
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
