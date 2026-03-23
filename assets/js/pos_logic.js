// Simple AJAX-style feedback for offline mode
document.querySelectorAll('.ajax-cart-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        // Since we are strictly offline/PHP, we can simulate the click 
        // Or actually use AJAX if you've set up the API endpoints
        const btn = this.querySelector('button');
        const originalText = btn.innerHTML;
        
        btn.innerHTML = "✓ Added";
        btn.style.backgroundColor = "#28a745";
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.style.backgroundColor = "";
        }, 1500);
    });
});