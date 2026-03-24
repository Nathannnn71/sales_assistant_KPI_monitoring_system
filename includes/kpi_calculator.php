<?php
/**
 * SAKMS - KPI Calculator
 * Implements the two-section KPI calculation model
 */

class KPICalculator {
    
    /**
     * Calculate overall KPI score for an employee in a given period
     * 
     * @param mysqli $conn Database connection
     * @param int $employee_id Employee ID
     * @param int $period_id Evaluation period ID
     * @return array ['section1' => float, 'section2' => float, 'overall' => float, 'rating' => string]
     */
    public static function calculateKPI($conn, $employee_id, $period_id) {
        
        // Get all KPI scores for this employee in this period
        $query = "
            SELECT 
                km.kpi_code,
                km.section,
                km.kpi_group,
                km.section_weight,
                ks.score
            FROM kpi_scores ks
            JOIN kpi_master km ON ks.kpi_code = km.kpi_code
            JOIN employees e ON ks.employee_id = e.employee_id
            JOIN evaluation_periods ep ON YEAR(ks.evaluation_date) = ep.year
            WHERE ks.employee_id = ? AND ep.period_id = ?
            ORDER BY km.section, km.kpi_group, km.kpi_code
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $employee_id, $period_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $section1_scores = [];
        $section2_groups = [];
        $kpi_weights = [];
        
        // Organize scores by section
        while ($row = $result->fetch_assoc()) {
            if ($row['section'] === 'Section 1') {
                $section1_scores[$row['kpi_code']] = [
                    'score' => $row['score'],
                    'weight' => $row['section_weight']
                ];
                $kpi_weights[$row['kpi_code']] = $row['section_weight'];
            } else {
                if (!isset($section2_groups[$row['kpi_group']])) {
                    $section2_groups[$row['kpi_group']] = [];
                }
                $section2_groups[$row['kpi_group']][] = $row['score'];
                $kpi_weights[$row['kpi_group']] = $row['section_weight'];
            }
        }
        
        // ======================================
        // SECTION 1: Core Competencies (25%)
        // Formula: Weighted Score = Score × Weight
        // ======================================
        $section1_total = 0;
        $section1_breakdown = [];
        
        foreach ($section1_scores as $kpi_code => $data) {
            $weighted_score = $data['score'] * $data['weight'];
            $section1_total += $weighted_score;
            
            $section1_breakdown[$kpi_code] = [
                'score' => $data['score'],
                'weight' => $data['weight'],
                'weighted_score' => round($weighted_score, 2)
            ];
        }
        
        // ======================================
        // SECTION 2: KPI Achievement (75%)
        // Formula: AVG(Ratings in group) × KPI Weight
        // ======================================
        $section2_total = 0;
        $section2_breakdown = [];
        
        foreach ($section2_groups as $kpi_group => $scores) {
            // Average all ratings within the KPI group
            $avg_score = array_sum($scores) / count($scores);
            $weight = $kpi_weights[$kpi_group] ?? 0.15;
            $weighted_score = $avg_score * $weight;
            $section2_total += $weighted_score;
            
            $section2_breakdown[$kpi_group] = [
                'avg_score' => round($avg_score, 2),
                'weight' => $weight,
                'weighted_score' => round($weighted_score, 2)
            ];
        }
        
        // ======================================
        // FINAL SCORE (Section 1 + Section 2)
        // ======================================
        $final_score = round($section1_total + $section2_total, 2);
        
        // Cap between 1-5
        $final_score = max(1, min(5, $final_score));
        
        // Get rating label
        $rating = self::getRatingLabel($final_score);
        
        return [
            'section1' => round($section1_total, 2),
            'section2' => round($section2_total, 2),
            'overall' => $final_score,
            'rating' => $rating,
            'section1_breakdown' => $section1_scores,
            'section2_breakdown' => $section2_breakdown
        ];
    }
    
    public static function getRatingLabel($score) {
        // Round DOWN to nearest integer (floor)
        $rating = floor($score);
        $rating = max(1, min(5, $rating)); // Ensure within 1-5 range
        
        switch ($rating) {
            case 5:
                return 'Excellent';
            case 4:
                return 'Good';
            case 3:
                return 'Satisfactory';
            case 2:
                return 'Poor';
            case 1:
            default:
                return 'Very Poor';
        }
    }
    
    /**
     * Get color coding for performance visualization
     */
    public static function getPerformanceColor($score) {
        if ($score >= 4.5) return '#22c55e';  // Green - Excellent
        if ($score >= 3.5) return '#3b82f6';  // Blue - Good
        if ($score >= 2.5) return '#f59e0b';  // Amber - Satisfactory
        if ($score >= 1.5) return '#ef4444';  // Red - Poor
        return '#7f1d1d';                      // Dark Red - Very Poor
    }
    
    /**
     * Get CSS class for performance badge
     */
    public static function getPerformanceClass($score) {
        if ($score >= 4.5) return 'badge-excellent';
        if ($score >= 3.5) return 'badge-good';
        if ($score >= 2.5) return 'badge-satisfactory';
        if ($score >= 1.5) return 'badge-poor';
        return 'badge-very-poor';
    }
    
    /**
     * Classify employee based on KPI score
     */
    public static function classifyPerformance($score) {
        if ($score >= 4.5) return 'Top Performer';
        if ($score >= 3.5) return 'Good Performer';
        if ($score >= 2.5) return 'Average Performer';
        if ($score >= 1.5) return 'At-Risk';
        return 'Critical Risk';
    }
    
    /**
     * Calculate KPI for multiple employees
     */
    public static function calculateBatchKPI($conn, $employee_ids, $period_id) {
        $results = [];
        foreach ($employee_ids as $emp_id) {
            $results[$emp_id] = self::calculateKPI($conn, $emp_id, $period_id);
        }
        return $results;
    }
    
    /**
     * Get KPI trend for an employee (multiple periods)
     * Returns: array['year'] => ['overall' => float, 'rating' => string, ...]
     */
    public static function getKPITrend($conn, $employee_id, $start_year = 2022, $end_year = 2025) {
        $query = "
            SELECT 
                ep.year,
                ep.period_id
            FROM evaluation_periods ep
            WHERE ep.year BETWEEN ? AND ?
            ORDER BY ep.year ASC
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $start_year, $end_year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $trend = [];
        while ($row = $result->fetch_assoc()) {
            $trend[$row['year']] = self::calculateKPI($conn, $employee_id, $row['period_id']);
        }
        
        return $trend;
    }
}
