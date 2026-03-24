<?php
/**
 * SAKMS - Analytics Dashboard Page
 */

$all_emps = getAllEmployeesSummary($conn, $period_id);
$analytics_data = [];

while ($emp = $all_emps->fetch_assoc()) {
    $kpi = KPICalculator::calculateKPI($conn, $emp['employee_id'], $period_id);
    $emp['kpi_score'] = $kpi['overall'];
    $emp['rating'] = $kpi['rating'];
    $analytics_data[] = $emp;
}

// Sort by KPI score descending
usort($analytics_data, function($a, $b) {
    return $b['kpi_score'] - $a['kpi_score'];
});

$dept_stats = getAverageByDepartment($conn, $period_id);
$dept_data = [];
while ($dept = $dept_stats->fetch_assoc()) {
    $dept_data[] = $dept;
}

$perf_dist = getPerformanceDistribution($conn, $period_id);

?>
<div class="content active fade-in">
  <div class="dash-grid">

    <!-- ══════════════════════════════════════
         DEPARTMENT PERFORMANCE COMPARISON
    ══════════════════════════════════════ -->
    <div class="card col-full">
      <div class="card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/><path d="M12 2v10m6.88-3.88l-7.07 7.07M12 22v-10M5.12 5.12l7.07 7.07M2 12h10m-3.88-6.88l7.07 7.07"/>
        </svg>
        Department Performance Comparison
      </div>
      <div style="height: 300px; margin-top: 16px; position: relative;">
        <canvas id="deptChart"></canvas>
      </div>
    </div>

    <!-- ══════════════════════════════════════
         INDIVIDUAL PERFORMANCE RANKING
         (Gamified Elements)
    ══════════════════════════════════════ -->
    <div class="card col-full">
      <div class="card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="12 3 20 7.5 20 16.5 12 21 4 16.5 4 7.5 12 3"/><polyline points="12 12 20 7.5"/><polyline points="12 12 12 21"/><polyline points="12 12 4 7.5"/>
        </svg>
        Gamified Performance Ranking
      </div>
      <div style="margin-top: 12px;">
        <?php
          $rank = 1;
          foreach ($analytics_data as $emp) {
            $medal = '';
            if ($rank === 1) $medal = '🥇';
            elseif ($rank === 2) $medal = '🥈';
            elseif ($rank === 3) $medal = '🥉';
            
            $color = KPICalculator::getPerformanceColor($emp['kpi_score']);
            $width = ($emp['kpi_score'] / 5) * 100;
            
            echo "
            <div style='margin-bottom: 12px; display: flex; align-items: center; gap: 12px;'>
              <div style='min-width: 30px; text-align: center; font-weight: 700; font-size: 16px;'>
                $medal #$rank
              </div>
              <div style='flex: 1;'>
                <div style='display: flex; justify-content: space-between; margin-bottom: 4px;'>
                  <span style='font-size: 12px; font-weight: 500; color: var(--text-primary);'>{$emp['name']}</span>
                  <span style='font-size: 12px; font-weight: 600; color: var(--text-primary);'>" . number_format($emp['kpi_score'], 2) . "</span>
                </div>
                <div style='background: var(--bg-input); height: 8px; border-radius: 4px; overflow: hidden;'>
                  <div style='background: $color; height: 100%; width: $width%; transition: width 0.3s ease;'></div>
                </div>
              </div>
            </div>
            ";
            $rank++;
          }
        ?>
      </div>
    </div>

    <!-- ══════════════════════════════════════
         PERFORMANCE DISTRIBUTION (PIE CHART)
    ══════════════════════════════════════ -->
    <div class="card">
      <div class="card-title">Distribution</div>
      <div style="height: 250px; position: relative; margin-top: 16px;">
        <canvas id="distChart"></canvas>
      </div>
    </div>

    <!-- ══════════════════════════════════════
         SUMMARY STATS
    ══════════════════════════════════════ -->
    <div class="card">
      <div class="card-title">Summary Statistics</div>
      <div style="margin-top: 12px;">
        <?php
          $scores = array_map(function($e) { return $e['kpi_score']; }, $analytics_data);
          sort($scores);
          
          $min = min($scores);
          $max = max($scores);
          $avg = array_sum($scores) / count($scores);
          $median = $scores[intval(count($scores) / 2)];
          
          echo "
          <div style='font-size: 12px; line-height: 1.8;'>
            <div style='display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid var(--border);'>
              <span style='color: var(--text-muted);'>Average</span>
              <span style='font-weight: 600; color: var(--text-primary);'>" . number_format($avg, 2) . "</span>
            </div>
            <div style='display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border);'>
              <span style='color: var(--text-muted);'>Median</span>
              <span style='font-weight: 600; color: var(--text-primary);'>" . number_format($median, 2) . "</span>
            </div>
            <div style='display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border);'>
              <span style='color: var(--text-muted);'>Highest</span>
              <span style='font-weight: 600; color: #22c55e;'>" . number_format($max, 2) . "</span>
            </div>
            <div style='display: flex; justify-content: space-between; padding: 8px 0;'>
              <span style='color: var(--text-muted);'>Lowest</span>
              <span style='font-weight: 600; color: #ef4444;'>" . number_format($min, 2) . "</span>
            </div>
          </div>
          ";
        ?>
      </div>
    </div>

  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    
    // Department Performance Chart
    const deptCtx = document.getElementById('deptChart').getContext('2d');
    const deptData = <?php echo json_encode(array_map(function($d) { return $d['avg_score'] ?? 0; }, $dept_data)); ?>;
    const deptLabels = <?php echo json_encode(array_map(function($d) { return $d['department']; }, $dept_data)); ?>;
    
    new Chart(deptCtx, {
      type: 'bar',
      data: {
        labels: deptLabels,
        datasets: [{
          label: 'Average KPI Score',
          data: deptData,
          backgroundColor: ['#3b82f6', '#06b6d4', '#f59e0b'],
          borderRadius: 6,
          barThickness: 50
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: {
            beginAtZero: true,
            max: 5,
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

    // Distribution Pie Chart
    const distCtx = document.getElementById('distChart').getContext('2d');
    const distData = <?php echo json_encode($perf_dist); ?>;
    
    new Chart(distCtx, {
      type: 'doughnut',
      data: {
        labels: Object.keys(distData),
        datasets: [{
          data: Object.values(distData),
          backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b', '#ef4444', '#7f1d1d'],
          borderColor: 'var(--border)',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: { color: 'var(--text-secondary)', font: { size: 11 }, padding: 12 }
          }
        }
      }
    });
  });
</script>
