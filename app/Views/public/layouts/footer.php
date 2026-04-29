</main>

<footer class="footer">
    <div class="container">
        <h2 style="font-weight: 800; color: var(--primary); margin-bottom: 1rem;">BÁO HƠI MỚI</h2>
        <p>Nguồn tin tức đáng tin cậy của bạn về các sự kiện mới nhất trên toàn cầu.</p>
        <p style="margin-top: 2rem; font-size: 0.875rem;">&copy; <?= date('Y') ?> BÁO HƠI MỚI. Bản quyền đã được bảo lưu.</p>
    </div>
</footer>

<script>
const PROVINCE_API = 'https://provinces.open-api.vn/api/v2/';
const GEO_API      = 'https://nominatim.openstreetmap.org/search';
const WEATHER_API  = 'https://api.open-meteo.com/v1/forecast';

function cleanCityName(name) {
    if (!name) return "";
    return name.replace(/^(Thành phố trực thuộc Trung ương|Thành phố|Tỉnh)\s+/i, '').trim();
}

async function loadProvinces() {
    const listContainer = document.getElementById('full-city-list');
    if (!listContainer) return;
    try {
        const response  = await fetch(PROVINCE_API);
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
        listContainer.innerHTML = '<div style="padding:10px;color:red;">Lỗi tải danh sách</div>';
        console.error("Province fetch error:", e);
    }
}

async function getWeatherByCityName(fullName) {
    const cityDisplay = document.querySelector('.weather-widget .city');
    const tempDisplay = document.querySelector('.weather-widget .temp');
    const iconDisplay = document.querySelector('.weather-widget .weather-detail i');
    if (!cityDisplay || !tempDisplay) return;

    cityDisplay.innerText = cleanCityName(fullName);
    tempDisplay.innerHTML = '<i class="fas fa-circle-notch fa-spin" style="font-size: 0.8rem;"></i>';

    try {
        const searchQuery = `${fullName}, Vietnam`;
        const geoRes  = await fetch(`${GEO_API}?q=${encodeURIComponent(searchQuery)}&format=json&limit=1`, { headers: { 'User-Agent': 'BaoHoiMoi/1.0' } });
        const geoData = await geoRes.json();
        if (!geoData || geoData.length === 0) throw new Error("Không tìm thấy tọa độ");

        const lat        = geoData[0].lat;
        const lon        = geoData[0].lon;
        const weatherRes = await fetch(`${WEATHER_API}?latitude=${lat}&longitude=${lon}&current=temperature_2m,weather_code&timezone=Asia%2FHo_Chi_Minh`);
        const weatherData = await weatherRes.json();
        const temp = Math.round(weatherData.current.temperature_2m);
        const code = weatherData.current.weather_code;

        tempDisplay.innerText = temp + '°C';
        if      (code <= 1)  iconDisplay.className = 'fas fa-sun';
        else if (code <= 3)  iconDisplay.className = 'fas fa-cloud-sun';
        else if (code <= 67) iconDisplay.className = 'fas fa-cloud-showers-heavy';
        else                 iconDisplay.className = 'fas fa-bolt';

        localStorage.setItem('selected_city_fullName', fullName);
    } catch (e) {
        tempDisplay.innerText = '--°';
        console.error("Weather error:", e);
    }
}

document.getElementById('city-search-input')?.addEventListener('input', (e) => {
    const term = e.target.value.toLowerCase().trim();
    document.querySelectorAll('.city-item').forEach(item => {
        item.style.display = item.innerText.toLowerCase().includes(term) ? 'block' : 'none';
    });
});

window.addEventListener('DOMContentLoaded', () => {
    loadProvinces();
    const saved = localStorage.getItem('selected_city_fullName') || 'Thành phố Hồ Chí Minh';
    getWeatherByCityName(saved);

    let lastScroll = 0;
    const navbar    = document.querySelector('.navbar');
    const navbarTop = document.querySelector('.navbar-top');
    if (navbar && navbarTop) {
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            const topHeight     = navbarTop.offsetHeight;
            if (currentScroll > topHeight + 50) {
                navbar.style.transform = currentScroll > lastScroll ? `translateY(-${topHeight}px)` : 'translateY(0)';
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
