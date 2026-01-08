<?php
/**
 * Component hiển thị trạng thái thanh toán
 * @param string $status - Trạng thái thanh toán (unpaid, paid)
 * @param string $size - Kích thước (sm, md, lg)
 */

$statusConfig = [
    'unpaid' => [
        'label' => 'Chưa thanh toán',
        'color' => 'bg-orange-50 text-orange-700 border-orange-200',
        'icon' => 'payments'
    ],
    'paid' => [
        'label' => 'Đã thanh toán',
        'color' => 'bg-green-50 text-green-700 border-green-200',
        'icon' => 'check_circle'
    ]
];

$config = $statusConfig[$status] ?? [
    'label' => ucfirst($status),
    'color' => 'bg-gray-100 text-gray-800 border-gray-300',
    'icon' => 'help'
];

$sizeClasses = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-3 py-1 text-sm',
    'lg' => 'px-4 py-1.5 text-base'
];
$sizeClass = $sizeClasses[$size ?? 'md'] ?? $sizeClasses['md'];
$showIcon = $showIcon ?? true;
?>

<span class="<?= $config['color'] ?> <?= $sizeClass ?> rounded-full font-semibold border inline-flex items-center gap-1.5">
    <?php if($showIcon): ?>
        <span class="material-symbols-outlined !text-base"><?= $config['icon'] ?></span>
    <?php endif; ?>
    <?= $config['label'] ?>
</span>