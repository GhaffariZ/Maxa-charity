(function($) {
    'use strict';
    
    $(function() {

        const imageList1 = document.getElementById('image-list-1'),
                imageList2 = document.getElementById('image-list-2'),
                cardEl = document.getElementById('sortable-card'),
                cardEl2 = document.getElementById('sortable-card-2'),
                cloneSource1 = document.getElementById('clone-list-1'),
                cloneSource2 = document.getElementById('clone-list-2');

        if (cardEl) {
            Sortable.create(cardEl, {
                animation: 150,
                group: 'avatarList'
            });
        }
        if (cardEl2) {
            Sortable.create(cardEl2, {
                animation: 150,
                group: 'avatarList'
            });
        }

        if (imageList1) {
            Sortable.create(imageList1, {
            animation: 150,
            group: 'imgList'
            });
        }
        if (imageList2) {
            Sortable.create(imageList2, {
            animation: 150,
            group: 'imgList'
            });
        }

        // clone list

        if (cloneSource1) {
            Sortable.create(cloneSource1, {
              animation: 150,
              group: {
                name: 'cloneList',
                pull: 'clone',
                revertClone: true
              }
            });
          }
          if (cloneSource2) {
            Sortable.create(cloneSource2, {
              animation: 150,
              group: {
                name: 'cloneList',
                pull: 'clone',
                revertClone: true
              }
            });
          }
    })
    
}(jQuery) )