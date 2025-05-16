<?php


// 获取所有 Markdown 文件并按修改时间排序
$files = glob(POSTS_DIR . '/*.md');
usort($files, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

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
?>
<?php
// 获取网站根目录的 URL 路径（适用于 Apache/Nginx）
$baseUrl = rtrim(str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__), '/');
?>
<link rel="stylesheet" href="/style.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>   
:root {
  --oaoo-1: #f7f9fe; /* 主颜色1 #f7f9fe background-color: var(--oaoo-1, #f7f9fe);*/
  --oaoo-2: #e3e8f7; /* 边框颜色#e3e8f7 */
  --oaoo-3: #f0f4ff; /* 颜色 3 页脚*/ 
  --oaoo-4: #00bbee;  /* 颜色 4 蓝色 蓝色 */
}

* {
	margin: 0;
	padding: 0;
	
}

body {
background-color: var(--oaoo-1, #f7f9fe);}
ul {
	list-style: none;
}
a {
	text-decoration: none;
	color: #1e395b;
}
header {
    width: 100%;
    background-color: #ffffff30;
    top: 0;
    backdrop-filter: blur(10px);
    box-shadow: 0 5px 6px -5px rgba(133, 133, 133, 0.6);
    z-index: 100;
}
.nav {
    width: 80%;
    height: 60px;
    display: flex;
    margin: 0 auto;
    align-items: center;
    justify-content: space-between;
}
.nav > a,
li > ul {
	display: none;
}

li.active > ul {
	display: block;
	background-color: #fff;
}

.menu-container > ul > li.active > ul {
	border: 1px solid var(--oaoo-2, #f7f9fe);
}

@media (min-width: 769px) {
	.menu-container{
		position: relative;
		width: 100%;
	}
	.menu-container > ul {
		display: flex;
		position: absolute;
		top:  -10px;
		right: 0;
		gap: 20px;
		text-align: right;
	}
	.has-children.active ul li  {
		text-align: left;
	}
	.menu-container > ul > li {
		position: relative;
	}
	.menu-container > ul > li > ul {
		padding:10px;
		position: absolute;
		right: 0;
		width: 120px;
	}
}	
@media (max-width: 768px) {
	.menu-container  {
		position: fixed;
		top: 60px;
		left: -150px;
		height: calc(100vh - 80px);
		margin: 0;
		padding: 0;
		list-style-type: none;
		width: 130px;
		padding: 10px;
		transition: left 0.3s ease; 
	}
	.sidebar-on .menu-container  {
		left:0;
		background-color: #fff;
	}
	.nav > a {
		display: block;
		font-size: 1.3em;
        font-weight: bold;
	}
	.menu-container > ul > li.active > ul {
		border: 0px solid var(--oaoo-2, #f7f9fe);
	}	
}

.has-children div span {
    display: inline-block;
    width: 6px;
    height: 6px;
    border-top: 1px solid #34495e;
    border-right: 1px solid #34495e;
    margin-left: 5px;
    transition: transform 0.3s ease;
    transform: rotate(45deg);
}
.has-children.active > div span {
	transform: rotate(135deg);
}
.has-children div {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

li.active > ul {border-radius: 8px;}



ul > li { padding: 5px 0;}

.nav img {    height: 25px;}
</style>  


 <header id="menuContainer">
	<div class="nav" >
	<div><a href="/" ><img src="/images/logo.svg" alt="Logo" class="logo"></a></div >
	<div class="menu-container sidebar"></div>
	<a href="javascript:void(0);" onclick="toggleSidebar()">☰</a>

	</div>
</header>
  <script>
    function toggleSidebar() {
      document.body.classList.toggle('sidebar-on');
    }

    // 点击 `.sidebar` 区域外的地方，移除类
    document.addEventListener('click', function(event) {
      const sidebar = document.querySelector('.sidebar');
      if (!sidebar.contains(event.target) && !event.target.matches('a')) {
        document.body.classList.remove('sidebar-on');
      }
    });
  </script>
<script>
	// 使用缩进格式定义菜单结构
	const menuData = `
首页
	menu 1-1
		menu 1-1-1,125.html
		menu 1-1-2,126.html
		menu 1-1-3,127.html
	menu 1-2
		menu 1-2-1
		menu 1-2-2
		menu 1-2-3
	menu 1-3
		menu 1-3-1
		menu 1-3-2
		menu 1-3-3
文章,/post.php		
关于
	2
	2
	2
产品手册
	2
	2
	2
1
	2
	2
	2
1
	2
		3
		3
		3
	2
		3
		3
		3
	2
		3
			4
				5
				5
				5
			4
			4
		3
		3
`;

	// 解析缩进格式的菜单数据
	function parseMenuData(data) {
		const lines = data.split('\n').filter(line => line.trim() !== '');
		const root = { children: [] };
		const stack = [{ level: -1, node: root }];
		
		lines.forEach(line => {
			const level = line.search(/\S|$/); // 计算缩进级别
			const lineContent = line.trim();
			
			// 分割文本和链接
			let text, href = "#";
			if (lineContent.includes(',')) {
				const parts = lineContent.split(',');
				text = parts[0].trim();
				href = parts[1].trim();
			} else {
				text = lineContent;
			}
			
			// 创建新节点
			const newNode = { text, href, children: [] };
			
			// 找到父节点
			while (stack.length > 1 && level <= stack[stack.length - 1].level) {
				stack.pop();
			}
			
			// 添加到父节点的children
			stack[stack.length - 1].node.children.push(newNode);
			
			// 压入堆栈
			stack.push({ level, node: newNode });
		});
		
		return root.children;
	}

	// 生成菜单HTML
	function createMenu(menuItems) {
		let html = '<ul>';
		menuItems.forEach(item => {
			html += '<li' + (item.children.length > 0 ? ' class="has-children"' : '') + '>';
			html += '<div><a href="' + (item.href || '#') + '">' + item.text + '</a>';
			if (item.children && item.children.length > 0) {
				html += '<span></span></div>';
				html += createMenu(item.children);
			}
			html += '</li>';
		});
		html += '</ul>';
		return html;
	}

	// 解析并渲染菜单
	const parsedMenuData = parseMenuData(menuData);
	document.querySelector('.menu-container').innerHTML = createMenu(parsedMenuData);

	// 添加点击事件处理
	document.querySelectorAll('.nav li').forEach(li => {
		const a = li.querySelector('a');
		a.addEventListener('click', function(e) {
			// 只有有子菜单的项才阻止默认行为
			if (li.classList.contains('has-children')) {
				e.preventDefault(); // 阻止链接跳转
				
				// 如果点击的是当前已激活的菜单，则关闭它
				if (li.classList.contains('active')) {
					li.classList.remove('active');
				} else {
					// 先关闭所有同级菜单
					const siblings = Array.from(li.parentNode.children).filter(child => child !== li);
					siblings.forEach(sibling => {
						sibling.classList.remove('active');
					});
					
					// 打开当前菜单
					li.classList.add('active');
				}
			}
			// 如果没有子菜单，让链接正常跳转
		});
	});
	
	// 点击文档其他区域关闭所有菜单
	document.addEventListener('click', function(e) {
		if (!e.target.closest('.nav li')) {
			document.querySelectorAll('.nav li.active').forEach(li => {
				li.classList.remove('active');
			});
		}
	});
</script>