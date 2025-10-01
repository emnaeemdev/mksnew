<?php $__env->startSection('content'); ?>
<div class="container-fluid py-3">
    <h4 class="mb-3">إضافة حلقة بودكاست</h4>

    <form action="<?php echo e(route('admin.podcasts.store')); ?>" method="post" enctype="multipart/form-data" class="row g-3">
        <?php echo csrf_field(); ?>
        <div class="col-md-8">
            <div class="mb-3">
                <label class="form-label">العنوان</label>
                <input type="text" name="title" value="<?php echo e(old('title')); ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Slug (اختياري)</label>
                <input type="text" name="slug" value="<?php echo e(old('slug')); ?>" class="form-control" placeholder="مثال: court-podcast-1">
            </div>
            <div class="mb-3">
                <label class="form-label">المحتوى</label>
                <textarea name="content" class="form-control" rows="8"><?php echo e(old('content')); ?></textarea>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label">صورة الغلاف</label>
                <input type="file" name="cover_image" class="form-control" accept="image/*">
            </div>
            <div class="mb-3">
                <label class="form-label">ملف الصوت</label>
                <input type="file" name="audio_file" class="form-control" accept="audio/*">
            </div>
            <div class="mb-3">
                <label class="form-label">Spotify URL</label>
                <input type="url" name="spotify_url" value="<?php echo e(old('spotify_url')); ?>" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Apple Podcasts URL</label>
                <input type="url" name="apple_podcasts_url" value="<?php echo e(old('apple_podcasts_url')); ?>" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">SoundCloud URL</label>
                <input type="url" name="soundcloud_url" value="<?php echo e(old('soundcloud_url')); ?>" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">YouTube URL</label>
                <input type="url" name="youtube_url" value="<?php echo e(old('youtube_url')); ?>" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">تاريخ النشر</label>
                <input type="datetime-local" name="published_at" value="<?php echo e(old('published_at')); ?>" class="form-control">
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="is_published" value="1" class="form-check-input" id="is_published" checked>
                <label class="form-check-label" for="is_published">منشور</label>
            </div>
            <div class="mb-3">
                <label class="form-label">الترتيب</label>
                <input type="number" name="sort_order" value="<?php echo e(old('sort_order', 0)); ?>" class="form-control" min="0">
            </div>
            <button class="btn btn-primary">حفظ</button>
            <a href="<?php echo e(route('admin.podcasts.index')); ?>" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  document.addEventListener('DOMContentLoaded', function(){
    if (window.tinymce) {
      tinymce.init({
        selector: 'textarea[name=content]',
        menubar: false,
        directionality: '<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>',
        plugins: 'link lists code table image media autoresize',
        toolbar: 'undo redo | styles | bold italic underline | align | bullist numlist | link | image media | removeformat | code',
        height: 400,
        relative_urls: false,
        convert_urls: false,
      });
    }
  });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mksnow\resources\views/admin/podcasts/create.blade.php ENDPATH**/ ?>