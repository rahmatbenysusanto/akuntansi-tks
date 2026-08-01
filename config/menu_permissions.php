<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Menu Permission Keys
    |--------------------------------------------------------------------------
    |
    | Key => [Label, Group]
    |
    | Group names match sidebar section headers exactly.
    | Keys correspond to route name prefixes used in sidebar routeIs() checks.
    | Dashboard is intentionally excluded — always visible to all users.
    | Admin-only menus (users, activity-logs, data-reset) are also excluded
    | from this list — they use isAdmin() check directly.
    |
    */
    'menus' => [
        'customers.index'            => ['Customer',             'Master Data'],
        'vendors.index'              => ['Vendor',               'Master Data'],
        'accounts.index'             => ['Chart of Account',     'Master Data'],
        'accounting-periods.index'   => ['Periode Akuntansi',    'Master Data'],
        'opening-balances.index'     => ['Saldo Awal',           'Master Data'],

        'journal-entries.index'      => ['Jurnal Umum',          'Transaksi'],
        'sales.index'                => ['Sales Invoice',        'Transaksi'],
        'purchases.index'            => ['Purchase Invoice',     'Transaksi'],

        'fixed-assets.index'         => ['Aset Tetap',           'Operasional'],
        'arap.kartu-piutang'         => ['Kartu Piutang',        'Operasional'],
        'arap.kartu-hutang'          => ['Kartu Hutang',         'Operasional'],
        'items.index'                => ['Inventory',            'Operasional'],
        'overtime-requests.index'    => ['Overtime (WH)',        'Operasional'],
        'loans.index'                => ['Cicilan',              'Operasional'],

        'employees.index'            => ['Data Karyawan',        'HR'],
        'attendances.index'          => ['Absensi',              'HR'],
        'employee-salaries.index'    => ['Setup Gaji',           'HR'],
        'payroll.index'              => ['Penggajian',           'HR'],
        'cash-advances.index'        => ['Kasbon',               'HR'],

        'tax.index'                  => ['Transaksi Pajak',      'Pajak'],
        'tax.ppn'                    => ['Rekap PPN',            'Pajak'],
        'exchange-rates.index'       => ['Kurs Valas',           'Pajak'],

        'reports.general-ledger'     => ['Buku Besar',           'Laporan'],
        'reports.trial-balance'      => ['Neraca Lajur',         'Laporan'],
        'reports.income-statement'   => ['Laba Rugi',            'Laporan'],
        'reports.balance-sheet'      => ['Neraca',               'Laporan'],
        'reports.financial-highlight' => ['Financial Highlight', 'Laporan'],
    ],

];
