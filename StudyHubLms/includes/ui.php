<?php
function statCard($icon, $label, $value, $tone='primary', $meta='') {
    $toneClass = match ($tone) {
        'warning' => 'warning', 'danger' => 'danger', 'success' => 'success', default => 'primary'
    };
    return '<div class="card stat-card"><div class="d-flex justify-content-between align-items-start"><div><div class="stat-label mb-1">'.e($label).'</div><div class="stat-number">'.e((string)$value).'</div>'.($meta?'<div class="small text-muted mt-1">'.e($meta).'</div>':'').'</div><div class="stat-icon bg-soft-'.$toneClass.' text-'.$toneClass.'"><i class="bi bi-'.$icon.'"></i></div></div></div>';
}
function badgeSoft($text,$tone='secondary'){ return '<span class="badge badge-soft-'.e($tone).'">'.e($text).'</span>'; }
function courseThumb(string $title, int $index = 0): string {
    $icons = ['code-slash','filetype-python','database','palette','diagram-3','cpu'];
    $classes = ['', 'alt-1', 'alt-2', 'alt-3', 'alt-4'];
    $i = $index % count($icons);
    $cls = $classes[$index % count($classes)];
    return '<div class="course-thumb '.$cls.'"><i class="bi bi-'.$icons[$i].' thumb-icon"></i><span class="thumb-label">'.e($title).'</span></div>';
}
