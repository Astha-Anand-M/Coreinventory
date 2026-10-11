/**
 * Dashboard Scripts & Live Indicators
 */

document.addEventListener('DOMContentLoaded', () => {
    // Current live time clock if element present
    const liveClock = document.getElementById('liveClock');
    if (liveClock) {
        function updateClock() {
            const now = new Date();
            liveClock.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
        updateClock();
        setInterval(updateClock, 1000);
    }
});
