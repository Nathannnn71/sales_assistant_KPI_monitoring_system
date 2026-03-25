<?php
//SAKMS - Analytics Dashboard Page
// Get all employees data for analytics
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

// Fetch KPI trend data for all employees
$kpi_trend_query = "
    SELECT 
        ks.employee_id,
        DATE_FORMAT(ks.evaluation_date, '%Y-%m-%d') as evaluation_date,
        YEAR(ks.evaluation_date) as year,
        MONTH(ks.evaluation_date) as month,
        DATE_FORMAT(ks.evaluation_date, '%Y-%m') as date_label,
        km.kpi_group,
        AVG(ks.score) as avg_score
    FROM kpi_scores ks
    JOIN kpi_master km ON ks.kpi_code = km.kpi_code
    WHERE km.section = 'Section 2'
    GROUP BY ks.employee_id, DATE_FORMAT(ks.evaluation_date, '%Y-%m-%d'), YEAR(ks.evaluation_date), MONTH(ks.evaluation_date), km.kpi_group
    ORDER BY ks.employee_id, ks.evaluation_date, km.kpi_group
";
$kpi_trend_result = $conn->query($kpi_trend_query);
$kpi_trend_data = [];
while ($row = $kpi_trend_result->fetch_assoc()) {
    $kpi_trend_data[] = $row;
}

// Fetch KPI category average scores
$kpi_category_query = "
    SELECT 
        km.kpi_group,
        AVG(ks.score) as avg_score,
        COUNT(ks.score) as count,
        ROUND(AVG(ks.score), 2) as rounded_avg
    FROM kpi_scores ks
    JOIN kpi_master km ON ks.kpi_code = km.kpi_code
    WHERE km.section = 'Section 2'
    GROUP BY km.kpi_group
    ORDER BY avg_score DESC
";
$kpi_category_result = $conn->query($kpi_category_query);
$kpi_categories = [];
while ($row = $kpi_category_result->fetch_assoc()) {
    $kpi_categories[] = $row;
}

// Get performance distribution
$perf_dist = getPerformanceDistribution($conn, $period_id);

// Get unique years from data
$year_query = "SELECT DISTINCT YEAR(evaluation_date) as year FROM kpi_scores ORDER BY year ASC";
$year_result = $conn->query($year_query);
$available_years = [];
while ($row = $year_result->fetch_assoc()) {
    $available_years[] = $row['year'];
}

?>
<div class="content active fade-in">
  <div class="dash-grid">

    <!-- PANEL 1: FILTER ENGINE - Date Range & KPI Category -->
    <div class="card col-full">
      <div class="card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        Filter Engine
      </div>
      <div class="filter-section-container">
        
        <!-- Date Range Picker -->
        <div class="filter-subsection">
          <label class="filter-label">Date Range</label>
          <button id="dateRangeBtn" class="date-range-trigger">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <span id="dateRangeLabel">Any Date</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left: auto;">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </button>

          <!-- Date Range Dropdown -->
          <div id="dateRangeDropdown" class="date-range-dropdown">
            <!-- Custom Range Section -->
            <div class="custom-range-section">
              <label class="filter-label-small">Custom Range</label>
              <div class="date-input-group">
                <input type="date" id="dateRangeFrom" placeholder="From Date" value="2024-01-01" class="date-input">
                <span class="date-separator">-</span>
                <input type="date" id="dateRangeTo" placeholder="To Date" value="2025-12-31" class="date-input">
              </div>
            </div>

            <!-- Quick Select Presets -->
            <div class="quick-select-section">
              <label class="filter-label-small">Quick Select</label>
              <button class="preset-btn" data-preset="today">Today</button>
              <button class="preset-btn" data-preset="lastMonth">This Month</button>
              <button class="preset-btn" data-preset="pastMonth">Past Month</button>
              <button class="preset-btn" data-preset="past3Months">Past 3 Months</button>
              <button class="preset-btn" data-preset="allYears">All Years</button>
            </div>
          </div>
        </div>

        <!-- KPI Category Filter -->
        <div class="filter-subsection">
          <label class="filter-label">KPI Category</label>
          <button id="kpiCategoryBtn" class="kpi-filter-trigger">
            <span id="kpiCategoryLabel">All Categories</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </button>

          <!-- KPI Category Dropdown -->
          <div id="kpiCategoryDropdown" class="kpi-category-dropdown">
            <label class="checkbox-label category-label">
              <input type="checkbox" class="checkbox-input kpi-checkbox" value="all" checked id="kpiCheckAll">
              <span>All Categories</span>
            </label>
            <?php foreach ($kpi_categories as $cat): ?>
              <label class="checkbox-label">
                <input type="checkbox" class="checkbox-input kpi-checkbox" value="<?php echo htmlspecialchars($cat['kpi_group']); ?>" checked>
                <span><?php echo htmlspecialchars($cat['kpi_group']); ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- PANEL 2: GAMIFIED PERFORMANCE RANKING -->
    <div class="card col-full">
      <div class="card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="12 3 20 7.5 20 16.5 12 21 4 16.5 4 7.5 12 3"/><polyline points="12 12 20 7.5"/><polyline points="12 12 12 21"/><polyline points="12 12 4 7.5"/>
        </svg>
        Performance Ranking
      </div>
      <div id="gamifiedRankingContainer" class="ranking-container">
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
            <div class='ranking-item'>
              <div class='ranking-medal'>
                $medal <span class='ranking-medal-number'>#$rank</span>
              </div>
              <div class='ranking-content'>
                <div class='ranking-name-score'>
                  <span class='ranking-name'>{$emp['name']}</span>
                  <span class='ranking-score-badge'>" . number_format($emp['kpi_score'], 2) . "</span>
                </div>
                <div class='progress-bar-wrap'>
                  <div class='progress-bar-fill' style='background: $color; width: $width%;'></div>
                </div>
              </div>
            </div>
            ";
            $rank++;
          }
        ?>
      </div>
    </div>

    <!--PANEL 3: TREND ANALYSIS : KPI PERFORMANCE OVER TIME- Line Graph -->
    <div class="card col-full">
      <div class="card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="23 6 13.5 15.5 8.5 10.5 1 17"/><polyline points="17 6 23 6 23 12"/>
        </svg>
        Trend Analysis - KPI Performance Over Time
      </div>
      <div class="chart-container-grid">
        <canvas id="trendChart"></canvas>
      </div>
    </div>

    <!--PANEL 4: PERFORMANCE SCORE DISTRIBUTION - HISTOGRAM-->
    <div class="card col-half" style="width:900px;">
  <div class="card-title">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
    </svg>
    Performance Score Distribution
  </div>
  <div style="height: 320px; margin-top: 16px; position: relative;">
    <canvas id="distributionChart"></canvas>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     PANEL 5: KPI CATEGORY AVERAGE SCORS - RADAR CHART
═════════════════════════════════════════════════════════════ -->
<div class="card col-half" style="width:900px;">
  <div class="card-title">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
    </svg>
    KPI Category Average Scores
  </div>
  <div style="height: 320px; margin-top: 16px; position: relative;">
    <canvas id="categoryChart"></canvas>
  </div>
</div>

  </div>
</div>

<script>
  // ═════════════════════════════════════════════════════════════
  // GLOBAL DATA VARIABLES
  // ═════════════════════════════════════════════════════════════
  const analyticsData = <?php echo json_encode($analytics_data); ?>;
  const kpiTrendData = <?php echo json_encode($kpi_trend_data); ?>;
  const kpiCategoryData = <?php echo json_encode($kpi_categories); ?>;
  const performanceDistribution = <?php echo json_encode($perf_dist); ?>;

  // Chart instances
  let trendChart = null;
  let distributionChart = null;
  let categoryChart = null;

  // Color palette for KPI categories
  const categoryColors = {
    'Daily Sales Operations': '#3B82F6',
    'Customer Service Quality': '#06B6D4',
    'Sales Target Contribution': '#8B5CF6',
    'Training, Learning & Team Contribution': '#EC4899',
    'Inventory & Cost Control': '#F59E0B',
    'Store Operations Support': '#10B981'
  };

  // ═════════════════════════════════════════════════════════════
  // DATE RANGE PICKER FUNCTIONALITY
  // ═════════════════════════════════════════════════════════════
  const dateRangeBtn = document.getElementById('dateRangeBtn');
  const dateRangeDropdown = document.getElementById('dateRangeDropdown');
  const dateRangeLabel = document.getElementById('dateRangeLabel');
  const dateRangeFrom = document.getElementById('dateRangeFrom');
  const dateRangeTo = document.getElementById('dateRangeTo');
  const presetBtns = document.querySelectorAll('.preset-btn');

  // Toggle dropdown
  dateRangeBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    const isVisible = dateRangeDropdown.style.display === 'block';
    dateRangeDropdown.style.display = isVisible ? 'none' : 'block';
  });

  // Close dropdown on outside click
  document.addEventListener('click', (e) => {
    if (!e.target.closest('#dateRangeBtn') && !e.target.closest('#dateRangeDropdown')) {
      dateRangeDropdown.style.display = 'none';
    }
    if (!e.target.closest('#kpiCategoryBtn') && !e.target.closest('#kpiCategoryDropdown')) {
      kpiCategoryDropdown.style.display = 'none';
    }
  });

  // Helper function to format date as YYYY-MM-DD
  function formatDateString(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return year + '-' + month + '-' + day;
  }

  // Helper function to format date for display
  function formatDateForDisplay(dateString) {
    const date = new Date(dateString + 'T00:00:00');
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
  }

  presetBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const preset = btn.dataset.preset;
      const today = new Date();
      let fromDate, toDate;

      switch(preset) {
        case 'today':
          fromDate = toDate = today;
          break;
        case 'lastMonth':
          fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
          toDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
          break;
        case 'pastMonth':
          fromDate = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());
          toDate = today;
          break;
        case 'past3Months':
          fromDate = new Date(today.getFullYear(), today.getMonth() - 3, today.getDate());
          toDate = today;
          break;
        case 'allYears':
          dateRangeFrom.value = '2022-01-01';
          dateRangeTo.value = '2025-12-31';
          updateDateRangeLabel();
          dateRangeDropdown.style.display = 'none';
          updateCharts();
          return;
      }

      dateRangeFrom.value = formatDateString(fromDate);
      dateRangeTo.value = formatDateString(toDate);
      updateDateRangeLabel();
      dateRangeDropdown.style.display = 'none';
      updateCharts();
    });
  });

  // Update date range label
  function updateDateRangeLabel() {
    const from = dateRangeFrom.value;
    const to = dateRangeTo.value;
    if (from && to) {
      const fromFormatted = formatDateForDisplay(from);
      const toFormatted = formatDateForDisplay(to);
      if (from === to) {
        dateRangeLabel.textContent = fromFormatted;
      } else {
        dateRangeLabel.textContent = fromFormatted + ' - ' + toFormatted;
      }
    }
  }

  // Listen for manual date input
  [dateRangeFrom, dateRangeTo].forEach(input => {
    input.addEventListener('change', () => {
      updateDateRangeLabel();
      updateCharts();
    });
  });

  // ═════════════════════════════════════════════════════════════
  // KPI CATEGORY FILTER FUNCTIONALITY
  // ═════════════════════════════════════════════════════════════
  const kpiCategoryBtn = document.getElementById('kpiCategoryBtn');
  const kpiCategoryDropdown = document.getElementById('kpiCategoryDropdown');
  const kpiCategoryLabel = document.getElementById('kpiCategoryLabel');
  const kpiCheckAll = document.getElementById('kpiCheckAll');
  const kpiCheckboxes = document.querySelectorAll('.kpi-checkbox:not(#kpiCheckAll)');

  kpiCategoryBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    const isVisible = kpiCategoryDropdown.style.display === 'block';
    kpiCategoryDropdown.style.display = isVisible ? 'none' : 'block';
  });

  kpiCheckAll.addEventListener('change', () => {
    kpiCheckboxes.forEach(cb => cb.checked = kpiCheckAll.checked);
    updateKPICategoryLabel();
    updateCharts();
  });

  kpiCheckboxes.forEach(cb => {
    cb.addEventListener('change', () => {
      const allChecked = Array.from(kpiCheckboxes).every(c => c.checked);
      const anyChecked = Array.from(kpiCheckboxes).some(c => c.checked);
      kpiCheckAll.checked = allChecked;
      if (!anyChecked) kpiCheckAll.checked = false;
      updateKPICategoryLabel();
      updateCharts();
    });
  });

  function updateKPICategoryLabel() {
    const checked = Array.from(kpiCheckboxes).filter(cb => cb.checked);
    if (checked.length === 0) {
      kpiCategoryLabel.textContent = 'No Categories';
    } else if (checked.length === kpiCheckboxes.length) {
      kpiCategoryLabel.textContent = 'All Categories';
    } else {
      // Show actual category names (first 2, then "+X more" if there are more)
      const names = checked.map(cb => cb.value);
      if (names.length <= 2) {
        kpiCategoryLabel.textContent = names.join(', ');
      } else {
        kpiCategoryLabel.textContent = names.slice(0, 2).join(', ') + ' +' + (names.length - 2) + ' more';
      }
    }
  }

  // ═════════════════════════════════════════════════════════════
  // CHART INITIALIZATION & UPDATE FUNCTIONS
  // ═════════════════════════════════════════════════════════════

  function getSelectedKPICategories() {
    return Array.from(kpiCheckboxes)
      .filter(cb => cb.checked)
      .map(cb => cb.value);
  }

  function filterDataByDateRange(data, fromDate, toDate) {
    return data.filter(item => {
      // item.date_label is in YYYY-MM format from the database
      // We need to compare with our YYYY-MM-DD format dates
      const itemYear = parseInt(item.year);
      const fromYear = parseInt(fromDate.substring(0, 4));
      const toYear = parseInt(toDate.substring(0, 4));
      
      // Simple year-based filtering for now
      // For more precise date filtering, would need month/day from database
      return itemYear >= fromYear && itemYear <= toYear;
    });
  }

  function initTrendChart() {
    const fromDate = dateRangeFrom.value || '2024-01-01';
    const toDate = dateRangeTo.value || '2025-12-31';
    const selectedCategories = getSelectedKPICategories();
    
    // Filter data
    const filteredData = filterDataByDateRange(kpiTrendData, fromDate, toDate)
      .filter(item => selectedCategories.includes(item.kpi_group));

    // Group by date and category
    const groupedData = {};
    filteredData.forEach(item => {
      const dateLabel = item.date_label;
      if (!groupedData[dateLabel]) {
        groupedData[dateLabel] = {};
      }
      groupedData[dateLabel][item.kpi_group] = item.avg_score;
    });

    const dates = Object.keys(groupedData).sort();
    const datasets = selectedCategories.map(category => ({
      label: category,
      data: dates.map(date => groupedData[date][category] || 0),
      borderColor: categoryColors[category] || '#3B82F6',
      backgroundColor: (categoryColors[category] || '#3B82F6') + '20',
      borderWidth: 2,
      tension: 0.4,
      fill: false,
      pointRadius: 4,
      pointHoverRadius: 6,
      pointBackgroundColor: categoryColors[category] || '#3B82F6',
      pointBorderColor: '#fff',
      pointBorderWidth: 2
    }));

    const ctx = document.getElementById('trendChart').getContext('2d');
    
    if (trendChart) {
      trendChart.destroy();
    }

    trendChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: dates,
        datasets: datasets
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: 'index',
          intersect: false
        },
        plugins: {
          legend: {
            display: true,
            position: 'top',
            labels: {
              color: '#fff',
              font: { size: 11, weight: '500' },
              padding: 12,
              usePointStyle: true
            }
          },
          tooltip: {
            backgroundColor: 'rgba(0,0,0,0.8)',
            padding: 12,
            titleFont: { size: 12, weight: '600', color: '#fff' },
            bodyFont: { size: 11, color: '#fff' },
            cornerRadius: 4,
            displayColors: true
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 5,
            ticks: {
              color: '#fff',
              font: { size: 11 },
              stepSize: 1
            },
            grid: {
              color: 'rgba(255,255,255,0.1)',
              drawBorder: false
            },
            title: {
              display: true,
              text: 'API Performance',
              color: '#fff',
              font: { size: 12, weight: '600' }
            }
          },
          x: {
            ticks: {
              color: '#fff',
              font: { size: 10 }
            },
            grid: {
              display: false
            },
            title: {
              display: true,
              text: 'Time',
              color: '#fff',
              font: { size: 12, weight: '600' }
            }
          }
        }
      }
    });
  }

  function initDistributionChart() {
    const ctx = document.getElementById('distributionChart').getContext('2d');
    
    if (distributionChart) {
      distributionChart.destroy();
    }

    // Get filter values
    const fromDate = dateRangeFrom.value; // YYYY-MM-DD format
    const toDate = dateRangeTo.value;     // YYYY-MM-DD format
    const selectedCategories = Array.from(kpiCheckboxes)
      .filter(cb => cb.checked)
      .map(cb => cb.value);

    // Filter trend data based on date range and categories
    const filteredTrendData = kpiTrendData.filter(d => {
      const dateMatch = d.evaluation_date >= fromDate && d.evaluation_date <= toDate;
      const categoryMatch = selectedCategories.length === 0 || selectedCategories.includes(d.kpi_group);
      return dateMatch && categoryMatch;
    });

    // Calculate performance distribution from filtered data
    const ratingCounts = {
      'Excellent': 0,
      'Good': 0,
      'Satisfactory': 0,
      'Poor': 0,
      'Very Poor': 0
    };

    // Get unique employees and their average scores
    const employeeScores = {};
    filteredTrendData.forEach(d => {
      if (!employeeScores[d.employee_id]) {
        employeeScores[d.employee_id] = [];
      }
      employeeScores[d.employee_id].push(parseFloat(d.avg_score));
    });

    // Count by rating
    Object.values(employeeScores).forEach(scores => {
      const avgScore = scores.reduce((a, b) => a + b, 0) / scores.length;
      if (avgScore >= 4) ratingCounts['Excellent']++;
      else if (avgScore >= 3) ratingCounts['Good']++;
      else if (avgScore >= 2) ratingCounts['Satisfactory']++;
      else if (avgScore >= 1) ratingCounts['Poor']++;
      else ratingCounts['Very Poor']++;
    });

    const scoreDistribution = {
      '5 - Excellent': ratingCounts['Excellent'],
      '4 - Good': ratingCounts['Good'],
      '3 - Satisfactory': ratingCounts['Satisfactory'],
      '2 - Poor': ratingCounts['Poor'],
      '1 - Very Poor': ratingCounts['Very Poor']
    };

    distributionChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: Object.keys(scoreDistribution),
        datasets: [{
          label: 'Number of Employees',
          data: Object.values(scoreDistribution),
          backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b', '#ef4444', '#7f1d1d'],
          borderRadius: 6,
          borderSkipped: false
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
            ticks: {
              color: '#fff',
              font: { size: 11 }
            },
            grid: {
              color: 'rgba(255,255,255,0.1)',
              drawBorder: false
            },
            title: {
              display: true,
              text: 'No. of Employee',
              color: '#fff',
              font: { size: 12, weight: '600' }
            }
          },
          x: {
            ticks: {
              color: '#fff',
              font: { size: 10 }
            },
            grid: { display: false },
            title: {
              display: true,
              text: 'Score',
              color: '#fff',
              font: { size: 12, weight: '600' }
            }
          }
        }
      }
    });
  }

  function initCategoryChart() {
    const ctx = document.getElementById('categoryChart').getContext('2d');
    
    if (categoryChart) {
      categoryChart.destroy();
    }

    // Get filter values
    const fromDate = dateRangeFrom.value; // YYYY-MM-DD format
    const toDate = dateRangeTo.value;     // YYYY-MM-DD format
    const selectedCategories = Array.from(kpiCheckboxes)
      .filter(cb => cb.checked)
      .map(cb => cb.value);

    // If no categories selected, show all
    const categoriesToShow = selectedCategories.length === 0 ? 
      Array.from(Object.keys(categoryColors)) : 
      selectedCategories;

    // Calculate average scores per category based on filtered date range
    const categoryScores = {};
    categoriesToShow.forEach(category => {
      const categoryData = kpiTrendData.filter(d => {
        if (!d.evaluation_date) return false;
        const dateMatch = d.evaluation_date >= fromDate && d.evaluation_date <= toDate;
        return d.kpi_group === category && dateMatch;
      });

      if (categoryData.length > 0) {
        const avgScore = categoryData.reduce((sum, d) => sum + parseFloat(d.avg_score), 0) / categoryData.length;
        categoryScores[category] = avgScore;
      } else {
        categoryScores[category] = 0;
      }
    });

    const categories = Object.keys(categoryScores);
    const scores = Object.values(categoryScores);
    const colors = categories.map(cat => categoryColors[cat] || '#3B82F6');

    categoryChart = new Chart(ctx, {
      type: 'radar',
      data: {
        labels: categories,
        datasets: [{
          label: 'Average Score',
          data: scores,
          borderColor: colors,
          backgroundColor: colors.map(color => color + '40'),
          borderWidth: 2,
          pointRadius: 5,
          pointHoverRadius: 7,
          pointBackgroundColor: colors,
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            position: 'bottom',
            labels: {
              color: '#fff',
              font: { size: 12, weight: '500' },
              padding: 15
            }
          },
          tooltip: {
            backgroundColor: 'rgba(0,0,0,0.8)',
            padding: 12,
            titleFont: { size: 12, weight: '600', color: '#fff' },
            bodyFont: { size: 11, color: '#fff' },
            displayColors: false,
            callbacks: {
              label: function(context) {
                return 'Score: ' + context.parsed.r.toFixed(2);
              }
            }
          }
        },
        scales: {
          r: {
            min: 0,
            max: 5,
            beginAtZero: true,
            ticks: {
              color: '#fff',
              font: { size: 10 },
              stepSize: 1,
              backdropColor: 'transparent'
            },
            grid: {
              color: 'rgba(255,255,255,0.1)',
              drawBorder: true
            },
            pointLabels: {
              color: '#fff',
              font: { size: 11, weight: '500' }
            }
          }
        }
      }
    });
  }

  // ═════════════════════════════════════════════════════════════
  // UPDATE GAMIFIED RANKING BASED ON FILTERS
  // ═════════════════════════════════════════════════════════════
  function updateGamifiedRanking() {
    const fromDate = dateRangeFrom.value; // YYYY-MM-DD format
    const toDate = dateRangeTo.value;     // YYYY-MM-DD format
    const selectedCategories = Array.from(kpiCheckboxes)
      .filter(cb => cb.checked)
      .map(cb => cb.value);

    // Filter employees based on date range and selected categories
    let filteredEmployees = analyticsData.map(emp => {
      // Calculate average KPI score based on filtered trend data
      const empTrendData = kpiTrendData.filter(d => {
        const dateMatch = d.evaluation_date >= fromDate && d.evaluation_date <= toDate;
        const categoryMatch = selectedCategories.length === 0 || selectedCategories.includes(d.kpi_group);
        return d.employee_id == emp.employee_id && dateMatch && categoryMatch;
      });

      if (empTrendData.length === 0) {
        return { ...emp, filtered_score: 0 };
      }

      const avgScore = empTrendData.reduce((sum, d) => sum + parseFloat(d.avg_score), 0) / empTrendData.length;
      return { ...emp, filtered_score: avgScore };
    }).filter(emp => emp.filtered_score > 0); // Only show employees with data in the filtered period

    // Sort by filtered score
    filteredEmployees.sort((a, b) => b.filtered_score - a.filtered_score);

    // Generate HTML
    let html = '';
    filteredEmployees.forEach((emp, index) => {
      const rank = index + 1;
      let medal = '';
      if (rank === 1) medal = '🥇';
      else if (rank === 2) medal = '🥈';
      else if (rank === 3) medal = '🥉';

      const score = emp.filtered_score;
      const color = getPerformanceColor(score);
      const width = (score / 5) * 100;

      html += `
        <div style="margin-bottom: 12px; display: flex; align-items: center; gap: 12px;">
          <div style="min-width: 40px; text-align: center; font-weight: 700; font-size: 16px; color: var(--text-primary);">
            ${medal} #${rank}
          </div>
          <div style="flex: 1;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
              <span style="font-size: 12px; font-weight: 500; color: var(--text-primary);">${emp.name}</span>
              <span style="font-size: 12px; font-weight: 600; color: var(--text-primary); background: var(--bg-input); padding: 2px 8px; border-radius: 4px;">${score.toFixed(2)}</span>
            </div>
            <div style="background: var(--bg-input); height: 6px; border-radius: 3px; overflow: hidden;">
              <div style="background: ${color}; height: 100%; width: ${width}%; transition: width 0.3s ease; border-radius: 3px;"></div>
            </div>
          </div>
        </div>
      `;
    });

    document.getElementById('gamifiedRankingContainer').innerHTML = html || '<div style="color: var(--text-secondary); padding: 20px; text-align: center;">No data available for selected filters</div>';
  }

  // Helper function to get performance color
  function getPerformanceColor(score) {
    if (score >= 4) return '#10b981'; // Green - Excellent
    if (score >= 3) return '#3b82f6'; // Blue - Good
    if (score >= 2) return '#f59e0b'; // Amber - Satisfactory
    if (score >= 1) return '#ef4444'; // Red - Poor
    return '#6b7280'; // Gray - Very Poor
  }

  // ═════════════════════════════════════════════════════════════
  // UPDATE CHARTS MASTER FUNCTION
  // ═════════════════════════════════════════════════════════════
  function updateCharts() {
    updateGamifiedRanking();
    initTrendChart();
    initDistributionChart();
    initCategoryChart();
  }

  // ═════════════════════════════════════════════════════════════
  // INITIALIZE ON DOCUMENT LOAD
  // ═════════════════════════════════════════════════════════════
  document.addEventListener('DOMContentLoaded', function() {
    updateDateRangeLabel();
    updateKPICategoryLabel();
    updateCharts();
  });
</script>
