<?php
/**
 * SAKMS - Sales Assistant Profiles Page
 */

// If viewing a specific employee profile
if ($employee_id) {
    $profile = getEmployeeProfile($conn, $employee_id);
    if (!$profile) {
        echo '<div class="content active"><div style="padding: 30px; text-align: center;"><p>Employee not found</p></div></div>';
        exit();
    }
    
    $kpi = KPICalculator::calculateKPI($conn, $employee_id, $period_id);
    $comments = getSupervisorComments($conn, $employee_id, $period_id);
    $group_scores = getKPIGroupScores($conn, $employee_id, $period_id);
    $risk = predictPerformanceRisk($conn, $employee_id);
    $training = getTrainingRecommendations($conn, $employee_id, $period_id);
    
    ?>
    <div class="content active fade-in">
      <div class="dash-grid">
        
        <!-- Back Button -->
        <div class="card col-full" style="background: transparent; border: none; padding: 0 0 16px 0;">
          <a href="index.php?page=profiles" style="color: var(--accent); text-decoration: none; font-size: 12px;">
            ← Back to Profiles
          </a>
        </div>

        <!-- Profile Header -->
        <div class="card col-full">
          <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
              <h2 style="font-size: 20px; margin-bottom: 4px;"><?php echo safe($profile['name']); ?></h2>
              <p style="color: var(--text-secondary); font-size: 12px;"><?php echo safe($profile['role']) . ' • ' . safe($profile['department']); ?></p>
              <p style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">Staff ID: <?php echo safe($profile['staff_id']); ?></p>
            </div>
            <div style="text-align: right;">
              <div style="font-size: 28px; font-weight: 700; color: var(--accent);"><?php echo number_format($kpi['overall'], 2); ?></div>
              <div style="background: " . KPICalculator::getPerformanceColor($kpi['overall']) . "; color: white; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 600; display: inline-block; margin-top: 4px;">
                <?php echo $kpi['rating']; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Performance Risk Alert -->
        <div class="card col-full" style="background: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.2);">
          <div class="card-title">Performance Risk Assessment</div>
          <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 12px;">
            <div>
              <div style="color: var(--text-muted); font-size: 11px;">Risk Level</div>
              <div style="font-weight: 700; font-size: 14px; margin-top: 4px; color: var(--text-primary);"><?php echo $risk['risk_level']; ?></div>
            </div>
            <div>
              <div style="color: var(--text-muted); font-size: 11px;">Trend</div>
              <div style="font-weight: 700; font-size: 14px; margin-top: 4px; color: var(--text-primary);"><?php echo $risk['trend']; ?></div>
            </div>
            <div>
              <div style="color: var(--text-muted); font-size: 11px;">Period Change</div>
              <div style="font-weight: 700; font-size: 14px; margin-top: 4px; color: <?php echo $risk['change'] > 0 ? '#22c55e' : '#ef4444'; ?>;">
                <?php echo $risk['change'] > 0 ? '+' : ''; echo number_format($risk['change'], 2); ?>
              </div>
            </div>
          </div>
        </div>

        <!-- KPI Breakdown by Section -->
        <div class="card">
          <div class="card-title">Core Competencies (Section 1)</div>
          <div style="margin-top: 16px;">
            <div style="font-size: 14px; font-weight: 700;"><?php echo number_format($kpi['section1'], 2); ?>/1.25</div>
            <div style="background: var(--bg-input); height: 6px; border-radius: 3px; margin-top: 6px; overflow: hidden;">
              <div style="background: linear-gradient(90deg, #3b82f6, #2563eb); height: 100%; width: <?php echo min(($kpi['section1'] / 1.25) * 100, 100); ?>%;"></div>
            </div>
            <div style="color: var(--text-secondary); font-size: 11px; margin-top: 8px;">25% of total score</div>
          </div>
        </div>

        <div class="card">
          <div class="card-title">KPI Achievement (Section 2)</div>
          <div style="margin-top: 16px;">
            <div style="font-size: 14px; font-weight: 700;"><?php echo number_format($kpi['section2'], 2); ?>/3.75</div>
            <div style="background: var(--bg-input); height: 6px; border-radius: 3px; margin-top: 6px; overflow: hidden;">
              <div style="background: linear-gradient(90deg, #06b6d4, #0891b2); height: 100%; width: <?php echo min(($kpi['section2'] / 3.75) * 100, 100); ?>%;"></div>
            </div>
            <div style="color: var(--text-secondary); font-size: 11px; margin-top: 8px;">75% of total score</div>
          </div>
        </div>

        <!-- KPI Group Performance -->
        <div class="card col-full">
          <div class="card-title">Performance by KPI Group</div>
          <table style="width: 100%; margin-top: 12px;">
            <thead>
              <tr style="border-bottom: 1px solid var(--border);">
                <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-align: left;">KPI Group</th>
                <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-align: center;">Avg Score</th>
                <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-align: right;">Count</th>
              </tr>
            </thead>
            <tbody>
              <?php
                while ($group = $group_scores->fetch_assoc()) {
                  $score_color = KPICalculator::getPerformanceColor($group['avg_score']);
                  echo "
                  <tr style='border-bottom: 1px solid var(--border);'>
                    <td style='padding: 12px 0; color: var(--text-primary); font-size: 12px;'>{$group['kpi_group']}</td>
                    <td style='padding: 12px 0; text-align: center;'>
                      <span style='background: $score_color; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;'>
                        " . number_format($group['avg_score'], 1) . "
                      </span>
                    </td>
                    <td style='padding: 12px 0; text-align: right; color: var(--text-secondary); font-size: 12px;'>{$group['count']} items</td>
                  </tr>
                  ";
                }
              ?>
            </tbody>
          </table>
        </div>

        <!-- Supervisor Comments -->
        <div class="card col-full">
          <div class="card-title">Supervisor Comments</div>
          <div style="margin-top: 12px; background: var(--bg-input); padding: 12px; border-radius: 8px; font-size: 12px; line-height: 1.6; color: var(--text-primary);">
            <?php echo nl2br(safe($comments['supervisor_comments'] ?? 'No comments available')); ?>
          </div>
        </div>

        <!-- Training Recommendations (Innovative Feature 3) -->
        <div class="card col-full" style="background: rgba(34, 197, 94, 0.05); border: 1px solid rgba(34, 197, 94, 0.2);">
          <div class="card-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2z"/><path d="M12 6v6l4 2"/>
            </svg>
            Goal-Setting & KPI Forecasting (Innovative Feature)
          </div>
          <div style="margin-top: 12px;">
            <div style="background: var(--card-hover); padding: 12px; border-radius: 8px; border-left: 3px solid var(--accent-2);">
              <div style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; font-weight: 600; margin-bottom: 4px;">Recommended Training</div>
              <p style="font-size: 12px; color: var(--text-primary); line-height: 1.6;">
                <?php echo nl2br(safe($training)); ?>
              </p>
            </div>
            <div style="margin-top: 12px; padding: 12px; background: var(--card-hover); border-radius: 8px; border-left: 3px solid var(--accent);">
              <div style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; font-weight: 600; margin-bottom: 4px;">KPI Forecast</div>
              <p style="font-size: 12px; color: var(--text-primary);">
                Based on current performance trend, predicted next period KPI: <strong style="color: var(--accent);">
                  <?php 
                    $forecast = $kpi['overall'] + ($risk['change'] * 0.5);
                    $forecast = max(1, min(5, $forecast));
                    echo number_format($forecast, 2);
                  ?>
                </strong>
              </p>
            </div>
          </div>
        </div>

      </div>
    </div>
    <?php
    
} else {
    // Show profiles list
    $all_emps = getAllEmployeesSummary($conn, $period_id);
    $profiles_data = [];
    
    while ($emp = $all_emps->fetch_assoc()) {
        $kpi = KPICalculator::calculateKPI($conn, $emp['employee_id'], $period_id);
        $emp['kpi_score'] = $kpi['overall'];
        $emp['rating'] = $kpi['rating'];
        $profiles_data[] = $emp;
    }
    
    // Sort by department then name
    usort($profiles_data, function($a, $b) {
        $dept_cmp = strcmp($a['department'], $b['department']);
        if ($dept_cmp === 0) {
            return strcmp($a['name'], $b['name']);
        }
        return $dept_cmp;
    });
    
    ?>
    <div class="content active fade-in">
      <div class="dash-grid">
        
        <div class="card col-full">
          <div class="card-title">All Sales Assistant Profiles</div>
          <table style="width: 100%; margin-top: 12px;">
            <thead>
              <tr style="border-bottom: 1px solid var(--border);">
                <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-align: left;">Name</th>
                <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-align: left;">Department</th>
                <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-align: center;">KPI Score</th>
                <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-align: center;">Rating</th>
                <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-align: center;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
                foreach ($profiles_data as $emp) {
                  $color = KPICalculator::getPerformanceColor($emp['kpi_score']);
                  echo "
                  <tr style='border-bottom: 1px solid var(--border);'>
                    <td style='padding: 12px 0; color: var(--text-primary); font-size: 12px;'>{$emp['name']}</td>
                    <td style='padding: 12px 0; color: var(--text-secondary); font-size: 12px;'>{$emp['department']}</td>
                    <td style='padding: 12px 0; text-align: center; color: var(--text-primary); font-weight: 600;'>" . number_format($emp['kpi_score'], 2) . "</td>
                    <td style='padding: 12px 0; text-align: center;'>
                      <span style='background: $color; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;'>
                        {$emp['rating']}
                      </span>
                    </td>
                    <td style='padding: 12px 0; text-align: center;'>
                      <a href='index.php?page=profiles&emp_id={$emp['employee_id']}' 
                         style='color: var(--accent); text-decoration: none; font-size: 11px; font-weight: 600;'>
                        View Profile
                      </a>
                    </td>
                  </tr>
                  ";
                }
              ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>
    <?php
}
