'use strict';

if($('.tinymce').length > 0){
    tinymce.init({
        selector: ".tinymce",
        plugins:  "anchor autolink charmap link lists searchreplace wordcount ",
        toolbar: "undo redo | blocks | bold italic underline strikethrough | link mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | removeformat",
        tinycomments_mode: "embedded",
        tinycomments_author: "Author name",
        menubar: false,
    });
}

const tagifyBasicEl = document.querySelector('#courseTag');
if(tagifyBasicEl){
    const TagifyBasic = new Tagify(tagifyBasicEl);
}

$('.curriculum-topic-item-sortable').each(function(){
    Sortable.create(this, {
        animation: 150,
        handle: '.curriculum-topic-item',
    });
});

$(function () {
    const cardEl = document.querySelector('.sortable-card');
    if (cardEl) {
        Sortable.create(cardEl, {
            animation: 150,
            handle: '.curriculum-accordion-header',
        });
    }

    $('.curriculum-accordion-item.show .curriculum-accordion-body').slideDown(0);

    $('.curriculum-accordion-button').on('click', function (e) {
        if ($(e.target).closest('.lms-curriculum-accordion-action-btn').length) return;
        const item = $(this).closest('.curriculum-accordion-item');
        const body = item.find('.curriculum-accordion-body');

        if (item.hasClass('show')) {
            body.slideUp(200);
            item.removeClass('show');
        } else {
            $('.curriculum-accordion-item.show').not(item).removeClass('show').find('.curriculum-accordion-body').slideUp(200);
            item.addClass('show');
            body.slideDown(200);
        }
    });

    $('.edit-btn').on('click', function (e) {
        e.stopPropagation();
        // Implement edit functionality here
    });

    $('.delete-btn').on('click', function (e) {
        e.stopPropagation();
        // Implement delete functionality here
    });
});

$('.tp-course-toggle-btn').on('click', function() {
    $('.tp-course-toggle-btn').removeClass('active');
    $(this).addClass('active');

    var dataValue = $(this).attr('data-value');

    if (dataValue === 'grid') {
        $('.tp-course-grid-wrapper .tp-course-style-grid').removeClass('d-none');
        $('.tp-course-grid-wrapper .tp-course-style-list').addClass('d-none');


    } else if (dataValue === 'list') {
        $('.tp-course-grid-wrapper .tp-course-style-list').removeClass('d-none');
        $('.tp-course-grid-wrapper .tp-course-style-grid').addClass('d-none');
    }
});