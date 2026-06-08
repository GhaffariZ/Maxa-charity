(function($) {
  'use strict'

    let currentUrl = window.location.href;
    let pageName = currentUrl.substring(currentUrl.lastIndexOf('../../index.html') + 1, currentUrl.lastIndexOf('.'));
    pageName = pageName + '.html';

    document.querySelectorAll('.profile-nav .nav-link').forEach(function(link) {
        if (link.getAttribute('href') === pageName) {
            link.classList.add('active');
        }
    }); 

    // user settings billing card delete alert
    $('.delete-card').on('click', function(e) {
        e.preventDefault();
        let card = $(this).closest('.card');
        let cardName = card.find('.card-title').text();
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete the ${cardName} card.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                card.remove();
                Swal.fire(
                    'Deleted!',
                    `${cardName} card has been deleted.`,
                    'success'
                );
            }
        });
    });

    const activeItem = document.querySelector('.pure-slide-tab-item.active');
    const bar = document.querySelector('.pure-slide-tab-bar');
    
    $('#addCardModal').on('shown.bs.modal', function () {
        if (activeItem && bar) {
            window.setBarPosition(activeItem, bar);
        }
    });


    const avatarInput = $('#userAvatar');
    const avatarImg = $('.container-avatar');
    const uploadBtn = $('.upload-avatar-btn');
    const resetBtn = $('.reset-avatar-btn');

    if(avatarInput.length && avatarImg.length && uploadBtn.length && resetBtn.length) {
        
        const defaultAvatarSrc = avatarImg.attr('src');

        uploadBtn.on('click', function() {
            avatarInput[0].click();
        });

        // Preview uploaded avatar
        avatarInput.on('change', function() {
            const file = this.files[0];
            if (file && file.type.startsWith('image/index.html') && file.size <= 2 * 1024 * 1024) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarImg.attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
            } else {
                alert('Invalid file. Please select a JPG, PNG, or JPEG under 2MB.');
                avatarInput.val('');
            }
        });

        resetBtn.on('click', function() {
            avatarImg.attr('src', defaultAvatarSrc);
            avatarInput.val('');
        });

        return;
    }
    
}(jQuery))