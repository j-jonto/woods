// تحسينات تفاعلية للدليل
document.addEventListener('DOMContentLoaded', function() {
    
    // إضافة زر الطباعة
    const printButton = document.createElement('button');
    printButton.className = 'print-button';
    printButton.innerHTML = '🖨️ طباعة الدليل';
    printButton.onclick = function() {
        window.print();
    };
    document.body.appendChild(printButton);

    // إضافة جدول المحتويات
    const toc = createTableOfContents();
    const firstSection = document.querySelector('.section');
    if (firstSection && toc) {
        firstSection.parentNode.insertBefore(toc, firstSection);
    }

    // تحسين التفاعل مع البطاقات
    enhanceCards();

    // تحسين التفاعل مع القوائم
    enhanceLists();

    // إضافة مؤشرات التقدم
    addProgressIndicators();

    // إضافة البحث
    addSearchFunctionality();

    // إضافة التنقل السلس
    addSmoothNavigation();
});

function createTableOfContents() {
    const sections = document.querySelectorAll('.section h2');
    if (sections.length === 0) return null;

    const toc = document.createElement('div');
    toc.className = 'section toc';
    toc.innerHTML = '<h3>📑 جدول المحتويات</h3><ul></ul>';

    const tocList = toc.querySelector('ul');
    
    sections.forEach((section, index) => {
        const li = document.createElement('li');
        const a = document.createElement('a');
        a.href = `#section-${index}`;
        a.textContent = section.textContent;
        a.onclick = function(e) {
            e.preventDefault();
            section.scrollIntoView({ behavior: 'smooth' });
        };
        li.appendChild(a);
        tocList.appendChild(li);
        
        // إضافة معرف للقسم
        section.parentNode.id = `section-${index}`;
    });

    return toc;
}

function enhanceCards() {
    const cards = document.querySelectorAll('.feature-card, .scenario');
    
    cards.forEach(card => {
        // تأثير التحويم
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
            this.style.boxShadow = '0 15px 35px rgba(0,0,0,0.3)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.2)';
        });

        // تأثير النقر
        card.addEventListener('click', function() {
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    });
}

function enhanceLists() {
    const listItems = document.querySelectorAll('.step-list li');
    
    listItems.forEach((item, index) => {
        // إضافة تأثير النقر
        item.addEventListener('click', function() {
            this.style.background = '#e3f2fd';
            this.style.transform = 'translateX(10px)';
            
            setTimeout(() => {
                this.style.background = '#f8f9fa';
                this.style.transform = 'translateX(0)';
            }, 500);
        });

        // إضافة تأثير التحميل التدريجي
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            item.style.transition = 'all 0.5s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

function addProgressIndicators() {
    const sections = document.querySelectorAll('.section');
    
    sections.forEach((section, index) => {
        const progress = document.createElement('div');
        progress.className = 'progress-indicator';
        progress.innerHTML = `
            <h4>التقدم في القسم ${index + 1}</h4>
            <div class="progress-bar" style="width: 0%"></div>
        `;
        
        section.appendChild(progress);
        
        // تحديث شريط التقدم عند التمرير
        const progressBar = progress.querySelector('.progress-bar');
        const updateProgress = () => {
            const rect = section.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            const sectionHeight = section.offsetHeight;
            
            if (rect.top < windowHeight && rect.bottom > 0) {
                const visibleHeight = Math.min(rect.bottom, windowHeight) - Math.max(rect.top, 0);
                const progress = (visibleHeight / sectionHeight) * 100;
                progressBar.style.width = `${Math.max(0, Math.min(100, progress))}%`;
            }
        };
        
        window.addEventListener('scroll', updateProgress);
        updateProgress();
    });
}

function addSearchFunctionality() {
    const searchBox = document.createElement('div');
    searchBox.className = 'section';
    searchBox.innerHTML = `
        <h2>🔍 البحث في الدليل</h2>
        <input type="text" id="searchInput" placeholder="اكتب للبحث..." style="width: 100%; padding: 10px; border: 2px solid #3498db; border-radius: 5px; font-size: 16px;">
        <div id="searchResults" style="margin-top: 15px;"></div>
    `;
    
    const firstSection = document.querySelector('.section');
    if (firstSection) {
        firstSection.parentNode.insertBefore(searchBox, firstSection);
    }
    
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        if (query.length < 2) {
            searchResults.innerHTML = '';
            return;
        }
        
        const sections = document.querySelectorAll('.section');
        const results = [];
        
        sections.forEach(section => {
            const text = section.textContent.toLowerCase();
            if (text.includes(query)) {
                const title = section.querySelector('h2, h3, h4');
                if (title) {
                    results.push({
                        title: title.textContent,
                        section: section,
                        relevance: text.split(query).length - 1
                    });
                }
            }
        });
        
        // ترتيب النتائج حسب الأهمية
        results.sort((a, b) => b.relevance - a.relevance);
        
        // عرض النتائج
        if (results.length > 0) {
            searchResults.innerHTML = `
                <h4>نتائج البحث (${results.length}):</h4>
                <ul style="list-style: none; padding: 0;">
                    ${results.slice(0, 5).map(result => `
                        <li style="padding: 8px; margin: 5px 0; background: #f8f9fa; border-radius: 5px; cursor: pointer;" 
                            onclick="document.querySelector('#${result.section.id}').scrollIntoView({behavior: 'smooth'})">
                            ${result.title}
                        </li>
                    `).join('')}
                </ul>
            `;
        } else {
            searchResults.innerHTML = '<p>لا توجد نتائج</p>';
        }
    });
}

function addSmoothNavigation() {
    // إضافة أزرار التنقل
    const sections = document.querySelectorAll('.section');
    
    sections.forEach((section, index) => {
        const navButtons = document.createElement('div');
        navButtons.style.cssText = 'text-align: center; margin: 20px 0;';
        
        if (index > 0) {
            const prevButton = document.createElement('button');
            prevButton.textContent = '← السابق';
            prevButton.style.cssText = 'margin: 0 10px; padding: 8px 15px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer;';
            prevButton.onclick = () => sections[index - 1].scrollIntoView({ behavior: 'smooth' });
            navButtons.appendChild(prevButton);
        }
        
        if (index < sections.length - 1) {
            const nextButton = document.createElement('button');
            nextButton.textContent = 'التالي →';
            nextButton.style.cssText = 'margin: 0 10px; padding: 8px 15px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer;';
            nextButton.onclick = () => sections[index + 1].scrollIntoView({ behavior: 'smooth' });
            navButtons.appendChild(nextButton);
        }
        
        section.appendChild(navButtons);
    });
}

// إضافة تأثيرات إضافية
function addExtraEffects() {
    // تأثير الكتابة للعناوين
    const titles = document.querySelectorAll('h1, h2');
    titles.forEach(title => {
        const text = title.textContent;
        title.textContent = '';
        
        let i = 0;
        const typeWriter = () => {
            if (i < text.length) {
                title.textContent += text.charAt(i);
                i++;
                setTimeout(typeWriter, 50);
            }
        };
        
        // بدء الكتابة عند ظهور العنوان
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    typeWriter();
                    observer.unobserve(entry.target);
                }
            });
        });
        
        observer.observe(title);
    });
}

// تشغيل التأثيرات الإضافية
setTimeout(addExtraEffects, 1000); 