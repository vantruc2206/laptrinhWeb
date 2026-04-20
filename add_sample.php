<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

try {
    echo "<h3>Adding Sample Data...</h3>";

    
    $admin = $pdo->query("SELECT id FROM users WHERE username = 'admin'")->fetch();
    $adminId = $admin ? $admin['id'] : 1;

    
    $categories = [
    ['name' => 'Công nghệ', 'slug' => 'cong-nghe', 'desc' => 'Tin tức công nghệ mới nhất'],
    ['name' => 'Thể thao', 'slug' => 'the-thao', 'desc' => 'Tin tức thể thao trong nước và quốc tế'],
    ['name' => 'Giải trí', 'slug' => 'giai-tri', 'desc' => 'Thông tin giải trí, showbiz'],
    ['name' => 'Kinh doanh', 'slug' => 'kinh-doanh', 'desc' => 'Tin tức kinh tế và thị trường'],
    ['name' => 'Sức khỏe', 'slug' => 'suc-khoe', 'desc' => 'Kiến thức y tế và chăm sóc sức khỏe'],
    ['name' => 'Giáo dục', 'slug' => 'giao-duc', 'desc' => 'Thông tin giáo dục và học tập'],
    ['name' => 'Đời sống', 'slug' => 'doi-song', 'desc' => 'Tin tức đời sống xã hội'],
    ['name' => 'Du lịch', 'slug' => 'du-lich', 'desc' => 'Khám phá các điểm đến hấp dẫn'],
    ['name' => 'Khác', 'slug' => 'khac', 'desc' => 'Các chủ đề khác']
];

$catIds = [];
$stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, slug, description) VALUES (?, ?, ?)");

foreach ($categories as $cat) {
   
    $stmt->execute([$cat['name'], $cat['slug'], $cat['desc']]);
    
    $catRow = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
    $catRow->execute([$cat['slug']]);
    $result = $catRow->fetch();
    
    if ($result) {
        $catIds[$cat['slug']] = $result['id'];
    }
}
echo "<p>Đã cập nhật " . count($categories) . " danh mục thành công!</p>";

   
    $articles = [
        [
            'title' => 'Apple ra mắt chip mới siêu mạnh',
            'summary' => 'Vi xử lý thế hệ mới mang lại hiệu năng đột phá cho các dòng máy Mac.',
            'content' => '<p>Hôm nay, Apple đã chính thức giới thiệu dòng chip mới với kiến trúc 3nm. Theo các chuyên gia, hiệu năng của chip mới vượt trội hơn 30% so với thế hệ tiền nhiệm, đồng thời tiết kiệm pin đáng kể.</p>',
            'cat_slug' => 'cong-nghe'
        ],
        [
            'title' => 'Đội tuyển bóng đá Nam có chiến thắng lịch sử',
            'summary' => 'Tuyển Việt Nam xuất sắc ghi 2 bàn thắng ở phút chót để giành chiến thắng.',
            'content' => '<p>Trong một trận đấu nghẹt thở, đội tuyển bóng đá Nam đã cho người hâm mộ trải qua nhiều cung bậc cảm xúc. Mặc dù bị đối thủ dẫn trước, nhưng bằng nỗ lực không mệt mỏi, các cầu thủ đã mang về bàn thắng quý như vàng.</p>',
            'cat_slug' => 'the-thao'
        ],
        [
            'title' => 'Phim điện ảnh mới phá kỷ lục phòng vé',
            'summary' => 'Bộ phim chiếu rạp mới nhất vừa lập kỷ lục chưa từng có tại thị trường phim Việt.',
            'content' => '<p>Đạo diễn cùng dàn diễn viên đã bất ngờ chia sẻ về thành tích khủng này. Bộ phim thu hút hàng triệu khán giả ra rạp chỉ sau vỏn vẹn 3 ngày ra mắt, mang lại doanh thu hàng chục tỷ đồng.</p>',
            'cat_slug' => 'giai-tri'
        ]
    ];

    foreach ($articles as $art) {
        $slug = generateSlug($art['title']);
        $catId = $catIds[$art['cat_slug']];
        
        $stmt = $pdo->prepare("INSERT IGNORE INTO articles (category_id, author_id, title, slug, summary, content, status) VALUES (?, ?, ?, ?, ?, ?, 'published')");
        $stmt->execute([$catId, $adminId, $art['title'], $slug, $art['summary'], $art['content']]);
    }
    echo "<p>Articles added!</p>";
    echo "<p style='color:green'><b>Xong! Bây giờ bạn đã có báo để đọc.</b></p>";
    echo "<p><a href='index.php'>=> Quay lại trang chủ đọc báo</a></p>";

} catch (PDOException $e) {
    echo "<p style='color:red'>Lỗi: " . $e->getMessage() . "</p>";
}
?>
