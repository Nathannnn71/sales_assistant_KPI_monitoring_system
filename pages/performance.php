<?php
/**
 * SAKMS - Performance Report & Training Page
 */

$at_risk = getAtRiskEmployees($conn, $period_id);
$top_performers = getTopPerformers($conn, $period_id, 15);

?>
<div class="content active fade-in">
  <div class="dash-grid">

    <!-- ══════════════════════════════════════
         AT-RISK PERFORMANCE IMPROVEMENT PLAN
    ══════════════════════════════════════ -->
    <div class="card col-full">
      <div class="card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3.05h16.94a2 2 0 0 0 1.71-3.05L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        Performance Improvement List
      </div>
      
      <?php if (count($at_risk) > 0): ?>
      <div style="margin-top: 12px;">
        <?php
          foreach ($at_risk as $emp) {
            $comments = getSupervisorComments($conn, $emp['employee_id'], $period_id);
            $training = getTrainingRecommendations($conn, $emp['employee_id'], $period_id);
            $risk = predictPerformanceRisk($conn, $emp['employee_id']);
            
            $risk_color = $risk['risk_level'] === 'Critical' ? '#ef4444' : 
                         ($risk['risk_level'] === 'High' ? '#f59e0b' : '#3b82f6');
            
            echo "
            <div style='background: var(--card-hover); padding: 16px; border-radius: 8px; margin-bottom: 12px; border-left: 4px solid " . KPICalculator::getPerformanceColor($emp['kpi_score']) . ";'>
              <div style='display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;'>
                <div>
                  <h3 style='font-size: 14px; font-weight: 600; color: var(--text-primary);'>{$emp['name']}</h3>
                  <p style='font-size: 11px; color: var(--text-muted); margin-top: 2px;'>{$emp['department']} • {$emp['role']}</p>
                </div>
                <div style='text-align: right;'>
                  <div style='font-size: 16px; font-weight: 700; color: " . KPICalculator::getPerformanceColor($emp['kpi_score']) . ";'>
                    " . number_format($emp['kpi_score'], 2) . "
                  </div>
                  <span style='background: $risk_color; color: white; padding: 3px 6px; border-radius: 3px; font-size: 10px; font-weight: 600; display: inline-block; margin-top: 4px;'>
                    {$risk['risk_level']}
                  </span>
                </div>
              </div>
              
              <div style='background: rgba(0,0,0,0.2); padding: 8px; border-radius: 6px; font-size: 11px; color: var(--text-secondary); margin: 8px 0;'>
                <strong>Training Required:</strong> $training
              </div>
            </div>
            ";
          }
        ?>
      </div>
      <?php else: ?>
      <div style="text-align: center; padding: 20px; color: var(--text-muted);">
        <p>No at-risk staff in this period. Well done! 🎉</p>
      </div>
      <?php endif; ?>
    </div>

    <!-- ══════════════════════════════════════
         TOP PERFORMER RECOGNITION
    ══════════════════════════════════════ -->
    <div class="card col-full">
      <div class="card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
        </svg>
        Top Performer Recognition
      </div>
      
      <table style="width: 100%; margin-top: 12px;">
        <thead>
          <tr style="border-bottom: 1px solid var(--border);">
            <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-align: left;">Employee</th>
            <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-align: center;">KPI Score</th>
            <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-align: left;">Strengths</th>
          </tr>
        </thead>
        <tbody>
          <?php
            foreach ($top_performers as $emp) {
              $group_scores = getKPIGroupScores($conn, $emp['employee_id'], $period_id);
              $strengths = [];
              while ($group = $group_scores->fetch_assoc()) {
                if ($group['avg_score'] >= 4.5) {
                  $strengths[] = $group['kpi_group'];
                }
              }
              $strengths_text = implode(', ', array_slice($strengths, 0, 2));
              
              echo "
              <tr style='border-bottom: 1px solid var(--border);'>
                <td style='padding: 12px 0; color: var(--text-primary); font-size: 12px;'>{$emp['name']}</td>
                <td style='padding: 12px 0; text-align: center;'>
                  <span style='background: #22c55e; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;'>
                    " . number_format($emp['kpi_score'], 2) . "
                  </span>
                </td>
                <td style='padding: 12px 0; color: var(--text-secondary); font-size: 11px;'>$strengths_text</td>
              </tr>
              ";
            }
          ?>
        </tbody>
      </table>
    </div>

    <!-- ══════════════════════════════════════
         TRAINING NEEDS SUMMARY
    ══════════════════════════════════════ -->
    <div class="card col-full">
      <div class="card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><circle cx="12" cy="11" r="3"/><path d="M4 11a8 8 0 0 1 15.998 0M2 12a10 10 0 1 0 20 0"/>
        </svg>
        Training Needs Summary
      </div>
      
      <div style="margin-top: 12px;">
        <?php
          $training_needs = [];
          $quality_count = 0;
          $sales_count = 0;
          $team_count = 0;
          $soft_skills_count = 0;
          
          foreach ($at_risk as $emp) {
            $group_scores = getKPIGroupScores($conn, $emp['employee_id'], $period_id);
            while ($group = $group_scores->fetch_assoc()) {
              if ($group['avg_score'] < 3.0) {
                switch ($group['kpi_group']) {
                  case 'Customer Service Quality':
                    $quality_count++;
                    break;
                  case 'Sales Target Contribution':
                    $sales_count++;
                    break;
                  case 'People, Training & Team':
                    $team_count++;
                    break;
                  default:
                    $soft_skills_count++;
                }
              }
            }
          }
          
          echo "
          <div style='display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;'>
            <div style='background: var(--card-hover); padding: 12px; border-radius: 8px; border-left: 3px solid #3b82f6;'>
              <div style='font-weight: 600; color: var(--text-primary); font-size: 14px;'>$quality_count</div>
              <div style='color: var(--text-secondary); font-size: 11px; margin-top: 4px;'>Customer Service Training</div>
            </div>
            <div style='background: var(--card-hover); padding: 12px; border-radius: 8px; border-left: 3px solid #06b6d4;'>
              <div style='font-weight: 600; color: var(--text-primary); font-size: 14px;'>$sales_count</div>
              <div style='color: var(--text-secondary); font-size: 11px; margin-top: 4px;'>Sales Techniques</div>
            </div>
            <div style='background: var(--card-hover); padding: 12px; border-radius: 8px; border-left: 3px solid #f59e0b;'>
              <div style='font-weight: 600; color: var(--text-primary); font-size: 14px;'>$team_count</div>
              <div style='color: var(--text-secondary); font-size: 11px; margin-top: 4px;'>Team Collaboration</div>
            </div>
            <div style='background: var(--card-hover); padding: 12px; border-radius: 8px; border-left: 3px solid #22c55e;'>
              <div style='font-weight: 600; color: var(--text-primary); font-size: 14px;'>$soft_skills_count</div>
              <div style='color: var(--text-secondary); font-size: 11px; margin-top: 4px;'>Professional Development</div>
            </div>
          </div>
          ";
        ?>
      </div>
    </div>

  </div>
</div>
