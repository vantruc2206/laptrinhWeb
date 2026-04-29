<?php require_once __DIR__ . '/layouts/header.php'; ?>

<div class="dashboard-content">

    <!-- Thống kê tổng quan -->
    <div class="dashboard-cards">
        <div>
            <a href="users.php" class="card-link">
                <div class="card-box bg-blue">
                    <div><p>NGƯỜI DÙNG</p><h2><?= number_format($userCount) ?></h2></div>
                    <i class="fas fa-users"></i>
                </div>
            </a>
        </div>
        <div>
            <a href="articles.php" class="card-link">
                <div class="card-box bg-purple">
                    <div><p>BÀI VIẾT</p><h2><?= number_format($articleCount) ?></h2></div>
                    <i class="fas fa-file-alt"></i>
                </div>
            </a>
        </div>
        <div>
            <a href="categories.php" class="card-link">
                <div class="card-box bg-orange">
                    <div><p>DANH MỤC</p><h2><?= number_format($categoryCount) ?></h2></div>
                    <i class="fas fa-folder"></i>
                </div>
            </a>
        </div>
    </div>

    <!-- Biểu đồ -->
    <div class="dashboard-grid">
        <div class="chart-card main-chart-wrapper">
            <div class="chart-header">
                <h3>Thống kê tăng trưởng</h3>
                <div class="chart-tabs">
                    <button class="tab active" onclick="switchChart('week', this)">7 ngày</button>
                    <button class="tab" onclick="switchChart('month', this)">1 tháng</button>
                </div>
            </div>
            <div class="canvas-container"><canvas id="mainChart"></canvas></div>
        </div>
        <div class="chart-card donut-chart-wrapper">
            <div class="chart-header"><h3>Phân bố danh mục</h3></div>
            <div class="canvas-container"><canvas id="categoryChart"></canvas></div>
        </div>
    </div>

    <!-- Top 5 bài viết -->
    <div class="chart-card mt-4">
        <div class="chart-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 12px;">
            <h3><i class="fas fa-fire" style="color: #ef4444; margin-right: 8px;"></i>Top 5 Bài viết đọc nhiều nhất</h3>
        </div>
        <div class="top-articles-list">
            <?php foreach ($topArticles as $index => $article): ?>
                <div class="top-article-item">
                    <div class="top-rank rank-<?= $index + 1 ?>"><?= $index + 1 ?></div>
                    <div class="top-info">
                        <a href="../article.php?slug=<?= htmlspecialchars($article['slug']) ?>" target="_blank" class="top-title"><?= htmlspecialchars($article['title']) ?></a>
                        <span class="top-category"><?= htmlspecialchars($article['category_name']) ?></span>
                    </div>
                    <div class="top-views"><i class="fas fa-eye"></i> <?= number_format($article['views']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.dashboard-content { padding: 20px; }
.dashboard-cards { display: flex; gap: 20px; margin-bottom: 30px; }
.dashboard-cards > div { flex: 1; }
.card-link { text-decoration: none; }
.card-box { display: flex; justify-content: space-between; align-items: center; padding: 20px; border-radius: 12px; background: #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s; }
.card-box:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
.card-box p { font-size: 14px; color: #666; }
.card-box h2 { margin: 10px 0 0; }
.card-box i { font-size: 40px; opacity: 0.7; }
.bg-blue { background: #e0f2fe; }
.bg-purple { background: #f3e8ff; }
.bg-orange { background: #fff7ed; }
.dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
.chart-card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); display: flex; flex-direction: column; }
.canvas-container { flex-grow: 1; position: relative; min-height: 280px; }
.mt-4 { margin-top: 20px; }
.top-articles-list { display: flex; flex-direction: column; gap: 12px; }
.top-article-item { display: flex; align-items: center; padding: 10px; border-radius: 8px; background: #f8fafc; transition: 0.2s; }
.top-article-item:hover { background: #f1f5f9; transform: translateX(4px); }
.top-rank { width: 32px; height: 32px; border-radius: 50%; background: #e2e8f0; color: #475569; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 15px; flex-shrink: 0; }
.rank-1 { background: #fee2e2; color: #ef4444; }
.rank-2 { background: #ffedd5; color: #f97316; }
.rank-3 { background: #fef3c7; color: #eab308; }
.top-info { flex-grow: 1; overflow: hidden; }
.top-title { display: block; font-weight: 600; color: #1e293b; text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 4px; }
.top-title:hover { color: #6366f1; }
.top-category { font-size: 12px; padding: 2px 8px; background: #e0f2fe; color: #0ea5e9; border-radius: 12px; font-weight: 500; }
.top-views { font-weight: bold; color: #64748b; white-space: nowrap; margin-left: 15px; }
.chart-header { display: flex; justify-content: space-between; margin-bottom: 15px; }
.chart-tabs { display: flex; gap: 10px; }
.tab { padding: 6px 12px; border-radius: 8px; border: none; background: #f1f5f9; cursor: pointer; }
.tab.active { background: #6366f1; color: #fff; }
.chart-fade-out { opacity: 0; transform: scale(0.96); }
.chart-fade-in { opacity: 1; transform: scale(1); }
@media (max-width: 992px) { .dashboard-grid { grid-template-columns: 1fr; } }
@media (max-width: 768px) { .dashboard-cards { flex-direction: column; } }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const weekLabels   = <?= json_encode($days) ?>;
const weekArticles = <?= json_encode($articleGrowth) ?>;
const weekUsers    = <?= json_encode($userGrowth) ?>;
const monthLabels   = <?= json_encode($months) ?>;
const monthArticles = <?= json_encode($articleMonthly) ?>;
const monthUsers    = <?= json_encode($userMonthly) ?>;

let chart = null;

function getChartOptions() {
    return { responsive: true, maintainAspectRatio: false, animation: { duration: 800, easing: 'easeInOutQuart' }, plugins: { legend: { position: 'bottom' } } };
}

function renderChart(type) {
    const canvas = document.getElementById('mainChart');
    const ctx = canvas.getContext('2d');
    canvas.classList.add('chart-fade-out');
    setTimeout(() => {
        if (chart) chart.destroy();
        const gradientPurple = ctx.createLinearGradient(0,0,0,300);
        gradientPurple.addColorStop(0, "rgba(196,181,253,0.4)");
        gradientPurple.addColorStop(1, "rgba(196,181,253,0)");
        const gradientPink = ctx.createLinearGradient(0,0,0,300);
        gradientPink.addColorStop(0, "rgba(251,207,232,0.4)");
        gradientPink.addColorStop(1, "rgba(251,207,232,0)");

        if (type === 'week') {
            chart = new Chart(ctx, { type: 'line', data: { labels: weekLabels, datasets: [
                { label: 'Bài viết', data: weekArticles, borderColor: '#a78bfa', backgroundColor: gradientPurple, fill: true, tension: 0.4 },
                { label: 'Người dùng', data: weekUsers, borderColor: '#f472b6', backgroundColor: gradientPink, fill: true, tension: 0.4 }
            ]}, options: getChartOptions() });
        } else {
            chart = new Chart(ctx, { type: 'bar', data: { labels: monthLabels, datasets: [
                { label: 'Bài viết', data: monthArticles, backgroundColor: '#c4b5fd', borderRadius: 8 },
                { label: 'Người dùng', data: monthUsers, backgroundColor: '#f9a8d4', borderRadius: 8 }
            ]}, options: getChartOptions() });
        }
        canvas.classList.remove('chart-fade-out');
        canvas.classList.add('chart-fade-in');
    }, 200);
}

const categoryLabels = <?= json_encode($categoryLabels) ?>;
const categoryData   = <?= json_encode($categoryCounts) ?>;
new Chart(document.getElementById('categoryChart').getContext('2d'), {
    type: 'doughnut',
    data: { labels: categoryLabels, datasets: [{ data: categoryData, backgroundColor: ['#c4b5fd','#fbcfe8','#fde68a','#86efac','#93c5fd','#fca5a5','#ddd6fe'], borderWidth: 0 }] },
    options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: function(ctx) { const total = ctx.dataset.data.reduce((a,b)=>a+b,0); return `${ctx.raw} bài (${((ctx.raw/total)*100).toFixed(1)}%)`; } } } } }
});

function switchChart(type, el) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    renderChart(type);
}

document.addEventListener('DOMContentLoaded', () => renderChart('week'));

function toggleSidebar() {
    document.querySelector('.admin-sidebar').classList.toggle('active');
}
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
