<?php
// 博客配置
define('POSTS_DIR', __DIR__ . '/posts/');

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
  
</head>
<body class="bg-neutral text-dark min-h-screen flex flex-col">
    <?php include 'header.php'; ?>

    <!-- 主内容区 -->
    <main>
        <?php if ($isProtected && !$passwordCorrect): ?>
            <!-- 密码输入表单 -->
            <div class="post">               
                <?php if (isset($errorMessage)): ?>
                    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4">
                        <i class="fa fa-exclamation-circle mr-2"></i> <?php echo $errorMessage; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="space-y-4">
                    <div>
                        <label for="password" >文章密码</label>
                        <input type="password" id="password" name="password"  placeholder="请输入密码...">
                    </div>
                    <button type="submit"> 提交</button>
                </form>
            </div>
        <?php else: ?>
            <!-- 文章内容 -->
            <article class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm overflow-hidden">

                <div class="post">

                    
                    <div class="flex">
						<h1><?php echo htmlspecialchars($post['meta']['title'] ?? basename($fileName, '.md')); ?></h1>
							<div>
							<div>#<?php echo $id; ?></div>
							<div>
								<i class="fas fa-calendar-alt"></i>
								<span><?php echo isset($post['meta']['date']) ? $post['meta']['date'] : date('Y-m-d', $post['mtime']); ?></span>
							</div>
							<div>
								<i class="fas fa-eye"></i>
								<span><?php echo round(str_word_count(strip_tags($post['body'])) / 200, 1); ?> 分钟阅读</span>
							</div>
						</div>
						
                    </div>
                    
                    <div class="markdown-content">
                        <?php echo $parsedown->text($post['body']); ?>
                    </div>
                    
                    <div class="reply">
                        <a href="post.php" >
                            <i class="fas fa-reply"></i> 返回博客列表
                        </a>
                    </div>
                </div>
            </article>
        <?php endif; ?>
    </main>

    <!-- 页脚 -->
    	<?php include 'footer.php'; ?>

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