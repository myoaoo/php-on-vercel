document.addEventListener('DOMContentLoaded', function() {
	const bannerContainers = document.querySelectorAll('.banner-container');
	
	bannerContainers.forEach(container => {
		const bannerTag = container.innerHTML.trim();
		
		if (bannerTag.startsWith('{% banner %}') && bannerTag.endsWith('{% endbanner %}')) {
			const content = bannerTag
				.replace('{% banner %}', '')
				.replace('{% endbanner %}', '')
				.trim();
			
			const carouselData = parseBannerContent(content);
			
			const carouselContainer = document.createElement('div');
			carouselContainer.className = 'carousel-container';
			
			carouselContainer.innerHTML = `
				<div class="carousel-track"></div>
				<button class="carousel-btn prev">&lt;</button>
				<button class="carousel-btn next">&gt;</button>
				<div class="carousel-indicators"></div>
			`;
			
			container.innerHTML = '';
			container.appendChild(carouselContainer);
			
			const carouselTrack = carouselContainer.querySelector('.carousel-track');
			const prevBtn = carouselContainer.querySelector('.carousel-btn.prev');
			const nextBtn = carouselContainer.querySelector('.carousel-btn.next');
			const indicatorsContainer = carouselContainer.querySelector('.carousel-indicators');
			
			const firstSlide = carouselData[0];
			const lastSlide = carouselData[carouselData.length - 1];
			const extendedData = [lastSlide, ...carouselData, firstSlide];
			
			let currentIndex = 1;
			let isPlaying = true;
			let autoPlayInterval;
			const slideInterval = 3000;
			let isVideoPlaying = false;

			function initCarousel() {
				carouselTrack.innerHTML = '';
				indicatorsContainer.innerHTML = '';
				
				extendedData.forEach((slide, index) => {
					const slideElement = document.createElement('div');
					slideElement.className = 'carousel-slide';
					
					if (slide.type === 'image') {
						const img = document.createElement('img');
						img.src = slide.src;
						img.alt = slide.alt || '';
						slideElement.appendChild(img);
					} else if (slide.type === 'video') {
						const video = document.createElement('video');
						video.src = slide.src;
						video.controls = true;
						video.muted = true;
						video.loop = false;
						video.playsinline = true;
						slideElement.appendChild(video);
						
						video.addEventListener('play', () => {
							isVideoPlaying = true;
							clearInterval(autoPlayInterval);
						});
						
						video.addEventListener('pause', () => {
							isVideoPlaying = false;
							if (isPlaying) {
								startAutoPlay();
							}
						});
						
						video.addEventListener('ended', () => {
							isVideoPlaying = false;
							if (isPlaying) {
								startAutoPlay();
							}
						});
						
						const controls = document.createElement('div');
						controls.className = 'video-controls';
						
						const playBtn = document.createElement('button');
						playBtn.className = 'video-btn';
						playBtn.textContent = '播放视频';
						playBtn.addEventListener('click', () => {
							video.play();
							controls.style.display = 'none';
						});
						
						controls.appendChild(playBtn);
						slideElement.appendChild(controls);
					}
					
					carouselTrack.appendChild(slideElement);
				});
				
				carouselData.forEach((slide, index) => {
					const indicator = document.createElement('div');
					indicator.className = 'indicator';
					if (index === 0) indicator.classList.add('active');
					indicator.addEventListener('click', () => goToSlide(index + 1));
					indicatorsContainer.appendChild(indicator);
				});
				
				updateCarouselPosition(true);
				startAutoPlay();
			}
			
			function updateCarouselPosition(instant = false) {
				if (instant) {
					carouselTrack.style.transition = 'none';
				} else {
					carouselTrack.style.transition = 'transform 0.5s ease';
				}
				
				carouselTrack.style.transform = `translateX(-${currentIndex * 100}%)`;
				
				if (currentIndex === 0) {
					setTimeout(() => {
						currentIndex = carouselData.length;
						carouselTrack.style.transition = 'none';
						carouselTrack.style.transform = `translateX(-${currentIndex * 100}%)`;
					}, 500);
				} else if (currentIndex === extendedData.length - 1) {
					setTimeout(() => {
						currentIndex = 1;
						carouselTrack.style.transition = 'none';
						carouselTrack.style.transform = `translateX(-${currentIndex * 100}%)`;
					}, 500);
				}
				
				updateIndicators();
				handleVideos();
			}
			
			function updateIndicators() {
				const indicators = document.querySelectorAll('.indicator');
				let realIndex;
				
				if (currentIndex === 0) {
					realIndex = carouselData.length - 1;
				} else if (currentIndex === extendedData.length - 1) {
					realIndex = 0;
				} else {
					realIndex = currentIndex - 1;
				}
				
				indicators.forEach((indicator, index) => {
					if (index === realIndex) {
						indicator.classList.add('active');
					} else {
						indicator.classList.remove('active');
					}
				});
			}
			
			function handleVideos() {
				const slides = document.querySelectorAll('.carousel-slide');
				
				slides.forEach((slide, index) => {
					const video = slide.querySelector('video');
					const controls = slide.querySelector('.video-controls');
					
					if (video) {
						if (index === currentIndex) {
							controls.style.display = 'flex';
							video.pause();
						} else {
							video.pause();
							video.currentTime = 0;
							controls.style.display = 'none';
						}
					}
				});
			}
			
			function nextSlide() {
				currentIndex++;
				updateCarouselPosition();
				resetAutoPlay();
			}
			
			function prevSlide() {
				currentIndex--;
				updateCarouselPosition();
				resetAutoPlay();
			}
			
			function goToSlide(index) {
				currentIndex = index;
				updateCarouselPosition();
				resetAutoPlay();
			}
			
			function startAutoPlay() {
				if (isPlaying && !isVideoPlaying) {
					autoPlayInterval = setInterval(nextSlide, slideInterval);
				}
			}
			
			function resetAutoPlay() {
				clearInterval(autoPlayInterval);
				startAutoPlay();
			}
			
			prevBtn.addEventListener('click', () => {
				prevSlide();
			});
			
			nextBtn.addEventListener('click', () => {
				nextSlide();
			});
			
			carouselTrack.addEventListener('mouseenter', () => {
				clearInterval(autoPlayInterval);
			});
			
			carouselTrack.addEventListener('mouseleave', () => {
				if (isPlaying && !isVideoPlaying) {
					startAutoPlay();
				}
			});
			
			let touchStartX = 0;
			let touchEndX = 0;
			
			carouselTrack.addEventListener('touchstart', (e) => {
				touchStartX = e.changedTouches[0].screenX;
				clearInterval(autoPlayInterval);
			}, {passive: true});
			
			carouselTrack.addEventListener('touchend', (e) => {
				touchEndX = e.changedTouches[0].screenX;
				handleSwipe();
				if (isPlaying && !isVideoPlaying) {
					startAutoPlay();
				}
			}, {passive: true});
			
			function handleSwipe() {
				const threshold = 50;
				if (touchEndX < touchStartX - threshold) {
					nextSlide();
				} else if (touchEndX > touchStartX + threshold) {
					prevSlide();
				}
			}
			
			function parseBannerContent(content) {
				const lines = content
					.split('\n')
					.map(line => line.trim())
					.filter(line => line.length > 0);
				
				return lines.map(line => {
					const parts = line.split(',');
					const src = parts[0].trim();
					
					const isVideo = src.toLowerCase().endsWith('.mp4') || 
								   src.toLowerCase().endsWith('.webm') || 
								   src.toLowerCase().endsWith('.ogg');
								   
					if (isVideo) {
						return {
							type: 'video',
							src: src
						};
					} else {
						const alt = parts.length > 1 ? parts[1].trim() : '';
						return {
							type: 'image',
							src: src,
							alt: alt
						};
					}
				});
			}
			
			initCarousel();
		}
	});
});

// 林氏历史名人数据
const linHistoricalFigures = `
林放,春秋,公元前552-480,孔子弟子，鲁国人，以知礼著称，《论语》中有记载,/public/images/linfang.webp,春秋时期著名贤人,1
林禄,东晋,274-357,晋安郡王，入闽始祖，被尊为"闽林始祖",/public/images/linlv.jpg,东晋开闽第一人,2
林披,唐朝,733-802,九牧林氏始祖，生九子皆官刺史，世称"九牧林家",/public/images/linpi.jpg,唐代名门望族开创者
林藻,唐朝,765—840,林披之子，进士及第，官至岭南节度副使,/public/images/linzao.jpg,唐代著名文学家
林慎思,唐末,844-881,福建历史上第一位思想家，著有《续孟子》《伸蒙子》,/public/images/linshengsi.jpg,唐末著名思想家,
林默,北宋,960-987,妈祖原名，海上保护神，被历代朝廷褒封,/public/images/linmoniang.jpg,宋代海上女神,3
林则徐,清朝,1785-1850,民族英雄，虎门销烟主持者，近代开眼看世界第一人,/public/images/linzexu.jpg,清代著名政治家,4
`;

// 解析数据
function parseFigureData(dataString) {
	return dataString.trim().split('\n').map(line => {
		const parts = line.split(',');
		return {
			name: parts[0],
			dynasty: parts[1],
			years: parts[2] || "生卒不详",
			description: parts[3],
			avatar: parts[4] || "/public/images/cover.jpg",
			brief: parts[5] || "",
			hasSerial: !!parts[6]
		};
	});
}

// 生成人物卡片
function createProfileCard(person) {
	const avatarImg = person.avatar 
		? `<img src="${person.avatar}" alt="${person.name}" class="avatar">`
		: `<div class="avatar no-image">暂无画像</div>`;
	
	return `
		<div class="profile">
			<span class="dynasty-tag">${person.dynasty}</span>
			${avatarImg}
			<div class="name">${person.name}</div>
			<div class="description">${person.description}</div>
			<div class="years">${person.years}</div>
		</div>
	`;
}

// 渲染函数
function renderProfiles(containerId, filterSerial = false) {
	const container = document.getElementById(containerId);
	if (!container) return;
	
	const figures = parseFigureData(linHistoricalFigures);
	container.innerHTML = '';
	
	const filteredFigures = filterSerial 
		? figures.filter(person => person.hasSerial)
		: figures;
	
	filteredFigures.forEach(person => {
		container.innerHTML += createProfileCard(person);
	});
}

// 自动检测并渲染
document.addEventListener('DOMContentLoaded', () => {
	// 如果存在profiles-container-only容器，则只渲染有序号的人物
	if (document.getElementById('profiles-container-only')) {
		renderProfiles('profiles-container-only', true);
	} 
	// 否则渲染profiles-container容器（全部人物）
	else if (document.getElementById('profiles-container')) {
		renderProfiles('profiles-container');
	}
});