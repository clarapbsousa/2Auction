// I added the variables like this so we can have this script separated.
// Everything should work as intended but please use window.var_name instead of var_name only
// As it's using defer it should always get the correct value before loading.
// - Vicente

// console.log(window.pusherAppKey);
// console.log(window.pusherCluster);

function getRelativeTime(timestamp) {
    const now = new Date();
    const eventTime = new Date(timestamp);
    const diffInSeconds = Math.floor((now - eventTime) / 1000);

    if (diffInSeconds < 60) return 'just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
    return `${Math.floor(diffInSeconds / 86400)} days ago`;
}
  
const pusher = new Pusher(window.pusherAppKey, {
    cluster: window.pusherCluster,
    encrypted: true
});

pusher.connection.bind('state_change', function(states) {
    console.log('Pusher connection state change:', states);
});

pusher.logToConsole = true


document.addEventListener('DOMContentLoaded', function () {
    const notificationBox = document.getElementById('notification-box');

    // Function to fetch and display notifications
    async function fetchNotifications() {
        try {
            const response = await fetch('/notifications');
            if (!response.ok) throw new Error('Failed to fetch notifications');
    
            const notifications = await response.json();
            console.log(notifications);
            notifications.forEach(notification => {
                const timeAgo = getRelativeTime(notification.creationdate);
    
                const notificationDiv = document.createElement('div');
                notificationDiv.classList.add('d-flex', 'border-bottom', 'ps-3', 'py-4', 'fs-4', 'notification-item');
    
                // Add 'fw-bold' class for unread notifications
                if (!notification.read) {
                    notificationDiv.classList.add('fw-bold');
                }
    
                const anchor = document.createElement('a');
                let href = '';
                anchor.classList.add('text-decoration-none'); // Remove underline for the outer link
                anchor.style.color = 'inherit'; // Inherit color to keep text consistent
                anchor.style.display = 'block'; // Make the entire notification clickable
                anchor.dataset.notificationId = notification.id; // Add notification ID for marking read
    
                let content = '';
                if (notification.type === 'auctionbid') {
                    href = notification.auctionurl;
                    content = `
                        <img src="${notification.imagepath}" alt="Item Image" class="rounded-4 me-3 flex-shrink-0" style="width: 50px; height: 50px;">
                        <div class="d-flex flex-column">
                            <span>
                                <a href="${notification.bidderurl}" class="text-primary fw-bold" style="color: #027782 !important;">${notification.biddername}</a>
                                placed a bid of 
                                <strong>${notification.bidvalue}€</strong> 
                                on your auction 
                                <a href="${notification.auctionurl}" class="text-primary fw-bold" style="color: #027782 !important;">${notification.auctionname}</a>.
                            </span>
                            <span class="fs-6 text-muted">${timeAgo}</span>
                        </div>
                    `;
                } else if (notification.type === 'auctionending') {
                    href = notification.auctionurl;
                    content = `
                        <img src="${notification.imagepath}" alt="Item Image" class="rounded-4 me-3 flex-shrink-0" style="width: 50px; height: 50px;">
                        <div class="d-flex flex-column">
                            <span>
                                Hurry up! 30 minutes left until auction
                                <a href="${notification.auctionurl}" class="text-primary fw-bold" style="color: #027782 !important;">${notification.auctionname}</a>
                                finishes!
                            </span>
                            <span class="fs-6 text-muted">${timeAgo}</span>
                        </div>
                    `;
                } else if (notification.type === 'auctionreport') {
                    href = notification.reporturl;
                    content = `
                        <img src="${notification.imagepath}" alt="Item Image" class="rounded-4 me-3 flex-shrink-0" style="width: 50px; height: 50px;">
                        <div class="d-flex flex-column">
                            <span>
                                <a href="${notification.bidderurl}" class="text-primary fw-bold" style="color: #027782 !important;">${notification.biddername}</a> 
                                placed a new report on auction 
                                <a href="${notification.auctionurl}" class="text-primary fw-bold" style="color: #027782 !important;">${notification.auctionname}</a>.
                            </span>
                            <span class="fs-6 text-muted">${timeAgo}</span>
                        </div>
                    `;
                } else if (notification.type === 'userreport') {
                    href = notification.reporturl;
                    content = `
                        <img src="${notification.imagepath}" alt="Item Image" class="rounded-4 me-3 flex-shrink-0" style="width: 50px; height: 50px;">
                        <div class="d-flex flex-column">
                            <span>
                                <a href="${notification.bidderurl}" class="text-primary fw-bold" style="color: #027782 !important;">${notification.biddername}</a> 
                                placed a new report on user
                                <a href="${notification.auctionurl}" class="text-primary fw-bold" style="color: #027782 !important;">${notification.auctionname}</a>.
                            </span>
                            <span class="fs-6 text-muted">${timeAgo}</span>
                        </div>
                    `;
                } else if (notification.type === 'auctionended') {
                    href = notification.auctionurl;
                    content = `
                        <img src="${notification.imagepath}" alt="Item Image" class="rounded-4 me-3 flex-shrink-0" style="width: 50px; height: 50px;">
                        <div class="d-flex flex-column">
                            <span>
                                Auction
                                <a href="${notification.auctionurl}" class="text-primary fw-bold" style="color: #027782 !important;">${notification.auctionname}</a> 
                                ${notification.reporturl === 'sold' ? 'has ended' : 'was cancelled'}.
                            </span>
                            <span class="fs-6 text-muted">${timeAgo}</span>
                        </div>
                    `;
                } else if (notification.type === 'biddeleted') {
                    href = notification.auctionurl;
                    content = `
                        <img src="${notification.imagepath}" alt="Item Image" class="rounded-4 me-3 flex-shrink-0" style="width: 50px; height: 50px;">
                        <div class="d-flex flex-column">
                            <span>
                                Your bid on auction
                                <a href="${notification.auctionurl}" class="text-primary fw-bold" style="color: #027782 !important;">${notification.auctionname}</a> 
                                was cancelled.
                            </span>
                            <span class="fs-6 text-muted">${timeAgo}</span>
                        </div>
                    `;
                } else if (notification.type === 'userrating') {
                    href = notification.bidderurl;
                    content = `
                        <img src="${notification.imagepath}" alt="Item Image" class="rounded-4 me-3 flex-shrink-0" style="width: 50px; height: 50px;">
                        <div class="d-flex flex-column">
                            <span>
                                User
                                <a href="${notification.bidderurl}" class="text-primary fw-bold" style="color: #027782 !important;">${notification.auctionname}</a> 
                                rated you with ${Math.floor(notification.bidvalue)}/5! 
                            </span>
                            <span class="fs-6 text-muted">${timeAgo}</span>
                        </div>
                    `;
                } else if (notification.type == 'auctionwishlist') {
                    href = notification.auctionurl;
                    content = `
                        <img src="${notification.imagepath}" alt="Item Image" class="rounded-4 me-3 flex-shrink-0" style="width: 50px; height: 50px;">
                        <div class="d-flex flex-column">
                            <span>
                                <a href="${notification.bidderurl}" class="text-primary fw-bold" style="color: #027782 !important;">${notification.biddername}</a>
                                has started following your auction
                                <a href="${notification.auctionurl}" class="text-primary fw-bold" style="color: #027782 !important;">${notification.auctionname}</a>.
                            </span>
                            <span class="fs-6 text-muted">${timeAgo}</span>
                        </div>
                    `;
                }
                
                document.getElementById('no-notis').style.display = 'none';
                notificationDiv.innerHTML = content;
                anchor.href = href;
                anchor.appendChild(notificationDiv);
    
                notificationBox.appendChild(anchor);
            });
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }

    notificationBox.addEventListener('click', function (event) {
        const anchor = event.target.closest('a');
        if (!anchor || !anchor.classList.contains('text-decoration-none')) {
            return;
        }
    
        const notificationId = anchor.dataset.notificationId;
        if (!notificationId) {
            console.error('Notification ID not found');
            return;
        }
    
        fetch(`/notifications/${notificationId}/mark-read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to mark notification as read');
                }
                console.log(`Notification ${notificationId} marked as read`);
    
                const notificationDiv = anchor.querySelector('.notification-item');
                if (notificationDiv) {
                    notificationDiv.classList.remove('fw-bold');
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
    });

    fetchNotifications();
});


document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');

    const channel = pusher.subscribe(`user.${window.authUser}`);
    console.log(`Subscribed to channel user.${window.authUser}`);

    channel.bind('auction-bid', function(data) {
        console.log('Received notification:', data);

        const notificationBox = document.getElementById('notification-box');
        if (!notificationBox) {
            console.error('Notification box not found');
            return;
        }

        const anchor = document.createElement('a');
        anchor.href = data.auctionUrl;
        anchor.classList.add('text-decoration-none');
        anchor.style.color = 'inherit';
        anchor.style.display = 'block';

        const timeAgo = getRelativeTime(data.timestamp);

        const notificationDiv = document.createElement('div');
        notificationDiv.classList.add('d-flex', 'border-bottom', 'ps-3', 'py-4', 'fs-4', 'notification-item', 'fw-bold');
        notificationDiv.innerHTML = `
            <img src="${data.imagepath}" alt="Item Image" class="rounded-4 me-3 flex-shrink-0" style="width: 50px; height: 50px;">
            <div class="d-flex flex-column">
                <span>
                    <a href="${data.bidderUrl}" class="text-primary fw-bold" style="color: #027782 !important;">${data.bidder}</a> 
                    placed a bid of 
                    <strong>${data.value}€</strong> 
                    on your auction 
                    <a href="${data.auctionUrl}" class="text-primary fw-bold" style="color: #027782 !important;">${data.auction}</a>.
                </span>
                <span class="fs-6 text-muted">${timeAgo}</span>
            </div>
        `;

        document.getElementById('no-notis').style.display = 'none';
        anchor.appendChild(notificationDiv);
        notificationBox.prepend(anchor);
        notificationBox.style.display = 'block';
    });

    channel.bind('auction-ending', function(data) {
        console.log('Received notification:', data);

        const notificationBox = document.getElementById('notification-box');
        if (!notificationBox) {
            console.error('Notification box not found');
            return;
        }

        const anchor = document.createElement('a');
        anchor.href = data.auctionUrl;
        anchor.classList.add('text-decoration-none');
        anchor.style.color = 'inherit';
        anchor.style.display = 'block';
        
        const timeAgo = getRelativeTime(data.timestamp);


        const notificationDiv = document.createElement('div');
        notificationDiv.classList.add('d-flex', 'border-bottom', 'ps-3', 'py-4', 'fs-4', 'notification-item', 'fw-bold');
        notificationDiv.innerHTML = `
            <img src="${data.imagepath}" alt="Item Image" class="rounded-4 me-3 flex-shrink-0" style="width: 50px; height: 50px;">
            <div class="d-flex flex-column">
                <span>
                Hurry up! 30 minutes left until auction
                <a href="${data.auctionUrl}" class="text-primary fw-bold" style="color: #027782 !important;">${data.auction}</a>
                finishes!
                </span>
                <span class="fs-6 text-muted">${timeAgo}</span>
            </div>
        `;
        
        document.getElementById('no-notis').style.display = 'none';
        anchor.appendChild(notificationDiv);
        notificationBox.prepend(anchor);
        notificationBox.style.display = 'block';
    });

    channel.bind('auction-report', function(data) {
        console.log('Received notification:', data);

        const notificationBox = document.getElementById('notification-box');
        if (!notificationBox) {
            console.error('Notification box not found');
            return;
        }

        const anchor = document.createElement('a');
        anchor.href = data.reportUrl;
        anchor.classList.add('text-decoration-none');
        anchor.style.color = 'inherit';
        anchor.style.display = 'block';
        
        const timeAgo = getRelativeTime(data.timestamp);

        const notificationDiv = document.createElement('div');
        notificationDiv.classList.add('d-flex', 'border-bottom', 'ps-3', 'py-4', 'fs-4', 'notification-item', 'fw-bold');
        notificationDiv.innerHTML = `
            <img src="${data.imagepath}" alt="Item Image" class="rounded-4 me-3 flex-shrink-0" style="width: 50px; height: 50px;">
            <div class="d-flex flex-column">
                <span>
                    <a href="${data.bidderUrl}" class="text-primary fw-bold" style="color: #027782 !important;">${data.bidder}</a> 
                    placed a new report on auction 
                    <a href="${data.auctionUrl}" class="text-primary fw-bold" style="color: #027782 !important;">${data.auction}</a>.
                </span>
                <span class="fs-6 text-muted">${timeAgo}</span>
            </div>
        `;

        document.getElementById('no-notis').style.display = 'none';
        anchor.appendChild(notificationDiv);
        notificationBox.prepend(anchor);
        notificationBox.style.display = 'block';
    });

    channel.bind('user-report', function(data) {
        console.log('Received notification:', data);

        const notificationBox = document.getElementById('notification-box');
        if (!notificationBox) {
            console.error('Notification box not found');
            return;
        }

        const anchor = document.createElement('a');
        anchor.href = data.reportUrl;
        anchor.classList.add('text-decoration-none');
        anchor.style.color = 'inherit';
        anchor.style.display = 'block';
        
        const timeAgo = getRelativeTime(data.timestamp);

        const notificationDiv = document.createElement('div');
        notificationDiv.classList.add('d-flex', 'border-bottom', 'ps-3', 'py-4', 'fs-4', 'notification-item', 'fw-bold');
        notificationDiv.innerHTML = `
            <img src="${data.imagepath}" alt="Item Image" class="rounded-4 me-3 flex-shrink-0" style="width: 50px; height: 50px;">
            <div class="d-flex flex-column">
                <span>
                    <a href="${data.reporterUrl}" class="text-primary fw-bold" style="color: #027782 !important;">${data.reporter}</a> 
                    placed a new report on user
                    <a href="${data.reportedUrl}" class="text-primary fw-bold" style="color: #027782 !important;">${data.reportedUser}</a>.
                </span>
                <span class="fs-6 text-muted">${timeAgo}</span>
            </div>
        `;

        document.getElementById('no-notis').style.display = 'none';
        anchor.appendChild(notificationDiv);
        notificationBox.prepend(anchor);
        notificationBox.style.display = 'block';
    });

    channel.bind('auction-finished', function(data) {
        console.log('Received notification:', data);

        const notificationBox = document.getElementById('notification-box');
        if (!notificationBox) {
            console.error('Notification box not found');
            return;
        }

        const anchor = document.createElement('a');
        anchor.href = data.reportUrl;
        anchor.classList.add('text-decoration-none');
        anchor.style.color = 'inherit';
        anchor.style.display = 'block';
        
        const timeAgo = getRelativeTime(data.timestamp);

        const notificationDiv = document.createElement('div');
        notificationDiv.classList.add('d-flex', 'border-bottom', 'ps-3', 'py-4', 'fs-4', 'notification-item', 'fw-bold');
        notificationDiv.innerHTML = `
            <img src="${data.imagepath}" alt="Item Image" class="rounded-4 me-3 flex-shrink-0" style="width: 50px; height: 50px;">
            <div class="d-flex flex-column">
                <span>
                    Auction
                    <a href="${data.auctionUrl}" class="text-primary fw-bold" style="color: #027782 !important;">${data.auction}</a> 
                    ${data.status === 'sold' ? 'has ended' : 'was cancelled'}.
                </span>
                <span class="fs-6 text-muted">${timeAgo}</span>
            </div>
        `;

        document.getElementById('no-notis').style.display = 'none';
        anchor.appendChild(notificationDiv);
        notificationBox.prepend(anchor);
        notificationBox.style.display = 'block';
    });

    channel.bind('bid-deleted', function(data) {
        console.log('Received notification:', data);

        const notificationBox = document.getElementById('notification-box');
        if (!notificationBox) {
            console.error('Notification box not found');
            return;
        }

        const anchor = document.createElement('a');
        anchor.href = data.reportUrl;
        anchor.classList.add('text-decoration-none');
        anchor.style.color = 'inherit';
        anchor.style.display = 'block';
        
        const timeAgo = getRelativeTime(data.timestamp);

        const notificationDiv = document.createElement('div');
        notificationDiv.classList.add('d-flex', 'border-bottom', 'ps-3', 'py-4', 'fs-4', 'notification-item', 'fw-bold');
        notificationDiv.innerHTML = `
            <img src="${data.imagepath}" alt="Item Image" class="rounded-4 me-3 flex-shrink-0" style="width: 50px; height: 50px;">
            <div class="d-flex flex-column">
                <span>
                    Your bid on auction
                    <a href="${data.auctionUrl}" class="text-primary fw-bold" style="color: #027782 !important;">${data.auction}</a> 
                    was cancelled.
                </span>
                <span class="fs-6 text-muted">${timeAgo}</span>
            </div>
        `;

        document.getElementById('no-notis').style.display = 'none';
        anchor.appendChild(notificationDiv);
        notificationBox.prepend(anchor);
        notificationBox.style.display = 'block';
    });

    channel.bind('user-rating', function(data) {
        console.log('Received notification:', data);

        const notificationBox = document.getElementById('notification-box');
        if (!notificationBox) {
            console.error('Notification box not found');
            return;
        }

        const anchor = document.createElement('a');
        anchor.href = data.bidderUrl;
        anchor.classList.add('text-decoration-none');
        anchor.style.color = 'inherit';
        anchor.style.display = 'block';
        
        const timeAgo = getRelativeTime(data.timestamp);

        const notificationDiv = document.createElement('div');
        notificationDiv.classList.add('d-flex', 'border-bottom', 'ps-3', 'py-4', 'fs-4', 'notification-item', 'fw-bold');
        notificationDiv.innerHTML = `
            <img src="${data.imagepath}" alt="Item Image" class="rounded-4 me-3 flex-shrink-0" style="width: 50px; height: 50px;">
            <div class="d-flex flex-column">
                <span>
                    User
                    <a href="${data.bidderUrl}" class="text-primary fw-bold" style="color: #027782 !important;">${data.username}</a> 
                    rated you with ${data.rating}/5! 
                </span>
                <span class="fs-6 text-muted">${timeAgo}</span>
            </div>
        `;

        document.getElementById('no-notis').style.display = 'none';
        anchor.appendChild(notificationDiv);
        notificationBox.prepend(anchor);
        notificationBox.style.display = 'block';
    });

    channel.bind('auction-wishlist', function(data) {
        console.log('Received notification:', data);

        const notificationBox = document.getElementById('notification-box');
        if (!notificationBox) {
            console.error('Notification box not found');
            return;
        }

        const anchor = document.createElement('a');
        anchor.href = data.auctionUrl;
        anchor.classList.add('text-decoration-none');
        anchor.style.color = 'inherit';
        anchor.style.display = 'block';

        const timeAgo = getRelativeTime(data.timestamp);

        const notificationDiv = document.createElement('div');
        notificationDiv.classList.add('d-flex', 'border-bottom', 'ps-3', 'py-4', 'fs-4', 'notification-item', 'fw-bold');
        notificationDiv.innerHTML = `
            <img src="${data.imagepath}" alt="Item Image" class="rounded-4 me-3 flex-shrink-0" style="width: 50px; height: 50px;">
            <div class="d-flex flex-column">
                <span>
                    <a href="${data.bidderUrl}" class="text-primary fw-bold" style="color: #027782 !important;">${data.bidder}</a> 
                    has started following your auction 
                    <a href="${data.auctionUrl}" class="text-primary fw-bold" style="color: #027782 !important;">${data.auction}</a>.
                </span>
                <span class="fs-6 text-muted">${timeAgo}</span>
            </div>
        `;

        document.getElementById('no-notis').style.display = 'none';
        anchor.appendChild(notificationDiv);
        notificationBox.prepend(anchor);
        notificationBox.style.display = 'block';
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const notificationBadge = document.getElementById('notification-badge');

    async function updateNotificationBadge() {
        try {
            const response = await fetch('/notifications/unread-count');
            if (!response.ok) throw new Error('Failed to fetch unread notifications');

            const { unreadCount } = await response.json();

            if (unreadCount > 0) {
                notificationBadge.textContent = unreadCount;
                notificationBadge.style.display = 'inline-block';
            } else {
                notificationBadge.style.display = 'none';
            }
        } catch (error) {
            console.error('Error updating notification badge:', error);
        }
    }

    updateNotificationBadge();
    setInterval(updateNotificationBadge, 60000);
});
