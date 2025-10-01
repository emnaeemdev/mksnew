<footer class="footer mt-auto py-3 bg-light border-top">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span class="text-muted">
                    &copy; <?php echo e(date('Y')); ?> <?php echo e(config('app.name', 'MK Snow')); ?>. جميع الحقوق محفوظة.
                </span>
            </div>
            <div class="col-md-6 text-end">
                <span class="text-muted">
                    الإصدار 1.0.0 | 
                    <a href="#" class="text-decoration-none">الدعم الفني</a> | 
                    <a href="#" class="text-decoration-none">التوثيق</a>
                </span>
            </div>
        </div>
    </div>
</footer>

<!-- Admin Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo e(asset('js/admin.js')); ?>"></script>
<?php echo $__env->yieldContent('scripts'); ?><?php /**PATH C:\xampp\htdocs\mksnow\resources\views/admin/partials/footer.blade.php ENDPATH**/ ?>