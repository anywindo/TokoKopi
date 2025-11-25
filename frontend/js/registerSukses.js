window.addEventListener('DOMContentLoaded', function () {
    var countdown = 3;
    function updateCountdown() {
        document.getElementById('count').firstChild.nodeValue = countdown;
        if (countdown === 0) {
            window.location.href = 'login.xhtml';
        } else {
            countdown--;
            setTimeout(updateCountdown, 1000);
        }
    }
    window.onload = updateCountdown;
});