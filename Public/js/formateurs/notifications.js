$(function(){
    // set notification to seen
    $(document).on('click', '.notification', function(){
        if($(this).find('.unread-indicator').length > 0){
            $.ajax({
                url : `${URLROOT}/api/notifications/${$(this).data('id')}`,
                type: 'PUT',
                data: { is_read: true }
            });
        }
    });

    // Periodically check for new notifications
    let lastNotificationTime = $('[name="last_notification"]').val();
    setInterval(function(){
        $.ajax({
            url : `${URLROOT}/api/notifications?last_notif_time=${lastNotificationTime}`,
            type: 'GET',
            success : function(data){
                if(data){
                    const { data: notification } = data; 

                    $('.notifications').prepend(`
                        <a data-id="${notification.id_notification}" class="notification list-group-item list-group-item-action unread d-block" href="${URLROOT}/formateur/messages/${notification.slug}"
                        class="list-group-item list-group-item-action unread">
                            <div class="d-flex align-items-center mb-1">
                                <small class="text-muted">${notification.created_at}</small>
                                <span class="ml-auto unread-indicator bg-primary"></span>
                            </div>
                            <div class="d-flex">
                                <div class="avatar avatar-xs mr-2">
                                    <img src="${URLROOT}/public/images/${notification.img}" alt="etudiant avatar" class="avatar-img rounded-circle">
                                </div>
                                <div class="flex d-flex flex-column">
                                    <strong>${notification.prenom} ${notification.nom}</strong>
                                    <span class="text-black-70">${notification.content}</span>
                                </div>
                            </div>
                        </a>
                    `);

                    lastNotificationTime = notification.unix_timestamp;
                    $('#btn-play-notify-sound').click();
                    const notificationCounter = parseInt($('#notification-counter').text().trim());
                    $('#notification-counter').text(notificationCounter + 1);
                }
            }
        });
    }, 3000);

    // Play sound when new notification exists
    const audio = new Audio(`${URLROOT}/public/audios/notification.mp3`);
    $("#btn-play-notify-sound").click(function(){
        audio.play();
    });

    // Clear seen notifications
    $('#btn-clear-notifications').click(function(e){
        e.stopPropagation();
        $.ajax({
            url : `${URLROOT}/api/notifications/all`,
            type: 'DELETE',
            success: function(){
                $('.notification[data-is-read="1"]').remove();
            }
        });
    });
});