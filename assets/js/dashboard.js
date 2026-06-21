/**
 * Cybersecurity Risk Management Dashboard
 * JavaScript Functions
 */

// Store chart instances globally
let riskLevelChart = null;
let incidentsChartInstance = null;

/**
 * Initialize dashboard charts via API (for refresh only)
 */
function initializeDashboardCharts() {
    // Check if we're on the dashboard page
    if (document.getElementById('riskLevelChart')) {
        loadChartData();
    }
}

/**
 * Check if inline charts are rendered (page has PHP-rendered charts)
 */
function hasInlineCharts() {
    return document.querySelector('script[data-inline-charts]') !== null;
}

/**
 * Fetch chart data from API and render charts
 */
function loadChartData() {
    fetch('api/chart-data.php')
        .then(response => response.json())
        .then(data => {
            renderRiskLevelChart(data.riskByLevel);
            renderIncidentsChart(data.incidentsByMonth);
        })
        .catch(error => {
            console.error('Error loading chart data:', error);
            alert('Failed to load chart data. Please refresh the page.');
        });
}

/**
 * Render Risk Level Chart (Pie Chart)
 */
function renderRiskLevelChart(data) {
    const canvas = document.getElementById('riskLevelChart');
    const ctx = canvas.getContext('2d');
    
    // Destroy existing chart if it exists
    if (riskLevelChart) {
        riskLevelChart.destroy();
    }
    const existingChart = Chart.getChart(canvas);
    if (existingChart) {
        existingChart.destroy();
    }
    
    riskLevelChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Critical Risk', 'High Risk', 'Medium Risk', 'Low Risk'],
            datasets: [{
                data: [data.critical || 0, data.high || 0, data.medium || 0, data.low || 0],
                backgroundColor: [
                    'rgba(239, 68, 68, 0.82)',
                    'rgba(245, 158, 11, 0.82)',
                    'rgba(34, 197, 94, 0.82)'
                ],
                borderColor: '#101b2d',
                borderWidth: 4,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#b6c2d2',
                        font: { size: 12, weight: '700' },
                        padding: 15,
                        usePointStyle: true
                    }
                },
                title: {
                    display: true,
                    text: 'Risk Level Distribution',
                    color: '#e5eefb',
                    font: { size: 14, weight: '700' }
                },
                tooltip: {
                    backgroundColor: '#07111f',
                    borderColor: 'rgba(34, 211, 238, 0.45)',
                    borderWidth: 1,
                    titleColor: '#e5eefb',
                    bodyColor: '#b6c2d2',
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + ' risks';
                        }
                    }
                }
            }
        }
    });
}

/**
 * Render Incidents per Month Chart (Bar Chart)
 */
function renderIncidentsChart(data) {
    const canvas = document.getElementById('incidentsChart');
    const ctx = canvas.getContext('2d');
    
    // Destroy existing chart if it exists
    if (incidentsChartInstance) {
        incidentsChartInstance.destroy();
    }
    const existingChart = Chart.getChart(canvas);
    if (existingChart) {
        existingChart.destroy();
    }
    
    // Prepare data for last 6 months
    const months = generateLast6Months();
    const counts = months.map(month => data[month] || 0);
    
    incidentsChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'Incidents',
                data: counts,
                backgroundColor: 'rgba(34, 211, 238, 0.68)',
                borderColor: '#22d3ee',
                borderWidth: 2,
                borderRadius: 6,
                hoverBackgroundColor: 'rgba(56, 189, 248, 0.9)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: 'x',
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        color: '#b6c2d2',
                        font: { size: 12, weight: '600' },
                        padding: 15
                    }
                },
                title: {
                    display: true,
                    text: 'Incident Volume, Last 6 Months',
                    color: '#e5eefb',
                    font: { size: 14, weight: '700' }
                },
                tooltip: {
                    backgroundColor: '#07111f',
                    borderColor: 'rgba(34, 211, 238, 0.45)',
                    borderWidth: 1,
                    titleColor: '#e5eefb',
                    bodyColor: '#b6c2d2',
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            return 'Incidents: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#8ea0b8',
                        stepSize: 1,
                        precision: 0
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.16)'
                    }
                },
                x: {
                    ticks: {
                        color: '#8ea0b8'
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

/**
 * Generate array of last 6 months in YYYY-MM format
 */
function generateLast6Months() {
    const months = [];
    const now = new Date();
    
    for (let i = 5; i >= 0; i--) {
        const date = new Date(now.getFullYear(), now.getMonth() - i, 1);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        months.push(year + '-' + month);
    }
    
    return months;
}

/**
 * Confirm deletion
 */
function confirmDelete(itemName) {
    return confirm('Are you sure you want to delete this ' + itemName + '? This action cannot be undone.');
}

/**
 * Show/Hide form validation feedback
 */
function validateForm(form) {
    // Basic validation
    const inputs = form.querySelectorAll('[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

/**
 * Format date input for display
 */
function formatDisplayDate(dateString) {
    const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

/**
 * Initialize all forms
 */
document.addEventListener('DOMContentLoaded', function() {
    // If inline charts are already rendered (from PHP), skip initial API load
    // Only set up the 5-minute refresh for updated data
    if (document.getElementById('riskLevelChart') && !hasInlineCharts()) {
        initializeDashboardCharts();
    }
    
    // Add form validation listeners
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Validation can be added here if needed
        });
    });
    
    // Refresh charts every 5 minutes on dashboard
    if (document.getElementById('riskLevelChart')) {
        setInterval(loadChartData, 5 * 60 * 1000);
    }
});

/**
 * Generate PDF-friendly print layout
 */
function printPage() {
    window.print();
}

/**
 * Export table to CSV
 */
function exportTableToCSV(filename) {
    const csv = [];
    const rows = document.querySelectorAll('table tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const csvRow = [];
        cols.forEach(col => {
            csvRow.push('"' + col.innerText.replace(/"/g, '""') + '"');
        });
        csv.push(csvRow.join(','));
    });
    
    downloadCSV(csv.join('\n'), filename);
}

/**
 * Helper function to download CSV
 */
function downloadCSV(csv, filename) {
    const csvFile = new Blob([csv], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.href = URL.createObjectURL(csvFile);
    downloadLink.download = filename;
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
