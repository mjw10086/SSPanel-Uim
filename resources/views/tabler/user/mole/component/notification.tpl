<div id="notification" class="position-fixed top-0 p-3 bg-info w-100 d-flex justify-content-center visually-hidden"
    style="z-index: 5">

</div>


<script>
    var notification_list = [
        {foreach $notifications as $notification}
            "{$notification.content}",
        {/foreach}
    ];
    var notification = document.getElementById("notification");

    function showNotification(noti) {
        notification.classList.remove("visually-hidden")
        notification.innerHTML = noti
    }

    function hiddenNotification() {
        notification.classList.add("visually-hidden")
        notification.innerHTML = ""
    }

    for (let i = 0; i < notification_list.length; i++) {
        setTimeout(() => {
            showNotification(notification_list[i])
        }, 7000 * i);
        setTimeout(() => {
            hiddenNotification()
        }, 6000 * (i + 1));
    }
</script>