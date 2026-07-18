/**
 * إعدادات موحّدة لمحرر TinyMCE في لوحة التحكم.
 * يدعم: YouTube، Google Drive/Docs/Sheets، ولصق أكواد iframe مباشرة.
 */
window.buildEmbedHtml = function (input) {
    if (!input || !String(input).trim()) {
        return '';
    }

    let raw = String(input).trim();

    // كود iframe جاهز
    if (/<iframe[\s\S]*?<\/iframe>/i.test(raw)) {
        if (/src=["']https?:\/\/(www\.)?(youtube\.com|youtu\.be|docs\.google\.com|drive\.google\.com)/i.test(raw)) {
            return raw.replace(/width=["'][^"']*["']/i, 'width="100%"').replace(/height=["'][^"']*["']/i, 'height="480"');
        }
        return raw;
    }

    // استخراج src من iframe جزئي
    const srcMatch = raw.match(/src=["']([^"']+)["']/i);
    if (srcMatch) {
        raw = srcMatch[1];
    }

    raw = raw.replace(/&amp;/g, '&');

    // YouTube
    if (raw.indexOf('youtube.com') !== -1 || raw.indexOf('youtu.be') !== -1) {
        const match = raw.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/);
        if (match) {
            return '<iframe width="100%" height="480" src="https://www.youtube.com/embed/' + match[1] + '" frameborder="0" allowfullscreen loading="lazy"></iframe>';
        }
    }

    // Google Docs / Sheets / Drive
    if (raw.indexOf('docs.google.com') !== -1 || raw.indexOf('drive.google.com') !== -1) {
        let embedUrl = raw;
        if (embedUrl.indexOf('/pubhtml') === -1 && embedUrl.indexOf('/preview') === -1 && embedUrl.indexOf('/embed') === -1) {
            if (embedUrl.indexOf('/edit') !== -1) {
                embedUrl = embedUrl.replace(/\/edit.*$/, '/preview');
            } else if (!/\/preview$/.test(embedUrl)) {
                embedUrl = embedUrl.replace(/\/?$/, '/preview');
            }
        }
        return '<iframe width="100%" height="480" src="' + embedUrl + '" frameborder="0" allowfullscreen loading="lazy"></iframe>';
    }

    // رابط عام — iframe بسيط
    if (/^https?:\/\//i.test(raw)) {
        return '<iframe width="100%" height="480" src="' + raw + '" frameborder="0" allowfullscreen loading="lazy"></iframe>';
    }

    return raw;
};

window.registerAdminTinyMCEEmbedButton = function (editor) {
    editor.ui.registry.addButton('embedhtml', {
        text: 'تضمين',
        tooltip: 'تضمين YouTube أو Google Drive أو iframe',
        icon: 'embed',
        onAction: function () {
            editor.windowManager.open({
                title: 'تضمين محتوى خارجي',
                size: 'large',
                body: {
                    type: 'panel',
                    items: [
                        {
                            type: 'textarea',
                            name: 'embedcode',
                            label: 'الصق رابط YouTube / Google Drive أو كود iframe كامل',
                        }
                    ]
                },
                buttons: [
                    { type: 'submit', text: 'إدراج', primary: true },
                    { type: 'cancel', text: 'إلغاء' }
                ],
                onSubmit: function (api) {
                    const html = window.buildEmbedHtml(api.getData().embedcode);
                    if (html) {
                        editor.insertContent('<div class="embed-responsive-wrapper">' + html + '</div><p></p>');
                    }
                    api.close();
                }
            });
        }
    });
};

window.initAdminTinyMCE = function (selector, options) {
    if (typeof tinymce === 'undefined') {
        console.error('TinyMCE is not loaded');
        return;
    }

    const defaults = {
        height: 400,
        language: 'ar',
        directionality: 'rtl',
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount', 'emoticons',
            'codesample', 'nonbreaking', 'pagebreak', 'save', 'directionality'
        ],
        toolbar: 'undo redo | embedhtml | blocks fontfamily fontsize | bold italic underline | ' +
            'alignleft aligncenter alignright alignjustify | numlist bullist | forecolor backcolor | ' +
            'image media link | codesample code | fullscreen preview | ltr rtl | help',
        toolbar_mode: 'wrap',
        menubar: 'file edit view insert format tools table help',
        content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; direction: rtl; } ' +
            '.embed-responsive-wrapper iframe { max-width: 100%; border: 0; }',
        font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
        font_family_formats: 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier,monospace; Times New Roman=times new roman,times,serif; Tahoma=tahoma,arial,helvetica,sans-serif; Verdana=verdana,geneva,sans-serif',
        image_advtab: true,
        media_live_embeds: true,
        media_url_resolver: function (data, resolve) {
            const html = window.buildEmbedHtml(data.url || '');
            if (html && html.indexOf('<iframe') !== -1) {
                resolve({ html: html });
                return;
            }
            resolve({ html: '' });
        },
        link_assume_external_targets: true,
        file_picker_types: 'image media',
        automatic_uploads: true,
        images_upload_url: '/upload',
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true,
        extended_valid_elements: 'iframe[src|width|height|frameborder|allow|allowfullscreen|style|class|loading|title]',
        valid_children: '+body[style|iframe|div]',
        sandbox_iframes: false,
        setup: function (editor) {
            window.registerAdminTinyMCEEmbedButton(editor);
            editor.on('change', function () {
                editor.save();
            });
        }
    };

    const config = Object.assign({}, defaults, options || {});
    const sel = config.selector || selector;

    if (sel && tinymce.get(sel.replace('#', ''))) {
        tinymce.remove(sel);
    }

    config.selector = sel;
    return tinymce.init(config);
};

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            if (typeof tinymce !== 'undefined' && tinymce.editors) {
                tinymce.editors.forEach(function (editor) {
                    editor.save();
                });
            }
        });
    });
});
