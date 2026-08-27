<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Kuota Cuti Tahunan
    |--------------------------------------------------------------------------
    |
    | Jumlah hari cuti tahunan per karyawan. Dipakai untuk menghitung sisa
    | cuti (leave balance) di dashboard karyawan: kuota dikurangi cuti
    | berstatus "approved" pada tahun berjalan.
    |
    | Belum ada kolom kuota per-karyawan di database, jadi nilai ini berlaku
    | untuk semua karyawan. Pindahkan ke kolom di employee_profiles kalau
    | nanti kuotanya perlu berbeda per orang.
    |
    */

    'annual_leave_quota' => (int) env('HRIS_ANNUAL_LEAVE_QUOTA', 12),

];
