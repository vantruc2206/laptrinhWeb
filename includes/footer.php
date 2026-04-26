</main>

<footer class="footer">
    <div class="container">
        <h2 style="font-weight: 800; color: var(--primary); margin-bottom: 1rem;">BÁO HƠI MỚI</h2>
        <p>Nguồn tin tức đáng tin cậy của bạn về các sự kiện mới nhất trên toàn cầu.</p>
        <p style="margin-top: 2rem; font-size: 0.875rem;">&copy; <?= date('Y') ?> BÁO HƠI MỚI. Bản quyền đã được bảo lưu.</p>
    </div>
</footer>

<script>
// API Config
const PROVINCE_API = 'https://provinces.open-api.vn/api/v2/';
const GEO_API = 'https://nominatim.openstreetmap.org/search'; 
const WEATHER_API = 'https://api.open-meteo.com/v1/forecast';

// Helper to clean city names
function cleanCityName(name) {
    if (!name) return "";
    return name.replace(/^(Thành phố trực thuộc Trung ương|Thành phố|Tỉnh)\s+/i, '').trim();
}

// Fetch and render 34 provinces
async function loadProvinces() {
    const listContainer = document.getElementById('full-city-list');
    if (!listContainer) return;

    try {
        const response = await fetch(PROVINCE_API);
        const provinces = await response.json(); 
        
        listContainer.innerHTML = provinces.map(p => {
            const displayName = cleanCityName(p.name);
            return `<div class="city-item" data-full-name="${p.name}">${displayName}</div>`;
        }).join('');

        document.querySelectorAll('.city-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.stopPropagation();
                getWeatherByCityName(item.dataset.fullName);
            });
        });
    } catch (e) {
        listContainer.innerHTML = '<div style="padding:10px;color:red;">Lỗi tải danh sách (Error loading list)</div>';
        console.error("Province fetch error:", e);
    }
}

// Get coordinates (Nominatim) then weather
// Get coordinates (Nominatim) then weather
async function getWeatherByCityName(fullName) {
    const cityDisplay = document.querySelector('.weather-widget .city');
    const tempDisplay = document.querySelector('.weather-widget .temp');
    const iconDisplay = document.querySelector('.weather-widget .weather-detail i');

    if (!cityDisplay || !tempDisplay) return;

    cityDisplay.innerText = cleanCityName(fullName);
    tempDisplay.innerHTML = '<i class="fas fa-circle-notch fa-spin" style="font-size: 0.8rem;"></i>';

    try {
        let lat, lon; 

        // Tối ưu hóa tìm kiếm: Thêm Vietnam để tránh nhầm lẫn quốc tế
        const searchQuery = `${fullName}, Vietnam`;
        const geoRes = await fetch(`${GEO_API}?q=${encodeURIComponent(searchQuery)}&format=json&limit=1`, {
            headers: { 'User-Agent': 'BaoHoiMoi/1.0' }
        });
        const geoData = await geoRes.json();
        
        if (!geoData || geoData.length === 0) {
            throw new Error("Không tìm thấy tọa độ");
        }

        lat = geoData[0].lat;
        lon = geoData[0].lon;

        // Cập nhật URL API: Lấy temperature_2m (nhiệt độ thực) và weather_code
        // Thêm current=temperature_2m để lấy dữ liệu mới nhất thay vì dự báo chung
        const weatherUrl = `${WEATHER_API}?latitude=${lat}&longitude=${lon}&current=temperature_2m,weather_code&timezone=Asia%2FHo_Chi_Minh`;
        
        const weatherRes = await fetch(weatherUrl);
        const weatherData = await weatherRes.json();
        
        // Truy xuất dữ liệu từ object 'current' mới của Open-Meteo
        const temp = Math.round(weatherData.current.temperature_2m);
        const code = weatherData.current.weather_code;

        tempDisplay.innerText = temp + '°C';
        
        // Cập nhật Icon dựa trên Weather Code (WMO Standard)
        if (code <= 1) iconDisplay.className = 'fas fa-sun'; // Trời quang
        else if (code <= 3) iconDisplay.className = 'fas fa-cloud-sun'; // Mây rải rác
        else if (code <= 67) iconDisplay.className = 'fas fa-cloud-showers-heavy'; // Mưa
        else iconDisplay.className = 'fas fa-bolt'; // Dông sét

        localStorage.setItem('selected_city_fullName', fullName);
    } catch (e) {
        tempDisplay.innerText = '--°';
        console.error("Weather error:", e);
    }
}

// Search Filter
document.getElementById('city-search-input')?.addEventListener('input', (e) => {
    const term = e.target.value.toLowerCase().trim();
    document.querySelectorAll('.city-item').forEach(item => {
        const text = item.innerText.toLowerCase();
        item.style.display = text.includes(term) ? 'block' : 'none';
    });
});

// Initialize
window.addEventListener('DOMContentLoaded', () => {
    loadProvinces();
    const saved = localStorage.getItem('selected_city_fullName') || 'Thành phố Hồ Chí Minh';
    getWeatherByCityName(saved);

    // Smart Sticky Header: Hide top bar on scroll down, show on scroll up
    let lastScroll = 0;
    const navbar = document.querySelector('.navbar');
    const navbarTop = document.querySelector('.navbar-top');
    
    if (navbar && navbarTop) {
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            const topHeight = navbarTop.offsetHeight;
            
            if (currentScroll > topHeight + 50) {
                if (currentScroll > lastScroll) {
                    // Scroll down
                    navbar.style.transform = `translateY(-${topHeight}px)`;
                } else {
                    // Scroll up
                    navbar.style.transform = 'translateY(0)';
                }
            } else {
                navbar.style.transform = 'translateY(0)';
            }
            lastScroll = currentScroll;
        });
    }
});
</script>

</body>
</html>
