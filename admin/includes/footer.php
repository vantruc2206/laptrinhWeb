        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tự động đóng các thông báo alert sau 1.5 giây
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            if (alerts.length > 0) {
                setTimeout(function() {
                    alerts.forEach(function(alert) {
                        // Thêm hiệu ứng fadeOut của animate.css nếu element đang dùng animate.css
                        if (alert.classList.contains('animate__animated')) {
                            alert.classList.add('animate__fadeOut');
                        } else {
                            alert.style.transition = 'opacity 0.5s ease';
                            alert.style.opacity = '0';
                        }
                        
                        // Xóa hoàn toàn khỏi DOM sau khi hiệu ứng kết thúc (.5s)
                        setTimeout(() => {
                            alert.remove();
                        }, 500);
                    });
                }, 1500);
            }
        });
    </script>
</body>
</html>
