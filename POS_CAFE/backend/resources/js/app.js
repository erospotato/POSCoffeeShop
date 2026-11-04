import './bootstrap';

import Chart from 'chart.js/auto';
window.Chart = Chart;

// Global Chart Dark & Purple Theme Defaults
if (window.Chart) {
    Chart.defaults.color = '#9f97c4';
    Chart.defaults.borderColor = 'rgba(168, 85, 247, 0.12)';
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
}

import Alpine from 'alpinejs';
window.Alpine = Alpine;

Alpine.start();
