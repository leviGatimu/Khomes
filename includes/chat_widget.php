<div id="chat-bubble" onclick="toggleChatWindow()">
    <i class="fas fa-comments"></i>
    <span class="chat-badge" id="bubble-badge" style="display:none;">0</span>
</div>

<div id="chat-window">
    <div class="chat-header">
        <div style="display: flex; gap: 15px;">
            <span class="chat-tab active" onclick="switchTab('dm')">Messages</span>
            <span class="chat-tab" onclick="switchTab('ai')">🤖 AI Help</span>
        </div>
        <i class="fas fa-times" onclick="toggleChatWindow()" style="cursor: pointer;"></i>
    </div>

    <div id="tab-dm" class="chat-body active">
        <div id="dm-list-view">
            <div id="dm-contacts"></div>
        </div>
        <div id="dm-chat-view" style="display: none; height: 100%; flex-direction: column;">
            <div class="chat-view-header">
                <i class="fas fa-arrow-left" onclick="showDmList()" style="cursor: pointer; margin-right: 10px;"></i>
                <span id="chat-partner-name">User</span>
            </div>
            <div id="dm-messages" class="messages-area"></div>
            <div class="chat-input-area">
                <input type="text" id="dm-input" placeholder="Type a message...">
                <button onclick="sendDm()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>

    <div id="tab-ai" class="chat-body">
        <div id="ai-messages" class="messages-area">
            <div class="msg msg-them">Hello! 🤖 I can help you list properties or answer questions.</div>
        </div>
        <div class="chat-input-area">
            <input type="text" id="ai-input" placeholder="Ask AI anything...">
            <button onclick="sendAi()"><i class="fas fa-robot"></i></button>
        </div>
    </div>
</div>

<style>
    /* --- FIXED POSITIONING (Bottom Right) --- */
    #chat-bubble {
        position: fixed !important; 
        bottom: 30px !important; 
        right: 30px !important;      /* Force Right */
        left: auto !important;       /* Reset Left */
        
        width: 60px; height: 60px;
        background: #27AE60; color: white;
        border-radius: 50%;
        display: flex; justify-content: center; align-items: center;
        font-size: 24px; cursor: pointer;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        z-index: 999999; /* Higher than footer */
        transition: transform 0.3s;
        position: relative;
    }
    #chat-bubble:hover { transform: scale(1.1); }
    
    .chat-badge {
        position: absolute; top: -5px; right: -5px;
        background: #e74c3c; color: white;
        font-size: 12px; font-weight: bold;
        padding: 4px 8px; border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    #chat-window {
        display: none;
        position: fixed !important; 
        bottom: 100px !important; 
        right: 30px !important;      /* Force Right */
        left: auto !important;       /* Reset Left */
        
        width: 350px; height: 500px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        z-index: 999999;
        overflow: hidden;
        flex-direction: column;
    }

    /* Keep existing styles... */
    .chat-header { background: #2c3e50; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
    .chat-tab { cursor: pointer; opacity: 0.6; font-weight: bold; border-bottom: 2px solid transparent; padding-bottom: 3px; }
    .chat-tab.active { opacity: 1; border-color: #27AE60; }
    .chat-body { display: none; height: calc(100% - 60px); flex-direction: column; }
    .chat-body.active { display: flex; }
    .messages-area { flex-grow: 1; padding: 15px; overflow-y: auto; background: #f9f9f9; display: flex; flex-direction: column; gap: 10px; }
    .chat-input-area { padding: 10px; background: white; border-top: 1px solid #eee; display: flex; gap: 10px; }
    .chat-input-area input { flex-grow: 1; padding: 10px; border-radius: 20px; border: 1px solid #ddd; outline: none; }
    .chat-input-area button { background: #27AE60; color: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; }
    .msg { max-width: 80%; padding: 10px 15px; border-radius: 15px; font-size: 0.9rem; line-height: 1.4; }
    .msg-me { align-self: flex-end; background: #27AE60; color: white; border-bottom-right-radius: 2px; }
    .msg-them { align-self: flex-start; background: #ecf0f1; color: #2c3e50; border-bottom-left-radius: 2px; }
    .contact-item { display: flex; align-items: center; gap: 10px; padding: 15px; border-bottom: 1px solid #eee; cursor: pointer; transition: 0.2s; }
    .contact-item:hover { background: #f5f5f5; }
    .contact-img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
    .role-badge { font-size: 0.7rem; padding: 2px 6px; border-radius: 4px; color: white; margin-left: 5px; }
    .role-admin { background: #2c3e50; }
    .role-host { background: #27AE60; }
    .role-guest { background: #95a5a6; }
    .chat-view-header { padding: 10px; background: #ecf0f1; border-bottom: 1px solid #ddd; font-weight: bold; display: flex; align-items: center; }
</style>

<script>
    let currentPartnerId = null;
    let chatInterval = null;
    let badgeInterval = null;

    // --- POLL FOR BADGE COUNT EVERY 3 SECONDS ---
    function checkUnreadBadge() {
        fetch('api_chat.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=check_unread' })
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById('bubble-badge');
            if(data.count > 0) {
                badge.style.display = 'block';
                badge.innerText = data.count;
            } else {
                badge.style.display = 'none';
            }
        });
    }

    // Start Badge Polling
    if(!window.badgePollingStarted) {
        setInterval(checkUnreadBadge, 3000);
        checkUnreadBadge(); 
        window.badgePollingStarted = true;
    }

    function toggleChatWindow() {
        const win = document.getElementById('chat-window');
        if(win.style.display === 'flex') {
            win.style.display = 'none';
        } else {
            win.style.display = 'flex';
            loadContacts();
        }
    }

    function startChatWith(userId, userName) {
        const win = document.getElementById('chat-window');
        win.style.display = 'flex';
        switchTab('dm');
        openDm(userId, userName);
    }

    function switchTab(tab) {
        document.querySelectorAll('.chat-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.chat-body').forEach(b => b.classList.remove('active'));
        if(event) event.target.classList.add('active'); 
        document.getElementById('tab-' + tab).classList.add('active');
    }

    function loadContacts() {
        fetch('api_chat.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=get_list' })
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById('dm-contacts');
            list.innerHTML = '';
            if(data.data.length === 0) { list.innerHTML = '<div style="padding:20px; text-align:center; color:#999;">No conversations yet.</div>'; return; }
            data.data.forEach(u => {
                let roleClass = 'role-guest';
                if(u.user_role === 'admin') roleClass = 'role-admin';
                if(u.user_role === 'host') roleClass = 'role-host';
                
                let unreadHtml = '';
                if(u.unread > 0) unreadHtml = `<span style="background:#e74c3c; width:10px; height:10px; border-radius:50%; display:inline-block; margin-left:auto;"></span>`;

                list.innerHTML += `<div class="contact-item" onclick="openDm(${u.user_id}, '${u.full_name}')"><img src="${u.profile_image}" class="contact-img"><div><div style="font-weight:600;">${u.full_name} <span class="role-badge ${roleClass}">${u.user_role}</span></div><div style="font-size:0.8rem; color:#888;">${u.last_msg || 'Start chatting...'}</div></div> ${unreadHtml}</div>`;
            });
        });
    }

    function openDm(id, name) {
        currentPartnerId = id;
        document.getElementById('dm-list-view').style.display = 'none';
        document.getElementById('dm-chat-view').style.display = 'flex';
        document.getElementById('chat-partner-name').innerText = name;
        loadMessages();
        if(chatInterval) clearInterval(chatInterval);
        chatInterval = setInterval(loadMessages, 3000);
    }

    function showDmList() {
        document.getElementById('dm-list-view').style.display = 'block';
        document.getElementById('dm-chat-view').style.display = 'none';
        clearInterval(chatInterval);
        loadContacts();
        checkUnreadBadge(); 
    }

    function loadMessages() {
        if(!currentPartnerId) return;
        fetch('api_chat.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: `action=get_msgs&partner_id=${currentPartnerId}` })
        .then(res => res.json())
        .then(data => {
            const area = document.getElementById('dm-messages');
            area.innerHTML = '';
            data.data.forEach(m => {
                let cls = (m.type === 'me') ? 'msg-me' : 'msg-them';
                area.innerHTML += `<div class="msg ${cls}">${m.message}</div>`;
            });
            area.scrollTop = area.scrollHeight;
        });
    }

    function sendDm() {
        const inp = document.getElementById('dm-input');
        const txt = inp.value.trim();
        if(!txt || !currentPartnerId) return;
        fetch('api_chat.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: `action=send_msg&receiver_id=${currentPartnerId}&message=${txt}` })
        .then(() => { inp.value = ''; loadMessages(); });
    }

    function sendAi() {
        const inp = document.getElementById('ai-input');
        const txt = inp.value.trim();
        if(!txt) return;
        const area = document.getElementById('ai-messages');
        area.innerHTML += `<div class="msg msg-me">${txt}</div>`;
        inp.value = '';
        area.scrollTop = area.scrollHeight;
        setTimeout(() => {
            fetch('api_chat.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: `action=ai_chat&message=${txt}` })
            .then(res => res.json())
            .then(data => { area.innerHTML += `<div class="msg msg-them">${data.reply}</div>`; area.scrollTop = area.scrollHeight; });
        }, 500);
    }
</script>