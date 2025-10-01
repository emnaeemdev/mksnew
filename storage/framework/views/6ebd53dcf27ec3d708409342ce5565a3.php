<?php $__env->startSection('content'); ?>
<div class="container-fluid py-3">
    <h4 class="mb-3">تعديل حلقة بودكاست</h4>

    <form action="<?php echo e(route('admin.podcasts.update', $podcast)); ?>" method="post" enctype="multipart/form-data" class="row g-3">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="col-md-8">
            <div class="mb-3">
                <label class="form-label">العنوان</label>
                <input type="text" name="title" value="<?php echo e(old('title', $podcast->title)); ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Slug (اختياري)</label>
                <input type="text" name="slug" value="<?php echo e(old('slug', $podcast->slug)); ?>" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">المحتوى</label>
                <textarea name="content" class="form-control" rows="8"><?php echo e(old('content', $podcast->content)); ?></textarea>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label">صورة الغلاف</label>
                <?php if($podcast->cover_image_path): ?>
                    <img src="<?php echo e(asset('storage/'.$podcast->cover_image_path)); ?>" class="img-fluid mb-2" style="max-height:150px;object-fit:cover">
                <?php endif; ?>
                <input type="file" name="cover_image" class="form-control" accept="image/*">
            </div>
            <div class="mb-3">
                <label class="form-label">ملف الصوت</label>
                <?php if($podcast->audio_path): ?>
                    <audio src="<?php echo e(asset('storage/'.$podcast->audio_path)); ?>" controls class="w-100 mb-2"></audio>
                <?php endif; ?>
                <input type="file" name="audio_file" class="form-control" accept="audio/*">
            </div>
            <div class="mb-3">
                <label class="form-label">Spotify URL</label>
                <input type="url" name="spotify_url" value="<?php echo e(old('spotify_url', $podcast->spotify_url)); ?>" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Apple Podcasts URL</label>
                <input type="url" name="apple_podcasts_url" value="<?php echo e(old('apple_podcasts_url', $podcast->apple_podcasts_url)); ?>" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">SoundCloud URL</label>
                <input type="url" name="soundcloud_url" value="<?php echo e(old('soundcloud_url', $podcast->soundcloud_url)); ?>" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">YouTube URL</label>
                <input type="url" name="youtube_url" value="<?php echo e(old('youtube_url', $podcast->youtube_url)); ?>" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">تاريخ النشر</label>
                <input type="datetime-local" name="published_at" value="<?php echo e(old('published_at', optional($podcast->published_at)->format('Y-m-d\TH:i'))); ?>" class="form-control">
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="is_published" value="1" class="form-check-input" id="is_published" <?php if(old('is_published', $podcast->is_published)): echo 'checked'; endif; ?>>
                <label class="form-check-label" for="is_published">منشور</label>
            </div>
            <div class="mb-3">
                <label class="form-label">الترتيب</label>
                <input type="number" name="sort_order" value="<?php echo e(old('sort_order', $podcast->sort_order)); ?>" class="form-control" min="0">
            </div>
            <button class="btn btn-primary">تحديث</button>
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
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mksnow\resources\views/admin/podcasts/edit.blade.php ENDPATH**/ ?>