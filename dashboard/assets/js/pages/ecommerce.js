(function($) {
  'use strict'

  $('.tinymce-active').each(function() {
        tinymce.init({
            selector: '#' + $(this).attr('id'),
            height: 300,
            menubar: false,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            skin: window.matchMedia("(prefers-color-scheme: dark)").matches
                ? "oxide-dark"
                : "oxide",
            content_css: window.matchMedia("(prefers-color-scheme: dark)").matches
                ? "dark"
                : "default",
            toolbar:
                'undo redo | formatselect | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help',
            content_style:
                'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const fileInput = document.getElementById('product-gallery-uploader-input');
        const galleryRow = document.getElementById('product-gallery-row');
        const uploaderCol = document.getElementById('product-gallery-uploader-col');

        // Keep track of images: { file: File|null, url: string, existing: boolean, existingId: string|null }
        let uploadedFiles = [];

        // Ensure uploader column always has the required classes
        function enforceUploaderColClass() {
            uploaderCol.className = 'col-lg-3 col-md-4 col-sm-6 col-12';
        }

        // Revoke blob URL (if any) to free memory
        function revokeIfBlob(item) {
            if (item && item.file && item.url && item.url.startsWith('blob:')) {
            try { URL.revokeObjectURL(item.url); } catch (e) { /* ignore */ }
            }
        }

        // Load any existing images that are present in the DOM on page load
        (function loadExistingFromDOM() {
            const singles = Array.from(galleryRow.querySelectorAll('.product-gallery-single'));
            singles.forEach(single => {
            // Skip if this single is inside uploader col (it shouldn't be)
            if (single.closest('#product-gallery-uploader-col')) return;

            const img = single.querySelector('img');
            if (!img) return;
            const existingId = single.dataset.existingId ?? null;
            uploadedFiles.push({
                file: null,
                url: img.src,
                existing: true,
                existingId
            });
            });
        })();

        function renderGallery() {
            Array.from(galleryRow.children).forEach(child => {
                if (child !== uploaderCol) child.remove();
            });

            uploadedFiles.forEach((item, i) => {
            const col = document.createElement('div');
            col.className = 'col-lg-3 col-md-4 col-sm-6 col-12';

            const dataExistingAttr = (item.existing && item.existingId) ? `data-existing-id="${item.existingId}"` : '';

            col.innerHTML = `
                <div class="product-gallery-single position-relative" ${dataExistingAttr}>
                    <button type="button" class="product-gallery-remove-btn" data-index="${i}" aria-label="Remove image">
                        <svg width="10" height="11" viewBox="0 0 10 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 1.98608L1 9.98608" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M1 1.98608L9 9.98608" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <img class="img-fluid" src="${item.url}" alt="preview-${i}">
                </div>
            `;
                galleryRow.insertBefore(col, uploaderCol);
            });

            enforceUploaderColClass();
        }

        galleryRow.addEventListener('click', (e) => {
            const btn = e.target.closest('.product-gallery-remove-btn');
            if (!btn) return;
            const idx = Number(btn.dataset.index);
            if (Number.isNaN(idx)) return;

            const removed = uploadedFiles.splice(idx, 1)[0];
            revokeIfBlob(removed);
            renderGallery();
        });

        // Handle new file selection
        fileInput.addEventListener('change', (e) => {
            const files = Array.from(e.target.files || []);
            if (!files.length) return;

            files.forEach(file => {
            const url = URL.createObjectURL(file);
            uploadedFiles.push({ file, url, existing: false, existingId: null });
            });

            fileInput.value = '';
            renderGallery();
        });

        function buildFormData() {
            const fd = new FormData();
            uploadedFiles.forEach(it => {
                if (it.file) {
                    fd.append('images[]', it.file);
                } else if (it.existing && it.existingId) {
                    fd.append('existing_images[]', it.existingId);
                } else if (it.existing) {
                    fd.append('existing_images_urls[]', it.url);
                }
            });
            return fd;
        }


        window.addEventListener('beforeunload', () => {
            uploadedFiles.forEach(revokeIfBlob);
        });

        renderGallery();

    });

}(jQuery))