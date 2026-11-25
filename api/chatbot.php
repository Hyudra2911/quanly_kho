<?php
require_once '../config/config.php';
checkLogin();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$raw_body = file_get_contents('php://input');
$payload = json_decode($raw_body, true);
$message = trim($payload['message'] ?? '');

if ($message === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Tin nhắn không được để trống']);
    exit();
}

if (strlen($message) > 1000) {
    http_response_code(400);
    echo json_encode(['error' => 'Tin nhắn quá dài']);
    exit();
}

try {
    $request_payload = [
        'model' => GROQ_MODEL,
        'messages' => [
            [
                'role' => 'system',
                'content' => 'Bạn là trợ lý cho hệ thống quản lý kho. Trả lời ngắn gọn bằng tiếng Việt, tập trung hỗ trợ thao tác trong hệ thống kho, báo cáo, nhập xuất, tồn kho.'
            ],
            [
                'role' => 'user',
                'content' => $message
            ]
        ],
        'temperature' => 0.4,
        'max_tokens' => 300
    ];

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . GROQ_API_KEY,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($request_payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 10
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($response === false || $curl_error) {
        logSystemError('Groq request failed', ['curl_error' => $curl_error]);
        http_response_code(502);
        echo json_encode(['error' => 'Không thể kết nối Groq API']);
        exit();
    }

    $response_data = json_decode($response, true);

    if ($http_code >= 400 || !isset($response_data['choices'][0]['message']['content'])) {
        logSystemError('Groq invalid response', ['http_code' => $http_code, 'response' => $response_data]);
        http_response_code(502);
        echo json_encode(['error' => 'Groq API trả về dữ liệu không hợp lệ']);
        exit();
    }

    $reply = trim($response_data['choices'][0]['message']['content']);
    echo json_encode(['reply' => $reply]);
} catch (Exception $e) {
    logSystemError('Chatbot API exception', ['error' => $e->getMessage()]);
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi hệ thống khi xử lý chatbot']);
}
