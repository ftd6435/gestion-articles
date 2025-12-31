// public/js/chart.js
(function() {
    let currentChart = null;
    let isUpdating = false;

    // Main chart update function
    function updateChart() {
        // Prevent multiple simultaneous updates
        if (isUpdating) {
            // console.log('Chart update already in progress');
            return;
        }

        isUpdating = true;

        try {
            // console.log('=== Starting chart update ===');

            // Get current data from window
            const chartData = window.chartData;
            const chartType = window.chartType;

            // console.log('Current window data:', {
            //     chartType: chartType,
            //     chartData: chartData,
            //     isArray: Array.isArray(chartData),
            //     length: chartData?.length || 0
            // });

            // Validate data
            if (!chartData || !Array.isArray(chartData)) {
                console.error('Invalid chart data:', chartData);
                showMessage('Données de graphique invalides');
                return;
            }

            if (!chartType) {
                console.error('No chart type specified');
                showMessage('Type de graphique non spécifié');
                return;
            }

            // Get canvas
            const canvas = document.getElementById('chartCanvas');
            if (!canvas) {
                console.error('Chart canvas not found');
                return;
            }

            // Destroy existing chart
            if (currentChart) {
                try {
                    currentChart.destroy();
                } catch (error) {
                    console.log('Error destroying old chart:', error);
                }
                currentChart = null;
            }

            // Create new chart based on type
            if (chartType === 'monthly' || chartType === 'daily') {
                createSalesChart(canvas, chartData, chartType);
            } else if (chartType === 'status') {
                createStatusChart(canvas, chartData);
            } else {
                showMessage('Type de graphique non supporté: ' + chartType);
            }

        } catch (error) {
            console.error('Error in updateChart:', error);
            showMessage('Erreur lors de la création du graphique');
        } finally {
            isUpdating = false;
            // console.log('=== Chart update complete ===');
        }
    }

    function createSalesChart(canvas, chartData, chartType) {
        // console.log('Creating sales chart with', chartData.length, 'items');

        // Validate data structure
        if (!chartData.every(item => item && typeof item === 'object')) {
            console.error('Invalid sales chart data structure:', chartData);
            showMessage('Structure de données invalide pour les ventes');
            return;
        }

        const labels = chartData.map(item => item.label || '');
        const totals = chartData.map(item => parseFloat(item.total) || 0);
        const paid = chartData.map(item => parseFloat(item.paid) || 0);
        const due = chartData.map(item => parseFloat(item.due) || 0);

        // console.log('Sales chart data:', { labels, totals, paid, due });

        // Check if we have any non-zero data
        const hasData = totals.some(v => v > 0) || paid.some(v => v > 0) || due.some(v => v > 0);
        if (!hasData) {
            showMessage('Aucune donnée de vente disponible');
            return;
        }

        currentChart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Ventes',
                        data: totals,
                        backgroundColor: 'rgba(13, 110, 253, 0.7)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Payé',
                        data: paid,
                        backgroundColor: 'rgba(25, 135, 84, 0.7)',
                        borderColor: 'rgba(25, 135, 84, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Dû',
                        data: due,
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += new Intl.NumberFormat('fr-FR').format(context.parsed.y) + ' FG';
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR').format(value) + ' FG';
                            }
                        }
                    }
                }
            }
        });

        // console.log('Sales chart created successfully');
    }

    function createStatusChart(canvas, chartData) {
        // console.log('Creating status chart with', chartData.length, 'items');

        // Validate data structure
        if (!chartData.every(item => item && typeof item === 'object')) {
            console.error('Invalid status chart data structure:', chartData);
            showMessage('Structure de données invalide pour le statut');
            return;
        }

        // Filter out items with total = 0
        const filteredData = chartData.filter(item => {
            const total = parseFloat(item.total) || 0;
            return total > 0;
        });

        // console.log('Filtered status data:', filteredData);

        if (filteredData.length === 0) {
            showMessage('Aucune donnée de statut avec montant > 0');
            return;
        }

        const labels = filteredData.map(item => item.label || '');
        const values = filteredData.map(item => parseFloat(item.total) || 0);
        const colors = filteredData.map(item => item.color || getStatusColor(item.label || ''));

        currentChart = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return `${label}: ${new Intl.NumberFormat('fr-FR').format(value)} FG (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // console.log('Status chart created successfully');
    }

    function showMessage(message) {
        const canvas = document.getElementById('chartCanvas');
        if (!canvas) return;

        // Destroy existing chart
        if (currentChart) {
            try {
                currentChart.destroy();
            } catch (error) {
                console.log('Error destroying chart:', error);
            }
            currentChart = null;
        }

        // Create message chart
        currentChart = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: [message],
                datasets: [{
                    data: [100],
                    backgroundColor: ['rgba(200, 200, 200, 0.5)']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
    }

    function getStatusColor(label) {
        const status = (label || '').toUpperCase();
        switch(status) {
            case 'PAYÉE':
            case 'PAYEE': return 'rgba(25, 135, 84, 0.8)';
            case 'PARTIELLE': return 'rgba(255, 193, 7, 0.8)';
            case 'IMPAYÉE':
            case 'IMPAYEE': return 'rgba(220, 53, 69, 0.8)';
            case 'ANNULÉE':
            case 'ANNULEE': return 'rgba(108, 117, 125, 0.8)';
            default: return 'rgba(128, 128, 128, 0.8)';
        }
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // console.log('Chart system initialized');

        // Initial chart creation
        setTimeout(() => {
            if (window.updateChart) {
                window.updateChart();
            }
        }, 800); // Longer delay to ensure everything is loaded
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (currentChart) {
            currentChart.resize();
        }
    });

    // Expose update function
    window.updateChart = updateChart;

    // Debug: Expose data for inspection
    window.getChartData = function() {
        return {
            chartData: window.chartData,
            chartType: window.chartType
        };
    };

})();

