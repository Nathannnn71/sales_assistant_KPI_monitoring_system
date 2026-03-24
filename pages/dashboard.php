<?php
/**
 * SAKMS - Supervisor Dashboard Page
 * Content loaded by index.php
 */

// Get summary data
$all_employees = getAllEmployeesSummary($conn, $period_id);
$total_employees = 0;
$employee_data = [];

while ($emp = $all_employees->fetch_assoc()) {
    $total_employees++;
    $kpi = KPICalculator::calculateKPI($conn, $emp['employee_id'], $period_id);
    $emp['kpi_score'] = $kpi['overall'];
    $emp['rating'] = $kpi['rating'];
    $employee_data[] = $emp;
}

$top_performers = getTopPerformers($conn, $period_id, 5);
$at_risk = getAtRiskEmployees($conn, $period_id);
$dept_stats = getAverageByDepartment($conn, $period_id);
$perf_dist = getPerformanceDistribution($conn, $period_id);

// Count classifications
$top_count = count($top_performers);
$risk_count = count($at_risk);
$avg_count = $total_employees - $top_count - $risk_count;
?>

<div class="content active fade-in">
  <div class="dash-grid">

    <!-- ══════════════════════════════════════
         KPI SUMMARY CARDS
    ══════════════════════════════════════ -->
    
    <!-- Total Employees Card -->
    <div class="card summary-card">
      <div class="summary-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
      </div>
      <div>
        <div class="summary-label">Total Sales Assistants</div>
        <div class="summary-value"><?php echo $total_employees; ?></div>
      </div>
    </div>

    <!-- Top Performers Card -->
    <div class="card summary-card">
      <div class="summary-icon" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
        </svg>
      </div>
      <div>
        <div class="summary-label">Top Performers</div>
        <div class="summary-value"><?php echo $top_count; ?></div>
      </div>
    </div>

    <!-- At-Risk Staff Card -->
    <div class="card summary-card">
      <div class="summary-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 9v2m0 4v2m5.16-9.16l-1.42 1.42m2.84-2.84l-1.42-1.42M7.16 7.16L5.74 5.74m1.42 8.84l-1.42 1.42"/>
        </svg>
      </div>
      <div>
        <div class="summary-label">At-Risk Staff</div>
        <div class="summary-value"><?php echo $risk_count; ?></div>
      </div>
    </div>

    <!-- Average Score Card -->
    <div class="card summary-card">
      <div class="summary-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="12 3 20 7.5 20 16.5 12 21 4 16.5 4 7.5 12 3"/><polyline points="12 12 20 7.5"/><polyline points="12 12 12 21"/><polyline points="12 12 4 7.5"/>
        </svg>
      </div>
      <div>
        <div class="summary-label">Avg KPI Score</div>
        <div class="summary-value">
          <?php 
            $avg_score = 0;
            foreach ($employee_data as $emp) {
              $avg_score += $emp['kpi_score'];
            }
            echo number_format($avg_score / $total_employees, 2);
          ?>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════
         PREDICTIVE PERFORMANCE RISK ALERTS
         (Innovative Feature 1)
    ══════════════════════════════════════ -->
    <div class="card col-full">
      <div class="card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 9v2m0 4v2M9.59 4.59A2 2 0 1 0 15 9.82m-6.82 5.18A2 2 0 1 0 15 19.82"/>
        </svg>
        Predictive Performance Risk Alerts
      </div>
      <div style="margin-top: 12px;">
        <?php
          $high_risk_count = 0;
          $medium_risk_count = 0;
          
          foreach ($at_risk as $emp) {
            $risk = predictPerformanceRisk($conn, $emp['employee_id']);
            if ($risk['risk_level'] === 'Critical' || $risk['risk_level'] === 'High') {
              $high_risk_count++;
            } else {
              $medium_risk_count++;
            }
          }
          
          if ($high_risk_count > 0) {
            echo "
            <div class='alert-box' style='background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444; padding: 12px; margin-bottom: 8px; border-radius: 6px;'>
              <div style='font-weight: 600; color: #ef4444; font-size: 12px;'>🔴 CRITICAL ALERT</div>
              <div style='color: var(--text-secondary); font-size: 12px; margin-top: 4px;'>
                <strong>$high_risk_count</strong> employee(s) showing critical or high performance risk with declining trends.
              </div>
            </div>
            ";
          }
          
          if ($medium_risk_count > 0 && $high_risk_count === 0) {
            echo "
            <div class='alert-box' style='background: rgba(245, 158, 11, 0.1); border-left: 4px solid #f59e0b; padding: 12px; margin-bottom: 8px; border-radius: 6px;'>
              <div style='font-weight: 600; color: #f59e0b; font-size: 12px;'>⚠️ WARNING</div>
              <div style='color: var(--text-secondary); font-size: 12px; margin-top: 4px;'>
                <strong>$medium_risk_count</strong> employee(s) require monitoring for performance improvement.
              </div>
            </div>
            ";
          }
          
          if ($high_risk_count === 0 && $medium_risk_count === 0) {
            echo "
            <div class='alert-box' style='background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e; padding: 12px; margin-bottom: 8px; border-radius: 6px;'>
              <div style='font-weight: 600; color: #22c55e; font-size: 12px;'>✓ HEALTHY</div>
              <div style='color: var(--text-secondary); font-size: 12px; margin-top: 4px;'>
                All employees are performing within acceptable ranges. No critical performance risks detected.
              </div>
            </div>
            ";
          }
        ?>
      </div>
    </div>

    <!-- ══════════════════════════════════════
         PERFORMANCE DISTRIBUTION CHART
    ══════════════════════════════════════ -->
    <div class="card col-full">
      <div class="card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="12" width="4" height="8"/><rect x="10" y="5" width="4" height="15"/><rect x="17" y="3" width="4" height="17"/>
        </svg>
        Performance Distribution
      </div>
      <div style="height: 300px; margin-top: 16px; position: relative;">
        <canvas id="distributionChart"></canvas>
      </div>
    </div>

    <!-- ══════════════════════════════════════
         TOP PERFORMERS
    ══════════════════════════════════════ -->
    <div class="card col-full">
      <div class="card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
        </svg>
        Top Performers (KPI ≥ 4.5)
      </div>
      <table style="width: 100%; margin-top: 12px;">
        <thead>
          <tr style="border-bottom: 1px solid var(--border); text-align: left;">
            <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Name</th>
            <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Department</th>
            <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">KPI Score</th>
            <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Rating</th>
          </tr>
        </thead>
        <tbody>
          <?php
            if (count($top_performers) > 0) {
              foreach ($top_performers as $emp) {
                $color = KPICalculator::getPerformanceColor($emp['kpi_score']);
                echo "
                <tr style='border-bottom: 1px solid var(--border);'>
                  <td style='padding: 12px 0; color: var(--text-primary);'>
                    <a href='index.php?page=profiles&emp_id={$emp['employee_id']}' style='text-decoration: none; color: var(--accent);'>
                      {$emp['name']}
                    </a>
                  </td>
                  <td style='padding: 12px 0; color: var(--text-secondary); font-size: 12px;'>{$emp['department']}</td>
                  <td style='padding: 12px 0; color: var(--text-primary); font-weight: 600;'>" . number_format($emp['kpi_score'], 2) . "</td>
                  <td style='padding: 12px 0;'>
                    <span style='background: " . $color . "; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;'>
                      {$emp['rating']}
                    </span>
                  </td>
                </tr>
                ";
              }
            } else {
              echo "<tr><td colspan='4' style='padding: 12px; text-align: center; color: var(--text-muted);'>No top performers yet</td></tr>";
            }
          ?>
        </tbody>
      </table>
    </div>

    <!-- ══════════════════════════════════════
         AT-RISK STAFF
    ══════════════════════════════════════ -->
    <div class="card col-full">
      <div class="card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 9v2m0 4v2m5.16-9.16l-1.42 1.42m2.84-2.84l-1.42-1.42M7.16 7.16L5.74 5.74m1.42 8.84l-1.42 1.42"/>
        </svg>
        At-Risk Staff (KPI < 3.0)
      </div>
      <table style="width: 100%; margin-top: 12px;">
        <thead>
          <tr style="border-bottom: 1px solid var(--border); text-align: left;">
            <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Name</th>
            <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Department</th>
            <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">KPI Score</th>
            <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php
            if (count($at_risk) > 0) {
              foreach ($at_risk as $emp) {
                $color = KPICalculator::getPerformanceColor($emp['kpi_score']);
                echo "
                <tr style='border-bottom: 1px solid var(--border);'>
                  <td style='padding: 12px 0; color: var(--text-primary);'>
                    <a href='index.php?page=profiles&emp_id={$emp['employee_id']}' style='text-decoration: none; color: var(--accent);'>
                      {$emp['name']}
                    </a>
                  </td>
                  <td style='padding: 12px 0; color: var(--text-secondary); font-size: 12px;'>{$emp['department']}</td>
                  <td style='padding: 12px 0; color: var(--text-primary); font-weight: 600;'>" . number_format($emp['kpi_score'], 2) . "</td>
                  <td style='padding: 12px 0;'>
                    <span style='background: " . $color . "; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;'>
                      {$emp['status']}
                    </span>
                  </td>
                </tr>
                ";
              }
            } else {
              echo "<tr><td colspan='4' style='padding: 12px; text-align: center; color: var(--text-muted);'>No at-risk staff</td></tr>";
            }
          ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<script>
  // Performance Distribution Chart
  document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('distributionChart').getContext('2d');
    const distributionData = <?php echo json_encode($perf_dist); ?>;
    
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: Object.keys(distributionData),
        datasets: [{
          label: 'Number of Employees',
          data: Object.values(distributionData),
          backgroundColor: [
            '#22c55e',  // Excellent
            '#3b82f6',  // Good
            '#f59e0b',  // Satisfactory
            '#ef4444',  // Poor
            '#7f1d1d'   // Very Poor
          ],
          borderRadius: 6,
          barThickness: 40
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { color: 'var(--text-secondary)', font: { size: 11 } },
            grid: { color: 'var(--border)', drawBorder: false }
          },
          x: {
            ticks: { color: 'var(--text-secondary)', font: { size: 11 } },
            grid: { display: false }
          }
        }
      }
    });
  });
</script>
