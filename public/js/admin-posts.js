// Admin Posts JavaScript Functions

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Posts JS loaded');
    
    // Add any admin posts specific functionality here
    
    // Handle form submissions
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Add any form validation or processing here
        });
    });
    
    // Handle delete confirmations
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('هل أنت متأكد من الحذف؟')) {
                e.preventDefault();
            }
        });
    });
});