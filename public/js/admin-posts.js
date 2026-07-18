

document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Posts JS loaded');

    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {

        });
    });

    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('هل أنت �
تأكد �
ن الحذف؟')) {
                e.preventDefault();
            }
        });
    });
});