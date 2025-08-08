document.addEventListener('DOMContentLoaded', function () {
    const previewSets = document.querySelectorAll('[data-image-preview-set]');

    previewSets.forEach(set => {
        const input = set.querySelector('.image-input');
        const previewBox = set.querySelector('.carousel-preview-box');
        const dotsContainer = set.querySelector('.carousel-dots');

        let images = [];
        let currentIndex = 0;

        input.addEventListener('change', function (event) {
            const files = Array.from(event.target.files);
            if (files.length > 6) {
                alert("画像は最大6枚までです。");
                return;
            }

            images = [];
            previewBox.innerHTML = '';
            dotsContainer.innerHTML = '';
            currentIndex = 0;

            files.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    images.push(e.target.result);
                    if (index === 0) {
                        showImage(0);
                    }
                    renderDots();
                };
                reader.readAsDataURL(file);
            });
        });

        function showImage(index) {
            previewBox.innerHTML = `<img src="${images[index]}" class="image-preview">`;
            currentIndex = index;
            updateDots();
        }

        function renderDots() {
            dotsContainer.innerHTML = '';
            images.forEach((_, index) => {
                const dot = document.createElement('span');
                dot.classList.add('dot');
                if (index === currentIndex) dot.classList.add('active');
                dot.addEventListener('click', () => showImage(index));
                dotsContainer.appendChild(dot);
            });
        }

        function updateDots() {
            const dots = dotsContainer.querySelectorAll('.dot');
            dots.forEach((dot, idx) => {
                dot.classList.toggle('active', idx === currentIndex);
            });
        }
    });

    // ▼ 現在の画像エリアの制御 ▼

    const currentPreviewBox = document.getElementById('current-carousel-preview-box');
    const currentPreviewImg = document.getElementById('current-carousel-preview-img');
    const currentDotsContainer = document.getElementById('current-carousel-dots');
    const currentImages = JSON.parse(currentPreviewBox.dataset.images || '[]');
    let currentIndex = 0;

    if (currentImages.length > 1) {
        renderCurrentDots();
        showCurrentImage(0);
    }

    function showCurrentImage(index) {
        currentPreviewImg.src = currentImages[index];
        currentIndex = index;
        updateCurrentDots();
    }

    function renderCurrentDots() {
        currentDotsContainer.innerHTML = '';
        currentImages.forEach((_, index) => {
            const dot = document.createElement('span');
            dot.classList.add('dot');
            if (index === currentIndex) dot.classList.add('active');
            dot.addEventListener('click', () => showCurrentImage(index));
            currentDotsContainer.appendChild(dot);
        });
    }

    function updateCurrentDots() {
        const dots = currentDotsContainer.querySelectorAll('.dot');
        dots.forEach((dot, idx) => {
            dot.classList.toggle('active', idx === currentIndex);
        });
    }
});
