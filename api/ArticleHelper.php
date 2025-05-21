<?php
// ArticleHelper.php - 优化版文章助手
// 在任何页面调用文章列表

class ArticleHelper {
    public static function showArticleList($limit = null, $style = 'card') {
        require_once 'Parsedown.php';
        $parsedown = new Parsedown();
        
        $files = glob('yinjis/*.md');
        $articles = [];
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/^---\s*(.*?)\s*---\s*(.*)/s', $content, $matches)) {
                $meta = [];
                foreach (explode("\n", $matches[1]) as $line) {
                    if (preg_match('/^(\w+):\s*(.*)/', trim($line), $m)) {
                        $meta[$m[1]] = $m[2];
                    }
                }
                
                $articles[] = [
                    'id' => $meta['id'] ?? null,
                    'title' => $meta['title'] ?? basename($file, '.md'),
                    'img' => $meta['img'] ?? null,
                    'url' => 'yinji.php?id='.($meta['id'] ?? '')
                ];
            }
        }
        
        // 限制显示数量
        if ($limit) {
            $articles = array_slice($articles, 0, $limit);
        }
        
        // 引入外部CSS
        self::includeStyles();
        
        // 渲染不同样式
        if ($style === 'card') {
            echo '<div class="grid bb">';
            foreach ($articles as $article) {
                echo self::getCardHtml($article);
            }
            echo '</div>';
        } else {
            echo '<ul class="article-list-simple">';
            foreach ($articles as $article) {
                echo self::getListItemHtml($article);
            }
            echo '</ul>';
        }
    }
    
    private static function includeStyles() {
        static $stylesIncluded = false;
        if (!$stylesIncluded) {
            echo '<link rel="stylesheet" href="article_styles.css">';
            $stylesIncluded = true;
        }
    }
    
	private static function getCardHtml($article) {
		return <<<HTML
		<div class="post-list">
			<div class="up">				
				<img src="{$article['img']}" alt="{$article['title']}" onerror="this.src='/public/images/cover.jpg'">
			</div>
			<div class="down">
				<a href="{$article['url']}" class="article-card">
					<h3>{$article['title']}</h3>
				</a>
			</div>
		</div>
		HTML;
	}
    
    private static function getListItemHtml($article) {
        return <<<HTML
        <li>
            <a href="{$article['url']}">{$article['title']}</a>
        </li>
        HTML;
    }

    public function getArticleById($id) {
        $files = glob('yinjis/*.md');
        require_once 'Parsedown.php';
        $parsedown = new Parsedown();
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/^---\s*(.*?)\s*---\s*(.*)/s', $content, $matches)) {
                $meta = [];
                foreach (explode("\n", $matches[1]) as $line) {
                    if (preg_match('/^(\w+):\s*(.*)/', trim($line), $m)) {
                        $meta[$m[1]] = $m[2];
                    }
                }
                
                if ($meta['id'] == $id) {
                    return [
                        'id' => $meta['id'],
                        'title' => $meta['title'] ?? basename($file, '.md'),
                        'img' => $meta['img'] ?? null,
                        'content' => $parsedown->text($matches[2])
                    ];
                }
            }
        }
        
        return null;
    }

    public function getAllArticles() {
        $files = glob('yinjis/*.md');
        require_once 'Parsedown.php';
        $parsedown = new Parsedown();
        $articles = [];
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/^---\s*(.*?)\s*---\s*(.*)/s', $content, $matches)) {
                $meta = [];
                foreach (explode("\n", $matches[1]) as $line) {
                    if (preg_match('/^(\w+):\s*(.*)/', trim($line), $m)) {
                        $meta[$m[1]] = $m[2];
                    }
                }
                
                $articles[] = [
                    'id' => $meta['id'] ?? null,
                    'title' => $meta['title'] ?? basename($file, '.md'),
                    'img' => $meta['img'] ?? null,
                    'url' => 'yinji.php?id='.($meta['id'] ?? ''),
                    'content' => $parsedown->text($matches[2])
                ];
            }
        }
        
        return $articles;
    }

    public function renderArticleList($articles) {
        $output = '<div class="grid aa">';
        foreach ($articles as $article) {
            $output .= self::getCardHtml($article);
        }
        $output .= '</div>';
        
        return $output;
    }
}
?>