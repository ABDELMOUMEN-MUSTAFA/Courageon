$(function(){ 
    let last_message_time = $('input[name="last_message_time"]').val();
    const audio = new Audio(`${URLROOT}/public/audios/new_message.mp3`);

    function poll() {
        const recieverID = $('input[name="to"]').val();
        
        $.ajax({
            url : `${URLROOT}/chat/${recieverID}?last_time=${last_message_time}`,
            success: function(res){
                if(res){
                    last_message_time = res.data.unix_timestamp
                    $('#messages').append(`
                        <li class="message d-inline-flex contact">
                            <div class="message__body card">
                                <div class="card-body px-2 py-1"">
                                    <span>${res.data.message}</span>
                                    <div>
                                        <small class="text-muted">${res.data.sent_time}</small>
                                    </div>
                                </div>
                            </div>
                        </li>
                    `).hide().fadeIn(500);

                    audio.play();
    
                    $(".ps--active-y").animate({ scrollTop: 999999999 });
                }
            }
        })
    }
   
    poll();
    setInterval(function(){
       poll();
    }, 1500);
})