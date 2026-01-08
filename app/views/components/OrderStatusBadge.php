<?php
$statusConfig = [
    'unconfirmed' => [
        'label' => 'Chờ xác nhận',
        'color' => 'bg-gray-100 text-gray-800 border-gray-200'
    ],
    'confirmed' => [
        'label' => 'Đã xác nhận',
        'color' => 'bg-blue-50 text-blue-700 border-blue-200'
    ],
    'shipping' => [
        'label' => 'Đang giao hàng',
        'color' => 'bg-yellow-50 text-yellow-700 border-yellow-200'
    ],
    'completed' => [
        'label' => 'Hoàn thành',
        'color' => 'bg-green-50 text-green-700 border-green-200'
    ],
    'cancelled' => [
        'label' => 'Đã hủy',
        'color' => 'bg-red-50 text-red-700 border-green-200'
    ]
];

$config = $statusConfig[$status] ?? [
    'label' => $status,
    'color' => 'bg-gray-100 text-gray-800'
];
?>

<span class="<?= $config['color'] ?> px-3 py-1 rounded-full text-xs font-semibold border">
    <?= $config['label'] ?>
</span>