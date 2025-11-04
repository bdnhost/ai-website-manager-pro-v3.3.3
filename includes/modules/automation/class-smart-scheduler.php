<?php
/**
 * Smart Scheduler
 *
 * @package AI_Manager_Pro
 * @subpackage Modules\Automation
 */

namespace AI_Manager_Pro\Modules\Automation;

use Monolog\Logger;

/**
 * Smart Scheduler Class
 */
class Smart_Scheduler {
    
    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;
    
    /**
     * Constructor
     *
     * @param Logger $logger Logger instance
     */
    public function __construct(Logger $logger) {
        $this->logger = $logger;
    }
    
    /**
     * Calculate next run time
     *
     * @param array $schedule Schedule configuration
     * @return string|null Next run time in MySQL datetime format
     */
    public function calculate_next_run($schedule) {
        $type = $schedule['type'] ?? '';
        
        try {
            switch ($type) {
                case 'recurring':
                    return $this->calculate_recurring_next_run($schedule);
                    
                case 'one_time':
                    return $this->calculate_one_time_next_run($schedule);
                    
                case 'smart':
                    return $this->calculate_smart_next_run($schedule);
                    
                default:
                    $this->logger->warning('Unknown schedule type', [
                        'type' => $type
                    ]);
                    return null;
            }
        } catch (\Exception $e) {
            $this->logger->error('Error calculating next run time', [
                'error' => $e->getMessage(),
                'schedule' => $schedule
            ]);
            return null;
        }
    }
    
    /**
     * Calculate next run for recurring schedule
     *
     * @param array $schedule Schedule configuration
     * @return string|null Next run time
     */
    private function calculate_recurring_next_run($schedule) {
        $frequency = $schedule['frequency'] ?? '';
        $timezone = $schedule['timezone'] ?? wp_timezone_string();
        
        $tz = new \DateTimeZone($timezone);
        $now = new \DateTime('now', $tz);
        
        switch ($frequency) {
            case 'hourly':
                $next = clone $now;
                $next->add(new \DateInterval('PT1H'));
                break;
                
            case 'daily':
                $time = $schedule['time'] ?? '09:00';
                $next = $this->get_next_daily_run($now, $time, $tz);
                break;
                
            case 'weekly':
                $day = $schedule['day'] ?? 'monday';
                $time = $schedule['time'] ?? '09:00';
                $next = $this->get_next_weekly_run($now, $day, $time, $tz);
                break;
                
            case 'monthly':
                $day = intval($schedule['day'] ?? 1);
                $time = $schedule['time'] ?? '09:00';
                $next = $this->get_next_monthly_run($now, $day, $time, $tz);
                break;
                
            case 'custom':
                $interval = $schedule['interval'] ?? 'PT1H';
                $next = clone $now;
                $next->add(new \DateInterval($interval));
                break;
                
            default:
                return null;
        }
        
        return $next->format('Y-m-d H:i:s');
    }
    
    /**
     * Calculate next run for one-time schedule
     *
     * @param array $schedule Schedule configuration
     * @return string|null Next run time
     */
    private function calculate_one_time_next_run($schedule) {
        $datetime = $schedule['datetime'] ?? '';
        $timezone = $schedule['timezone'] ?? wp_timezone_string();
        
        if (empty($datetime)) {
            return null;
        }
        
        try {
            $tz = new \DateTimeZone($timezone);
            $scheduled_time = new \DateTime($datetime, $tz);
            $now = new \DateTime('now', $tz);
            
            // Only return if the scheduled time is in the future
            if ($scheduled_time > $now) {
                return $scheduled_time->format('Y-m-d H:i:s');
            }
            
            return null; // Past time, won't run
            
        } catch (\Exception $e) {
            $this->logger->error('Error parsing one-time schedule datetime', [
                'datetime' => $datetime,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Calculate next run for smart schedule
     *
     * @param array $schedule Schedule configuration
     * @return string|null Next run time
     */
    private function calculate_smart_next_run($schedule) {
        $strategy = $schedule['strategy'] ?? 'optimal_engagement';
        $timezone = $schedule['timezone'] ?? wp_timezone_string();
        
        $tz = new \DateTimeZone($timezone);
        $now = new \DateTime('now', $tz);
        
        switch ($strategy) {
            case 'optimal_engagement':
                return $this->calculate_optimal_engagement_time($now, $tz, $schedule);
                
            case 'content_gap_filling':
                return $this->calculate_content_gap_time($now, $tz, $schedule);
                
            case 'traffic_based':
                return $this->calculate_traffic_based_time($now, $tz, $schedule);
                
            case 'competitor_analysis':
                return $this->calculate_competitor_based_time($now, $tz, $schedule);
                
            default:
                // Fallback to daily at optimal time
                return $this->get_next_daily_run($now, '10:00', $tz)->format('Y-m-d H:i:s');
        }
    }
    
    /**
     * Get next daily run time
     *
     * @param \DateTime $now Current time
     * @param string $time Target time (HH:MM)
     * @param \DateTimeZone $tz Timezone
     * @return \DateTime Next run time
     */
    private function get_next_daily_run($now, $time, $tz) {
        list($hour, $minute) = explode(':', $time);
        
        $next = clone $now;
        $next->setTime(intval($hour), intval($minute), 0);
        
        // If the time has already passed today, schedule for tomorrow
        if ($next <= $now) {
            $next->add(new \DateInterval('P1D'));
        }
        
        return $next;
    }
    
    /**
     * Get next weekly run time
     *
     * @param \DateTime $now Current time
     * @param string $day Day of week
     * @param string $time Target time (HH:MM)
     * @param \DateTimeZone $tz Timezone
     * @return \DateTime Next run time
     */
    private function get_next_weekly_run($now, $day, $time, $tz) {
        $day_map = [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 0
        ];
        
        $target_day = $day_map[strtolower($day)] ?? 1;
        $current_day = intval($now->format('w'));
        
        list($hour, $minute) = explode(':', $time);
        
        $next = clone $now;
        $next->setTime(intval($hour), intval($minute), 0);
        
        // Calculate days to add
        $days_to_add = ($target_day - $current_day + 7) % 7;
        
        // If it's the same day but time has passed, schedule for next week
        if ($days_to_add === 0 && $next <= $now) {
            $days_to_add = 7;
        }
        
        if ($days_to_add > 0) {
            $next->add(new \DateInterval("P{$days_to_add}D"));
        }
        
        return $next;
    }
    
    /**
     * Get next monthly run time
     *
     * @param \DateTime $now Current time
     * @param int $day Day of month
     * @param string $time Target time (HH:MM)
     * @param \DateTimeZone $tz Timezone
     * @return \DateTime Next run time
     */
    private function get_next_monthly_run($now, $day, $time, $tz) {
        list($hour, $minute) = explode(':', $time);
        
        $next = clone $now;
        $next->setDate($next->format('Y'), $next->format('n'), $day);
        $next->setTime(intval($hour), intval($minute), 0);
        
        // If the date has passed this month, schedule for next month
        if ($next <= $now) {
            $next->add(new \DateInterval('P1M'));
            
            // Handle cases where the day doesn't exist in the next month
            $max_day = intval($next->format('t'));
            if ($day > $max_day) {
                $next->setDate($next->format('Y'), $next->format('n'), $max_day);
            }
        }
        
        return $next;
    }
    
    /**
     * Calculate optimal engagement time
     *
     * @param \DateTime $now Current time
     * @param \DateTimeZone $tz Timezone
     * @param array $schedule Schedule configuration
     * @return string Next run time
     */
    private function calculate_optimal_engagement_time($now, $tz, $schedule) {
        // Analyze historical engagement data to find optimal posting times
        $engagement_data = $this->get_engagement_data();
        
        $optimal_hours = $engagement_data['optimal_hours'] ?? [9, 14, 18];
        $optimal_days = $engagement_data['optimal_days'] ?? [1, 2, 3, 4, 5]; // Monday-Friday
        
        $next = clone $now;
        $next->add(new \DateInterval('PT1H')); // Start from next hour
        
        // Find next optimal time
        for ($i = 0; $i < 168; $i++) { // Check up to 1 week ahead
            $hour = intval($next->format('G'));
            $day_of_week = intval($next->format('w'));
            
            if (in_array($hour, $optimal_hours) && in_array($day_of_week, $optimal_days)) {
                return $next->format('Y-m-d H:i:s');
            }
            
            $next->add(new \DateInterval('PT1H'));
        }
        
        // Fallback to tomorrow at 10 AM
        $fallback = clone $now;
        $fallback->add(new \DateInterval('P1D'));
        $fallback->setTime(10, 0, 0);
        
        return $fallback->format('Y-m-d H:i:s');
    }
    
    /**
     * Calculate content gap filling time
     *
     * @param \DateTime $now Current time
     * @param \DateTimeZone $tz Timezone
     * @param array $schedule Schedule configuration
     * @return string Next run time
     */
    private function calculate_content_gap_time($now, $tz, $schedule) {
        // Analyze content publishing patterns to fill gaps
        $content_analysis = $this->analyze_content_gaps();
        
        $target_posts_per_week = $schedule['target_posts_per_week'] ?? 3;
        $current_week_posts = $content_analysis['current_week_posts'] ?? 0;
        
        if ($current_week_posts >= $target_posts_per_week) {
            // Target met, schedule for next week
            $next = clone $now;
            $next->add(new \DateInterval('P7D'));
            $next->setTime(10, 0, 0);
            return $next->format('Y-m-d H:i:s');
        }
        
        // Calculate when to publish next to evenly distribute content
        $days_left_in_week = 7 - intval($now->format('w'));
        $posts_needed = $target_posts_per_week - $current_week_posts;
        
        if ($posts_needed > 0 && $days_left_in_week > 0) {
            $days_between_posts = max(1, floor($days_left_in_week / $posts_needed));
            
            $next = clone $now;
            $next->add(new \DateInterval("P{$days_between_posts}D"));
            $next->setTime(10, 0, 0);
            
            return $next->format('Y-m-d H:i:s');
        }
        
        // Fallback to tomorrow
        $next = clone $now;
        $next->add(new \DateInterval('P1D'));
        $next->setTime(10, 0, 0);
        
        return $next->format('Y-m-d H:i:s');
    }
    
    /**
     * Calculate traffic-based time
     *
     * @param \DateTime $now Current time
     * @param \DateTimeZone $tz Timezone
     * @param array $schedule Schedule configuration
     * @return string Next run time
     */
    private function calculate_traffic_based_time($now, $tz, $schedule) {
        // Schedule content based on traffic patterns
        $traffic_data = $this->get_traffic_patterns();
        
        $peak_hours = $traffic_data['peak_hours'] ?? [9, 12, 15, 18];
        $peak_days = $traffic_data['peak_days'] ?? [1, 2, 3, 4, 5];
        
        $next = clone $now;
        $next->add(new \DateInterval('PT2H')); // Give some buffer time
        
        // Find next peak traffic time
        for ($i = 0; $i < 168; $i++) {
            $hour = intval($next->format('G'));
            $day_of_week = intval($next->format('w'));
            
            if (in_array($hour, $peak_hours) && in_array($day_of_week, $peak_days)) {
                return $next->format('Y-m-d H:i:s');
            }
            
            $next->add(new \DateInterval('PT1H'));
        }
        
        // Fallback
        $fallback = clone $now;
        $fallback->add(new \DateInterval('P1D'));
        $fallback->setTime(12, 0, 0);
        
        return $fallback->format('Y-m-d H:i:s');
    }
    
    /**
     * Calculate competitor-based time
     *
     * @param \DateTime $now Current time
     * @param \DateTimeZone $tz Timezone
     * @param array $schedule Schedule configuration
     * @return string Next run time
     */
    private function calculate_competitor_based_time($now, $tz, $schedule) {
        // Schedule content to avoid competitor posting times or to counter them
        $competitor_data = $this->get_competitor_posting_patterns();
        
        $strategy = $schedule['competitor_strategy'] ?? 'avoid'; // avoid or counter
        $competitor_peak_hours = $competitor_data['peak_hours'] ?? [10, 14, 16];
        
        $next = clone $now;
        $next->add(new \DateInterval('PT1H'));
        
        for ($i = 0; $i < 72; $i++) { // Check up to 3 days ahead
            $hour = intval($next->format('G'));
            
            if ($strategy === 'avoid') {
                // Avoid competitor peak hours
                if (!in_array($hour, $competitor_peak_hours)) {
                    return $next->format('Y-m-d H:i:s');
                }
            } else {
                // Counter by posting during their peak hours
                if (in_array($hour, $competitor_peak_hours)) {
                    return $next->format('Y-m-d H:i:s');
                }
            }
            
            $next->add(new \DateInterval('PT1H'));
        }
        
        // Fallback
        $fallback = clone $now;
        $fallback->add(new \DateInterval('P1D'));
        $fallback->setTime(11, 0, 0);
        
        return $fallback->format('Y-m-d H:i:s');
    }
    
    /**
     * Validate schedule configuration
     *
     * @param array $schedule Schedule configuration
     * @return bool|array True if valid, array of errors if invalid
     */
    public function validate_schedule($schedule) {
        $errors = [];
        
        if (empty($schedule['type'])) {
            $errors[] = 'Schedule type is required';
            return $errors;
        }
        
        $type = $schedule['type'];
        
        switch ($type) {
            case 'recurring':
                if (empty($schedule['frequency'])) {
                    $errors[] = 'Frequency is required for recurring schedule';
                }
                
                $frequency = $schedule['frequency'] ?? '';
                
                if (in_array($frequency, ['daily', 'weekly', 'monthly'])) {
                    if (empty($schedule['time'])) {
                        $errors[] = 'Time is required for daily/weekly/monthly schedule';
                    } elseif (!preg_match('/^\d{2}:\d{2}$/', $schedule['time'])) {
                        $errors[] = 'Time must be in HH:MM format';
                    }
                }
                
                if ($frequency === 'weekly' && empty($schedule['day'])) {
                    $errors[] = 'Day is required for weekly schedule';
                }
                
                if ($frequency === 'monthly') {
                    $day = intval($schedule['day'] ?? 0);
                    if ($day < 1 || $day > 31) {
                        $errors[] = 'Day must be between 1 and 31 for monthly schedule';
                    }
                }
                
                if ($frequency === 'custom' && empty($schedule['interval'])) {
                    $errors[] = 'Interval is required for custom schedule';
                }
                
                break;
                
            case 'one_time':
                if (empty($schedule['datetime'])) {
                    $errors[] = 'Datetime is required for one-time schedule';
                }
                break;
                
            case 'smart':
                if (empty($schedule['strategy'])) {
                    $errors[] = 'Strategy is required for smart schedule';
                }
                break;
        }
        
        return empty($errors) ? true : $errors;
    }
    
    /**
     * Get engagement data (placeholder implementation)
     *
     * @return array Engagement data
     */
    private function get_engagement_data() {
        // This would integrate with analytics to get real engagement data
        return [
            'optimal_hours' => [9, 12, 15, 18],
            'optimal_days' => [1, 2, 3, 4, 5] // Monday-Friday
        ];
    }
    
    /**
     * Analyze content gaps (placeholder implementation)
     *
     * @return array Content analysis
     */
    private function analyze_content_gaps() {
        // Count posts published this week
        $start_of_week = date('Y-m-d', strtotime('monday this week'));
        $end_of_week = date('Y-m-d', strtotime('sunday this week'));
        
        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'date_query' => [
                [
                    'after' => $start_of_week,
                    'before' => $end_of_week,
                    'inclusive' => true
                ]
            ],
            'fields' => 'ids'
        ]);
        
        return [
            'current_week_posts' => count($posts)
        ];
    }
    
    /**
     * Get traffic patterns (placeholder implementation)
     *
     * @return array Traffic patterns
     */
    private function get_traffic_patterns() {
        // This would integrate with analytics to get real traffic data
        return [
            'peak_hours' => [9, 12, 15, 18],
            'peak_days' => [1, 2, 3, 4, 5]
        ];
    }
    
    /**
     * Get competitor posting patterns (placeholder implementation)
     *
     * @return array Competitor data
     */
    private function get_competitor_posting_patterns() {
        // This would integrate with competitor analysis tools
        return [
            'peak_hours' => [10, 14, 16]
        ];
    }
    
    /**
     * Get available schedule types
     *
     * @return array Available schedule types
     */
    public function get_available_schedule_types() {
        return [
            'recurring' => [
                'name' => 'Recurring',
                'description' => 'Schedule content to repeat at regular intervals',
                'frequencies' => [
                    'hourly' => 'Every Hour',
                    'daily' => 'Daily',
                    'weekly' => 'Weekly',
                    'monthly' => 'Monthly',
                    'custom' => 'Custom Interval'
                ]
            ],
            'one_time' => [
                'name' => 'One Time',
                'description' => 'Schedule content for a specific date and time',
                'fields' => ['datetime', 'timezone']
            ],
            'smart' => [
                'name' => 'Smart Scheduling',
                'description' => 'AI-powered scheduling based on various factors',
                'strategies' => [
                    'optimal_engagement' => 'Optimal Engagement Times',
                    'content_gap_filling' => 'Content Gap Filling',
                    'traffic_based' => 'Traffic-Based Scheduling',
                    'competitor_analysis' => 'Competitor-Based Scheduling'
                ]
            ]
        ];
    }
}

