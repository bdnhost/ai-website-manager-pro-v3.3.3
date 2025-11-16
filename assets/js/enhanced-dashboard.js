/**
 * Enhanced Dashboard JavaScript
 * Modern, Interactive, Real-time Dashboard Functionality
 *
 * @package AI_Manager_Pro
 * @version 3.3.4
 */

(function($) {
    'use strict';

    /**
     * Enhanced Dashboard Controller
     */
    const EnhancedDashboard = {

        /**
         * Configuration
         */
        config: {
            brandId: null,
            nonce: null,
            ajaxUrl: null,
            primaryColor: '#2563eb',
            accentColor: '#10b981',
            refreshInterval: 60000, // 1 minute
            performanceChart: null
        },

        /**
         * Initialize dashboard
         */
        init: function() {
            this.loadConfig();
            this.bindEvents();
            this.initializeCharts();
            this.loadInitialData();
            this.startAutoRefresh();
        },

        /**
         * Load configuration from hidden fields
         */
        loadConfig: function() {
            this.config.brandId = $('#ai-manager-brand-id').val();
            this.config.nonce = $('#ai-manager-nonce').val();
            this.config.ajaxUrl = $('#ai-manager-ajax-url').val();
            this.config.primaryColor = $('#ai-manager-primary-color').val() || '#2563eb';
            this.config.accentColor = $('#ai-manager-accent-color').val() || '#10b981';
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            const self = this;

            // Brand switcher
            $('#active-brand-select').on('change', function() {
                self.switchBrand($(this).val());
            });

            // Refresh features button
            $('.refresh-features-btn').on('click', function(e) {
                e.preventDefault();
                self.refreshFeatures($(this).data('brand-id'));
            });

            // Generate topics button
            $('.generate-topics-btn').on('click', function(e) {
                e.preventDefault();
                self.generateTopics($(this).data('brand-id'));
            });

            // Detect trends button
            $('.detect-trends-btn').on('click', function(e) {
                e.preventDefault();
                self.detectTrends($(this).data('brand-id'));
            });

            // Update performance button
            $('.update-performance-btn').on('click', function(e) {
                e.preventDefault();
                self.updatePerformance($(this).data('brand-id'));
            });

            // Analyze gaps button
            $('.analyze-gaps-btn').on('click', function(e) {
                e.preventDefault();
                self.analyzeGaps($(this).data('brand-id'));
            });

            // Performance period selector
            $('.performance-period-select').on('change', function() {
                self.updatePerformanceChart($(this).val());
            });
        },

        /**
         * Initialize Chart.js charts
         */
        initializeCharts: function() {
            const canvas = document.getElementById('performance-chart');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');

            this.config.performanceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Performance Score',
                        data: [],
                        borderColor: this.config.primaryColor,
                        backgroundColor: this.hexToRgba(this.config.primaryColor, 0.1),
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: this.config.primaryColor,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            cornerRadius: 8,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 10,
                            ticks: {
                                stepSize: 2
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        },

        /**
         * Load initial data
         */
        loadInitialData: function() {
            if (!this.config.brandId) return;

            this.loadPerformanceData(30);
            this.analyzeGaps(this.config.brandId);
            this.loadActivity();
        },

        /**
         * Start auto-refresh
         */
        startAutoRefresh: function() {
            const self = this;
            setInterval(function() {
                if (self.config.brandId) {
                    self.loadActivity();
                }
            }, this.config.refreshInterval);
        },

        /**
         * Switch brand
         */
        switchBrand: function(brandId) {
            if (!brandId) {
                window.location.reload();
                return;
            }

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ai_manager_pro_set_active_brand',
                    nonce: this.config.nonce,
                    brand_id: brandId
                },
                success: function(response) {
                    if (response.success) {
                        window.location.reload();
                    }
                },
                error: function() {
                    alert('Error switching brand. Please try again.');
                }
            });
        },

        /**
         * Refresh all features
         */
        refreshFeatures: function(brandId) {
            this.showNotification('Refreshing features...', 'info');
            window.location.reload();
        },

        /**
         * Generate topics with AI
         */
        generateTopics: function(brandId) {
            const self = this;
            const $btn = $('.generate-topics-btn');
            const originalText = $btn.html();

            $btn.prop('disabled', true).html('<span class="dashicons dashicons-update-alt" style="animation: spin 1s linear infinite;"></span> Generating...');

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ai_manager_pro_generate_topics',
                    nonce: this.config.nonce,
                    brand_id: brandId,
                    count: 10,
                    auto_add_to_pool: true
                },
                success: function(response) {
                    $btn.prop('disabled', false).html(originalText);

                    if (response.success) {
                        self.showNotification(`Successfully generated ${response.data.count} topics!`, 'success');
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    } else {
                        self.showNotification('Error generating topics: ' + response.data, 'error');
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(originalText);
                    self.showNotification('Network error. Please try again.', 'error');
                }
            });
        },

        /**
         * Detect trending topics
         */
        detectTrends: function(brandId) {
            const self = this;
            const $btn = $('.detect-trends-btn');
            const originalText = $btn.html();

            $btn.prop('disabled', true).html('<span class="dashicons dashicons-update-alt" style="animation: spin 1s linear infinite;"></span> Detecting...');

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ai_manager_pro_detect_trends',
                    nonce: this.config.nonce,
                    brand_id: brandId,
                    auto_add_to_pool: true
                },
                success: function(response) {
                    $btn.prop('disabled', false).html(originalText);

                    if (response.success) {
                        self.showNotification(`Found ${response.data.count} trending topics!`, 'success');

                        // Show trending topics in a modal or notification
                        if (response.data.trending_topics && response.data.trending_topics.length > 0) {
                            const topics = response.data.trending_topics.slice(0, 5).map(t => t.keyword).join(', ');
                            self.showNotification('Trending: ' + topics, 'info');
                        }
                    } else {
                        self.showNotification('Error detecting trends: ' + response.data, 'error');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html(originalText);
                    self.showNotification('Network error. Please try again.', 'error');
                }
            });
        },

        /**
         * Update performance scores
         */
        updatePerformance: function(brandId) {
            const self = this;
            const $btn = $('.update-performance-btn');
            const originalText = $btn.html();

            $btn.prop('disabled', true).html('<span class="dashicons dashicons-update-alt" style="animation: spin 1s linear infinite;"></span> Updating...');

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ai_manager_pro_update_performance',
                    nonce: this.config.nonce,
                    brand_id: brandId
                },
                success: function(response) {
                    $btn.prop('disabled', false).html(originalText);

                    if (response.success) {
                        self.showNotification(`Updated ${response.data.topics_updated} topics!`, 'success');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        self.showNotification('Error updating performance: ' + response.data, 'error');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html(originalText);
                    self.showNotification('Network error. Please try again.', 'error');
                }
            });
        },

        /**
         * Analyze content gaps
         */
        analyzeGaps: function(brandId) {
            const self = this;

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ai_manager_pro_analyze_gaps',
                    nonce: this.config.nonce,
                    brand_id: brandId
                },
                success: function(response) {
                    if (response.success) {
                        self.renderGaps(response.data);
                    } else {
                        $('#gaps-content').html('<div class="empty-state"><p>Error loading gap analysis</p></div>');
                    }
                },
                error: function() {
                    $('#gaps-content').html('<div class="empty-state"><p>Network error</p></div>');
                }
            });
        },

        /**
         * Render gap analysis results
         */
        renderGaps: function(data) {
            const $content = $('#gaps-content');
            const $count = $('#gap-count');

            $count.text(data.gap_count || 0);

            if (!data.recommendations || data.recommendations.length === 0) {
                $content.html('<div class="empty-state"><span class="dashicons dashicons-yes-alt"></span><p>No content gaps detected. Great job!</p></div>');
                return;
            }

            let html = '';
            data.recommendations.forEach(function(rec) {
                const priorityClass = rec.priority === 'high' ? 'gap-high' : (rec.priority === 'medium' ? 'gap-medium' : 'gap-low');
                html += `
                    <div class="gap-item ${priorityClass}">
                        <h4>${rec.type.charAt(0).toUpperCase() + rec.type.slice(1)} Gap - ${rec.priority.toUpperCase()}</h4>
                        <p>${rec.message}</p>
                    </div>
                `;
            });

            $content.html(html);
        },

        /**
         * Load performance data for chart
         */
        loadPerformanceData: function(days) {
            const self = this;

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ai_manager_pro_get_performance_report',
                    nonce: this.config.nonce,
                    brand_id: this.config.brandId
                },
                success: function(response) {
                    if (response.success && response.data.report) {
                        self.updateChartData(response.data.report, days);
                    }
                }
            });
        },

        /**
         * Update chart with data
         */
        updateChartData: function(report, days) {
            if (!this.config.performanceChart) return;

            // Generate mock data for demonstration (in real implementation, use actual historical data)
            const labels = [];
            const data = [];
            const avgScore = report.average_score || 6.5;

            for (let i = days - 1; i >= 0; i--) {
                const date = new Date();
                date.setDate(date.getDate() - i);
                labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));

                // Simulate data variation around average
                const variation = (Math.random() - 0.5) * 2;
                data.push(Math.max(0, Math.min(10, avgScore + variation)));
            }

            this.config.performanceChart.data.labels = labels;
            this.config.performanceChart.data.datasets[0].data = data;
            this.config.performanceChart.update();
        },

        /**
         * Update performance chart based on period
         */
        updatePerformanceChart: function(days) {
            this.loadPerformanceData(parseInt(days));
        },

        /**
         * Load recent activity
         */
        loadActivity: function() {
            const self = this;

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ai_manager_pro_get_recent_activity',
                    nonce: this.config.nonce,
                    brand_id: this.config.brandId,
                    limit: 10
                },
                success: function(response) {
                    if (response.success && response.data.activities) {
                        self.renderActivity(response.data.activities);
                    } else {
                        self.renderMockActivity();
                    }
                },
                error: function() {
                    self.renderMockActivity();
                }
            });
        },

        /**
         * Render activity timeline
         */
        renderActivity: function(activities) {
            const $timeline = $('#activity-timeline');
            let html = '';

            if (!activities || activities.length === 0) {
                html = '<div class="empty-state"><span class="dashicons dashicons-clock"></span><p>No recent activity</p></div>';
            } else {
                activities.forEach(function(activity) {
                    const iconClass = self.getActivityIcon(activity.type);
                    const timeAgo = self.formatTimeAgo(activity.timestamp);

                    html += `
                        <div class="activity-item">
                            <div class="activity-icon">
                                <span class="dashicons dashicons-${iconClass}"></span>
                            </div>
                            <div class="activity-content">
                                <h4>${activity.title}</h4>
                                <p>${activity.description}</p>
                                <div class="activity-time">${timeAgo}</div>
                            </div>
                        </div>
                    `;
                });
            }

            $timeline.html(html);
        },

        /**
         * Render mock activity for demonstration
         */
        renderMockActivity: function() {
            const mockActivities = [
                {
                    type: 'topic_generated',
                    title: 'AI Topics Generated',
                    description: '10 new topics added to pool',
                    timestamp: new Date(Date.now() - 3600000)
                },
                {
                    type: 'content_published',
                    title: 'Content Published',
                    description: 'Complete Guide to Cloud Migration published',
                    timestamp: new Date(Date.now() - 7200000)
                },
                {
                    type: 'trending_detected',
                    title: 'Trending Topics Detected',
                    description: '5 trending topics found in industry feeds',
                    timestamp: new Date(Date.now() - 86400000)
                },
                {
                    type: 'performance_updated',
                    title: 'Performance Updated',
                    description: 'Topic performance scores recalculated',
                    timestamp: new Date(Date.now() - 172800000)
                }
            ];

            this.renderActivity(mockActivities);
        },

        /**
         * Get icon for activity type
         */
        getActivityIcon: function(type) {
            const icons = {
                'topic_generated': 'lightbulb',
                'content_published': 'media-document',
                'trending_detected': 'chart-line',
                'performance_updated': 'analytics',
                'gap_analyzed': 'warning',
                'brand_updated': 'admin-generic'
            };

            return icons[type] || 'marker';
        },

        /**
         * Format timestamp to "time ago" format
         */
        formatTimeAgo: function(timestamp) {
            const now = new Date();
            const past = new Date(timestamp);
            const diffMs = now - past;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
            if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
            if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;

            return past.toLocaleDateString();
        },

        /**
         * Show notification
         */
        showNotification: function(message, type) {
            // Create notification element
            const $notification = $('<div>', {
                class: 'amp-notification amp-notification-' + type,
                html: message
            });

            // Add to page
            $('body').append($notification);

            // Animate in
            setTimeout(function() {
                $notification.addClass('amp-notification-show');
            }, 10);

            // Remove after 5 seconds
            setTimeout(function() {
                $notification.removeClass('amp-notification-show');
                setTimeout(function() {
                    $notification.remove();
                }, 300);
            }, 5000);
        },

        /**
         * Convert hex to rgba
         */
        hexToRgba: function(hex, alpha) {
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);

            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        // Only initialize on enhanced dashboard page
        if ($('.ai-manager-enhanced-dashboard').length > 0) {
            EnhancedDashboard.init();
        }
    });

    // Add spin animation CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .amp-notification {
            position: fixed;
            top: 32px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            font-weight: 500;
            z-index: 999999;
            transform: translateX(400px);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .amp-notification-show {
            transform: translateX(0);
        }

        .amp-notification-success {
            background: #10b981;
            color: white;
        }

        .amp-notification-error {
            background: #ef4444;
            color: white;
        }

        .amp-notification-info {
            background: #3b82f6;
            color: white;
        }

        .amp-notification-warning {
            background: #f59e0b;
            color: white;
        }
    `;
    document.head.appendChild(style);

})(jQuery);
