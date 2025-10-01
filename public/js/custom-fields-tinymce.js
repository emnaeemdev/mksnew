// Custom Fields TinyMCE JavaScript Functions

// Initialize TinyMCE for custom textarea fields
function initCustomFieldTinyMCE() {
    // Remove existing TinyMCE instances for custom fields first
    if (typeof tinymce !== 'undefined') {
        const existingEditors = tinymce.editors.filter(editor => 
            editor.id && editor.id.startsWith('custom_field_')
        );
        existingEditors.forEach(editor => {
            tinymce.remove(editor);
        });
    }
    
    initTinyMCEForCustomFields();
}

// Function that can be called externally
function initCustomFieldsTinyMCE() {
    initCustomFieldTinyMCE();
}

// Internal function to initialize TinyMCE
function initTinyMCEForCustomFields() {
    // Find all textarea custom fields
    const textareaFields = document.querySelectorAll('textarea[id^="custom_field_"]');
    
    textareaFields.forEach(function(textarea) {
        // Check if this textarea belongs to a custom field of type 'textarea'
        const fieldContainer = textarea.closest('.mb-3');
        if (fieldContainer && fieldContainer.querySelector('label')) {
            // Initialize TinyMCE for this textarea
            tinymce.init({
                target: textarea,
                height: 300,
                language: 'ar',
                directionality: 'rtl',
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'table', 'help', 'wordcount',
                    'nonbreaking', 'save', 'directionality'
                ],
                toolbar: 'undo redo | blocks fontsize | bold italic underline | ' +
                        'alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | ' +
                        'forecolor backcolor removeformat | charmap | ' +
                        'fullscreen preview | link | ltr rtl | help',
                menubar: false,
                content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; direction: rtl; }',
                font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt',
                font_family_formats: 'Arial=arial,helvetica,sans-serif; Times New Roman=times new roman,times,serif; Tahoma=tahoma,arial,helvetica,sans-serif; Verdana=verdana,geneva,sans-serif',
                relative_urls: false,
                remove_script_host: false,
                convert_urls: true,
                setup: function(editor) {
                    // Ensure content is saved when form is submitted
                    editor.on('change', function() {
                        editor.save();
                    });
                }
            });
        }
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if TinyMCE is available
    if (typeof tinymce !== 'undefined') {
        initTinyMCEForCustomFields();
    }
    
    // Re-initialize when section changes (for dynamic loading)
    const sectionSelect = document.getElementById('section_id');
    if (sectionSelect) {
        sectionSelect.addEventListener('change', function() {
            // Wait for the form to reload/update, then reinitialize
            setTimeout(function() {
                // Remove existing TinyMCE instances for custom fields
                if (typeof tinymce !== 'undefined' && tinymce.editors) {
                    tinymce.editors.forEach(function(editor) {
                        if (editor.id && editor.id.startsWith('custom_field_')) {
                            tinymce.remove('#' + editor.id);
                        }
                    });
                }
                
                // Reinitialize
                initCustomFieldTinyMCE();
            }, 500);
        });
    }
});

// Function to save all TinyMCE content before form submission
function saveAllTinyMCEContent() {
    if (typeof tinymce !== 'undefined' && tinymce.editors) {
        tinymce.editors.forEach(function(editor) {
            if (editor.id && editor.id.startsWith('custom_field_')) {
                editor.save();
            }
        });
    }
}

// Add event listener to forms to save TinyMCE content before submission
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function() {
            saveAllTinyMCEContent();
        });
    });
});