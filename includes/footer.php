    <footer class="mt-5 py-4 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Hệ thống Quản Lý Kho Hàng. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <?php if (isset($_SESSION['user_id'])): ?>
    <div id="chatbotWidget" class="chatbot-widget">
        <button type="button" id="chatbotToggle" class="chatbot-toggle" aria-label="Mở chatbot">
            <i class="bi bi-chat-dots-fill"></i>
        </button>

        <div id="chatbotPanel" class="chatbot-panel" aria-hidden="true">
            <div class="chatbot-header">
                <div>
                    <strong>Trợ lý kho</strong>
                    <div class="small">Hỗ trợ nhanh trong hệ thống</div>
                </div>
                <button type="button" id="chatbotClose" class="chatbot-close" aria-label="Đóng chatbot">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div id="chatbotMessages" class="chatbot-messages">
                <div class="chatbot-msg bot">
                    Xin chào! Tôi có thể giúp bạn tìm nhanh các chức năng như nhập kho, xuất kho, báo cáo, hàng sắp hết hạn.
                </div>
            </div>
            <form id="chatbotForm" class="chatbot-input-wrap">
                <input type="text" id="chatbotInput" class="chatbot-input" placeholder="Nhập câu hỏi..." autocomplete="off">
                <button type="submit" class="chatbot-send" aria-label="Gửi">
                    <i class="bi bi-send-fill"></i>
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        window.APP_BASE_URL = <?php echo json_encode(BASE_URL); ?>;
    </script>
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
    <?php if (isset($extra_scripts)) echo $extra_scripts; ?>
</body>
</html>
