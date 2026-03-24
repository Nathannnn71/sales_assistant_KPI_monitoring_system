<?php
/**
 * SAKMS - Settings Page
 */

// Get all departments
$query = "SELECT DISTINCT department FROM employees WHERE status = 'Active' ORDER BY department";
$result = $conn->query($query);
$departments = [];
while ($row = $result->fetch_assoc()) {
    $departments[] = $row['department'];
}

?>
<div class="content active fade-in">
  <div class="dash-grid">

    <!-- ══════════════════════════════════════
         PERFORMANCE THRESHOLD RULES
    ══════════════════════════════════════ -->
    <div class="card col-full">
      <div class="card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/><path d="M8 12h8M12 8v8"/>
        </svg>
        Performance Threshold Rules
      </div>
      
      <div style="margin-top: 12px;">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
          
          <!-- Top Performer Threshold -->
          <div style="background: var(--card-hover); padding: 16px; border-radius: 8px; border-left: 3px solid #22c55e;">
            <div style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; font-weight: 600; margin-bottom: 8px;">Top Performer Threshold</div>
            <div style="display: flex; align-items: center; gap: 8px;">
              <input type="number" value="4.5" min="1" max="5" step="0.1" style="width: 80px; padding: 8px; background: #141c2b; border: 1px solid var(--border); border-radius: 6px; color: var(--text-primary); font-weight: 600;">
              <span style="color: var(--text-secondary); font-size: 12px;">KPI Score or above</span>
            </div>
            <p style="font-size: 11px; color: var(--text-muted); margin-top: 8px;">Employees scoring at or above this threshold are classified as top performers.</p>
          </div>

          <!-- At-Risk Threshold -->
          <div style="background: var(--card-hover); padding: 16px; border-radius: 8px; border-left: 3px solid #ef4444;">
            <div style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; font-weight: 600; margin-bottom: 8px;">At-Risk Threshold</div>
            <div style="display: flex; align-items: center; gap: 8px;">
              <input type="number" value="3.0" min="1" max="5" step="0.1" style="width: 80px; padding: 8px; background: #141c2b; border: 1px solid var(--border); border-radius: 6px; color: var(--text-primary); font-weight: 600;">
              <span style="color: var(--text-secondary); font-size: 12px;">KPI Score or below</span>
            </div>
            <p style="font-size: 11px; color: var(--text-muted); margin-top: 8px;">Employees scoring below this threshold require improvement plans.</p>
          </div>

          <!-- Performance Warning Trend -->
          <div style="background: var(--card-hover); padding: 16px; border-radius: 8px; border-left: 3px solid #f59e0b;">
            <div style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; font-weight: 600; margin-bottom: 8px;">Decline Alert Threshold</div>
            <div style="display: flex; align-items: center; gap: 8px;">
              <input type="number" value="0.5" min="0" max="5" step="0.1" style="width: 80px; padding: 8px; background: #141c2b; border: 1px solid var(--border); border-radius: 6px; color: var(--text-primary); font-weight: 600;">
              <span style="color: var(--text-secondary); font-size: 12px;">Point decline per period</span>
            </div>
            <p style="font-size: 11px; color: var(--text-muted); margin-top: 8px;">Alert if KPI declines by more than this amount.</p>
          </div>

          <!-- Evaluation Periods -->
          <div style="background: var(--card-hover); padding: 16px; border-radius: 8px; border-left: 3px solid #3b82f6;">
            <div style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; font-weight: 600; margin-bottom: 8px;">Current Evaluation Period</div>
            <select style="width: 100%; padding: 8px; background: #141c2b; border: 1px solid var(--border); border-radius: 6px; color: var(--text-primary); font-weight: 600;">
              <option value="4">2025</option>
              <option value="3">2024</option>
              <option value="2">2023</option>
              <option value="1">2022</option>
            </select>
            <p style="font-size: 11px; color: var(--text-muted); margin-top: 8px;">Select the evaluation period for reports and analysis.</p>
          </div>

        </div>
        
        <button onclick="alert('Threshold settings saved!')" style="margin-top: 16px; padding: 11px 20px; background: linear-gradient(135deg, #3b82f6, #2563eb); border: none; border-radius: 8px; color: white; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
          Save Thresholds
        </button>
      </div>
    </div>

    <!-- ══════════════════════════════════════
         NOTIFICATION SETTINGS
    ══════════════════════════════════════ -->
    <div class="card col-full">
      <div class="card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        Notification Settings
      </div>
      
      <div style="margin-top: 12px;">
        <!-- Toggle Switches -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--border);">
          <div>
            <p style="font-size: 12px; font-weight: 600; color: var(--text-primary);">At-Risk Alert Notifications</p>
            <p style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Notify when employees fall below threshold</p>
          </div>
          <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
            <input type="checkbox" checked style="width: 0; height: 0; opacity: 0;">
            <div style="width: 44px; height: 24px; background: #22c55e; border-radius: 12px; position: relative; transition: background 0.3s;">
              <div style="width: 20px; height: 20px; background: white; border-radius: 10px; position: absolute; top: 2px; right: 2px; transition: all 0.3s;"></div>
            </div>
          </label>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--border);">
          <div>
            <p style="font-size: 12px; font-weight: 600; color: var(--text-primary);">Performance Decline Alerts</p>
            <p style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Alert on significant score drops</p>
          </div>
          <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
            <input type="checkbox" checked style="width: 0; height: 0; opacity: 0;">
            <div style="width: 44px; height: 24px; background: #22c55e; border-radius: 12px; position: relative; transition: background 0.3s;">
              <div style="width: 20px; height: 20px; background: white; border-radius: 10px; position: absolute; top: 2px; right: 2px; transition: all 0.3s;"></div>
            </div>
          </label>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--border);">
          <div>
            <p style="font-size: 12px; font-weight: 600; color: var(--text-primary);">Monthly Report Summary</p>
            <p style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Receive monthly KPI analysis reports</p>
          </div>
          <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
            <input type="checkbox" checked style="width: 0; height: 0; opacity: 0;">
            <div style="width: 44px; height: 24px; background: #22c55e; border-radius: 12px; position: relative; transition: background 0.3s;">
              <div style="width: 20px; height: 20px; background: white; border-radius: 10px; position: absolute; top: 2px; right: 2px; transition: all 0.3s;"></div>
            </div>
          </label>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0;">
          <div>
            <p style="font-size: 12px; font-weight: 600; color: var(--text-primary);">Email Notifications</p>
            <p style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Send all notifications via email</p>
          </div>
          <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
            <input type="checkbox" style="width: 0; height: 0; opacity: 0;">
            <div style="width: 44px; height: 24px; background: #e5e7eb; border-radius: 12px; position: relative; transition: background 0.3s;">
              <div style="width: 20px; height: 20px; background: white; border-radius: 10px; position: absolute; top: 2px; left: 2px; transition: all 0.3s;"></div>
            </div>
          </label>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════
         DEPARTMENT MANAGEMENT
    ══════════════════════════════════════ -->
    <div class="card col-full">
      <div class="card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M17 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2z"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/>
        </svg>
        Department & Staff Management
      </div>
      
      <table style="width: 100%; margin-top: 12px;">
        <thead>
          <tr style="border-bottom: 1px solid var(--border);">
            <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-align: left;">Department</th>
            <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-align: center;">Staff Count</th>
            <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-align: center;">Avg KPI</th>
            <th style="padding: 8px 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-align: right;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
            foreach ($departments as $dept) {
              $query = "SELECT COUNT(*) as count, AVG(score) as avg_score FROM employees e 
                       LEFT JOIN kpi_scores ks ON e.employee_id = ks.employee_id 
                       WHERE e.department = ? AND e.status = 'Active'";
              $stmt = $conn->prepare($query);
              $stmt->bind_param("s", $dept);
              $stmt->execute();
              $row = $stmt->get_result()->fetch_assoc();
              
              $avg = $row['avg_score'] ?? 0;
              $color = KPICalculator::getPerformanceColor($avg);
              
              echo "
              <tr style='border-bottom: 1px solid var(--border);'>
                <td style='padding: 12px 0; color: var(--text-primary); font-size: 12px;'>$dept</td>
                <td style='padding: 12px 0; text-align: center; color: var(--text-primary); font-weight: 600;'>{$row['count']}</td>
                <td style='padding: 12px 0; text-align: center;'>
                  <span style='background: $color; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;'>
                    " . number_format($avg, 2) . "
                  </span>
                </td>
                <td style='padding: 12px 0; text-align: right;'>
                  <button onclick=\"alert('Edit department: $dept')\" style='background: var(--accent); color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 11px; cursor: pointer;'>
                    Edit
                  </button>
                </td>
              </tr>
              ";
            }
          ?>
        </tbody>
      </table>
      
      <button onclick="alert('Add new department form')" style="margin-top: 16px; padding: 11px 20px; background: linear-gradient(135deg, #3b82f6, #2563eb); border: none; border-radius: 8px; color: white; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
        + Add Department
      </button>
    </div>

    <!-- ══════════════════════════════════════
         DATA IMPORT
    ══════════════════════════════════════ -->
    <div class="card col-full">
      <div class="card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 3v12m4.5-9.5l-4.5 4.5-4.5-4.5"/><path d="M3 12H2m1 8h16a2 2 0 0 0 2-2v-5a2 2 0 0 0-2-2H3"/>
        </svg>
        Data Import / Export
      </div>
      
      <div style="margin-top: 12px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
        
        <!-- Import Section -->
        <div style="background: var(--card-hover); padding: 16px; border-radius: 8px; border: 2px dashed var(--border);">
          <div style="text-align: center;">
            <div style="font-size: 24px; margin-bottom: 8px;">📥</div>
            <p style="font-size: 12px; font-weight: 600; color: var(--text-primary); margin-bottom: 8px;">Import from Excel</p>
            <p style="font-size: 11px; color: var(--text-muted); margin-bottom: 12px;">Upload KPI scores and employee data</p>
            <input type="file" id="import-file" accept=".xlsx,.xls,.csv" style="display: none;" onchange="alert('File selected. In production, this would process the Excel file.')">
            <button onclick="document.getElementById('import-file').click()" style="background: var(--accent); color: white; border: none; padding: 8px 14px; border-radius: 6px; font-size: 11px; cursor: pointer;">
              Choose File
            </button>
          </div>
        </div>

        <!-- Export Section -->
        <div style="background: var(--card-hover); padding: 16px; border-radius: 8px; border: 2px dashed var(--border);">
          <div style="text-align: center;">
            <div style="font-size: 24px; margin-bottom: 8px;">📤</div>
            <p style="font-size: 12px; font-weight: 600; color: var(--text-primary); margin-bottom: 8px;">Export Data</p>
            <p style="font-size: 11px; color: var(--text-muted); margin-bottom: 12px;">Download RPI reports to Excel</p>
            <button onclick="alert('Export initiated. File will download as sakms-report.xlsx')" style="background: #22c55e; color: white; border: none; padding: 8px 14px; border-radius: 6px; font-size: 11px; cursor: pointer;">
              Export Excel
            </button>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>
