<?php
// ----- Tampilkan flash toast notification -----
if (isset($_SESSION['flash'])) {
    $tipe = $_SESSION['flash']['tipe'] ?? 'success';
    $pesan = $_SESSION['flash']['pesan'] ?? '';

    $config = [
        'success' => ['bg' => '#dcfce7', 'text' => '#15803d', 'border' => '#bbf7d0', 'icon' => '&#10003;'],
        'danger'  => ['bg' => '#fee2e2', 'text' => '#b91c1c', 'border' => '#fca5a5', 'icon' => '&#10007;'],
        'warning' => ['bg' => '#fef3c7', 'text' => '#d97706', 'border' => '#fde68a', 'icon' => '&#9888;'],
        'info'    => ['bg' => '#dbeafe', 'text' => '#2563eb', 'border' => '#bfdbfe', 'icon' => '&#8505;'],
    ];

    $c = $config[$tipe] ?? $config['success'];

    echo "
    <div id='toast' style='
        position: fixed;
        bottom: 24px;
        right: 24px;
        background-color: {$c['bg']};
        color: {$c['text']};
        padding: 14px 20px;
        padding-left: 16px;
        border-radius: 8px;
        border-left: 4px solid {$c['text']};
        border: 1px solid {$c['border']};
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        z-index: 9999;
        font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
        max-width: 420px;
        animation: toastIn 0.35s ease, toastOut 0.35s ease 3.2s forwards;
    '>
        <span style='font-size: 16px; font-weight: 700;'>{$c['icon']}</span>
        <span><?= htmlspecialchars($pesan); ?></span>
    </div>

    <style>
        @keyframes toastIn {
            from { transform: translateX(120%); opacity: 0; }
            to   { transform: translateX(0); opacity: 1; }
        }
        @keyframes toastOut {
            from { transform: translateX(0); opacity: 1; }
            to   { transform: translateX(120%); opacity: 0; }
        }
    </style>

    <script>
        setTimeout(function(){
            var t = document.getElementById('toast');
            if(t) setTimeout(function(){ t.remove(); }, 350);
        }, 3200);
    </script>
    ";

    unset($_SESSION['flash']);
}
?>