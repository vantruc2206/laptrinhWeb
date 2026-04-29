        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Xử lý hộp thoại xóa bằng SweetAlert2
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-del, .delete');
            deleteButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const href = this.getAttribute('href');
                    const type = this.getAttribute('data-type') || 'mục';
                    Swal.fire({
                        title: 'Thông báo',
                        text: `Bạn có chắc chắn muốn xóa ${type} này không? Hành động này không thể hoàn tác!`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#adb5bd',
                        confirmButtonText: 'Đồng ý, xóa!',
                        cancelButtonText: 'Hủy bỏ',
                        width: '400px',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = href;
                        }
                    });
                });
            });
        });

        // Tự động đóng các thông báo alert sau 1.5 giây
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            if (alerts.length > 0) {
                setTimeout(function() {
                    alerts.forEach(function(alert) {
                        if (alert.classList.contains('animate__animated')) {
                            alert.classList.add('animate__fadeOut');
                        } else {
                            alert.style.transition = 'opacity 0.5s ease';
                            alert.style.opacity = '0';
                        }
                        setTimeout(() => { alert.remove(); }, 500);
                    });
                }, 1500);
            }
        });
    </script>
</body>
</html>
