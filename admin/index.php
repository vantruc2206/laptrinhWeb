<?php
require_once 'includes/header.php';

$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$categoryCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$articleCount = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();

$categoryLabels = [];
$categoryCounts = [];

$stmt = $pdo->query("
    SELECT c.name, COUNT(a.id) as total
    FROM categories c
    LEFT JOIN articles a ON a.category_id = c.id
    GROUP BY c.id
");

while ($row = $stmt->fetch()) {
    $categoryLabels[] = $row['name'];
    $categoryCounts[] = (int)$row['total'];
}

$days = [];
$articleGrowth = [];
$userGrowth = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $days[] = date('d/m', strtotime($date));

    $stmtArt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM articles 
        WHERE DATE(created_at) = ?
    ");
    $stmtArt->execute([$date]);
    $articleGrowth[] = (int)$stmtArt->fetchColumn();

    
    $stmtUsr = $pdo->prepare("
        SELECT COUNT(*) 
        FROM users 
        WHERE DATE(created_at) = ?
    ");
    $stmtUsr->execute([$date]);
    $userGrowth[] = (int)$stmtUsr->fetchColumn();
}

$months = [];
$articleMonthly = [];
$userMonthly = [];

for ($i = 5; $i >= 0; $i--) {
    $startMonth = date('Y-m-01', strtotime("-$i months"));
    $endMonth   = date('Y-m-t', strtotime($startMonth));

    $months[] = date('M', strtotime($startMonth));

    
    $stmtMonth = $pdo->prepare("
        SELECT COUNT(*) 
        FROM articles 
        WHERE created_at BETWEEN ? AND ?
    ");
    $stmtMonth->execute([$startMonth, $endMonth]);
    $articleMonthly[] = (int)$stmtMonth->fetchColumn();

    $stmtUser = $pdo->prepare("
        SELECT COUNT(*) 
        FROM users 
        WHERE created_at BETWEEN ? AND ?
    ");
    $stmtUser->execute([$startMonth, $endMonth]);
    $userMonthly[] = (int)$stmtUser->fetchColumn();
}
$growthArticles = 0;
$growthUsers = 0;

$lastIndex = count($articleMonthly) - 1;
$prevIndex = $lastIndex - 1;

if ($prevIndex >= 0) {
    $prevArticles = $articleMonthly[$prevIndex];
    $lastArticles = $articleMonthly[$lastIndex];

    $prevUsers = $userMonthly[$prevIndex];
    $lastUsers = $userMonthly[$lastIndex];

    if ($prevArticles > 0) {
        $growthArticles = (($lastArticles - $prevArticles) / $prevArticles) * 100;
    }

    if ($prevUsers > 0) {
        $growthUsers = (($lastUsers - $prevUsers) / $prevUsers) * 100;
    }
}

$categoryLabels = [];
$categoryCounts = [];

$stmt = $pdo->query("
    SELECT c.name, COUNT(a.id) as total
    FROM categories c
    LEFT JOIN articles a ON a.category_id = c.id
    GROUP BY c.id
");

while ($row = $stmt->fetch()) {
    $categoryLabels[] = $row['name'];
    $categoryCounts[] = (int)$row['total'];
}

$topArticlesStmt = $pdo->query("
    SELECT a.title, a.views, c.name as category_name, a.slug
    FROM articles a
    LEFT JOIN categories c ON a.category_id = c.id
    ORDER BY a.views DESC
    LIMIT 5
");
$topArticles = $topArticlesStmt->fetchAll();
?>

<div class="dashboard-content">

    
    <div class="dashboard-cards">


        <div>
            <a href="users.php" class="card-link">
                <div class="card-box bg-blue">
                    <div>
                        <p>NGƯỜI DÙNG</p>
                        <h2><?= number_format($userCount) ?></h2>
                    </div>
                    <i class="fas fa-users"></i>
                </div>
            </a>
        </div>

         <div>
        <a href="articles.php" class="card-link">
            <div class="card-box bg-purple">
                <div>
                    <p>BÀI VIẾT</p>
                    <h2><?= number_format($articleCount) ?></h2>

                </div>
                <i class="fas fa-file-alt"></i>
            </div>
        </a>
    </div>


    <div>
        <a href="categories.php" class="card-link">
            <div class="card-box bg-orange">
                <div>
                    <p>DANH MỤC</p>
                    <h2><?= number_format($categoryCount) ?></h2>
                </div>
                <i class="fas fa-folder"></i>
            </div>
        </a>
    </div>

</div>


<!-- ===== CHARTS GRID ===== -->
<div class="dashboard-grid">
    <div class="chart-card main-chart-wrapper">
        <div class="chart-header">
            <h3>Thống kê tăng trưởng</h3>
            <div class="chart-tabs">
                <button class="tab active" onclick="switchChart('week', this)">7 ngày</button>
                <button class="tab" onclick="switchChart('month', this)">1 tháng</button>
            </div>
        </div>
        <div class="canvas-container">
            <canvas id="mainChart"></canvas>
        </div>
    </div>

    <div class="chart-card donut-chart-wrapper">
        <div class="chart-header">
            <h3>Phân bố danh mục</h3>
        </div>
        <div class="canvas-container">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
</div>

<!-- ===== TOP ARTICLES ===== -->
<div class="chart-card mt-4">
    <div class="chart-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 12px;">
        <h3><i class="fas fa-fire" style="color: #ef4444; margin-right: 8px;"></i>Top 5 Bài viết đọc nhiều nhất</h3>
    </div>
    <div class="top-articles-list">
        <?php foreach($topArticles as $index => $article): ?>
            <div class="top-article-item">
                <div class="top-rank rank-<?= $index + 1 ?>"><?= $index + 1 ?></div>
                <div class="top-info">
                    <a href="../article.php?slug=<?= htmlspecialchars($article['slug']) ?>" target="_blank" class="top-title"><?= htmlspecialchars($article['title']) ?></a>
                    <span class="top-category"><?= htmlspecialchars($article['category_name']) ?></span>
                </div>
                <div class="top-views">
                    <i class="fas fa-eye"></i> <?= number_format($article['views']) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>


<!-- ===== CSS ===== -->
<style>
.dashboard-content { padding: 20px; }

/* CARDS */
.dashboard-cards {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}
.dashboard-cards > div { flex: 1; }

.card-link { text-decoration: none; }

.card-box {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: 0.3s;
}
.card-box:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.1);
}

.card-box p { font-size: 14px; color: #666; }
.card-box h2 { margin: 10px 0 0; }
.card-box i { font-size: 40px; opacity: 0.7; }

.bg-blue   { background: #e0f2fe; }
.bg-purple { background: #f3e8ff; }
.bg-orange { background: #fff7ed; }


/* CHART */
.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

.chart-card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
}

.canvas-container {
    flex-grow: 1;
    position: relative;
    min-height: 280px;
}

.mt-4 { margin-top: 20px; }

/* TOP ARTICLES */
.top-articles-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.top-article-item {
    display: flex;
    align-items: center;
    padding: 10px;
    border-radius: 8px;
    background: #f8fafc;
    transition: 0.2s;
}
.top-article-item:hover {
    background: #f1f5f9;
    transform: translateX(4px);
}
.top-rank {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #475569;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 15px;
    flex-shrink: 0;
}
.rank-1 { background: #fee2e2; color: #ef4444; }
.rank-2 { background: #ffedd5; color: #f97316; }
.rank-3 { background: #fef3c7; color: #eab308; }
.top-info {
    flex-grow: 1;
    overflow: hidden;
}
.top-title {
    display: block;
    font-weight: 600;
    color: #1e293b;
    text-decoration: none;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 4px;
}
.top-title:hover { color: #6366f1; }
.top-category {
    font-size: 12px;
    padding: 2px 8px;
    background: #e0f2fe;
    color: #0ea5e9;
    border-radius: 12px;
    font-weight: 500;
}
.top-views {
    font-weight: bold;
    color: #64748b;
    white-space: nowrap;
    margin-left: 15px;
}

.chart-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
}

.chart-tabs {
    display: flex;
    gap: 10px;
}

.tab {
    padding: 6px 12px;
    border-radius: 8px;
    border: none;
    background: #f1f5f9;
    cursor: pointer;
}
.tab.active {
    background: #6366f1;
    color: #fff;
}

/* animation */
.chart-fade-out {
    opacity: 0;
    transform: scale(0.96);
}
.chart-fade-in {
    opacity: 1;
    transform: scale(1);
}

/* responsive */
@media (max-width: 992px) {
    .dashboard-grid { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .dashboard-cards { flex-direction: column; }
}
</style>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const weekLabels = <?= json_encode($days) ?>;
const weekArticles = <?= json_encode($articleGrowth) ?>;
const weekUsers = <?= json_encode($userGrowth) ?>;

const monthLabels = <?= json_encode($months) ?>;
const monthArticles = <?= json_encode($articleMonthly) ?>;
const monthUsers = <?= json_encode($userMonthly) ?>;

let chart = null;

function getChartOptions() {
    return {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 800,
            easing: 'easeInOutQuart'
        },
        plugins: {
            legend: { position: 'bottom' }
        }
    };
}

function renderChart(type) {
    const canvas = document.getElementById('mainChart');
    const ctx = canvas.getContext('2d');

    canvas.classList.add('chart-fade-out');

    setTimeout(() => {

        if (chart) chart.destroy();
       
        const gradientPurple = ctx.createLinearGradient(0, 0, 0, 300);
        gradientPurple.addColorStop(0, "rgba(196, 181, 253, 0.4)"); 
        gradientPurple.addColorStop(1, "rgba(196, 181, 253, 0)");

        const gradientPink = ctx.createLinearGradient(0, 0, 0, 300);
        gradientPink.addColorStop(0, "rgba(251, 207, 232, 0.4)"); 
        gradientPink.addColorStop(1, "rgba(251, 207, 232, 0)");


// ===== LINE / BAR CHART =====
if (type === 'week') {
    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: weekLabels,
            datasets: [
                {
                    label: 'Bài viết',
                    data: weekArticles,
                    borderColor: '#a78bfa',
                    backgroundColor: gradientPurple,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Người dùng',
                    data: weekUsers,
                    borderColor: '#f472b6', 
                    backgroundColor: gradientPink,
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: getChartOptions()
    });

} else {
    chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [
                {
                    label: 'Bài viết',
                    data: monthArticles,
                    backgroundColor: '#c4b5fd', 
                    borderRadius: 8
                },
                {
                    label: 'Người dùng',
                    data: monthUsers,
                    backgroundColor: '#f9a8d4', 
                    borderRadius: 8
                }
            ]
        },
        options: getChartOptions()
    });
}
        canvas.classList.remove('chart-fade-out');
        canvas.classList.add('chart-fade-in');

    }, 200);
}


// ===== PIE / DOUGHNUT CHART =====
const categoryLabels = <?= json_encode($categoryLabels) ?>;
const categoryData = <?= json_encode($categoryCounts) ?>;

const ctxPie = document.getElementById('categoryChart').getContext('2d');

new Chart(ctxPie, {
    type: 'doughnut',
    data: {
        labels: categoryLabels,
        datasets: [{
            data: categoryData,
            backgroundColor: [
                '#c4b5fd', 
                '#fbcfe8', 
                '#fde68a', 
                '#86efac', 
                '#93c5fd', 
                '#fca5a5', 
                '#ddd6fe'  
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '60%', 
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a,b) => a+b, 0);
                        const value = context.raw;
                        const percent = ((value / total) * 100).toFixed(1);
                        return `${value} bài (${percent}%)`;
                    }
                }
            }
        }
    }
});

function switchChart(type, el) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    renderChart(type);
}


document.addEventListener('DOMContentLoaded', () => {
    renderChart('week');
});

function toggleSidebar() {
    document.querySelector('.admin-sidebar').classList.toggle('active');
}

</script>
<?php require_once 'includes/footer.php'; ?>