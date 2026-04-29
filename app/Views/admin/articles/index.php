<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php
if (!function_exists('buildQuery')) {
    function buildQuery($overrides = []) {
        $base   = [
            'search'        => $_GET['search']        ?? '',
            'category_id'   => $_GET['category_id']   ?? '',
            'status_filter' => $_GET['status_filter'] ?? '',
        ];
        $merged = array_merge($base, $overrides);
        $merged = array_filter($merged, fn($v) => $v !== '' && $v !== '0' && $v !== 0);
        return http_build_query($merged);
    }
}
?>

<!-- Tiêu đề trang -->
<div class="admin-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="fw-bold m-0">Quản lý Bài viết</h1>
    <a href="?action=create" class="btn btn-primary rounded-pill px-4 shadow-sm">
        <i class="fas fa-plus me-2"></i>Viết Bài mới
    </a>
</div>

<!-- Thông báo -->
<?php if ($error):   ?><div class="alert alert-danger  border-0 shadow-sm rounded-4 animate__animated animate__shakeX"><?= $error   ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success border-0 shadow-sm rounded-4 animate__animated animate__bounceIn"><?= $success ?></div><?php endif; ?>

<!-- ── Search bar -->
<div class="search-wrapper">
    <form method="GET" action="articles.php" class="search-bar">
        <input type="hidden" name="action" value="list">
        <?php if ($filterCat > 0): ?><input type="hidden" name="category_id" value="<?= $filterCat ?>"><?php endif; ?>
        <?php if ($filterStatus !== ''): ?><input type="hidden" name="status_filter" value="<?= htmlspecialchars($filterStatus) ?>"><?php endif; ?>
        
        <input type="text"
               name="search"
               placeholder="Tìm bài viết theo tiêu đề, tóm tắt..."
               value="<?= htmlspecialchars($search) ?>">
        <button type="submit"><i class="fas fa-search"></i></button>
        <?php if ($search !== ''): ?>
            <a href="articles.php?<?= buildQuery(['search' => '']) ?>" class="btn-clear">✕</a>
        <?php endif; ?>
    </form>
</div>

<!-- ── Filter row -->
    <div class="filter-row">
        <!-- Lọc danh mục -->
        <div class="btn-group">
            <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" style="border: 1.5px solid #e5e7eb; border-radius: 999px; padding: 0.4rem 1.1rem; background: #fff; color: #374151; font-size: 0.9rem;">
                <?php 
                $selectedCatName = 'Tất cả danh mục';
                foreach ($categories as $cat) {
                    if ($filterCat == $cat['id']) {
                        $selectedCatName = $cat['name'];
                        break;
                    }
                }
                echo htmlspecialchars($selectedCatName);
                ?>
            </button>
            <ul class="dropdown-menu shadow-sm mt-1" style="border-radius: 12px; max-height: 300px; overflow-y: auto; font-size: 0.9rem;">
                <li><a class="dropdown-item <?= $filterCat == 0 ? 'active' : '' ?>" href="articles.php?<?= buildQuery(['category_id' => '']) ?>">Tất cả danh mục</a></li>
                <?php foreach ($categories as $cat): ?>
                    <li><a class="dropdown-item <?= $filterCat == $cat['id'] ? 'active' : '' ?>" href="articles.php?<?= buildQuery(['category_id' => $cat['id']]) ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Lọc trạng thái -->
        <div class="btn-group">
            <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" style="border: 1.5px solid #e5e7eb; border-radius: 999px; padding: 0.4rem 1.1rem; background: #fff; color: #374151; font-size: 0.9rem;">
                <?php 
                if ($filterStatus === 'published') echo 'Đã xuất bản';
                elseif ($filterStatus === 'draft') echo 'Bản nháp';
                else echo 'Tất cả trạng thái';
                ?>
            </button>
            <ul class="dropdown-menu shadow-sm mt-1" style="border-radius: 12px; font-size: 0.9rem;">
                <li><a class="dropdown-item <?= $filterStatus === '' ? 'active' : '' ?>" href="articles.php?<?= buildQuery(['status_filter' => '']) ?>">Tất cả trạng thái</a></li>
                <li><a class="dropdown-item <?= $filterStatus === 'published' ? 'active' : '' ?>" href="articles.php?<?= buildQuery(['status_filter' => 'published']) ?>">Đã xuất bản</a></li>
                <li><a class="dropdown-item <?= $filterStatus === 'draft' ? 'active' : '' ?>" href="articles.php?<?= buildQuery(['status_filter' => 'draft']) ?>">Bản nháp</a></li>
            </ul>
        </div>

        <!-- Xóa bộ lọc -->
        <?php if ($filterCat > 0 || $filterStatus !== '' || $search !== ''): ?>
            <a href="articles.php" class="text-decoration-none d-inline-flex align-items-center" 
               style="background: #fee2e2; color: #ef4444; padding: 0.35rem 0.8rem; font-size: 0.9rem; font-weight: 600; border-radius: 20px; margin-left: 0.5rem; border: 1px solid #fecaca; transition: 0.2s;"
               onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'">
                <i class="fas fa-times me-1"></i> Xóa lọc
            </a>
        <?php endif; ?>

        <!-- Đếm kết quả -->
        <div style="display: inline-flex; align-items: center; margin-left: auto;">
            <span style="background: #e0f2fe; color: #0284c7; padding: 0.4rem 1rem; border-radius: 20px; font-size: 0.95rem; font-weight: 500; border: 1px solid #bae6fd; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                <i class="fas fa-poll-h me-1" style="color: #0ea5e9;"></i> Tìm thấy <strong style="margin: 0 4px; color: #0f172a; font-size: 1.05rem;"><?= number_format($totalResults) ?></strong> bài viết
            </span>
        </div>
    </div>

<!-- Table card -->
<div class="art-card">
    <div class="art-table-header">
        <h5>Danh sách bài viết</h5>
    </div>

    <div class="table-responsive">
        <table class="art-table">
            <thead>
                <tr>
                    <th style="padding-left:1.25rem; width:74px;">Ảnh</th>
                    <th>Tiêu đề</th>
                    <th>Danh mục</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th style="text-align:right; padding-right:1.25rem;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($articles)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding: 2rem;">
                        <div class="text-muted">
                            <i class="fas fa-box-open mb-2" style="font-size: 2rem; color: #cbd5e1;"></i>
                            <p>Không tìm thấy bài viết nào.</p>
                        </div>
                    </td>
                </tr>
            <?php else: foreach ($articles as $row): ?>
                <tr>
                    <!-- Ảnh -->
                    <td style="padding-left:1.25rem;">
                        <?php if ($row['image']): ?>
                            <img src="../assets/images/articles/<?= htmlspecialchars($row['image']) ?>" alt="" class="art-thumb">
                        <?php else: ?>
                            <div class="art-thumb no-img"><i class="fas fa-image"></i></div>
                        <?php endif; ?>
                    </td>

                    <!-- Tiêu đề -->
                    <td style="max-width: 250px;">
                        <a href="articles.php?action=edit&id=<?= $row['id'] ?>" class="art-title" title="<?= htmlspecialchars($row['title']) ?>">
                            <?= htmlspecialchars($row['title']) ?>
                        </a>
                    </td>

                    <!-- Danh mục -->
                    <td>
                        <span class="art-category">
                            <?= htmlspecialchars($row['category_name'] ?? '—') ?>
                        </span>
                    </td>

                    <!-- Trạng thái -->
                    <td>
                        <?php if ($row['status'] === 'published'): ?>
                            <span class="status-published">Đã xuất bản</span>
                        <?php else: ?>
                            <span class="status-draft">Bản nháp</span>
                        <?php endif; ?>
                    </td>

                    <!-- Ngày tạo -->
                    <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>

                    <!-- Thao tác -->
                    <td style="text-align:right; padding-right:1.25rem; white-space:nowrap;">
                        <a href="?action=edit&id=<?= $row['id'] ?>" class="btn-edit">
                            <i class="fas fa-pen me-1"></i>Sửa
                        </a>
                        <a href="?action=delete&id=<?= $row['id'] ?>&<?= buildQuery() ?>"
                           class="btn-del ms-1" data-type="bài viết" onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này?')">
                            <i class="fas fa-trash me-1"></i>Xóa
                        </a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Phân trang -->
<?php if ($totalPages > 1): ?>
<nav class="art-pagination">
    <?php if ($page > 1): ?>
        <a href="?<?= buildQuery(['page' => $page - 1]) ?>">
            <i class="fas fa-chevron-left"></i>
        </a>
    <?php endif; ?>

    <?php
    $startPage = max(1, $page - 2);
    $endPage   = min($totalPages, $page + 2);
    $prevDot   = false;
    $nextDot   = false;

    for ($i = 1; $i <= $totalPages; $i++):
        if ($i == 1 || $i == $totalPages || ($i >= $startPage && $i <= $endPage)) {
            ?>
            <a class="<?= ($page == $i) ? 'active' : '' ?>" href="?<?= buildQuery(['page' => $i]) ?>">
                <?= $i ?>
            </a>
        <?php } elseif (!$prevDot) {
            $prevDot = true;
            echo '<span>…</span>';
        }
    endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?<?= buildQuery(['page' => $page + 1]) ?>">
            <i class="fas fa-chevron-right"></i>
        </a>
    <?php endif; ?>
</nav>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
