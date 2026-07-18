@php
    $documentFiles = $documentFiles ?? ($document->files ?? collect());
@endphp

@if($documentFiles->count() > 0)
    <div class="card border-0 shadow-sm mb-4 doc-attached-files">
        <div class="card-header bg-light">
            <h6 class="card-title mb-0">
                <i class="fas fa-paperclip me-2"></i>
                الملفات المرفقة
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush doc-attached-files-list">
                @foreach($documentFiles as $file)
                    <div class="list-group-item py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="flex-shrink-0">
                                <i class="{{ $file->getIcon() }} fa-2x" style="color: var(--brand-blue, #2b4596);"></i>
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="fw-bold" style="color: var(--brand-orange, #e9640a);">
                                    {{ $file->display_name ?: $file->original_name }}
                                </div>
                                <!-- <small class="text-muted d-block">{{ $file->original_name }}</small> -->
                                <small class="text-muted">{{ $file->getFormattedSize() }}</small>
                            </div>
                            <div class="flex-shrink-0">
                                <a href="{{ route('frontend.documents.files.download', [app()->getLocale(), $file->id]) }}"
                                   class="btn btn-sm"
                                   style="border: 1px solid var(--brand-blue, #2b4596); color: var(--brand-blue, #2b4596);">
                                    <i class="fas fa-download me-1"></i>
                                    تحميل
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
