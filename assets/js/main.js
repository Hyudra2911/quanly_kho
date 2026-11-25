// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileSidebar = document.getElementById('mobileSidebar');
    const closeMobileMenu = document.getElementById('closeMobileMenu');
    const mobileSidebarOverlay = document.getElementById('mobileSidebarOverlay');
    
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            if (mobileSidebar) {
                mobileSidebar.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    }
    
    function closeMenu() {
        if (mobileSidebar) {
            mobileSidebar.classList.remove('active');
            document.body.style.overflow = '';
        }
    }
    
    if (closeMobileMenu) {
        closeMobileMenu.addEventListener('click', closeMenu);
    }
    
    if (mobileSidebarOverlay) {
        mobileSidebarOverlay.addEventListener('click', closeMenu);
    }
    
    // Mobile dropdown menu toggle
    const mobileMenuToggles = document.querySelectorAll('.mobile-menu-toggle');
    mobileMenuToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.closest('.mobile-menu-dropdown');
            if (parent) {
                parent.classList.toggle('active');
            }
        });
    });
    
    // Close menu when clicking on a link
    const mobileMenuLinks = document.querySelectorAll('.mobile-menu-list a[href]');
    mobileMenuLinks.forEach(function(link) {
        if (!link.classList.contains('mobile-menu-toggle')) {
            link.addEventListener('click', function() {
                setTimeout(closeMenu, 300);
            });
        }
    });
    
    // Delete confirmation
    document.querySelectorAll('.btn-delete').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            if (!confirm('Bạn có chắc chắn muốn xóa?')) {
                e.preventDefault();
            }
        });
    });

    const chatbotToggle = document.getElementById('chatbotToggle');
    const chatbotPanel = document.getElementById('chatbotPanel');
    const chatbotClose = document.getElementById('chatbotClose');
    const chatbotForm = document.getElementById('chatbotForm');
    const chatbotInput = document.getElementById('chatbotInput');
    const chatbotMessages = document.getElementById('chatbotMessages');
    const chatbotWidget = document.getElementById('chatbotWidget');

    function addChatMessage(text, role) {
        if (!chatbotMessages) return;
        const bubble = document.createElement('div');
        bubble.className = 'chatbot-msg ' + role;
        bubble.textContent = text;
        chatbotMessages.appendChild(bubble);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    function getFallbackReply(message) {
        const text = message.toLowerCase();

        if (text.includes('nhập kho') || text.includes('phiếu nhập')) {
            return 'Bạn vào menu Nhập Xuất > Phiếu nhập kho để tạo hoặc duyệt phiếu nhập.';
        }
        if (text.includes('xuất kho') || text.includes('phiếu xuất')) {
            return 'Bạn vào menu Nhập Xuất > Phiếu xuất kho để tạo, duyệt hoặc in phiếu xuất.';
        }
        if (text.includes('tồn kho') || text.includes('số lượng')) {
            return 'Bạn vào menu Tồn Kho để xem số lượng theo kho, vị trí và cảnh báo tồn thấp.';
        }
        if (text.includes('báo cáo') || text.includes('doanh thu')) {
            return 'Bạn vào menu Báo Cáo để xem báo cáo nhập kho, xuất kho, doanh thu và xuất Excel/PDF.';
        }
        if (text.includes('quên mật khẩu') || text.includes('đổi mật khẩu')) {
            return 'Bạn có thể dùng trang Quên mật khẩu để xác thực email và đổi mật khẩu nhanh.';
        }

        return 'Mình chưa hiểu rõ câu hỏi. Bạn thử hỏi theo từ khóa như: nhập kho, xuất kho, tồn kho, báo cáo, doanh thu.';
    }

    async function getBotReply(message) {
        try {
            const baseUrl = window.APP_BASE_URL || '/';
            const apiUrl = baseUrl.replace(/\/$/, '') + '/api/chatbot.php';

            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: message })
            });

            const data = await response.json();
            if (!response.ok || !data.reply) {
                return getFallbackReply(message);
            }

            return data.reply;
        } catch (error) {
            return getFallbackReply(message);
        }
    }

    function openChatbot() {
        if (!chatbotPanel) return;
        chatbotPanel.classList.add('open');
        chatbotPanel.setAttribute('aria-hidden', 'false');
        if (chatbotInput) chatbotInput.focus();
    }

    function closeChatbot() {
        if (!chatbotPanel) return;
        chatbotPanel.classList.remove('open');
        chatbotPanel.setAttribute('aria-hidden', 'true');
    }

    if (chatbotToggle) {
        chatbotToggle.addEventListener('click', function() {
            if (chatbotPanel && chatbotPanel.classList.contains('open')) {
                closeChatbot();
            } else {
                openChatbot();
            }
        });
    }

    if (chatbotClose) {
        chatbotClose.addEventListener('click', closeChatbot);
    }

    if (chatbotWidget && chatbotToggle) {
        let isDragging = false;
        let dragMoved = false;
        let startX = 0;
        let startY = 0;
        let startLeft = 0;
        let startTop = 0;

        function startDrag(clientX, clientY) {
            const rect = chatbotWidget.getBoundingClientRect();
            chatbotWidget.style.left = rect.left + 'px';
            chatbotWidget.style.top = rect.top + 'px';
            chatbotWidget.style.right = 'auto';
            chatbotWidget.style.bottom = 'auto';

            startX = clientX;
            startY = clientY;
            startLeft = rect.left;
            startTop = rect.top;
            isDragging = true;
            dragMoved = false;
        }

        function moveDrag(clientX, clientY) {
            if (!isDragging) return;

            const deltaX = clientX - startX;
            const deltaY = clientY - startY;
            if (Math.abs(deltaX) > 3 || Math.abs(deltaY) > 3) {
                dragMoved = true;
            }

            const maxLeft = window.innerWidth - chatbotWidget.offsetWidth;
            const maxTop = window.innerHeight - chatbotWidget.offsetHeight;
            const nextLeft = Math.min(Math.max(0, startLeft + deltaX), Math.max(0, maxLeft));
            const nextTop = Math.min(Math.max(0, startTop + deltaY), Math.max(0, maxTop));

            chatbotWidget.style.left = nextLeft + 'px';
            chatbotWidget.style.top = nextTop + 'px';
        }

        function endDrag() {
            if (!isDragging) return;
            isDragging = false;
            if (dragMoved) {
                chatbotToggle.dataset.dragged = '1';
                setTimeout(function() {
                    chatbotToggle.dataset.dragged = '';
                }, 0);
            }
        }

        chatbotToggle.addEventListener('mousedown', function(e) {
            e.preventDefault();
            startDrag(e.clientX, e.clientY);
        });

        document.addEventListener('mousemove', function(e) {
            moveDrag(e.clientX, e.clientY);
        });

        document.addEventListener('mouseup', function() {
            endDrag();
        });

        chatbotToggle.addEventListener('touchstart', function(e) {
            const touch = e.touches[0];
            if (!touch) return;
            startDrag(touch.clientX, touch.clientY);
        }, { passive: true });

        document.addEventListener('touchmove', function(e) {
            const touch = e.touches[0];
            if (!touch) return;
            moveDrag(touch.clientX, touch.clientY);
        }, { passive: true });

        document.addEventListener('touchend', function() {
            endDrag();
        });

        chatbotToggle.addEventListener('click', function(e) {
            if (chatbotToggle.dataset.dragged === '1') {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);
    }

    if (chatbotForm && chatbotInput) {
        chatbotForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const message = chatbotInput.value.trim();
            if (!message) return;

            addChatMessage(message, 'user');
            chatbotInput.value = '';
            chatbotInput.disabled = true;

            addChatMessage('Đang xử lý...', 'bot');
            const loadingBubble = chatbotMessages ? chatbotMessages.lastElementChild : null;

            const reply = await getBotReply(message);

            if (loadingBubble && loadingBubble.parentNode) {
                loadingBubble.parentNode.removeChild(loadingBubble);
            }

            addChatMessage(reply, 'bot');
            chatbotInput.disabled = false;
            chatbotInput.focus();
        });
    }
});
