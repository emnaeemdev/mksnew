

function initCustomFieldTinyMCE() {

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

function initCustomFieldsTinyMCE() {
    initCustomFieldTinyMCE();
}

function initTinyMCEForCustomFields() {

    const textareaFields = document.querySelectorAll('textarea[id^="custom_field_"]');

    textareaFields.forEach(function(textarea) {

        const fieldContainer = textarea.closest('.mb-3');
        if (fieldContainer && fieldContainer.querySelector('label')) {

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

                    editor.on('change', function() {
                        editor.save();
                    });
                }
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {

    if (typeof tinymce !== 'undefined') {
        initTinyMCEForCustomFields();
    }

    const sectionSelect = document.getElementById('section_id');
    if (sectionSelect) {
        sectionSelect.addEventListener('change', function() {

            setTimeout(function() {

                if (typeof tinymce !== 'undefined' && tinymce.editors) {
                    tinymce.editors.forEach(function(editor) {
                        if (editor.id && editor.id.startsWith('custom_field_')) {
                            tinymce.remove('#' + editor.id);
                        }
                    });
                }

                initCustomFieldTinyMCE();
            }, 500);
        });
    }
});

function saveAllTinyMCEContent() {
    if (typeof tinymce !== 'undefined' && tinymce.editors) {
        tinymce.editors.forEach(function(editor) {
            if (editor.id && editor.id.startsWith('custom_field_')) {
                editor.save();
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function() {
            saveAllTinyMCEContent();
        });
    });
});