<?php
// 博客配置
define('POSTS_DIR', __DIR__ . '/source/_posts/');

// 获取所有 Markdown 文件
$files = glob(POSTS_DIR . '/*.md');

// 生成文章ID映射表 (ID => 文件路径)
$postIdMap = [];
foreach ($files as $index => $file) {
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    
    $meta = [];
    // 解析 YAML 元数据
    if (isset($lines[0]) && trim($lines[0]) === '---') {
        for ($i = 1; $i < count($lines); $i++) {
            if (trim($lines[$i]) === '---') break;
            $line = trim($lines[$i]);
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $meta[trim($key)] = trim($value);
            }
        }
    }
    
    // 使用文章中的ID，如果没有则使用索引+1
    $id = isset($meta['id']) ? (int)$meta['id'] : ($index + 1);
    $postIdMap[$id] = $file;
}

// 获取请求的文章ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 检查ID是否存在
if (!isset($postIdMap[$id])) {
    http_response_code(404);
    include '404.php';
    exit;
}

// 获取文章文件路径
$filePath = $postIdMap[$id];

// 解析 Markdown 文件
function parseMarkdownFile($filePath) {
    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);
    
    $meta = [];
    $bodyStart = 0;
    
    // 解析 YAML 元数据
    if (isset($lines[0]) && trim($lines[0]) === '---') {
        for ($i = 1; $i < count($lines); $i++) {
            if (trim($lines[$i]) === '---') {
                $bodyStart = $i + 1;
                break;
            }
            $line = trim($lines[$i]);
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $meta[trim($key)] = trim($value);
            }
        }
    }
    
    // 获取文章主体内容
    $body = implode("\n", array_slice($lines, $bodyStart));
    
    return [
        'meta' => $meta,
        'body' => $body,
        'file' => basename($filePath),
        'mtime' => filemtime($filePath)
    ];
}

$post = parseMarkdownFile($filePath);

// 检查密码保护
// 检查密码保护
$isProtected = isset($post['meta']['password']);
$passwordCorrect = false;

if ($isProtected) {
    // 配置会话有效期为45分钟（2700秒）
    $lifetime = 7200; // 45分钟
    
    // 在会话启动前设置有效期配置
    ini_set('session.gc_maxlifetime', $lifetime);
    session_set_cookie_params($lifetime);
    
    // 启动会话
    session_start();
    
    $sessionKey = 'post_password_' . $id;
    
    // 检查会话是否过期
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $lifetime)) {
        session_unset();
        session_destroy();
        session_start();
    }
    
    // 更新最后活动时间
    $_SESSION['last_activity'] = time();
    
    // 检查是否有会话中保存的密码
    if (isset($_SESSION[$sessionKey]) && $_SESSION[$sessionKey] === $post['meta']['password']) {
        $passwordCorrect = true;
    }
    
    // 检查是否有表单提交的密码
    if (isset($_POST['password'])) {
        if ($_POST['password'] === $post['meta']['password']) {
            $_SESSION[$sessionKey] = $post['meta']['password'];
            $_SESSION['last_activity'] = time(); // 重置活动时间
            $passwordCorrect = true;
        } else {
            $errorMessage = "密码错误，请重试";
        }
    }
} else {
    $passwordCorrect = true;
}

// 引入 Parsedown 类
require __DIR__ . '/Parsedown.php';
$parsedown = new Parsedown();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($post['meta']['title'] ?? basename($fileName, '.md')); ?></title>
    <meta charset="UTF-8">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#165DFF',
                        secondary: '#69b1ff',
                        neutral: '#f5f7fa',
                        dark: '#1d2129',
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            .content-auto {
                content-visibility: auto;
            }
            .markdown-content h1 {
                @apply text-3xl font-bold mt-8 mb-4;
            }
            .markdown-content h2 {
                @apply text-2xl font-bold mt-6 mb-3;
            }
            .markdown-content h3 {
                @apply text-xl font-bold mt-5 mb-2;
            }
            .markdown-content p {
                @apply mb-4 leading-relaxed text-gray-700;
            }
            .markdown-content a {
                @apply text-primary hover:underline;
            }
            .markdown-content ul {
                @apply list-disc pl-5 mb-4;
            }
            .markdown-content ol {
                @apply list-decimal pl-5 mb-4;
            }
            .markdown-content img {
                @apply max-w-full rounded-lg my-6 shadow-md;
            }
            .markdown-content blockquote {
                @apply border-l-4 border-primary pl-4 italic my-4 text-gray-600;
            }
            .markdown-content code {
                @apply bg-gray-100 px-2 py-1 rounded text-sm font-mono;
            }
            .markdown-content pre {
                @apply bg-gray-800 text-white p-4 rounded my-4 overflow-x-auto;
            }
            .markdown-content pre code {
                @apply bg-transparent p-0;
            }
        }
    </style>
</head>
<body class="bg-neutral text-dark min-h-screen flex flex-col">
    <?php include 'header.php'; ?>

    <!-- 主内容区 -->
    <main class="flex-grow container mx-auto px-4 py-8">
        <?php if ($isProtected && !$passwordCorrect): ?>
            <!-- 密码输入表单 -->
            <div class="max-w-md mx-auto bg-white rounded-xl shadow-sm p-6 md:p-8">
                <div class="text-center mb-6">
                    <i class="fa fa-lock text-4xl text-primary mb-3"></i>
                    <h2 class="text-xl font-bold">此文章需要密码</h2>
                    <p class="text-gray-500 mt-2">请输入密码查看内容</p>
                </div>
                
                <?php if (isset($errorMessage)): ?>
                    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4">
                        <i class="fa fa-exclamation-circle mr-2"></i> <?php echo $errorMessage; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="space-y-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">文章密码</label>
                        <input type="password" id="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all" placeholder="请输入密码...">
                    </div>
                    <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded-lg hover:bg-primary/90 transition-colors duration-200 flex items-center justify-center">
                        <i class="fa fa-unlock-alt mr-2"></i> 解锁文章
                    </button>
                </form>
            </div>
        <?php else: ?>
            <!-- 文章内容 -->
            <article class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm overflow-hidden">
                <?php if (isset($post['meta']['cover'])): ?>
                    <div class="relative">
                        <img src="<?php echo htmlspecialchars($post['meta']['cover']); ?>" alt="<?php echo htmlspecialchars($post['meta']['title']); ?>" class="w-full h-64 md:h-80 object-cover">
                        <?php if ($isProtected): ?>
                            <div class="absolute top-3 right-3 bg-dark/70 text-white text-sm px-3 py-1 rounded-full backdrop-blur-sm">
                                <i class="fa fa-lock mr-1"></i> 加密
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="p-6 md:p-8">
                    <div class="text-sm text-primary font-medium mb-2">#<?php echo $id; ?></div>
                    <h1 class="text-[clamp(1.5rem,3vw,2.25rem)] font-bold mb-3">
                        <?php echo htmlspecialchars($post['meta']['title'] ?? basename($fileName, '.md')); ?>
                    </h1>
                    
                    <div class="flex items-center text-sm text-gray-500 mb-6">
                        <div class="flex items-center mr-4">
                            <i class="fa fa-calendar-o mr-1"></i>
                            <span><?php echo isset($post['meta']['date']) ? $post['meta']['date'] : date('Y-m-d', $post['mtime']); ?></span>
                        </div>
                        <div class="flex items-center">
                            <i class="fa fa-file-text-o mr-1"></i>
                            <span><?php echo round(str_word_count(strip_tags($post['body'])) / 200, 1); ?> 分钟阅读</span>
                        </div>
                    </div>
                    
                    <div class="markdown-content">
                        <?php echo $parsedown->text($post['body']); ?>
                    </div>
                    
                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <a href="post.php" class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors duration-200">
                            <i class="fa fa-arrow-left mr-2"></i> 返回博客列表
                        </a>
                    </div>
                </div>
            </article>
        <?php endif; ?>
    </main>

    <!-- 页脚 -->
    <footer class="bg-dark text-white py-8">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <p>&copy; <?php echo date('Y'); ?> My Markdown Blog. All rights reserved.</p>
                <div class="mt-4 flex justify-center space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">
                        <i class="fa fa-github text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">
                        <i class="fa fa-twitter text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">
                        <i class="fa fa-envelope text-xl"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // 导航栏滚动效果
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('py-2');
                header.classList.remove('py-3');
            } else {
                header.classList.add('py-3');
                header.classList.remove('py-2');
            }
        });
    </script>
</body>
</html>  