<?php

function badgeStatus($status) {
    $peta = [
        'menunggu_acc' => ['class' => 'status-pending', 'label' => 'Menunggu Persetujuan'],
        'siap_diambil' => ['class' => 'status-active', 'label' => 'Siap Diambil'],
        'disewa'       => ['class' => 'status-disewa', 'label' => 'Sedang Disewa'],
        'selesai'      => ['class' => 'status-success', 'label' => 'Selesai'],
        'ditolak'      => ['class' => 'status-danger', 'label' => 'Ditolak'],
    ];

    $s = $peta[$status] ?? ['class' => 'status-pending', 'label' => ucfirst($status)];

    return "<span class=\"badge-status {$s['class']}\">" . htmlspecialchars($s['label']) . "</span>";
}
