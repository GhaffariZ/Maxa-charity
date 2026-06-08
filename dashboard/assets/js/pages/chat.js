(function($) {
  'use strict'

  if(document.querySelector('.chat-sidebar-body')){
        new PerfectScrollbar(document.querySelector('.chat-sidebar-body'), {
            suppressScrollX: true
        });
    }

    const chatBox = document.querySelector('.chat-conversation-body');
    const ps =  new PerfectScrollbar(chatBox, {suppressScrollX: true});

    function scrollToBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
        ps.update();
    }

    window.addEventListener('load', scrollToBottom);

    const chatMsgHTML = (author, msg, time) =>{

     return (`<li class="chat-conversation-msg msg-right d-flex justify-content-end">
            <div class="d-flex overflow-hidden gap-3">
                <div class="chat-conversation-msg-content flex-grow-1">
                    <div class="chat-conversation-msg-text-wrapper">
                        <div class="chat-conversation-msg-text bg-gray-3 text-black">
                            ${msg}
                        </div>
                    </div>
                    <div class="chat-conversation-msg-content-meta">
                        <span class="chat-view-status send">
                            <svg width="18" height="11" viewBox="0 0 18 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M11.7929 0.999995L4.6333 8.87832L1.142 5.53908" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <span class="chat-time">${time}</span>
                    </div>
                </div>
                <div class="chat-conversation-msg-avatar flex-shrink-0">
                    <div class="avatar avatar-sm rounded-pill">
                        <img src="../assets/img/avatar/12.jpg" alt="conca">
                    </div>
                </div>
            </div>
        </li>`);
    }

    $('#chatConversationForm').on('submit', function(e) {
        e.preventDefault();
        const messageInput = document.querySelector('#chatMessageInput');
        const message = messageInput.value.trim();

        if (message) {
            const msgHTML = chatMsgHTML('You', message, new Date().toLocaleTimeString());
            chatBox.insertAdjacentHTML('beforeend', msgHTML);
            messageInput.value = '';
            scrollToBottom();
        }
    });

    $('.chat-list-item').on('click', function() {
        $(this).closest('.chat-sidebar-body').find('.chat-list-item').removeClass('active');
        $(this).addClass('active');
    });

    $('.chat-open-btn').on('click', function() {
        $('.app-chat-sidebar-main').addClass('chat-opened');
        $('.app-chat-backdrop').addClass('active');
    });

    $('.chat-sidebar-close-btn, .chat-back-btn').on('click', function() {
        $('.app-chat-sidebar-main').removeClass('chat-opened');
        $('.app-chat-backdrop').removeClass('active');
    });

    $('.app-chat-backdrop').on('click', function() {
        $('.app-chat-sidebar-main').removeClass('chat-opened');
        $('.app-chat-backdrop').removeClass('active');
    });

}(jQuery))