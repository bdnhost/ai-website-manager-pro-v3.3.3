<?php
/**
 * Enhanced Modern Dashboard
 *
 * @package AI_Manager_Pro
 * @subpackage Admin\Views
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current brand
global $wpdb;
$brands_table = $wpdb->prefix . 'ai_manager_pro_brands';
$active_brand_id = get_option('ai_manager_pro_active_brand_id', 0);
$brand = null;
$brand_data = null;

if ($active_brand_id) {
    $brand = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$brands_table} WHERE id = %d",
        $active_brand_id
    ));

    if ($brand) {
        $brand_data = json_decode($brand->brand_data, true);
    }
}

// Get all brands for switcher
$all_brands = $wpdb->get_results("SELECT id, name FROM {$brands_table} ORDER BY name ASC");

// Calculate stats
$total_topics = 0;
$unused_topics = 0;
$avg_performance = 0;
$topics_with_scores = 0;

if ($brand_data && !empty($brand_data['topic_pool']['categories'])) {
    foreach ($brand_data['topic_pool']['categories'] as $category) {
        foreach ($category['topics'] as $topic) {
            $total_topics++;
            if (($topic['usage_count'] ?? 0) === 0) {
                $unused_topics++;
            }
            if (isset($topic['performance_score']) && $topic['performance_score'] > 0) {
                $avg_performance += $topic['performance_score'];
                $topics_with_scores++;
            }
        }
    }
}

if ($topics_with_scores > 0) {
    $avg_performance = round($avg_performance / $topics_with_scores, 2);
}

// Get recent posts count
$recent_posts_count = $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->posts}
     WHERE post_type = 'post'
     AND post_status = 'publish'
     AND post_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
);

// Brand colors
$primary_color = $brand_data['brand_colors']['primary'] ?? '#2563eb';
$accent_color = $brand_data['brand_colors']['accent'] ?? '#10b981';
?>

<div class="wrap ai-manager-enhanced-dashboard">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="header-content">
            <h1 class="dashboard-title">
                <span class="dashicons dashicons-dashboard"></span>
                AI Content Intelligence Hub
            </h1>
            <p class="dashboard-subtitle">Smart Brand Content Engine • Powered by AI</p>
        </div>

        <!-- Brand Switcher -->
        <div class="brand-switcher">
            <label for="active-brand-select">Active Brand:</label>
            <select id="active-brand-select" class="brand-select">
                <option value="">-- Select Brand --</option>
                <?php foreach ($all_brands as $b): ?>
                    <option value="<?php echo esc_attr($b->id); ?>"
                            <?php selected($active_brand_id, $b->id); ?>>
                        <?php echo esc_html($b->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <?php if (!$brand): ?>
        <!-- No Brand Selected -->
        <div class="no-brand-notice">
            <div class="notice-icon">
                <span class="dashicons dashicons-info"></span>
            </div>
            <h2>Welcome to AI Content Intelligence Hub</h2>
            <p>Get started by selecting a brand or importing a new brand profile.</p>
            <div class="quick-actions">
                <a href="?page=ai-manager-pro-brands" class="button button-primary button-hero">
                    <span class="dashicons dashicons-plus-alt"></span>
                    Create New Brand
                </a>
                <a href="?page=ai-manager-pro-brands&action=import" class="button button-hero">
                    <span class="dashicons dashicons-upload"></span>
                    Import Brand
                </a>
            </div>
        </div>
    <?php else: ?>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">

            <!-- Quick Stats Row -->
            <div class="stats-row">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <span class="dashicons dashicons-lightbulb"></span>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo esc_html($total_topics); ?></div>
                        <div class="stat-label">Total Topics</div>
                        <div class="stat-meta"><?php echo esc_html($unused_topics); ?> unused</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <span class="dashicons dashicons-chart-line"></span>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo esc_html($avg_performance); ?></div>
                        <div class="stat-label">Avg Performance</div>
                        <div class="stat-meta">Out of 10.0</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <span class="dashicons dashicons-media-document"></span>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo esc_html($recent_posts_count); ?></div>
                        <div class="stat-label">Posts (30 days)</div>
                        <div class="stat-meta">
                            Target: <?php echo esc_html($brand_data['analytics_goals']['monthly_posts_target'] ?? 30); ?>
                        </div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <span class="dashicons dashicons-update"></span>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value" id="automation-status">Active</div>
                        <div class="stat-label">Automation Status</div>
                        <div class="stat-meta">Running smoothly</div>
                    </div>
                </div>
            </div>

            <!-- AI Features Status -->
            <div class="widget-card ai-features-widget">
                <div class="widget-header">
                    <h3>
                        <span class="dashicons dashicons-admin-generic"></span>
                        AI Features Status
                    </h3>
                    <button class="button button-small refresh-features-btn" data-brand-id="<?php echo esc_attr($active_brand_id); ?>">
                        <span class="dashicons dashicons-update"></span>
                        Refresh
                    </button>
                </div>
                <div class="widget-content">
                    <div class="features-grid">
                        <!-- Topic Generation -->
                        <div class="feature-item <?php echo !empty($brand_data['ai_features']['topic_generation']['enabled']) ? 'feature-active' : 'feature-inactive'; ?>">
                            <div class="feature-icon">
                                <span class="dashicons dashicons-admin-post"></span>
                            </div>
                            <div class="feature-info">
                                <h4>AI Topic Generation</h4>
                                <p><?php echo !empty($brand_data['ai_features']['topic_generation']['auto_refresh']) ? 'Auto-refresh enabled' : 'Manual only'; ?></p>
                                <button class="button button-small generate-topics-btn" data-brand-id="<?php echo esc_attr($active_brand_id); ?>">
                                    <span class="dashicons dashicons-plus"></span>
                                    Generate Topics
                                </button>
                            </div>
                            <div class="feature-status">
                                <span class="status-badge <?php echo !empty($brand_data['ai_features']['topic_generation']['enabled']) ? 'badge-success' : 'badge-inactive'; ?>">
                                    <?php echo !empty($brand_data['ai_features']['topic_generation']['enabled']) ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Trending Detection -->
                        <div class="feature-item <?php echo !empty($brand_data['ai_features']['trending_detection']['enabled']) ? 'feature-active' : 'feature-inactive'; ?>">
                            <div class="feature-icon">
                                <span class="dashicons dashicons-chart-area"></span>
                            </div>
                            <div class="feature-info">
                                <h4>Trending Detection</h4>
                                <p><?php echo !empty($brand_data['content_calendar']['trending_sources']['enabled']) ? count($brand_data['content_calendar']['trending_sources']['rss_feeds'] ?? []) . ' RSS feeds' : 'Not configured'; ?></p>
                                <button class="button button-small detect-trends-btn" data-brand-id="<?php echo esc_attr($active_brand_id); ?>">
                                    <span class="dashicons dashicons-search"></span>
                                    Detect Trends
                                </button>
                            </div>
                            <div class="feature-status">
                                <span class="status-badge <?php echo !empty($brand_data['ai_features']['trending_detection']['enabled']) ? 'badge-success' : 'badge-inactive'; ?>">
                                    <?php echo !empty($brand_data['ai_features']['trending_detection']['enabled']) ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Performance Tracking -->
                        <div class="feature-item <?php echo !empty($brand_data['ai_features']['performance_tracking']['enabled']) ? 'feature-active' : 'feature-inactive'; ?>">
                            <div class="feature-icon">
                                <span class="dashicons dashicons-analytics"></span>
                            </div>
                            <div class="feature-info">
                                <h4>Performance Analytics</h4>
                                <p>Tracking <?php echo esc_html($topics_with_scores); ?> topics</p>
                                <button class="button button-small update-performance-btn" data-brand-id="<?php echo esc_attr($active_brand_id); ?>">
                                    <span class="dashicons dashicons-update"></span>
                                    Update Scores
                                </button>
                            </div>
                            <div class="feature-status">
                                <span class="status-badge <?php echo !empty($brand_data['ai_features']['performance_tracking']['enabled']) ? 'badge-success' : 'badge-inactive'; ?>">
                                    <?php echo !empty($brand_data['ai_features']['performance_tracking']['enabled']) ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Gap Analysis -->
                        <div class="feature-item <?php echo !empty($brand_data['ai_features']['gap_analysis']['enabled']) ? 'feature-active' : 'feature-inactive'; ?>">
                            <div class="feature-icon">
                                <span class="dashicons dashicons-warning"></span>
                            </div>
                            <div class="feature-info">
                                <h4>Content Gap Analysis</h4>
                                <p>Last <?php echo esc_html($brand_data['ai_features']['gap_analysis']['days_back'] ?? 90); ?> days</p>
                                <button class="button button-small analyze-gaps-btn" data-brand-id="<?php echo esc_attr($active_brand_id); ?>">
                                    <span class="dashicons dashicons-search"></span>
                                    Analyze Gaps
                                </button>
                            </div>
                            <div class="feature-status">
                                <span class="status-badge <?php echo !empty($brand_data['ai_features']['gap_analysis']['enabled']) ? 'badge-success' : 'badge-inactive'; ?>">
                                    <?php echo !empty($brand_data['ai_features']['gap_analysis']['enabled']) ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Topic Pool Overview -->
            <div class="widget-card topic-pool-widget">
                <div class="widget-header">
                    <h3>
                        <span class="dashicons dashicons-media-text"></span>
                        Topic Pool Distribution
                    </h3>
                    <a href="?page=ai-manager-pro-topics" class="button button-small">
                        <span class="dashicons dashicons-edit"></span>
                        Manage Topics
                    </a>
                </div>
                <div class="widget-content">
                    <?php if (!empty($brand_data['topic_pool']['categories'])): ?>
                        <div class="category-distribution">
                            <?php foreach ($brand_data['topic_pool']['categories'] as $category):
                                $cat_total = count($category['topics']);
                                $cat_used = 0;
                                foreach ($category['topics'] as $topic) {
                                    if (($topic['usage_count'] ?? 0) > 0) {
                                        $cat_used++;
                                    }
                                }
                                $usage_percentage = $cat_total > 0 ? ($cat_used / $cat_total) * 100 : 0;
                                $weight = $category['weight'] ?? 25;
                            ?>
                                <div class="category-item">
                                    <div class="category-header">
                                        <span class="category-name"><?php echo esc_html($category['name']); ?></span>
                                        <span class="category-stats"><?php echo esc_html($cat_total); ?> topics • <?php echo esc_html($weight); ?>% weight</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo esc_attr($usage_percentage); ?>%; background-color: <?php echo esc_attr($primary_color); ?>;">
                                            <span class="progress-label"><?php echo round($usage_percentage); ?>% used</span>
                                        </div>
                                    </div>
                                    <div class="category-meta">
                                        <span><?php echo esc_html($cat_used); ?> used</span>
                                        <span><?php echo esc_html($cat_total - $cat_used); ?> available</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <span class="dashicons dashicons-info"></span>
                            <p>No topic pool configured. <a href="?page=ai-manager-pro-topics">Set up topics</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Performance Chart -->
            <div class="widget-card performance-widget">
                <div class="widget-header">
                    <h3>
                        <span class="dashicons dashicons-chart-bar"></span>
                        Performance Overview
                    </h3>
                    <select class="performance-period-select">
                        <option value="7">Last 7 Days</option>
                        <option value="30" selected>Last 30 Days</option>
                        <option value="90">Last 90 Days</option>
                    </select>
                </div>
                <div class="widget-content">
                    <canvas id="performance-chart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Gap Analysis Results -->
            <div class="widget-card gaps-widget">
                <div class="widget-header">
                    <h3>
                        <span class="dashicons dashicons-flag"></span>
                        Content Gaps & Recommendations
                    </h3>
                    <span class="badge badge-warning" id="gap-count">0</span>
                </div>
                <div class="widget-content" id="gaps-content">
                    <div class="loading-state">
                        <span class="spinner is-active"></span>
                        <p>Loading gap analysis...</p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="widget-card activity-widget">
                <div class="widget-header">
                    <h3>
                        <span class="dashicons dashicons-clock"></span>
                        Recent Activity
                    </h3>
                </div>
                <div class="widget-content">
                    <div class="activity-timeline" id="activity-timeline">
                        <div class="loading-state">
                            <span class="spinner is-active"></span>
                            <p>Loading activity...</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    <?php endif; ?>
</div>

<!-- Hidden fields for JavaScript -->
<input type="hidden" id="ai-manager-brand-id" value="<?php echo esc_attr($active_brand_id); ?>">
<input type="hidden" id="ai-manager-nonce" value="<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>">
<input type="hidden" id="ai-manager-ajax-url" value="<?php echo admin_url('admin-ajax.php'); ?>">
<input type="hidden" id="ai-manager-primary-color" value="<?php echo esc_attr($primary_color); ?>">
<input type="hidden" id="ai-manager-accent-color" value="<?php echo esc_attr($accent_color); ?>">
