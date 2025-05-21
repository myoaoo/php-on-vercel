<?php
// 引入 Parsedown
require_once 'Parsedown.php';

// 读取文件夹中的所有Markdown文件
function getMarkdownFiles($dir) {
    $files = array();
    foreach (glob($dir . "*.md") as $file) {
        $files[] = $file;
    }
    return $files;
}

// 解析Markdown文件的元数据
function parseMarkdownMetadata($file) {
    $contents = file_get_contents($file);
    
    // 使用正则表达式提取YAML头部
    if (preg_match('/^---\s*(.*?)\s*---/s', $contents, $matches)) {
        $metadata = $matches[1];
        $metadataArr = parseYaml($metadata);
        
        return $metadataArr;
    }
    
    return null;
}

// 提取Markdown文件的内容部分（去除元数据）
function getMarkdownContent($file) {
    $contents = file_get_contents($file);
    
    // 使用正则表达式去除YAML头部
    return preg_replace('/^---\s*(.*?)\s*---/s', '', $contents);
}

// 简单的YAML解析（你可以使用更强大的库来解析YAML）
function parseYaml($yaml) {
    $lines = explode("\n", $yaml);
    $data = array();
    foreach ($lines as $line) {
        if (strpos($line, ':') !== false) {
            list($key, $value) = explode(':', $line, 2);
            $data[trim($key)] = trim($value);
        }
    }
    return $data;
}

// 获取所有的markdown文件
$files = getMarkdownFiles('pages/');
define('POSTS_DIR', __DIR__ . '/pages/');

// 初始化当前文章变量
$currentPost = null;
$passwordRequired = false;

// 处理点击后的显示内容
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // 检查是否有密码输入
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    foreach ($files as $file) {
        $metadata = parseMarkdownMetadata($file);
        if ($metadata && $metadata['id'] == $id) {
            // 如果存在密码并且输入密码不正确
            if (isset($metadata['password']) && $metadata['password'] !== $password) {
                $passwordRequired = true;
            } else {
                $currentPost = [
                    'metadata' => $metadata,
                    'content' => getMarkdownContent($file)
                ];
            }
            break;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Markdown 文件展示</title>
</head>
<body>
<?php include 'header.php'; ?>
    <?php if ($passwordRequired): ?>
        <main>
			<div class="post markdown-content">
				<form method='post'>
					<label for='password'>请输入密码：</label>
					<input type='password' name='password' required>
					<input type='submit' value='提交'>
				</form>
			</div>
		</main >
		<?php include 'footer.php'; ?>	
    <?php elseif ($currentPost): ?>
	<main>
		<div class="post markdown-content">
            <?php
            $Parsedown = new Parsedown();
            echo $Parsedown->text($currentPost['content']);
            ?>
		</div>
	</main>
    <!-- 页脚 -->
    <?php include 'footer.php'; ?>		
    <?php else: ?>
	<main>
		<div class="post">
        <h2>页面列表</h2>
        <ul>
            <?php
            foreach ($files as $file) {
                $metadata = parseMarkdownMetadata($file);
                if ($metadata) {
                    echo "<li><a href=\"?id=" . $metadata['id'] . "\">" . htmlspecialchars($metadata['title']) . "</a></li>";
                }
            }
            ?>
        </ul>
		</div>
	</main >
	<?php include 'footer.php'; ?>
    <?php endif; ?>
</body>
</html>