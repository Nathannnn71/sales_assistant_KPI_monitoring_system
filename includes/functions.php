<?php
/**
 * SAKMS - Utility Functions
 */

require_once 'db_config.php';
require_once 'kpi_calculator.php';

/**
 * Get all employees with their latest KPI scores
 */
function getAllEmployeesSummary($conn, $period_id = 4) {
    $query = "
        SELECT 
            e.employee_id,
            e.name,
            e.staff_id,
            e.department,
            e.role,
            COUNT(DISTINCT ks.kpi_code) as kpi_count,
            AVG(ks.score) as avg_score
        FROM employees e
        LEFT JOIN kpi_scores ks ON e.employee_id = ks.employee_id 
            AND YEAR(ks.evaluation_date) = (SELECT year FROM evaluation_periods WHERE period_id = ?)
        WHERE e.status = 'Active'
        GROUP BY e.employee_id
        ORDER BY avg_score DESC, e.name ASC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Get single employee profile
 */
function getEmployeeProfile($conn, $employee_id) {
    $query = "
        SELECT 
            e.*,
            COUNT(DISTINCT ks.kpi_code) as total_kpis
        FROM employees e
        LEFT JOIN kpi_scores ks ON e.employee_id = ks.employee_id
        WHERE e.employee_id = ?
        GROUP BY e.employee_id
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Get supervisor comments for employee
 */
function getSupervisorComments($conn, $employee_id, $period_id) {
    $query = "
        SELECT 
            sc.*,
            ep.year
        FROM supervisor_comments sc
        JOIN evaluation_periods ep ON sc.period_id = ep.period_id
        WHERE sc.employee_id = ? AND sc.period_id = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $employee_id, $period_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Get top performers (KPI >= 4.5)
 */
function getTopPerformers($conn, $period_id = 4, $limit = 10) {
    $employees = getAllEmployeesSummary($conn, $period_id);
    $top_performers = [];
    
    while ($emp = $employees->fetch_assoc()) {
        $kpi = KPICalculator::calculateKPI($conn, $emp['employee_id'], $period_id);
        if ($kpi['overall'] >= 4.5) {
            $emp['kpi_score'] = $kpi['overall'];
            $emp['rating'] = $kpi['rating'];
            $top_performers[] = $emp;
        }
    }
    
    return array_slice($top_performers, 0, $limit);
}

/**
 * Get at-risk employees (KPI < 3.0)
 */
function getAtRiskEmployees($conn, $period_id = 4) {
    $employees = getAllEmployeesSummary($conn, $period_id);
    $at_risk = [];
    
    while ($emp = $employees->fetch_assoc()) {
        $kpi = KPICalculator::calculateKPI($conn, $emp['employee_id'], $period_id);
        if ($kpi['overall'] < 3.0) {
            $emp['kpi_score'] = $kpi['overall'];
            $emp['rating'] = $kpi['rating'];
            $emp['status'] = KPICalculator::classifyPerformance($kpi['overall']);
            $at_risk[] = $emp;
        }
    }
    
    return $at_risk;
}

/**
 * Get average KPI by department
 */
function getAverageByDepartment($conn, $period_id = 4) {
    $query = "
        SELECT 
            e.department,
            COUNT(DISTINCT e.employee_id) as emp_count,
            AVG(ks.score) as avg_score
        FROM employees e
        LEFT JOIN kpi_scores ks ON e.employee_id = ks.employee_id
            AND YEAR(ks.evaluation_date) = (SELECT year FROM evaluation_periods WHERE period_id = ?)
        WHERE e.status = 'Active'
        GROUP BY e.department
        ORDER BY avg_score DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Get performance distribution
 */
function getPerformanceDistribution($conn, $period_id = 4) {
    $distribution = [
        'Excellent' => 0,
        'Good' => 0,
        'Satisfactory' => 0,
        'Poor' => 0,
        'Very Poor' => 0
    ];
    
    $employees = getAllEmployeesSummary($conn, $period_id);
    
    while ($emp = $employees->fetch_assoc()) {
        $kpi = KPICalculator::calculateKPI($conn, $emp['employee_id'], $period_id);
        $rating = $kpi['rating'];
        if (isset($distribution[$rating])) {
            $distribution[$rating]++;
        }
    }
    
    return $distribution;
}

/**
 * Get KPI scores by group
 */
function getKPIGroupScores($conn, $employee_id, $period_id = 4) {
    $query = "
        SELECT 
            km.kpi_group,
            AVG(ks.score) as avg_score,
            COUNT(ks.score) as count,
            MIN(ks.score) as min_score,
            MAX(ks.score) as max_score
        FROM kpi_scores ks
        JOIN kpi_master km ON ks.kpi_code = km.kpi_code
        JOIN evaluation_periods ep ON YEAR(ks.evaluation_date) = ep.year
        WHERE ks.employee_id = ? AND ep.period_id = ?
        GROUP BY km.kpi_group
        ORDER BY avg_score DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $employee_id, $period_id);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Predict performance risk (trend analysis)
 */
function predictPerformanceRisk($conn, $employee_id) {
    $trend = KPICalculator::getKPITrend($conn, $employee_id);
    
    if (count($trend) < 2) {
        return ['risk_level' => 'Unknown', 'trend' => 'Insufficient Data'];
    }
    
    $scores = array_values($trend);
    $latest_score = $scores[count($scores) - 1]['overall'];
    $previous_score = $scores[count($scores) - 2]['overall'];
    
    $change = $latest_score - $previous_score;
    
    if ($latest_score < 2.5) {
        return ['risk_level' => 'Critical', 'trend' => 'Declining', 'change' => $change];
    } elseif ($latest_score < 3.0 && $change < -0.5) {
        return ['risk_level' => 'High', 'trend' => 'Declining', 'change' => $change];
    } elseif ($change < -1.0) {
        return ['risk_level' => 'Medium', 'trend' => 'Declining', 'change' => $change];
    } elseif ($change > 1.0) {
        return ['risk_level' => 'Low', 'trend' => 'Improving', 'change' => $change];
    }
    
    return ['risk_level' => 'Low', 'trend' => 'Stable', 'change' => $change];
}

/**
 * Get training recommendations
 */
function getTrainingRecommendations($conn, $employee_id, $period_id) {
    $query = "
        SELECT training_recommendations
        FROM supervisor_comments
        WHERE employee_id = ? AND period_id = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $employee_id, $period_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    return $result['training_recommendations'] ?? 'No recommendations available';
}

/**
 * Format number as percentage
 */
function formatPercent($value, $decimals = 1) {
    return number_format($value * 100, $decimals) . '%';
}

/**
 * Format score with color
 */
function formatScoreWithColor($score) {
    $rating = KPICalculator::getRatingLabel($score);
    $color = KPICalculator::getPerformanceColor($score);
    return "<span style='color: $color; font-weight: 600;'>$score ($rating)</span>";
}

/**
 * Safe output (prevent XSS)
 */
function safe($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
